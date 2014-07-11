<?php

class MessageMentionController extends MessageBaseController
{
    public function actionIndex()
    {
        $uid = Ibos::app()->user->uid;
        $unreadAtMe = UserData::model()->countUnreadAtMeByUid($uid);
        $pageCount = Atme::model()->countByAttributes(array("uid" => $uid));
        $pages = PageUtil::create($pageCount);
        $atList = Atme::model()->fetchAllAtmeListByUid($uid, $pages->getLimit(), $pages->getOffset());
        $feedIds = ConvertUtil::getSubByKey($atList, "feedid");
        $diggArr = FeedDigg::model()->checkIsDigg($feedIds, $uid);
        $data = array("unreadAtmeCount" => $unreadAtMe, "list" => $atList, "pages" => $pages, "digg" => $diggArr);
        $this->setPageTitle(Ibos::lang("Mention me"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Message center"), "url" => $this->createUrl("mention/index")),
            array("name" => Ibos::lang("Mention me"))
        ));
        $this->render("index", $data);
    }
}
