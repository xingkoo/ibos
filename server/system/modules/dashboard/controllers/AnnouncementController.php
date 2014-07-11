<?php

class DashboardAnnouncementController extends DashboardBaseController
{
    public function actionSetup()
    {
        $formSubmit = EnvUtil::submitCheck("announcementSubmit");

        if ($formSubmit) {
            $sort = $_POST["sort"];

            foreach ($sort as $id => $value) {
                Announcement::model()->modify($id, array("sort" => $value));
            }

            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array();
            $count = Announcement::model()->count(array("select" => "id"));
            $pages = PageUtil::create($count);
            $list = Announcement::model()->fetchAllOnList($pages->getLimit(), $pages->getOffset());
            $data["list"] = $list;
            $data["pages"] = $pages;
            $this->render("setup", $data);
        }
    }

    public function actionAdd()
    {
        $formSubmit = EnvUtil::submitCheck("announcementSubmit");

        if ($formSubmit) {
            $this->beforeSave();
            $_POST["author"] = Ibos::app()->user->realname;
            $data = Announcement::model()->create();
            $rs = Announcement::model()->add($data);
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $this->render("add");
        }
    }

    public function actionEdit()
    {
        $id = EnvUtil::getRequest("id");
        $formSubmit = EnvUtil::submitCheck("announcementSubmit");

        if ($formSubmit) {
            $this->beforeSave();
            $data = Announcement::model()->create();
            Announcement::model()->updateByPk($id, $data);
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $data = array();

            if (intval($id)) {
                $data["id"] = $id;
                $data["record"] = Announcement::model()->fetchByPk($id);
                $this->render("edit", $data);
            }
        }
    }

    public function actionDel()
    {
        $formSubmit = EnvUtil::submitCheck("announcementSubmit");

        if ($formSubmit) {
            $ids = EnvUtil::getRequest("id");
            $id = implode(",", $ids);
            $this->announcementDelete($id);
            $this->success(Ibos::lang("Save succeed", "message"));
        } else {
            $id = EnvUtil::getRequest("id");

            if ($this->announcementDelete($id)) {
                $this->success(Ibos::lang("Del succeed", "message"));
            } else {
                $this->error(Ibos::lang("Del failed", "message"));
            }
        }
    }

    protected function beforeSave()
    {
        $_POST["starttime"] = strtotime($_POST["starttime"]);
        $_POST["endtime"] = strtotime($_POST["endtime"]);

        if ($_POST["endtime"] < $_POST["starttime"]) {
            $this->error(Ibos::lang("Sorry, you did not enter the start time or the end time you input is not correct", "error"));
        }
    }

    private function announcementDelete($id)
    {
        return Announcement::model()->deleteById($id);
    }
}
