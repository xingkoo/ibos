<?php

class ReportTypeController extends ReportBaseController
{
    public function actionAdd()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeData = EnvUtil::getRequest("typeData");
            $type = ICReportType::handleSaveData($typeData);

            if (empty($type["sort"])) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Sort can not be empty")));
            }

            if (empty($type["typename"])) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Typename can not be empty")));
            }

            $typeid = ReportType::model()->add($type, true);

            if ($typeid) {
                $return = ReportType::model()->fetchByPk($typeid);

                if ($return["intervaltype"] == 5) {
                    $return["intervalTypeName"] = $return["intervals"] . Ibos::lang("Day");
                } else {
                    $return["intervalTypeName"] = ICReportType::handleShowInterval($typeData["intervaltype"]);
                }

                $return["url"] = Ibos::app()->urlManager->createUrl("report/default/index", array("typeid" => $typeid));
                $return["isSuccess"] = true;
                $return["msg"] = Ibos::lang("Add succeed");
            } else {
                $return["isSuccess"] = false;
                $return["msg"] = Ibos::lang("Add failed");
            }

            $this->ajaxReturn($return);
        }
    }

    public function actionEdit()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeid = intval(EnvUtil::getRequest("typeid"));
            $typeData = EnvUtil::getRequest("typeData");
            $type = ICReportType::handleSaveData($typeData);

            if (empty($typeid)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Parameters error", "error")));
            }

            ReportType::model()->modify($typeid, $type);
            $return = ReportType::model()->fetchByPk($typeid);

            if ($return["intervaltype"] == 5) {
                $return["intervalTypeName"] = $return["intervals"] . Ibos::lang("Day");
            } else {
                $return["intervalTypeName"] = ICReportType::handleShowInterval($typeData["intervaltype"]);
            }

            $return["url"] = Ibos::app()->urlManager->createUrl("report/default/index", array("typeid" => $typeid));
            $return["isSuccess"] = true;
            $return["msg"] = Ibos::lang("Update succeed", "message");
            $this->ajaxReturn($return);
        }
    }

    public function actionDel()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $typeid = intval(EnvUtil::getRequest("typeid"));

            if (empty($typeid)) {
                $this->ajaxReturn(array("isSuccess" => false, "msg" => Ibos::lang("Parameters error", "error")));
            }

            $removeSuccess = ReportType::model()->remove($typeid);

            if ($removeSuccess) {
                $reports = Report::model()->fetchRepidAndAidByTypeids($typeid);

                if (!empty($reports)) {
                    if ($reports["aids"]) {
                        AttachUtil::delAttach($reports["aids"]);
                    }

                    ReportRecord::model()->deleteAll("repid IN('{$reports["repids"]}')");
                    Report::model()->deleteAll("repid IN('{$reports["repids"]}')");
                }

                $return["isSuccess"] = true;
                $return["msg"] = Ibos::lang("Del succeed", "message");
            } else {
                $return["isSuccess"] = false;
                $return["msg"] = Ibos::lang("Del failed", "message");
            }

            $this->ajaxReturn($return);
        }
    }
}
