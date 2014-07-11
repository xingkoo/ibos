<?php

class IWWfListSidebar extends IWWfBase
{
    const VIEW = "wfwidget.listsidebar";

    protected $category = array();
    protected $catId;

    public function run()
    {
        $var["control"] = $this->getController()->getId();
        $var["lang"] = Ibos::getLangSources();
        $var["category"] = $this->getCategory();
        $var["catId"] = $this->getCatId();
        $this->render(self::VIEW, $var);
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCatId($catId)
    {
        $this->catId = intval($catId);
    }

    public function getCatId()
    {
        return $this->catId;
    }
}
