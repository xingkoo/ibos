<?php

class ICDiaryChart extends ICChart
{
    public function getIsPersonal()
    {
        $uids = $this->getCounter()->getUid();
        return count($uids) == 1;
    }

    public function getUserName()
    {
        $users = User::model()->fetchAllByUids($this->getCounter()->getUid());
        return StringUtil::iImplode(ConvertUtil::getSubByKey($users, "realname"));
    }

    public function getStampName()
    {
        $name = StringUtil::iImplode($this->getCounter()->getStampName());
        return $name;
    }

    public function getSeries()
    {
    }

    public function getYaxis()
    {
    }

    public function getXaxis()
    {
    }
}
