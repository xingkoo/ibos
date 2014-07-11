<?php

class MobileApiController extends CController
{
    public function actionIndex()
    {
        $server = new HproseHttpServer();
        $server->setCrossDomainEnabled(true);
        $server->addInstanceMethods($this);
        $server->handle();
        exit();
    }

    public function hello($word)
    {
        return "hello,$word";
    }

    public function sum()
    {
        return strval(array_sum(func_get_args()));
    }

    public function login($userName, $passWord)
    {
        if (!$passWord || ($passWord != addslashes($passWord))) {
            return false;
        }

        $identity = new ICUserIdentity($userName, $passWord);
        $result = $identity->authenticate(true);

        if (0 < $result) {
            Yii::app()->user->login($identity);
            return true;
        } else {
            return false;
        }
    }

    public function authcode()
    {
    }
}

Yii::import("ext.hprose.php5.HproseHttpServer");
