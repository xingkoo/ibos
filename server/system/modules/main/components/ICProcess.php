<?php

class ICProcess extends CApplicationComponent
{
    public function isLocked($process, $ttl = 0)
    {
        $ttl = ($ttl < 1 ? 600 : intval($ttl));
        return $this->status("get", $process) || $this->find($process, $ttl);
    }

    public function unLock($process)
    {
        $this->status("rm", $process);
        $this->cmd("rm", $process);
    }

    private function status($action, $process)
    {
        static $processList = array();

        switch ($action) {
            case "set":
                $processList[$process] = true;
                break;

            case "get":
                return !empty($processList[$process]);
                break;

            case "rm":
                $processList[$process] = null;
                break;

            case "clear":
                $processList = array();
                break;
        }

        return true;
    }

    private function find($name, $ttl = 0)
    {
        if (!$this->cmd("get", $name)) {
            $this->cmd("set", $name, $ttl);
            $ret = false;
        } else {
            $ret = true;
        }

        $this->status("set", $name);
        return $ret;
    }

    private function cmd($cmd, $name, $ttl = 0)
    {
        static $allowcache;

        if ($allowcache === null) {
            $cc = CacheUtil::check();
            $allowcache = ($cc == "mem") || ($cc == "redis");
        }

        if ($allowcache) {
            return $this->processCmdCache($cmd, $name, $ttl);
        } else {
            return $this->processCmdDb($cmd, $name, $ttl);
        }
    }

    private function processCmdCache($cmd, $name, $ttl = 0)
    {
        $ret = "";

        switch ($cmd) {
            case "set":
                $ret = CacheUtil::set("process_lock_" . $name, TIMESTAMP, $ttl);
                break;

            case "get":
                $ret = CacheUtil::get("process_lock_" . $name);
                break;

            case "rm":
                $ret = CacheUtil::rm("process_lock_" . $name);
        }

        return $ret;
    }

    private function processCmdDb($cmd, $name, $ttl = 0)
    {
        $ret = "";

        switch ($cmd) {
            case "set":
                $ret = Process::model()->add(array("processid" => $name, "expiry" => TIMESTAMP + $ttl));
                break;

            case "get":
                $ret = Process::model()->find("processid = '$name'");
                if (empty($ret) || ($ret["expiry"] < TIMESTAMP)) {
                    $ret = false;
                } else {
                    $ret = true;
                }

                break;

            case "rm":
                $ret = Process::model()->deleteProcess($name, TIMESTAMP);
                break;
        }

        return $ret;
    }
}
