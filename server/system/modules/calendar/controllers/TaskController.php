<?php

class CalendarTaskController extends CalendarBaseController
{
    /**
     * 查询条件
     * @var string 
     * @access protected 
     */
    private $_condition;
    private $complete;

    public function actionIndex()
    {
        if (!$this->checkIsMe()) {
            $this->error(Ibos::lang("No permission to view task"), $this->createUrl("task/index"));
        }

        $postComp = EnvUtil::getRequest("complete");
        $this->complete = (empty($postComp) ? 0 : $postComp);

        if (EnvUtil::getRequest("param") == "search") {
            $this->search();
        }

        $this->_condition = CalendarUtil::joinCondition($this->_condition, "uid = " . $this->uid);
        $data = Tasks::model()->fetchTaskByComplete($this->_condition, $this->complete);
        $data["complete"] = $this->complete;
        $data["user"] = User::model()->fetchByUid($this->uid);
        $this->setPageTitle(Ibos::lang("Personal task"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Calendar arrangement"), "url" => $this->createUrl("schedule/index")),
            array("name" => Ibos::lang("Personal task"))
        ));
        $this->render("index", $data);
    }

    public function actionSubTask()
    {
        if (!UserUtil::checkIsSub(Ibos::app()->user->uid, $this->uid)) {
            $this->error(Ibos::lang("No permission to view task"), $this->createUrl("task/index"));
        }

        $postComp = EnvUtil::getRequest("complete");
        $this->complete = (empty($postComp) ? 0 : $postComp);

        if (EnvUtil::getRequest("param") == "search") {
            $this->search();
        }

        $this->_condition = CalendarUtil::joinCondition($this->_condition, "uid = " . $this->uid);
        $data = Tasks::model()->fetchTaskByComplete($this->_condition, $this->complete);
        $data["complete"] = $this->complete;
        $data["user"] = User::model()->fetchByUid($this->uid);
        $data["supUid"] = UserUtil::getSupUid($this->uid);
        $data["allowEditTask"] = CalendarUtil::getIsAllowEidtTask();
        $this->setPageTitle(Ibos::lang("Subordinate task"));
        $this->setPageState("breadCrumbs", array(
            array("name" => Ibos::lang("Personal Office")),
            array("name" => Ibos::lang("Calendar arrangement"), "url" => $this->createUrl("schedule/index")),
            array("name" => Ibos::lang("Subordinate task"))
        ));
        $this->render("subtask", $data);
    }

    public function actionAdd()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (!$this->checkTaskPermission()) {
                $this->error(Ibos::lang("No permission to add task"), $this->createUrl("task/index"));
            }

            foreach ($_POST as $key => $value) {
                $_POST[$key] = StringUtil::filterCleanHtml($value);
            }

            $_POST["upuid"] = $this->upuid;
            $_POST["uid"] = $this->uid;
            $_POST["addtime"] = time();

            if (!isset($_POST["pid"])) {
                $count = Tasks::model()->count("pid=:pid", array(":pid" => ""));
                $_POST["sort"] = $count + 1;
            }

            Tasks::model()->add($_POST, true);

            if ($this->upuid != $this->uid) {
                $config = array("{sender}" => User::model()->fetchRealnameByUid($this->upuid), "{subject}" => $_POST["text"], "{url}" => Ibos::app()->urlManager->createUrl("calendar/task/index"));
                Notify::model()->sendNotify($this->uid, "task_message", $config, $this->upuid);
            }

            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionEdit()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (!$this->checkTaskPermission()) {
                $this->error(Ibos::lang("No permission to edit task"), $this->createUrl("task/index"));
            }

            $op = EnvUtil::getRequest("op");
            $id = EnvUtil::getRequest("id");

