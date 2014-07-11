<?php

class IWStatDiaryBase extends CWidget
{
    /**
     * 统计的类型
     * @var string 
     */
    private $_type;

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
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
