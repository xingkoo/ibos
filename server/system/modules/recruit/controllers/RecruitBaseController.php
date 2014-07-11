<?php

class RecruitBaseController extends ICController
{
    /**
     * 查询的条件
     * @var string 
     */
    protected $condition = "";

    protected function getSidebar()
    {
        $sidebarAlias = "application.modules.recruit.views.resume.sidebar";
        $params = array("statModule" => Ibos::app()->setting->get("setting/statmodules"));
        $sidebarView = $this->renderPartial($sidebarAlias, $params, true);
        return $sidebarView;
    }

    public function getDashboardConfig()
    {
        $config = Ibos::app()->setting->get("setting/recruitconfig");
        $result = array();

        foreach ($config as $configName => $configValue) {
            list($visi, $fieldRule) = explode(",", $configValue);
            $result[$configName]["visi"] = $visi;
            $result[$configName]["fieldrule"] = $fieldRule;
        }

        return $result;
    }

    protected function checkIsInstallEmail()
    {
        $isInstallEmail = ModuleUtil::getIsEnabled("email");
        return $isInstallEmail;
    }

    public function actionGetRealname()
    {
        if (Ibos::app()->request->isAjaxRequest) {
            $keyword = EnvUtil::getRequest("keyword");
            $records = ResumeDetail::model()->fetchPKAndRealnameByKeyword($keyword);
            parent::ajaxReturn($records);
        }
    }

    public function actionSearch()
    {
        $type = EnvUtil::getRequest("type");
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->condition, 10 * 60);
        }

        if ($type == "advanced_search") {
            $search = $_POST["search"];
            $methodName = "join" . ucfirst($this->id) . "SearchCondition";
            $this->condition = RecruitUtil::$methodName($search, $this->condition);
        } elseif ($type == "normal_search") {
            $keyword = $_POST["keyword"];
            $this->condition = " rd.realname LIKE '%$keyword%' ";
        } else {
            $this->condition = $conditionCookie;
        }

        if ($this->condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->condition, 10 * 60);
        }

        $this->actionIndex();
    }

    public function actionCheckRealname()
    {
        $fullname = EnvUtil::getRequest("fullname");
        $fullnameToUnicode = str_replace("%", "\\", $fullname);
        $fullnameToUtf8 = StringUtil::unicodeToUtf8($fullnameToUnicode);
        $realnames = ResumeDetail::model()->fetchAllRealnames();
        $isExist["statu"] = (in_array($fullnameToUtf8, $realnames) ? true : false);
        $this->ajaxReturn($isExist);
    }
}
