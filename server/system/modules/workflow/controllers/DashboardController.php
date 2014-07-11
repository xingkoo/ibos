<?php

class WorkflowDashboardController extends DashboardBaseController
{
    public function actionParam()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $data = array("sealfrom" => $_POST["seal_from"]);
            $workRemindBefore = intval($_POST["work_remind_before"]);
            $unitBefore = $_POST["unit_before"];

            if (!empty($workRemindBefore)) {
                $workRemindBefore .= $unitBefore;
                $data["wfremindbefore"] = $workRemindBefore;
            }

            $workRemindAfter = $_POST["work_remind_after"];
            $unitAfter = $_POST["unit_after"];

            if (!empty($workRemindAfter)) {
                $workRemindAfter .= $unitAfter;
                $data["wfremindafter"] = $workRemindAfter;
            }

            foreach ($data as $key => $value) {
                Setting::model()->updateSettingValueByKey($key, $value);
            }

            CacheUtil::update("setting");
            $this->success(Ibos::lang("Operation succeed", "message"));
        } else {
            $keys = "wfremindbefore,wfremindafter,sealfrom";
            $values = Setting::model()->fetchSettingValueByKeys($keys);
            $param = array();

            foreach ($values as $key => $value) {
                if (($key == "wfremindbefore") || ($key == "wfremindafter")) {
                    $param[$key . "desc"] = substr($value, 0, -1);
                    $param[$key . "unit"] = substr($value, -1, 1);
                }

                $param[$key] = $value;
            }

            $this->render("param", array("param" => $param));
        }
    }

    public function actionCategory()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (isset($_POST["name"])) {
                foreach ($_POST["name"] as $id => $val) {
                    if (!empty($val)) {
                        $data = array("name" => StringUtil::filterCleanHtml($val), "sort" => intval($_POST["sort"][$id]), "deptid" => !empty($_POST["deptid"][$id]) ? implode(",", StringUtil::getId($_POST["deptid"][$id])) : "");
                        FlowCategory::model()->modify(intval($id), $data);
                    }
                }
            }

            if (isset($_POST["newname"])) {
                foreach ($_POST["newname"] as $id => $val) {
                    if (!empty($val)) {
                        $data = array("name" => StringUtil::filterCleanHtml($val), "sort" => intval($_POST["newsort"][$id]), "deptid" => !empty($_POST["newdeptid"][$id]) ? implode(",", StringUtil::getId($_POST["newdeptid"][$id])) : "");
                        FlowCategory::model()->add($data);
                    }
                }
            }

            if (!empty($_POST["delid"])) {
                $id = StringUtil::filterStr($_POST["delid"]);

                if (!FlowCategory::model()->del($id)) {
                    $this->error(Ibos::lang("Category delete require"));
                }
            }

            $this->success(Ibos::lang("Operation succeed", "message"));
        } else {
            $categorys = FlowCategory::model()->fetchAll(array("order" => "sort ASC"));

            foreach ($categorys as $key => &$cat) {
                if ($cat["deptid"] !== "") {
                    $cat["deptid"] = StringUtil::wrapId($cat["deptid"], "d");
                }

                $cat["flownums"] = FlowType::model()->countByAttributes(array("catid" => $cat["catid"]));
                $cat["formnums"] = FlowFormType::model()->countByAttributes(array("catid" => $cat["catid"]));
            }

            $this->render("category", array("list" => $categorys));
        }
    }
}
