<?php

class WeiboTopicController extends ICController
{
    public function actionIndex()
    {
        $this->render("index");
    }

    public function actionMyTopic()
    {
        $this->render("mytopic");
    }

    public function actionRanking()
    {
        $this->render("ranking");
    }

    public function actionDetail()
    {
        $this->render("detail");
    }
}
