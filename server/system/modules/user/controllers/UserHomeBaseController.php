<?php

class UserHomeBaseController extends ICController
{
    /**
     * 当前用户ID
     * @var integer 
     */
    private $_uid = 0;
    /**
     * 当前用户数组
     * @var array 
     */
    private $_user = array();
    /**
     * 是否本人标识
     * @var boolean 
     */
    private $_isMe = false;

    public function init()
    {
        $uid = intval(EnvUtil::getRequest("uid"));

        if (!$uid) {
            $uid = Ibos::app()->user->uid;
        }

        $this->_uid = $uid;
        $user = User::model()->fetchByUid($uid);

        if (!$user) {
            $this->error(Ibos::lang("Cannot find the user"), $this->createUrl("home/index"));
        } else {
            $this->_user = $user;
        }

        $this->_isMe = $uid == Ibos::app()->user->uid;
        parent::init();
    }

    public function getIsMe()
    {
        return $this->_isMe;
    }

    public function getIsWeiboEnabled()
    {
        return ModuleUtil::getIsEnabled("weibo");
    }

    public function getUid()
    {
        return $this->_uid;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getCreditSidebar($lang = array())
    {
        $data["lang"] = $lang;
        $data["creditFormulaExp"] = strip_tags(Ibos::app()->setting->get("setting/creditsformulaexp"));
        $extcredits = Ibos::app()->setting->get("setting/extcredits");

        if (!empty($extcredits)) {
            $user = UserCount::model()->fetchByPk($this->getUid());

            foreach ($extcredits as $index => &$ext) {
                if (!empty($ext)) {
                    $ext["value"] = $user["extcredits" . $index];
                }
            }
        }

        $data["userCount"] = UserCount::model()->fetchByPk($this->getUid());
        $data["extcredits"] = $extcredits;
        $data["user"] = $this->getUser();
        return $this->renderPartial("application.modules.user.views.home.creditSidebar", $data, true);
    }

    public function getHeader($lang = array())
    {
        $onlineStatus = UserUtil::getOnlineStatus($this->getUid());
        $styleMap = array(-1 => "o-pm-offline", 1 => "o-pm-online");
        $data = array("user" => $this->getUser(), "assetUrl" => $this->getAssetUrl("user"), "swfConfig" => AttachUtil::getUploadConfig(), "onlineIcon" => $styleMap[$onlineStatus], "lang" => $lang);

        if ($this->getIsWeiboEnabled()) {
            $data["userData"] = UserData::model()->getUserData($this->getUid());
            !$this->getIsMe() && ($data["states"] = Follow::model()->getFollowState(Ibos::app()->user->uid, $this->getUid()));
        }

        return $this->renderPartial("application.modules.user.views.header", $data, true);
    }

    public function getColleagues($user, $includeMe = true)
    {
        $contacts = array();

        if (!empty($user["deptid"])) {
            $upId = $user["upuid"];
            $deptUsers = User::model()->fetchAll(array(
                "select"    => "uid",
                "condition" => "`deptid` = :deptid AND `status` IN (0,1)",
                "params"    => array(":deptid" => $user["deptid"])
            ));

            if (!empty($deptUsers)) {
                $deptUserIds = ConvertUtil::getSubByKey($deptUsers, "uid");
                $meUidIndex = array_search($this->getUid(), $deptUserIds);

                if ($meUidIndex !== false) {
                    unset($deptUserIds[$meUidIndex]);
                }

                $includeMe && ($contacts[0] = $user);
                if ($upId && ($upId != $this->getUid())) {
                    $upIdIndex = array_search($upId, $deptUserIds);

                    if ($upIdIndex !== false) {
                        unset($deptUserIds[$upIdIndex]);
                    }

                    $contacts[1] = User::model()->fetchByUid($upId);
                }

                $deptUserIds = array_values($deptUserIds);
                $i = 2;

                for ($j = 0; $j < count($deptUserIds); $i++, $j++) {
                    $contacts[$i] = User::model()->fetchByUid($deptUserIds[$j]);
                }
            }
        }

        return $contacts;
    }
}
