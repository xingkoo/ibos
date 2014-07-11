<?php

class OrganizationPositionController extends OrganizationBaseController
{
    /**
     *
     * @var string 下拉列表中的<option>格式字符串 
     */
    public $selectFormat = "<option value='\$catid' \$selected>\$spacer\$name</option>";

    public function actionIndex()
    {
        $catId = intval(EnvUtil::getRequest("catid"));

        if (EnvUtil::submitCheck("search")) {
            $key = $_POST["keyword"];
            $list = Position::model()->fetchAll("`posname` LIKE '%$key%'");
        } else {
            $catContidion = (empty($catId) ? "" : "catid = $catId");
            $count = Position::model()->count($catContidion);
            $pages = PageUtil::create($count);
            $list = Position::model()->fetchAllByCatId($catId, $pages->getLimit(), $pages->getOffset());
            $data["pages"] = $pages;
        }

        foreach ($list as $k => $pos) {
            $list[$k]["num"] = User::model()->count("positionid = :positionid AND status != 2", array(":positionid" => $pos["positionid"]));
        }

        $data["catid"] = $catId;
        $catData = PositionUtil::loadPositionCategory();
        $data["catData"] = $catData;
        $data["list"] = $list;
        $data["category"] = StringUtil::getTree($catData, $this->selectFormat);
        $this->setPageTitle(Ibos::lang("Position manager"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
            array("name" => Ibos::lang("Position manager"))
        ));
        $this->render("index", $data, false, array("category"));
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("posSubmit")) {
            $data = Position::model()->create();
            $newId = Position::model()->add($data, true);

            if (isset($_POST["responsibility"])) {
                $this->addResponsibility($newId, $_POST["responsibility"], $_POST["criteria"]);
            }

            if (isset($_POST["nodes"])) {
                $this->updateAuthItem($newId, $_POST["nodes"], $_POST["data-privilege"]);
            }

            if (isset($_POST["member"])) {
                UserUtil::setPosition($newId, $_POST["member"]);
            }

            $newId && OrgUtil::update();
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("position/index"));
        } else {
            $catid = intval(EnvUtil::getRequest("catid"));
            $catData = PositionUtil::loadPositionCategory();
            $data["category"] = StringUtil::getTree($catData, $this->selectFormat, $catid);
            $authItem = AuthUtil::loadAuthItem();
            $data["authItem"] = $authItem;
            $this->setPageTitle(Ibos::lang("Add position"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
                array("name" => Ibos::lang("Position manager"), "url" => $this->createUrl("position/index")),
                array("name" => Ibos::lang("Add position"))
            ));
            $this->render("add", $data);
        }
    }

    public function actionEdit()
    {
        $id = EnvUtil::getRequest("id");

        if (EnvUtil::submitCheck("posSubmit")) {
            if (isset($_POST["posname"])) {
                $data["posname"] = $_POST["posname"];
                $data["sort"] = $_POST["sort"];
                $data["catid"] = intval(EnvUtil::getRequest("catid"));
                $data["goal"] = $_POST["goal"];
                $data["minrequirement"] = $_POST["minrequirement"];
                Position::model()->modify($id, $data);
            }

            if (isset($_POST["responsibility"])) {
                foreach ($_POST["responsibility"] as $rId => $value) {
                    $data = array("positionid" => $id, "responsibility" => $value, "criteria" => $_POST["criteria"][$rId]);
                    PositionResponsibility::model()->modify($rId, $data);
                }
            }

            if (isset($_POST["newResponsibility"])) {
                $this->addResponsibility($id, $_POST["newResponsibility"], $_POST["newCriteria"]);
            }

            if (!empty($_POST["resDelId"])) {
                $delId = trim($_POST["resDelId"], ",");
                PositionResponsibility::model()->deleteByPk(explode(",", $delId));
            }

            if (isset($_POST["nodes"])) {
                $this->updateAuthItem($id, $_POST["nodes"], $_POST["data-privilege"]);
            }

            if (isset($_POST["member"])) {
                UserUtil::setPosition($id, $_POST["member"]);
            } else {
                OrgUtil::update();
            }

            PositionUtil::cleanPurvCache($id);
            $this->success(Ibos::lang("Save succeed", "message"), $this->createUrl("position/index"));
        } else {
            $pos = Position::model()->fetchByPk($id);
            $related = NodeRelated::model()->fetchAllByPosId($id);
            $relateCombine = PositionUtil::combineRelated($related);
            $responsibility = PositionResponsibility::model()->fetchAllByPosId($id);
            $data["id"] = $id;
            $data["pos"] = $pos;
            $catData = PositionUtil::loadPositionCategory();
            $data["category"] = StringUtil::getTree($catData, $this->selectFormat, $pos["catid"]);
            $data["related"] = $relateCombine;
            $data["responsibility"] = $responsibility;
            $authItem = AuthUtil::loadAuthItem();
            $data["authItem"] = $authItem;
            $uids = User::model()->fetchUidByPosId($id, false);

            if (!empty($uids)) {
                $data["uids"] = $uids;
                $data["uidString"] = "";

                foreach ($uids as $uid) {
                    $data["uidString"] .= "'u_" . $uid . "',";
                }

                $data["uidString"] = trim($data["uidString"], ",");
            }

            $this->setPageTitle(Ibos::lang("Edit position"));
            $this->setPageState("breadCrumbs", array(
                array("name" => Ibos::lang("Organization"), "url" => $this->createUrl("department/index")),
                array("name" => Ibos::lang("Position manager"), "url" => $this->createUrl("position/index")),
                array("name" => Ibos::lang("Edit position"))
            ));
            $this->render("edit", $data);
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->getIsAjaxRequest()) {
            $id = EnvUtil::getRequest("id");
            $ids = explode(",", trim($id, ","));

            foreach ($ids as $positionId) {
                Position::model()->deleteByPk($positionId);
                Ibos::app()->authManager->removeAuthItem($positionId);
                PositionResponsibility::model()->deleteAll("`positionid` = :positionid", array(":positionid" => $positionId));
                PositionRelated::model()->deleteAll("positionid = :positionid", array(":positionid" => $positionId));
                NodeRelated::model()->deleteAll("positionid = :positionid", array(":positionid" => $positionId));
                $relatedIds = User::model()->fetchUidByPosId($positionId);

                if (!empty($relatedIds)) {
                    User::model()->updateByUids($relatedIds, array("positionid" => 0));
                }

                PositionUtil::cleanPurvCache($positionId);
            }

            OrgUtil::update();
            $this->ajaxReturn(array("IsSuccess" => true), "json");
        }
    }

    private function addResponsibility($positionId, $responsibility, $criteria)
    {
        foreach ($responsibility as $key => $value) {
            if (empty($value) && empty($criteria[$key])) {
                continue;
            }

            $data = array("positionid" => $positionId, "responsibility" => $value, "criteria" => $criteria[$key]);
            PositionResponsibility::model()->add($data);
        }
    }

    private function updateAuthItem($positionId, $authItem = array(), $dataVal = array())
    {
        if (!empty($authItem)) {
            $nodes = Node::model()->fetchAllSortByPk("id");
            NodeRelated::model()->deleteAllByPositionId($positionId);
            $auth = Ibos::app()->authManager;
            $role = $auth->getAuthItem($positionId);

            if ($role === null) {
                $role = $auth->createRole($positionId, "", "", "");
            }

            AuthItemChild::model()->deleteByParent($positionId);

            foreach ($authItem as $key => $nodeId) {
                $node = $nodes[$key];
                if ((strcasecmp($key, $nodeId) !== 0) && ($nodeId === "data")) {
                    $vals = $dataVal[$key];

                    if (is_array($vals)) {
                        NodeRelated::model()->addRelated("", $positionId, $node);

                        foreach ($vals as $id => $val) {
                            $childNode = Node::model()->fetchByPk($id);
                            NodeRelated::model()->addRelated($val, $positionId, $childNode, $id);
                            AuthUtil::addRoleChildItem($role, $childNode, explode(",", $childNode["routes"]));
                        }
                    }
                } else {
                    NodeRelated::model()->addRelated("", $positionId, $node);
                    $routes = explode(",", $node["routes"]);
                    AuthUtil::addRoleChildItem($role, $node, $routes);
                }
            }
        }
    }
}
