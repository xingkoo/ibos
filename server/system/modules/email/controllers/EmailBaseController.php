<?php

class EmailBaseController extends ICController
{
    const INBOX_ID = 1;
    const DRAFT_ID = 2;
    const SENT_ID = 3;
    const TRASH_ID = 4;
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * 默认的页面属性
     * @var array 
     */
    private $_attributes = array(
        "uid"          => 0,
        "fid"          => 0,
        "webId"        => 0,
        "archiveId"    => 0,
        "allowWebMail" => false,
        "subOp"        => "",
        "folders"      => array(),
        "webMails"     => array()
        );

    public function __set($name, $value)
    {
        if (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            parent::__get($name);
        }
    }

    public function init()
    {
        $this->uid = $uid = intval(Yii::app()->user->uid);
        $this->folders = EmailFolder::model()->fetchAllUserFolderByUid($uid);
        $this->allowWebMail = (bool) Yii::app()->setting->get("setting/emailexternalmail");
        $this->webMails = ($this->allowWebMail ? EmailWeb::model()->fetchAllByUid($uid) : array());
        parent::init();
    }

    protected function getSidebar($op = "")
    {
        $archiveTable = array();
        $settings = Yii::app()->setting->get("setting");
        $archiveTable["ids"] = ($settings["emailtableids"] ? $settings["emailtableids"] : array());
        $archiveTable["info"] = ($settings["emailtable_info"] ? $settings["emailtable_info"] : array());

        foreach ($archiveTable["ids"] as $tableId) {
            if (($tableId != 0) && empty($archiveTable["info"][$tableId]["displayname"])) {
                $archiveTable["info"][$tableId]["displayname"] = Ibos::lang("Unnamed archive") . "(" . $tableId . ")";
            }
        }

        $data = array("op" => $op, "uid" => $this->uid, "lang" => Ibos::getLangSources(), "folders" => $this->folders, "allowWebMail" => $this->allowWebMail, "webEmails" => $this->webMails, "fid" => $this->fid, "webId" => $this->webId, "archiveId" => $this->archiveId, "hasArchive" => 1 < count($archiveTable["ids"]), "archiveTable" => $archiveTable);
        $sidebarAlias = "application.modules.email.views.sidebar";
        $sidebarView = $this->renderPartial($sidebarAlias, $data, true);
        return $sidebarView;
    }

    protected function setListPageSize($size)
    {
        $size = intval($size);
        if ((0 < $size) && in_array($size, array(5, 10, 20))) {
            MainUtil::setCookie("email_pagesize_" . $this->uid, $size, 0, 0);
        }
    }

    protected function getListPageSize()
    {
        $pageSize = MainUtil::getCookie("email_pagesize_" . $this->uid, 0);

        if (is_null($pageSize)) {
            $pageSize = self::DEFAULT_PAGE_SIZE;
        }

        return $pageSize;
    }
}
