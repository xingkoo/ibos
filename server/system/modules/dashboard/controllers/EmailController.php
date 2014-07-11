<?php

class DashboardEmailController extends DashboardBaseController
{
    public function actionSetup()
    {
        $mailSetting = Setting::model()->fetchSettingValueByKey("mail");
        $mail = unserialize($mailSetting);
        $formSubmit = EnvUtil::submitCheck("emailSubmit");

        if ($formSubmit) {
            $serverList = array();
            $filterCheck = false;

            if ($_POST["mailsend"] == 1) {
                $filterCheck = true;
                $postArea = "socket";
            } else {
                $postArea = "smtp";
            }

            if (isset($_POST[$postArea])) {
                $serverList = array_merge($serverList, $_POST[$postArea]);
            }

            if (isset($_POST["new" . $postArea])) {
                $serverList = array_merge($serverList, $_POST["new" . $postArea]);
            }

            if ($filterCheck) {
                foreach ($serverList as $index => $server) {
                    if (isset($mail["server"][$index])) {
                        $passwordmask = StringUtil::passwordMask($mail["server"][$index]["password"]);
                        $serverList[$index]["password"] = ($server["password"] == $passwordmask ? $mail["server"][$index]["password"] : $server["password"]);
                    }

                    if (!isset($server["auth"])) {
                        $serverList[$index]["auth"] = 0;
                    }
                }
            }

            $data = array("mailsend" => $_POST["mailsend"], "maildelimiter" => $_POST["maildelimiter"], "mailusername" => isset($_POST["mailusername"]) ? 1 : 0, "sendmailsilent" => isset($_POST["sendmailsilent"]) ? 1 : 0, "server" => $serverList);
            Setting::model()->updateSettingValueByKey("mail", $data);
            CacheUtil::update(array("setting"));
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $this->render("setup", array("mail" => $mail));
        }
    }

    public function actionCheck()
    {
        $formSubmit = EnvUtil::submitCheck("emailSubmit");

        if ($formSubmit) {
            $testFrom = $_POST["testfrom"];
            $testTo = $_POST["testto"];
            if (empty($testFrom) || empty($testTo)) {
                $this->error(Ibos::lang("Parameters error", "error"));
            }

            $toEmails = explode(",", $testTo);
            $subject = Ibos::lang("IBOS test email subject");
            $message = Ibos::lang("IBOS test email content");

            foreach ($toEmails as $to) {
                MailUtil::sendMail($to, $subject, $message, $testFrom);
            }

            $this->success(Ibos::lang("Operation succeed", "message"));
        } else {
            $this->render("check");
        }
    }
}
