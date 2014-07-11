<?php

class ICSession extends CHttpSession
{
    /**
     * session id
     * @var mixed 
     */
    public $sid;
    /**
     * 当前session变量数组
     * @var mixed 
     */
    public $var;
    /**
     * 新用户标识
     * @var boolean 
     */
    public $isNew = false;
    /**
     * 上一个session
     * @var array 
     */
    private $old = array("sid" => "", "ip" => "", "uid" => 0);
    /**
     * 新用户初始化session数组
     * @var array 
     */
    private $newGuest = array("sid" => 0, "ip1" => 0, "ip2" => 0, "ip3" => 0, "ip4" => 0, "uid" => 0, "username" => "", "groupid" => 0, "invisible" => 0, "action" => 0, "lastactivity" => 0, "lastolupdate" => 0);

    public function load($sid = "", $ip = "", $uid = 0)
    {
        $this->old = array("sid" => $sid, "ip" => $ip, "uid" => $uid);
        $this->var = $this->newGuest;

        if (!empty($ip)) {
            $this->initialize($sid, $ip, $uid);
        }
    }

    public function initialize($sid, $ip, $uid)
    {
        $this->old = array("sid" => $sid, "ip" => $ip, "uid" => $uid);
        $session = array();

        if ($sid) {
            $session = Session::model()->fetchBySid($sid, $ip, $uid);
        }

        if (empty($session) || ($session["uid"] != $uid)) {
            $session = $this->create($ip, $uid);
        }

        $this->var = $session;
        $this->sid = $session["sid"];
    }

    public function create($ip, $uid)
    {
        $this->isNew = true;
        $this->var = $this->newGuest;
        $this->setKey("sid", StringUtil::random(6));
        $this->setKey("uid", $uid);
        $this->setKey("ip", $ip);

        if ($uid) {
            $this->setKey("invisible", UserUtil::getUserProfile("invisible"));
        }

        $this->setKey("lastactivity", time());
        $this->sid = $this->var["sid"];
        return $this->var;
    }

    public function setKey($key, $value)
    {
        if (isset($this->newGuest[$key])) {
            $this->var[$key] = $value;
        } elseif ($key == "ip") {
            $ips = explode(".", $value);

            if (count($ips) == 4) {
                $this->setKey("ip1", $ips[0]);
                $this->setKey("ip2", $ips[1]);
                $this->setKey("ip3", $ips[2]);
                $this->setKey("ip4", $ips[3]);
            }
        }
    }

    public function getKey($key)
    {
        if (isset($this->newGuest[$key])) {
            return $this->var[$key];
        } elseif ($key == "ip") {
            return $this->getKey("ip1") . "." . $this->getKey("ip2") . "." . $this->getKey("ip3") . "." . $this->getKey("ip4");
        }
    }

    public function updateSession()
    {
        static $updated = false;

        if (!$updated) {
            $global = Ibos::app()->setting->toArray();

            if (!Ibos::app()->user->isGuest) {
                if (isset($global["cookie"]["ulastactivity"])) {
                    $userLastActivity = StringUtil::authCode($global["cookie"]["ulastactivity"], "DECODE");
                } else {
                    $userLastActivity = UserUtil::getUserProfile("lastactivity");
                    MainUtil::setCookie("ulastactivity", StringUtil::authCode($userLastActivity, "ENCODE"), 31536000);
                }
            }

            $onlineTimeSpan = 10;
            $lastOnlineUpdate = $this->var["lastolupdate"];
            $onlineTimeOffset = ($lastOnlineUpdate ? $lastOnlineUpdate : $userLastActivity);
            $allowUpdateOnlineTime = ($onlineTimeSpan * 60) < (TIMESTAMP - $onlineTimeOffset);
            if (!Ibos::app()->user->isGuest && $allowUpdateOnlineTime) {
                $updateStatus = OnlineTime::model()->updateOnlineTime(Ibos::app()->user->uid, $onlineTimeSpan, $onlineTimeSpan, TIMESTAMP);

                if ($updateStatus === false) {
                    $onlineTime = new OnlineTime();
                    $onlineTime->uid = Ibos::app()->user->uid;
                    $onlineTime->thismonth = $onlineTimeSpan;
                    $onlineTime->total = $onlineTimeSpan;
                    $onlineTime->lastupdate = $global["timestamp"];
                    $onlineTime->save();
                }

                $this->setKey("lastolupdate", TIMESTAMP);
            }

            $this->var["invisible"] = UserUtil::getUserProfile("invisible");

            foreach ($this->var as $key => $value) {
                if (Ibos::app()->user->hasState($key) && ($key != "lastactivity")) {
                    $this->setKey($key, Ibos::app()->user->$key);
                }
            }

            Ibos::app()->session->update();

            if (!Ibos::app()->user->isGuest) {
                $updateStatusField = array("lastip" => $global["clientip"], "lastactivity" => TIMESTAMP, "lastvisit" => TIMESTAMP, "invisible" => 1);

                if (21600 < (TIMESTAMP - $userLastActivity)) {
                    if ($onlineTimeSpan && (43200 < (TIMESTAMP - $userLastActivity))) {
                        $onlineTime = OnlineTime::model()->fetchByPk(Ibos::app()->user->uid);
                        UserCount::model()->updateByPk(Ibos::app()->user->uid, array("oltime" => round(intval($onlineTime["total"]) / 60)));
                    }

                    MainUtil::setCookie("ulastactivity", StringUtil::authCode(TIMESTAMP, "ENCODE"), 31536000);
                    UserStatus::model()->updateByPk(Ibos::app()->user->uid, $updateStatusField);
                }
            }

            $updated = true;
        }

        return $updated;
    }

    public function update()
    {
        if ($this->sid !== null) {
            if ($this->isNew) {
                $this->delete();
                Session::model()->add($this->var);
            } else {
                if (IN_DASHBOARD) {
                    MainUtil::setCookie("lastactivity", TIMESTAMP);
                }

                Session::model()->updateByPk($this->var["sid"], $this->var);
            }

            Ibos::app()->setting->set("session", $this->var);
            MainUtil::setCookie("sid", $this->sid, 86400);
        }
    }

    public function delete()
    {
        return Session::model()->deleteBySession($this->var);
    }
}
