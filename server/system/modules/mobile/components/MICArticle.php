<?php

class MICArticle
{
    /**
     * 分类id
     * @var integer
     * @access protected
     */
    protected $catid = 0;
    /**
     * 条件
     * @var string
     * @access protected
     */
    protected $condition = "";

    protected function getArticleInstalled()
    {
    }

    public function getList($type = 1, $catid = 0, $search = "")
    {
        $gUid = Ibos::app()->user->uid;
        $childCatIds = "";

        if (!empty($catid)) {
            $this->catid = $catid;
            $childCatIds = ArticleCategory::model()->fetchCatidByPid($this->catid, true);
        }

        if (!empty($search)) {
            $this->condition = "subject like '%$search%'";
        }

        $articleidArr = ArticleReader::model()->fetchArticleidsByUid(Ibos::app()->user->uid);
        $this->condition = ArticleUtil::joinListCondition($type, $articleidArr, $childCatIds, $this->condition);
        $datas = Article::model()->fetchAllAndPage($this->condition);
        $listData = $datas["datas"];

        foreach ($listData as $key => $value) {
            $value["content"] = StringUtil::cutStr(strip_tags($value["content"]), 30);
            $listData[$key] = array_filter($value);
            $listData[$key]["readstatus"] = ArticleReader::model()->checkIsRead($value["articleid"], $gUid);
        }

        $return["datas"] = $listData;
        $return["pages"] = array("pageCount" => $datas["pages"]->getPageCount(), "page" => $datas["pages"]->getCurrentPage(), "pageSize" => $datas["pages"]->getPageSize());
        return $return;
    }

    public function getCategory()
    {
        $category = new ICCategory("ArticleCategory");
        $data = $category->getData();
        $format = "<li> <a href='#news' onclick='news.loadList(\$catid)'>\$spacer<i class='ao-file'></i>\$name</a> </li>";
        $return = StringUtil::getTree($data, $format, 0, "&nbsp;&nbsp;&nbsp;&nbsp;", array("", "", ""));
        return $return;
    }

    public function getNews($id)
    {
        $gUid = Ibos::app()->user->uid;
        $article = Article::model()->fetchByPk($id);
        $attribute = ICArticle::getShowData($article);
        return $attribute;
    }
}
