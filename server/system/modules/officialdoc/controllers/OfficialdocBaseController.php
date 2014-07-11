<?php

class OfficialdocBaseController extends ICController
{
    const ARTICLE_VERSION_DEFAULT = 1;
    const ARTICLE_VERSION_CREATE = 0.1;

    protected function getDefaultVersion()
    {
        return self::ARTICLE_VERSION_DEFAULT;
    }

    protected function getSidebar($catid = 0)
    {
        $sidebarAlias = "application.modules.officialdoc.views.sidebar";
        $approvals = Approval::model()->fetchAllApproval();
        $params = array("approvals" => $approvals, "categoryData" => $this->getCategoryOption(), "catid" => $catid);
        $noSignCount = Officialdoc::model()->countNoSignByUid(Ibos::app()->user->uid);
        $params["noSignCount"] = $noSignCount;
        return $this->renderPartial($sidebarAlias, $params, true);
    }

    protected function getCategoryOption()
    {
        $category = new ICOfficialdocCategory("OfficialdocCategory");
        $categoryData = $category->getAjaxCategory($category->getData(array("order" => "sort ASC")));
        return StringUtil::getTree($categoryData, "<option value='\$catid' \$selected>\$spacer\$name</option>");
    }

    protected function getNewestVerByDocid($docid)
    {
        $doc = Officialdoc::model()->fetchByPk($docid);

        if (!empty($doc)) {
            return $doc["version"];
        } else {
            return 1;
        }
    }
}
