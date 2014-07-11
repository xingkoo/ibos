<?php

class WorkflowFormtypeController extends WfsetupBaseController
{
    public function actionIndex()
    {
        $this->catid = intval(EnvUtil::getRequest("catid"));
        $this->category = FlowCategory::model()->fetchAllByUserPurv($this->uid);
        $catId = $this->getCatId();
        $keyword = EnvUtil::getRequest("keyword");

        if (!empty($keyword)) {
            $keyword = StringUtil::filterCleanHtml($keyword);
        } else {
            $keyword = "";
        }

        if (EnvUtil::getRequest("inajax") == "1") {
            $limit = intval(EnvUtil::getRequest("limit"));
            $offset = intval(EnvUtil::getRequest("offset"));
            $condition = ($catId ? "ff.catid = " . intval($catId) : "1");

            if (!empty($keyword)) {
                $condition .= " AND ff.formname LIKE '%$keyword%'";
            }

            $list = FlowFormType::model()->fetchAllByList($condition, $offset, $limit);
            $list = $this->handleFormData($list);
            $count = count($list);
            $this->ajaxReturn(array("count" => $count, "list" => $list));
        } else {
            if (isset($_GET["pagesize"])) {
                $this->setListPageSize($_GET["pagesize"]);
            }

            $condition = ($catId ? "catid = " . intval($catId) : "1");

            if (!empty($keyword)) {
                $condition .= " AND t.formname LIKE '%$keyword%'";
            }

            $count = FlowFormType::model()->countByCondition($condition);
            $pages = PageUtil::create($count, $this->getListPageSize());
            $data = array("limit" => $pages->getLimit(), "offset" => $pages->getOffset(), "pageSize" => $this->getListPageSize(), "keyword" => $keyword, "pages" => $pages, "count" => $count, "category" => $this->category, "catId" => $this->catid);
            $this->setPageTitle(Ibos::lang("Form library manager"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Workflow")),
                array("name" => Ibos::lang("Form library manager"), "url" => $this->createUrl("formtype/index")),
                array("name" => Ibos::lang("List"))
            ));
            $this->render("index", $data);
        }
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            unset($_POST["formid"]);
            $this->beforeSave();
            $_POST["printmodel"] = $_POST["printmodelshort"] = $_POST["script"] = $_POST["css"] = "";
            $formID = FlowFormType::model()->add($_POST, true);
            $this->ajaxReturn(array("isSuccess" => true, "url" => $this->createUrl("formtype/index", array("catid" => intval($_POST["catid"]))), "formid" => $formID));
        }
    }

    public function actionEdit()
    {
        if (EnvUtil::getRequest("inajax") == 1) {
            $fid = intval(EnvUtil::getRequest("formid"));
            $form = new ICFlowForm($fid);
            $this->ajaxReturn(array("data" => $this->handleFormData(array($form->toArray()))));
        }

        if (EnvUtil::submitCheck("formhash")) {
            $formId = intval($_POST["formid"]);
            $this->beforeSave();
            $data = FlowFormType::model()->create();
            FlowFormType::model()->modify($formId, $data);
            $this->ajaxReturn(array("isSuccess" => true, "url" => $this->createUrl("formtype/index", array("catid" => intval($_POST["catid"]))), "formid" => $formId));
        }
    }

    public function actionDel()
    {
        $id = EnvUtil::getRequest("id");
        $idstr = StringUtil::filterStr(StringUtil::filterCleanHtml($id));

        if (FlowType::model()->getIsAssociated($idstr)) {
            $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Cancel the form associated with process")));
        } else {
            FlowFormType::model()->del($idstr);
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionImport()
    {
        $id = EnvUtil::getRequest("formid");
        $catId = EnvUtil::getRequest("catid");

        if (EnvUtil::submitCheck("formhash")) {
            $fileName = $_FILES["import"]["name"];
            $fileExt = StringUtil::getFileExt($fileName);

            if (!in_array($fileExt, array("txt", "htm", "html"))) {
                echo "<script type='text/javascript'>parent.Ui.alert('" . Ibos::lang("Form import desc") . "');</script>";
            } else {
                $upload = FileUtil::getUpload($_FILES["import"]);
                $upload->save();
                $files = $upload->getAttach();
                $file = $files["target"];
                $inajax = 0;

                if (empty($id)) {
                    $name = strstr($files["name"], ".", true);
                    $inajax = 1;
                    $id = FlowFormType::model()->quickAdd($name, $catId);
                }

                WfFormUtil::import($id, $file);
                $nextOpt = $_POST["nextopt"];
                $exec = "";

                if ($nextOpt == "edit") {
                    $param = sprintf("{'formid':'%d','inajax':%d}", $id, $inajax);
                    $exec = "parent.Wfs.formItem.edit($param);";
                } elseif ($nextOpt == "design") {
                    $param = sprintf("{'formid':'%d'}", $id);
                    $exec = "parent.Wfs.formItem.design($param);";
                }

                $this->ajaxReturn("<script type='text/javascript'>parent.Ui.tip('" . Ibos::lang("Import success") . "', 'success');{$exec}parent.Ui.getDialog('d_import_form').close();</script>", "eval");
            }
        }

        $lang = Ibos::getLangSources();
        $this->renderPartial("import", array("lang" => $lang, "id" => $id, "catid" => $catId));
    }

    public function actionExport()
    {
        $id = EnvUtil::getRequest("id");
        $idstr = StringUtil::filterStr(StringUtil::filterCleanHtml($id));
        WfFormUtil::export($idstr);
    }

    public function actionDesign()
    {
        if (filter_input(INPUT_GET, "op") == "showcomponet") {
            $type = filter_input(INPUT_GET, "type");
            return $this->renderPartial("ic" . $type, null, false);
        }

        $formId = intval(EnvUtil::getRequest("formid"));
        $mode = EnvUtil::getRequest("mode");

        if (!in_array($mode, array("advanced", "simple"))) {
            $mode = "advanced";
        }

        if (EnvUtil::submitCheck("formhash")) {
            $content = preg_replace("/<form.*?>(.*?)<\/form>/is", "\1", $_POST["content"]);

            if ($_POST["op"] == "version") {
                $verMax = FlowFormVersion::model()->getMaxMark($formId);
                $mark = ($verMax ? $verMax + 1 : 1);
                $oldForm = FlowFormType::model()->fetchByPk($formId);
                $data = array("formid" => $formId, "printmodel" => $oldForm["printmodel"], "printmodel_short" => $oldForm["printmodelshort"], "script" => $oldForm["script"], "css" => $oldForm["css"], "time" => TIMESTAMP, "mark" => $mark);
                FlowFormVersion::model()->add($data);
                MainUtil::setCookie("form_op_version", 1, 30, 0);
            } else {
                $this->setAllGuideInfo($formId);
                MainUtil::setCookie("form_op_save", 1, 30, 0);
            }

            FlowFormType::model()->modify($formId, array("printmodel" => $content));
            CacheUtil::rm("form_" . $formId);
            $form = new ICFlowForm($formId);
            $form->parser->parse();
            $url = $this->createUrl("formtype/design", array("mode" => $mode, "formid" => $formId));
            $this->redirect($url);
            exit();
        }

        $form = new ICFlowForm($formId);
        $arr = $form->toArray();
        $data = array("form" => $arr, "mode" => $mode, "formid" => $formId);
        $this->layout = false;
        $this->render("designer" . ucfirst($mode), $data, false, array("workflow.item"));
    }

    public function actionPreview()
    {
        $formId = intval(EnvUtil::getRequest("formid"));
        $ver = intval(EnvUtil::getRequest("verid"));

        if (!$ver) {
            $arr = FlowFormType::model()->fetchByPk($formId);
        } else {
            $arr = FlowFormVersion::model()->fetchByPk($ver);
        }

        if ($arr) {
            $form = new ICFlowForm($arr["formid"]);
            $printmodel = $form->printmodelshort;
            $hidden = $read = array();
            $viewer = new ICFlowFormViewer(array("form" => $form));
            $viewer->handleForm($printmodel, $hidden, $read, true);
            $data = array("formname" => $form->formname, "printmodel" => $printmodel, "script" => $form->script, "css" => $form->css);
            $this->layout = false;
            $this->render("preview", $data);
        }
    }

    protected function setAllGuideInfo($formID)
    {
        foreach (FlowType::model()->fetchAllFlowIDByFormID($formID) as $flow) {
            $this->setGuideProcess(new ICFlowType(intval($flow["flowid"]), false), 2);
        }
    }

    protected function beforeSave()
    {
        if (!empty($_POST["deptid"])) {
            $deptId = StringUtil::getId($_POST["deptid"]);
            $_POST["deptid"] = implode(",", $deptId);
        } else {
            $_POST["deptid"] = 0;
        }
    }

    protected function handleFormData($list)
    {
        $return = array();

        if (!empty($list)) {
            $depts = DepartmentUtil::loadDepartment();
            $undefined = Ibos::lang("Undefined");
            $sysdept = Ibos::lang("Form sys dept");

            foreach ($list as $form) {
                if (!empty($form["formid"]) && WfCommonUtil::checkDeptPurv($this->uid, $form["deptid"])) {
                    $form["id"] = $form["formid"];
                    $form["name"] = $form["formname"];
                    $form["catelog"] = $form["catid"];
                    if (!isset($form["flow"]) || empty($form["flow"])) {
                        $form["flow"] = $undefined;
                    }

                    $form["department"] = (isset($depts[$form["deptid"]]) ? $depts[$form["deptid"]]["deptname"] : $sysdept);
                    $form["departmentId"] = (!empty($form["deptid"]) ? StringUtil::wrapId($form["deptid"], "d") : "");
                    $return[] = $form;
                }
            }
        }

        return $return;
    }
}