            switch ($op) {
                case "mark":
                    $mark = EnvUtil::getRequest("mark");
                    Tasks::model()->modifyTasksMark($id, $mark);
                    break;

                case "complete":
                    $complete = EnvUtil::getRequest("complete");
                    Tasks::model()->modifyTasksComplete($id, $complete);
                    Tasks::model()->updateCalendar($id, $complete);
                    break;

                case "save":
                    $text = StringUtil::filterCleanHtml(EnvUtil::getRequest("text"));
                    Tasks::model()->modify($id, array("text" => $text));
                    $schedule = Calendars::model()->fetchByAttributes(array("taskid" => $id));

                    if (!empty($schedule)) {
                        Calendars::model()->modify($schedule["calendarid"], array("subject" => $text));
                    }

                    break;

                case "date":
                    $date = EnvUtil::getRequest("date");
                    Tasks::model()->modify($id, array("date" => date("Y-m-d", $date)));

                    if ($date) {
                        $data = Tasks::model()->handleCalendar($id);
                        $schedule = Calendars::model()->fetchByAttributes(array("taskid" => $id));

                        if (empty($schedule)) {
                            Calendars::model()->add($data);
                        } else {
                            $task = Tasks::model()->fetchByPk($id);
                            $data["status"] = ($task["complete"] ? 1 : 0);
                            Calendars::model()->modify($schedule["calendarid"], $data);
                        }
                    } else {
                        $this->delCalendarByTaskid($id);
                    }

                    break;

                case "sort":
                    $currentId = EnvUtil::getRequest("currentId");
                    $targetId = EnvUtil::getRequest("targetId");
                    $type = EnvUtil::getRequest("type");
                    $this->sortTask($currentId, $targetId, $type);
                    break;
            }

            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    public function actionDel()
    {
        if (EnvUtil::submitCheck("formhash")) {
            if (!$this->checkTaskPermission()) {
                $this->error(Ibos::lang("No permission to del task"), $this->createUrl("task/index"));
            }

            $id = StringUtil::filterCleanHtml($_POST["id"]);
            Tasks::model()->removeTasksById($id);
            Calendars::model()->deleteAllByAttributes(array("taskid" => $id));
            $this->ajaxReturn(array("isSuccess" => true));
        }
    }

    private function delCalendarByTaskid($taskid)
    {
        $schedule = Calendars::model()->fetchByAttributes(array("taskid" => $taskid));

        if (!empty($schedule)) {
            Calendars::model()->remove($schedule["calendarid"]);
        }
    }

    private function sortTask($currentId, $targetId, $type)
    {
        $current = Tasks::model()->fetchByPk($currentId);
        $target = Tasks::model()->fetchByPk($targetId);
        $cSort = $current["sort"];
        $tSort = $target["sort"];
        if (($type == "up") && (($cSort - $tSort) != 1)) {
            Tasks::model()->updateCounters(array("sort" => -1), "sort BETWEEN ($cSort+1) AND $tSort");
            Tasks::model()->modify($currentId, array("sort" => $tSort));
        } else {
            if (($type == "down") && (($tSort - $cSort) != 1)) {
                Tasks::model()->updateCounters(array("sort" => 1), "sort BETWEEN $tSort AND ($cSort-1)");
                Tasks::model()->modify($currentId, array("sort" => $tSort));
            } else {
                return null;
            }
        }
    }

    private function search()
    {
        $uid = $this->uid;
        $complete = $this->complete;
        $type = EnvUtil::getRequest("type");
        $conditionCookie = MainUtil::getCookie("condition");

        if (empty($conditionCookie)) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }

        if ($type == "normal_search") {
            $keyword = EnvUtil::getRequest("keyword");
            MainUtil::setCookie("keyword", $keyword, 10 * 60);
            $pTasks = Tasks::model()->fetchPTasks($uid, $complete, $keyword);
            $cTasks = Tasks::model()->fetchCTasks($uid, $complete, $keyword);
            $array = array();

            foreach ($pTasks as $task) {
                $array[] = $task["id"];
            }

            foreach ($cTasks as $task) {
                $array[] = $task["pid"];
            }

            $pids = array_unique($array);
            $pidTemp = "";

            foreach ($pids as $v) {
                $pidTemp .= "\"" . $v . "\",";
            }

            $pidStr = rtrim($pidTemp, ",");

            if (!empty($pidStr)) {
                $this->_condition = " uid='$uid' AND id IN($pidStr) AND allcomplete='$complete'";
            } else {
                $this->_condition = " uid='$uid' AND id IN('') AND allcomplete='$complete'";
            }
        } else {
            $this->_condition = $conditionCookie;
        }

        if ($this->_condition != MainUtil::getCookie("condition")) {
            MainUtil::setCookie("condition", $this->_condition, 10 * 60);
        }
    }
}
