<?php

class OrganizationApiController extends ICController
{
    public function filterRoutes($routes)
    {
        return true;
    }

    public function actionSyncUser()
    {
        $type = EnvUtil::getRequest("type");
        $uid = StringUtil::filterStr(EnvUtil::getRequest("uid"));
        $flag = intval(EnvUtil::getRequest("flag"));
        $pwd = EnvUtil::getRequest("pwd");

        if (MessageUtil::getIsImOpen($type)) {
            $im = Ibos::app()->setting->get("setting/im");
            $imCfg = $im[$type];
            $className = "ICIM" . ucfirst($type);
            $factory = new ICIMFactory();
            $properties = array("uid" => explode(",", $uid), "syncFlag" => $flag);

            if ($type == "rtx") {
                $properties["pwd"] = $pwd;
            }

            $adapter = $factory->createAdapter($className, $imCfg, $properties);
            return $adapter !== false ? $adapter->syncUser() : EnvUtil::iExit("初始化IM组件失败");
        } else {
            EnvUtil::iExit("未开启IM绑定");
        }
    }
}
