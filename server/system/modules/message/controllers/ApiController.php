<?php

class MessageApiController extends MessageBaseController
{
    public function actionGetUnreadCount()
    {
        $count = UserData::model()->getUnreadCount(Ibos::app()->user->uid);
        $data["status"] = 1;
        $data["data"] = $count;
        $this->ajaxReturn($data);
    }

    public function actionSearchAt()
    {
        $users = UserData::model()->fetchRecentAt(Ibos::app()->user->uid);
        $this->ajaxReturn(!empty($users) ? $users : array());
    }

    public function actionLoadMoreDiggUser()
    {
        $feedId = intval(EnvUtil::getRequest("feedid"));
        $offset = intval(EnvUtil::getRequest("offset"));
        $result = FeedDigg::model()->fetchUserList($feedId, 5, $offset);
        $uids = ConvertUtil::getSubByKey($result, "uid");
        $followStates = Follow::model()->getFollowStateByFids(Ibos::app()->user->uid, $uids);
        $data["data"] = $this->renderPartial("application.modules.message.views.feed.digglistmore", array("list" => $result, "followstates" => $followStates), true);
        $data["isSuccess"] = true;
        $this->ajaxReturn($data);
    }

    public function actionDoFollow()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $fid = StringUtil::filterCleanHtml($_POST["fid"]);
            $res = Follow::model()->doFollow(Ibos::app()->user->uid, intval($fid));
            $isFriend = $res["following"] && $res["follower"];
            $this->ajaxReturn(array("isSuccess" => !!$res, "both" => $isFriend, "msg" => Follow::model()->getError("doFollow")));
        }
    }

    public function actionUnFollow()
    {
        if (EnvUtil::submitCheck("formhash")) {
            $fid = StringUtil::filterCleanHtml($_POST["fid"]);
            $res = Follow::model()->unFollow(Ibos::app()->user->uid, intval($fid));
            $this->ajaxReturn(array("isSuccess" => !!$res, "msg" => Follow::model()->getError("unFollow")));
        }
    }
}
