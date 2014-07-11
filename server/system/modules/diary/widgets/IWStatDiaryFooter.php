<?php

class IWStatDiaryFooter extends IWStatDiaryBase
{
    const VIEW = "application.modules.diary.views.widget.footer";

    public function run()
    {
        $this->checkReviewAccess();
        $uid = $this->getUid();
        $time = StatCommonUtil::getCommonTimeScope();
        $list = Diary::model()->fetchAddTimeByUid($uid, $time["start"], $time["end"]);
        $data = array("delay" => $this->getDelay($list), "nums" => $this->getDiaryNums($list));
        $this->render(self::VIEW, $data);
    }

    protected function getDelay($list)
    {
        $res = array();

        foreach ($list as $rec) {
            if (86400 < ($rec["addtime"] - $rec["diarytime"])) {
                !isset($res[$rec["uid"]]) && ($res[$rec["uid"]] = array("user" => User::model()->fetchByUid($rec["uid"]), "count" => 0));
                $res[$rec["uid"]]["count"]++;
            }
        }

        return $res;
    }

    protected function getDiaryNums($list)
    {
        $res = array();

        foreach ($list as $rec) {
            !isset($res[$rec["uid"]]) && ($res[$rec["uid"]] = array("user" => User::model()->fetchByUid($rec["uid"]), "count" => 0));
            $res[$rec["uid"]]["count"]++;
        }

        return $res;
    }
}
