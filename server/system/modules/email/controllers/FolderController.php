<?php

class EmailFolderController extends EmailBaseController
{
    public $layout = false;

    public function init()
    {
        $this->fid = intval(EnvUtil::getRequest("fid"));
        parent::init();
    }

    public function actionIndex()
    {
        $uid = $this->uid;
        $total = 0;
        $folders = $this->folders;

        foreach ($folders as &$folder) {
            $size = EmailFolder::model()->getFolderSize($uid, $folder["fid"]);
            $folder["size"] = ConvertUtil::sizeCount($size);
            $total += $size;
        }

        $inbox = EmailFolder::model()->getSysFolderSize($uid, "inbox");
        $web = EmailFolder::model()->getSysFolderSize($uid, "web");
        $sent = EmailFolder::model()->getSysFolderSize($uid, "send");
        $deleted = EmailFolder::model()->getSysFolderSize($uid, "del");
        $userSize = EmailUtil::getUserSize($uid);
        $data = array("folders" => $folders, "inbox" => ConvertUtil::sizeCount($inbox), "web" => ConvertUtil::sizeCount($web), "sent" => ConvertUtil::sizeCount($sent), "deleted" => ConvertUtil::sizeCount($deleted), "userSize" => $userSize, "total" => ConvertUtil::sizeCount(array_sum(array($total, $inbox, $web, $sent, $deleted))));
        $this->setPageTitle(Ibos::lang("Folder setting"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Email center"), "url" => $this->createUrl("list/index")),
            array("name" => Ibos::lang("Folder setting"))
        ));
        $this->render("index", $data);
    }

    public function actionAdd()
    {
        $sort = EnvUtil::getRequest("sort");
        $name = EnvUtil::getRequest("name");

        if (!empty($name)) {
            $data = array("sort" => intval($sort), "name" => $name, "uid" => $this->uid);
            $newId = EmailFolder::model()->add($data, true);
            $this->ajaxReturn(array("isSuccess" => true, "fid" => $newId));
        } else {
            $this->ajaxReturn(array("isSuccess" => false, "errorMsg" => Ibos::lang("Save failed", "message")));
        }
    }

    public function actionEdit()
    {
        $fid = $this->fid;
        $sort = EnvUtil::getRequest("sort");
        $name = EnvUtil::getRequest("name");

        if (!empty($name)) {
            EmailFolder::model()->modify($fid, array("sort" => intval($sort), "name" => $name));
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $this->ajaxReturn(array("isSuccess" => false, "errorMsg" => Ibos::lang("Save failed", "message")));
        }
    }

    public function actionDel()
    {
        $fid = $this->fid;
        $cleanAll = EnvUtil::getRequest("delemail");
        $emailIds = Email::model()->fetchAllEmailIdsByFolderId($fid, $this->uid);

        if ($cleanAll) {
            $emailIds && Email::model()->completelyDelete($emailIds, $this->uid);
        } else {
            $emailIds && Email::model()->updateByPk($emailIds, array("fid" => parent::INBOX_ID));
        }

        $deleted = EmailFolder::model()->deleteByPk($fid);

        if ($deleted) {
            $this->ajaxReturn(array("isSuccess" => true));
        } else {
            $this->ajaxReturn(array("isSuccess" => false, "errorMsg" => Ibos::lang("Del failed", "message")));
        }
    }
}
