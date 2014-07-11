<?php

class IWStatReportBase extends CWidget
{
    /**
     * 统计的类型
     * @var string 
     */
    private $_type;
    /**
     * 总结的类型id（1周、2月、3季、4年）
     * @var type 
     */
    private $_typeid = 1;

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setTypeid($typeid)
    {
        $this->_typeid = $typeid;
    }

    public function getTypeid()
    {
        return $this->_typeid;
    }

    protected function inPersonal()
    {
        return $this->getType() === "personal";
    }

    protected function getUid()
    {
        if ($this->inPersonal()) {
            $uid = array(Ibos::app()->user->uid);
        } else {
            $id = EnvUtil::getRequest("uid");
            $uids = StringUtil::filterCleanHtml(StringUtil::filterStr($id));

            if (empty($uids)) {
                $uid = User::model()->fetchSubUidByUid(Ibos::app()->user->uid);

                if (empty($uid)) {
                    return array();
                }
            } else {
                $uid = explode(",", $uids);
            }
        }

        return $uid;
    }

    public function getTimeScope($time = TIMESTAMP)
    {
        static $timeScope = array();

        if (empty($timeScope)) {
            $start = EnvUtil::getRequest("start");
            $end = EnvUtil::getRequest("end");
            if (!empty($start) && !empty($end)) {
                $start = strtotime($start);
                $end = strtotime($end);
                if ($start && $end) {
                    $timeScope = array("start" => $start, "end" => $end);
                }
            }

            if (empty($timeScope)) {
                $typeid = $this->getTypeid();
                $currentY = date("Y", $time);

                switch ($typeid) {
                    case "1":
                        $start = strtotime("first day of this month 00:00:00", $time);
                        $end = strtotime("last day of this month 23:59:59", $time);
                        break;

                    case "2":
                    case "3":
                        $start = strtotime($currentY . "-01-01 00:00:00");
                        $end = strtotime(($currentY + 1) . "-01-01 00:00:00") - 1;
                        break;

                    case "4":
                        $start = strtotime(($currentY - 4) . "-01-01 00:00:00");
                        $end = strtotime(($currentY + 1) . "-01-01 00:00:00") - 1;
                        break;

                    default:
                        $start = $end = null;
                        break;
                }
            }
        }

        return array("start" => $start, "end" => $end);
    }

    protected function checkReviewAccess()
    {
        $uid = $this->getUid();

        if (empty($uid)) {
            $this->getController()->redirect("stats/personal");
        }
    }

    protected function createComponent($class, $properties = array())
    {
        return Ibos::createComponent(array_merge(array("class" => $class), $properties));
    }
}
