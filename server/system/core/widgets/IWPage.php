<?php

class IWPage extends CLinkPager
{
    public $maxButtonCount = 5;
    /**
     * 当前页面Url
     * @var string 
     */
    public $currentUrl;
    /**
     * 当前页数
     * @var integer
     */
    private $currentPage = 1;
    /**
     * 总页数
     * @var integer 
     */
    private $itemCount = 0;
    /**
     * 上一页和下一页按钮的父节点的html属性
     * @var string 
     * @access private
     */
    private $pervNextHtmlOption = array();
    /**
     * 链接的类型 默认为空，值可以为ajax
     * @var string 
     * @author gzwwb <gzwwb@ibos.com.cn>
     */
    public $type = "";
    /**
     * ajax提交的地址或者函数,值示例:getAjaxPage(参数1，参数2...)；，JS函数
     * @var type 
     * @author gzwwb <gzwwb@ibos.com.cn>
     */
    public $ajaxUrl = "";

    public function init()
    {
        $this->setCurrentUrl($this->currentUrl);
        $this->currentPage = parent::getCurrentPage(false) + 1;
        $this->itemCount = parent::getPageCount();
        $this->header = Ibos::lang("page_header", "page");
        $this->htmlOptions["id"] = Ibos::lang("htmlOptions_id", "page");
        $this->htmlOptions["class"] = Ibos::lang("htmlOptions_class", "page");
        $this->pervNextHtmlOption["id"] = Ibos::lang("pervNextHtmlOption_id", "page");
        $this->pervNextHtmlOption["class"] = Ibos::lang("pervNextHtmlOption_class", "page");
        $this->setFooter();
        $this->prevPageLabel = Ibos::lang("prevpage_label", "page");
        $this->nextPageLabel = Ibos::lang("nextpage_label", "page");
        $this->firstPageLabel = Ibos::lang("firstpage_label", "page");
        $this->firstPageCssClass = Ibos::lang("firstpage_cssclass", "page");
        $this->lastPageLabel = Ibos::lang("lastpage_label", "page");
        $this->lastPageCssClass = Ibos::lang("lastpage_cssclass", "page");
        $this->hiddenPageCssClass = Ibos::lang("hiddenpage_cssclass", "page");
        $this->selectedPageCssClass = Ibos::lang("selected_cssclass", "page");
        $this->cssFile = false;
        $this->previousPageCssClass = "btn btn-small";
        $this->nextPageCssClass = "btn btn-small";
    }

    protected function createPageButtons()
    {
        if (($pageCount = $this->getPageCount()) <= 1) {
            return array();
        }

        list($beginPage, $endPage) = $this->getPageRange();
        $currentPage = $this->getCurrentPage(false);
        $buttons = array();
        $buttons[] = $this->createPageButton($this->firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);

        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->createPageButton($i + 1, $i, $this->internalPageCssClass, false, $i == $currentPage);
        }

        $buttons[] = $this->createPageButton($this->lastPageLabel, $pageCount - 1, $this->lastPageCssClass, ($pageCount - 1) <= $currentPage, false);

        if (($page = $currentPage - 1) < 0) {
            $page = 0;
        }

        $buttons[] = $this->createPervNextButton($this->prevPageLabel, $page, $this->previousPageCssClass, $currentPage <= 0, false);

        if (($pageCount - 1) <= $page = $currentPage + 1) {
            $page = $pageCount - 1;
        }

        $buttons[] = $this->createPervNextButton($this->nextPageLabel, $page, $this->nextPageCssClass, ($pageCount - 1) <= $currentPage, false);
        return $buttons;
    }

    private function createPervNextButton($label, $page, $class, $hidden, $selected)
    {
        if ($hidden || $selected) {
            $class .= " " . ($hidden ? $this->hiddenPageCssClass : $this->selectedPageCssClass);
        }

        return CHtml::link($label, $this->createPageUrl($page), array("class" => $class));
    }

    public function run()
    {
        $this->registerClientScript();
        $buttons = $this->createPageButtons();

        if (empty($buttons)) {
            return null;
        } else {
            $count = count($buttons);
            $prev = $buttons[$count - 2];
            unset($buttons[$count - 2]);
            $next = $buttons[$count - 1];
            unset($buttons[$count - 1]);
            $pervNextArray = array($prev, $next);
        }

        echo $this->header;
        echo CHtml::tag("ul", $this->htmlOptions, implode("\n", $buttons));
        echo $this->footer;
        echo CHtml::tag("div", $this->pervNextHtmlOption, implode("\n", $pervNextArray));
    }

    public function setCurrentUrl($value = null)
    {
        if (is_null($value)) {
            $currentUrl = (string) Yii::app()->getRequest()->getUrl();

            if (strpos($currentUrl, "?page=") !== false) {
                $splitArray = explode("?page=", $currentUrl);
                $this->currentUrl = $splitArray[0];
            } elseif (strpos($currentUrl, "&page=") !== false) {
                $splitArray = explode("&page=", $currentUrl);
                $this->currentUrl = $splitArray[0];
            } else {
                $this->currentUrl = $currentUrl;
            }
        } else {
            $this->currentUrl = $value;
        }
    }

    public function setFooter($value = null)
    {
        if (is_null($value)) {
            $this->footer = Ibos::lang("page_footer", "page", array("currentUrl" => $this->currentUrl, "currentPage" => $this->currentPage, "itemCount" => $this->itemCount));
        } else {
            $this->footer = $value;
        }
    }

    protected function createPageUrl($page)
    {
        if (empty($this->type)) {
            return $this->getPages()->createPageUrl($this->getController(), $page);
        } elseif ($this->type == "ajax") {
            return substr($this->ajaxUrl, 0, strpos($this->ajaxUrl, ")")) . "," . $page . ");";
        }
    }
}
