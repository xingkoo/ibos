<?php

class ArticleBaseController extends ICController
{
    const ARTICLE_TYPE_DEFAULT = 0;
    const ARTICLE_TYPE_PICTURE = 1;
    const ARTICLE_TYPE_LINK = 2;

    /**
     * 分类id
     * @var integer
     */
    protected $catid = 0;
    /**
     * 条件
     * @var string
     */
    protected $condition = "";

    public function getSidebar($catid = 0)
    {
        $sidebarAlias = "application.modules.article.views.sidebar";
        $approvals = Approval::model()->fetchAllApproval();
        $params = array("approvals" => $approvals, "categoryData" => $this->getCategoryOption(), "catid" => $catid);
        return $this->renderPartial($sidebarAlias, $params, true);
    }

    protected function getVoteInstalled()
    {
        Yii::import("application.modules.vote.components.ICVotePlugManager");
        $installed = new ICVotePlugManager();
        $installed->setInit("vote");
        return $installed->getInit();
    }

    protected function getEmailInstalled()
    {
        $isInstallEmail = ModuleUtil::getIsEnabled("email");
        return $isInstallEmail;
    }

    protected function getCategoryOption()
    {
        $category = new ICArticleCategory("ArticleCategory");
        $categoryData = $category->getAjaxCategory($category->getData(array("order" => "sort ASC")));
        return StringUtil::getTree($categoryData, "<option value='\$catid' \$selected>\$spacer\$name</option>");
    }

    public function getDashboardConfig()
    {
        $result = array();
        $fields = array("articleapprover", "articlecommentenable", "articlevoteenable", "articlemessageenable");

        foreach ($fields as $field) {
            $result[$field] = Yii::app()->setting->get("setting/" . $field);
        }

        return $result;
    }
}
