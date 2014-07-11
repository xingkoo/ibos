<?php

class MICMail
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

    protected function getMailInstalled()
    {
    }

    public function getList($type = "inbox")
    {
        $gUid = Ibos::app()->user->uid;
        $boxid = EnvUtil::getRequest("boxid");
        $pageSize = 10;
        $count = Email::model()->countByToid($gUid, $type, $boxid);
        $pages = EmailUtil::getListPage($count, $pageSize);
        $records = Email::model()->fetchAllByToid($gUid, $pages->getLimit(), $pages->getOffset(), $type, $boxid);
        $list = array();

        if (!empty($records)) {
            $list = EmailUtil::getEmailData($records);
        }

        $params = array("type" => $type, "boxid" => $boxid, "list" => $list, "pages" => $pages, "pageSize" => $pageSize);

        if ($type == "folder") {
            $emailBox = new ICEmailBox($boxid);
            $params["folderName"] = $emailBox->name;
        }

        if ($type == "web") {
            $params["myWebEmails"] = EmailBox::model()->fetchAllNotSysByUid($gUid, true);
            $params["gUid"] = $gUid;
        }

        return $params;
    }

    public function getCategory()
    {
        $gUid = Ibos::app()->user->uid;
        $myFolders = EmailBox::model()->fetchAllNotSysByUid($gUid);
        $notReadCount = Email::model()->countNotReadByToid($gUid, "inbox");
        $return = array("folders" => $myFolders, "notread" => $notReadCount);
        return $return;
    }

    public function getMail($id)
    {
        $gUid = Ibos::app()->user->uid;
        $email = new ICEmail($id);
        $bodyAttr = $email->emailBody->attributes;
        $bodyAttr = EmailUtil::processViewData($bodyAttr);
        return $bodyAttr;
    }
}
