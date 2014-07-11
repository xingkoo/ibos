<?php

class WorkflowTypeController extends WfsetupBaseController
{
    public function init()
    {
        parent::init();
        $this->catid = intval(EnvUtil::getRequest("catid"));
        $this->category = FlowCategory::model()->fetchAllByUserPurv($this->uid);
        $this->flowid = intval(EnvUtil::getRequest("flowid"));
    }

    public function actionIndex()
    {
        $keyword = EnvUtil::getRequest("keyword");

        if (!empty($keyword)) {
            $keyword = StringUtil::filterCleanHtml($keyword);
        } else {
            $keyword = "";
        }

        if (isset($_GET["pagesize"])) {
            $this->setListPageSize($_GET["pagesize"]);
        }

        $catId = $this->getCatId();
        $condition = ($catId ? "ft.catid = " . intval($catId) : "1");

        if (!empty($keyword)) {
            $condition .= " AND ft.name LIKE '%$keyword%'";
        }

        $count = FlowType::model()->countByList($condition);
        $pages = PageUtil::create($count, $this->getListPageSize());
        $list = FlowType::model()->fetchAllByList($this->uid, $condition, $pages->getOffset(), $pages->getLimit());
        $data = array("list" => $list, "pages" => $pages, "category" => $this->category, "catId" => $this->catid, "pageSize" => $this->getListPageSize());
        $this->setPageTitle(Ibos::lang("Workflow manager"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Workflow")),
            array("name" => Ibos::lang("Workflow manager"), "url" => $this->createUrl("type/index")),
            array("name" => Ibos::lang("List"))
        ));
        $this->render("index", $data);
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("typeSubmit")) {
            $this->beforeSave();
            $newId = FlowType::model()->add($_POST, true);
            FlowProcess::model()->addSpecialNode($newId);
            $catId = intval($_POST["catid"]);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("type/index", array("catid" => $catId, "flowid" => $newId)));
        } else {
            $data = array("formList" => FlowFormType::model()->fetchAllOnOptListByUid($this->uid, $this->category), "category" => $this->category, "catId" => $this->catid);
            $this->setPageTitle(Ibos::lang("Workflow manager"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Workflow")),
                array("name" => Ibos::lang("Workflow manager"), "url" => $this->createUrl("type/index")),
                array("name" => Ibos::lang("New flow"))
            ));
            $this->render("add", $data);
        }
    }

    public function actionEdit()
    {
        if (!$this->flowid) {
            $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("type/index"));
        }

        if (EnvUtil::submitCheck("typeSubmit")) {
            $this->beforeSave();
            $data = FlowType::model()->create();
            FlowType::model()->modify($this->flowid, $data);
            $catId = intval($_POST["catid"]);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("type/index", array("catid" => $catId, "flowid" => $this->flowid)));
        } else {
            $flow = FlowType::model()->fetchByPk($this->flowid);

            if (empty($flow)) {
                $this->error(Ibos::lang("Parameters error", "error"), $this->createUrl("type/index"));
            }

            if (!WfCommonUtil::checkDeptPurv($this->uid, $flow["deptid"], $flow["catid"])) {
                $this->error(Ibos::lang("Permission denied"), $this->createUrl("list/index"));
            }

            if (!empty($flow["deptid"])) {
                $flow["deptid"] = StringUtil::wrapId($flow["deptid"], "d");
            } else {
                $flow["deptid"] = "";
            }

            $readonly = FlowRun::model()->countAllByFlowId($this->flowid);
            $formName = FlowFormType::model()->fetchFormNameByFormId($flow["formid"]);
            $formList = FlowFormType::model()->fetchAllOnOptListByUid($this->uid, $this->category);
            $data = array("flow" => $flow, "readonly" => !!$readonly, "formName" => $formName, "formList" => $formList, "category" => $this->category, "catId" => $this->catid);
            $this->setPageTitle(Ibos::lang("Workflow manager"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Workflow")),
                array("name" => Ibos::lang("Workflow manager"), "url" => $this->createUrl("type/index")),
                array("name" => Ibos::lang("Edit flow"))
            ));
            $this->render("edit", $data);
        }
    }

    public function actionDel()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $ids = EnvUtil::getRequest("flowids");
            $id = StringUtil::filterStr($ids);

            if (EnvUtil::getRequest("op") == "clear") {
                $status = FlowType::model()->clearFlow($id);
            } else {
                $status = FlowType::model()->delFlow($id);
            }

            $this->ajaxReturn(array("isSuccess" => $status));
        }
    }

    public function actionImport()
    {
        if ($this->flowid) {
            if (EnvUtil::submitCheck("typeSubmit")) {
                $userOn = (!isset($_POST["useron"]) ? false : true);
                $fileName = $_FILES["import"]["name"];

                if (!stristr($fileName, ".xml")) {
                    $this->ajaxReturn("<script type='text/javascript'>parent.Ui.alert('" . Ibos::lang("Import xml files only") . "');</script>", "eval");
                } else {
                    $upload = FileUtil::getUpload($_FILES["import"]);
                    $upload->save();
                    $files = $upload->getAttach();
                    $file = $files["target"];
                    WfTypeUtil::import($this->flowid, $file, $userOn);
                    $this->ajaxReturn("<script type='text/javascript'>parent.Ui.tip('" . Ibos::lang("Import success") . "', 'success');parent.Ui.getDialog('import_frame').close();</script>", "eval");
                }
            }

            $lang = Ibos::getLangSources();
            $this->renderPartial("import", array("lang" => $lang, "flowId" => $this->flowid));
        } else {
            $this->ajaxReturn("<script type='text/javascript'>parent.Ui.alert('" . Ibos::lang("Parameters error", "error") . "');parent.Ui.getDialog('import_frame').close();</script>", "eval");
        }
    }

    public function actionExport()
    {
        if ($this->flowid) {
            $flow = FlowType::model()->fetchByPk($this->flowid);
            $checkPurv = WfCommonUtil::checkDeptPurv($this->uid, $flow["deptid"]);

            if ($checkPurv) {
                WfTypeUtil::export($this->flowid);
            }
        }

        exit("Access Denied");
    }

    public function actionGetGuide()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $id = EnvUtil::getRequest("id");
            $guideInfo = FlowType::model()->getGuideInfo(intval($id));
            $this->ajaxReturn($guideInfo);
        }
    }

    public function actionTrans()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = &$_POST;
            $conArr = array("begin" => !empty($data["begin"]) ? strtotime($data["begin"]) : "", "end" => !empty($data["end"]) ? strtotime($data["end"]) : "", "runbegin" => !empty($data["runbegin"]) ? intval($data["runbegin"]) : "", "runend" => !empty($data["runend"]) ? intval($data["runend"]) : "");
            $flowStr = StringUtil::filterStr($data["flowid"]);
            $uid = implode(",", StringUtil::getId($data["uid"]));
            $toid = implode(",", StringUtil::getId($data["toid"]));
            $fitRunIds = FlowRun::model()->fetchAllRunIdByFlowIdFeatCondition($flowStr, $conArr);

            if (!empty($fitRunIds)) {
                FlowRunProcess::model()->updateTransRun($uid, $toid, $fitRunIds);
            }

            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $data = array("flows" => FlowType::model()->fetchAllOnOptlist($this->uid), "lang" => Ibos::getLangSources());
            $this->renderPartial("trans", $data);
        }
    }

    public function actionFreeNew()
    {
        if ($this->flowid) {
            $flow = new ICFlowType($this->flowid);

            if (EnvUtil::submitCheck("formhash")) {
                $users = EnvUtil::getRequest("newuser");
                FlowType::model()->modify($this->flowid, array("newuser" => $users));
                $this->setGuideProcess($flow, 3);
                $this->ajaxReturn(array("isSuccess" => true));
            } elseif ($flow->isFree()) {
                $users = $flow->newuser;
                $this->renderPartial("freeNew", array("users" => $users));
            } else {
                exit(Ibos::lang("Parameters error", "error"));
            }
        }
    }

    public function actionVerify()
    {
        $flowID = intval(EnvUtil::getRequest("flowid"));

        if ($flowID) {
            $result = FlowType::model()->examFlow($flowID, true);
            $data = array("result" => $result, "lang" => Ibos::getLangSources());
            $this->renderPartial("verify", $data);
        }
    }

    protected function beforeSave()
    {
        $_POST["catid"] = intval($_POST["catid"]);
        $_POST["sort"] = intval($_POST["sort"]);
        $_POST["autonum"] = intval($_POST["autonum"]);
        $_POST["autolen"] = intval($_POST["autolen"]);
        $_POST["deptid"] = (!empty($_POST["deptid"]) ? StringUtil::getId($_POST["deptid"]) : "");

        if (is_array($_POST["deptid"])) {
            $_POST["deptid"] = implode(",", $_POST["deptid"]);
        }

        if (!isset($_POST["allowattachment"])) {
            $_POST["allowattachment"] = 0;
        }

        if (!isset($_POST["allowversion"])) {
            $_POST["allowversion"] = 0;
        }

        if (!isset($_POST["forcepreset"])) {
            $_POST["forcepreset"] = 0;
        }

        if (empty($_POST["formid"])) {
            if (empty($_POST["formname"])) {
                $this->error(Ibos::lang("Form name invalid"));
            } else {
                $_POST["formid"] = FlowFormType::model()->quickAdd($_POST["formname"], $_POST["catid"]);
            }
        }
    }
}
