<?php

class MobileNewsController extends MobileBaseController
{
    public function actionIndex()
    {
        $catid = EnvUtil::getRequest("catid");
        $type = EnvUtil::getRequest("type");
        $search = EnvUtil::getRequest("search");

        if ($catid == -1) {
            $type = "new";
            $catid = 0;
        }

        if ($catid == -2) {
            $type = "old";
            $catid = 0;
        }

        $article = new MICArticle();
        $this->ajaxReturn($article->getList($type, $catid, $search), "JSONP");
    }

    public function actionCategory()
    {
        $article = new MICArticle();
        $this->ajaxReturn($article->getCategory(), "JSONP");
    }

    public function actionShow()
    {
        $newsid = EnvUtil::getRequest("id");
        $article = new MICArticle();
        $data = $article->getNews($newsid);

        if (!empty($data)) {
            if (!empty($data["attachmentid"])) {
                $data["attach"] = AttachUtil::getAttach($data["attachmentid"]);
                $attachmentArr = explode(",", $data["attachmentid"]);
            }
        }

        $this->ajaxReturn($data, "JSONP");
    }
}
