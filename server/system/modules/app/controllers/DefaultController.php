<?php

class AppDefaultController extends ICController
{
    public function actionIndex()
    {
        $data = array("shortcut" => AppPersonal::model()->fetchShortcutByUid(Ibos::app()->user->uid), "widget" => AppPersonal::model()->fetchWidgetByUid(Ibos::app()->user->uid));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Application Market"), "url" => $this->createUrl("default/index"))
        ));
        $this->render("index", $data);
    }

    public function actionApplist()
    {
        $detailAlias = "application.modules.app.views.default.applist";
        $category = AppCategory::model()->fetchAllSort();

        foreach ($category as $key => $value) {
            $category[$key]["count"] = App::model()->count("catid = {$value["catid"]}");
        }

        $params = array("lang" => Ibos::getLangSource("app.default"), "assetUrl" => Ibos::app()->assetManager->getAssetsUrl("app"), "category" => $category, "appCount" => App::model()->count("appid"));
        $detailView = $this->renderPartial($detailAlias, $params, true);
        echo $detailView;
    }

    public function actionGetApp()
    {
        $catId = intval(EnvUtil::getRequest("catid"));
        $category = AppCategory::model()->fetch("catid = $catId");
        $page = intval(EnvUtil::getRequest("page"));
        $limit = ($page - 1) * 18;
        $pageNum = 18;

        if ($catId == 0) {
            $appCount = App::model()->count("appid");
        } else {
            $appCount = App::model()->count("catid = $catId");
        }

        $pages = (($appCount % $pageNum) == 0 ? intval($appCount / $pageNum) : intval($appCount / $pageNum) + 1);
        $condition = ($catId == 0 ? "appid LIMIT $limit,$pageNum" : "catid = {$category["catid"]} and appid LIMIT $limit,$pageNum");
        $appList = App::model()->fetchAll($condition);
        $this->ajaxReturn(array("isSuccess" => true, "data" => $appList, "pages" => $pages));
    }

    public function actionAdd()
    {
        $type = EnvUtil::getRequest("type");
        $field = ($type == "shortcut" ? "shortcut" : "widget");
        $uid = Ibos::app()->user->uid;
        $appId = intval(EnvUtil::getRequest("appid"));
        $personal = AppPersonal::model()->fetch("uid = $uid");

        if (!empty($personal)) {
            $old = explode(",", $personal[$field]);
            $old[] = $appId;
            $imp = implode(",", $old);
            $new = trim($imp, ",");
            AppPersonal::model()->updateByPk($personal["id"], array($field => $new));
        } else {
            $data = array("uid" => $uid, $field => $appId);
            AppPersonal::model()->add($data);
        }

        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionEdit()
    {
        $shortcuts = EnvUtil::getRequest("shortcuts");
        $uid = Ibos::app()->user->uid;

        if (!empty($shortcuts)) {
            $imp = implode(",", $shortcuts);
        } else {
            $imp = "";
        }

        AppPersonal::model()->updateAll(array("shortcut" => $imp), "uid = $uid");
        $this->ajaxReturn(array("isSuccess" => true));
    }

    public function actionDel()
    {
        $uid = Ibos::app()->user->uid;
        $appId = EnvUtil::getRequest("id");
        $personal = AppPersonal::model()->fetch("uid = $uid");
        $old = explode(",", $personal["widget"]);

        if (in_array($appId, $old)) {
            $newAppId[] = $appId;
            $new = array_diff($old, $newAppId);
        }

        $imp = implode(",", $new);
        AppPersonal::model()->updateAll(array("widget" => $imp), "uid = $uid");
        $this->ajaxReturn(array("isSuccess" => true));
    }
}
