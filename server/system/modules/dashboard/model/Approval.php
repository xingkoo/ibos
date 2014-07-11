<?php

class Approval extends ICModel
{
    public static function model($className = "Approval")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{approval}}";
    }

    public function fetchAllApproval()
    {
        return $this->fetchAll(array("order" => "addtime DESC"));
    }

    public function fetchNextApprovalUids($id, $step)
    {
        $ret = array();

        if (empty($id)) {
            return $ret;
        }

        $approval = $this->fetchByPk($id);
        $nextStep = $step + 1;

        if (!empty($approval)) {
            if ($approval["level"] < $nextStep) {
                $ret = array(
                    "step" => "publish",
                    "uids" => array()
                );
            } else {
                $nextLevelName = $this->getLevelNameByStep($nextStep);
                $ret = array("step" => $nextStep, "uids" => explode(",", $approval[$nextLevelName]));
            }
        }

        return $ret;
    }

    public function getLevelNameByStep($step)
    {
        $levels = array("1" => "level1", "2" => "level2", "3" => "level3", "4" => "level4", "5" => "level5");

        if (in_array($step, array_keys($levels))) {
            return $levels[$step];
        } else {
            return $levels["1"];
        }
    }

    public function fetchApprovalUidsByIds($ids)
    {
        $ids = (is_array($ids) ? implode(",", $ids) : $ids);
        $uidStr = "";
        $approvals = $this->fetchAll("FIND_IN_SET(`id`, '$ids')");

        foreach ($approvals as $approval) {
            for ($i = 1; $i <= $approval["level"]; $i++) {
                $uidStr .= $approval["level$i"] . ",";
            }
        }

        $uidArrTemp = explode(",", $uidStr);
        $uidArr = array_unique($uidArrTemp);
        return array_filter($uidArr);
    }

    public function deleteApproval($id)
    {
        if (empty($id)) {
            return false;
        }

        $ret = $this->deleteByPk($id);

        if ($ret) {
            ArticleCategory::model()->updateAll(array("aid" => 0), "aid=$id");
            OfficialdocCategory::model()->updateAll(array("aid" => 0), "aid=$id");
        }

        return $ret;
    }
}
