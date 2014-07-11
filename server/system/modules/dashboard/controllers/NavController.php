<?php

class DashboardNavController extends DashboardBaseController
{
    public function actionIndex()
    {
        $formSubmit = EnvUtil::submitCheck("navSubmit");

        if ($formSubmit) {
            Nav::model()->deleteAll();
            $navs = $_POST["data"];

            foreach ($navs as $pnav) {
                $pnav["pid"] = 0;
                $id = $this->runAdd($pnav);
                if ($id && isset($pnav["child"]) && !empty($pnav["child"])) {
                    foreach ($pnav["child"] as $cnav) {
                        $cnav["pid"] = $id;
                        $this->runAdd($cnav);
                    }
                }
            }

            CacheUtil::update("nav");
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $navs = Nav::model()->fetchAllByAllPid();
            $this->render("index", array("navs" => $navs));
        }
    }

    private function runAdd($nav)
    {
        if (!isset($nav["disabled"])) {
            $nav["disabled"] = 1;
        }

        if (!isset($nav["targetnew"])) {
            $nav["targetnew"] = 0;
        }

        if (!isset($nav["type"]) || ($nav["type"] == 0)) {
            $nav["pageid"] = 0;
        } else {
            if (($nav["type"] == 1) && ($nav["pageid"] == 0)) {
                $nav["pageid"] = Page::model()->add(array("template" => "index", "content" => ""), true);
            }
        }

        if (isset($nav["type"]) && ($nav["type"] == 1)) {
            $nav["url"] = "main/page/index&pageid={$nav["pageid"]}&name={$nav["name"]}";
        }

        $navid = Nav::model()->add($nav, true);
        return $navid;
    }
}
