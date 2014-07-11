<?php

class Tasks extends ICModel
{
    public static function model($className = "Tasks")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{tasks}}";
    }

    public function fetchTaskByComplete($condition, $complete = 0, $pagesize = null)
    {
        if (!empty($condition)) {
            $condition .= " AND `allcomplete`=" . $complete;
        } else {
            $condition = "`allcomplete`=" . $complete;
        }

        if ($complete == 0) {
            $tasks = Tasks::model()->fetchAll(array("condition" => $condition, "order" => "sort ASC"));
            $data["todolist"] = CJSON::encode($tasks);
        } elseif ($complete == 1) {
            $tasks = $this->fetchAllAndPage($condition, $pagesize);

            foreach ($tasks["datas"] as $v) {
                $subTasks = $this->fetchAll("pid=:pid", array(":pid" => $v["id"]));
                $tasks["datas"] = array_merge($tasks["datas"], $subTasks);
            }

            $data = array("pages" => $tasks["pages"], "todolist" => CJSON::encode($tasks["datas"]));
        }

        return $data;
    }

    public function fetchAllAndPage($conditions = "", $pageSize = null)
    {
        $pages = new CPagination($this->countByCondition($conditions));
        $pageSize = (is_null($pageSize) ? Yii::app()->params["basePerPage"] : $pageSize);
        $pages->setPageSize(intval($pageSize));
        $offset = $pages->getOffset();
        $limit = $pages->getLimit();
        $criteria = new CDbCriteria(array("limit" => $limit, "offset" => $offset));
        $pages->applyLimit($criteria);
        $fields = "*";
        $sql = "SELECT $fields FROM {{tasks}}";

        if (!empty($conditions)) {
            $sql .= " WHERE " . $conditions;
        }

        $sql .= " ORDER BY sort DESC LIMIT $offset,$limit";
        $records = $this->getDbConnection()->createCommand($sql)->queryAll();
        return array("pages" => $pages, "datas" => $records);
    }

    public function countByCondition($condition = "")
    {
        if (!empty($condition)) {
            $whereCondition = " WHERE `pid`='' AND " . $condition;
            $sql = "SELECT COUNT(*) AS number FROM {{tasks}} $whereCondition";
            $record = $this->getDbConnection()->createCommand($sql)->queryAll();
            return $record[0]["number"];
        } else {
            return $this->count();
        }
    }

    public function fetchTasksByUid($uid, $complete)
    {
        $tasks = $this->fetchAll(array(
            "select"    => "id, pid, text, date, mark, complete, allcomplete",
            "condition" => "uid=:uid AND allcomplete=:allcomplete",
            "params"    => array(":uid" => $uid, ":allcomplete" => $complete)
        ));
        return $tasks;
    }

    public function modifyTasksMark($id, $mark)
    {
        $this->updateAll(array("mark" => $mark), "id=:id", array(":id" => $id));
    }

    public function modifyTasksComplete($id, $complete)
    {
        $task = $this->fetchByPk($id);

        if (empty($task["pid"])) {
            $this->updateAll(array("complete" => $complete, "allcomplete" => $complete), "id=:id OR pid=:id", array(":id" => $id));
        } else {
            if (!empty($task["pid"]) && ($complete == 0)) {
                $this->updateAll(array("complete" => 0), "id=:id OR id=:pid", array(":id" => $id, ":pid" => $task["pid"]));
                $this->updateAll(array("allcomplete" => 0), "id=:pid OR pid=:pid", array(":pid" => $task["pid"]));
            } else {
                if (!empty($task["pid"]) && ($complete == 1)) {
                    $this->modify($id, array("complete" => 1));
                    $allSubTask = $this->fetchAll("pid=:pid", array(":pid" => $task["pid"]));

                    foreach ($allSubTask as $k => $v) {
                        $newArr[] = $v["complete"];
                    }

                    if (!in_array(0, $newArr)) {
                        $this->modify($task["pid"], array("complete" => 1));
                        $this->updateAll(array("allcomplete" => 1), "id=:pid OR pid=:pid", array(":pid" => $task["pid"]));
                    }
                }
            }
        }

        $ret = $this->fetchByPk($id);
        return $ret;
    }

    public function removeTasksById($id)
    {
        $this->deleteAll("id=:id OR pid=:id", array("id" => $id));
    }

    public function fetchPTasks($uid, $complete, $keyword)
    {
        $pTasks = $this->fetchAll(array(
            "select"    => "id",
            "condition" => "uid=:uid AND pid=:pid AND allcomplete=:allcomplete AND text LIKE :keyword",
            "params"    => array(":uid" => $uid, ":pid" => "", ":allcomplete" => $complete, ":keyword" => "%$keyword%")
        ));
        return $pTasks;
    }

    public function fetchCTasks($uid, $complete, $keyword)
    {
        $cTasks = $this->fetchAll(array(
            "select"    => "pid",
            "condition" => "uid=:uid AND pid!=:pid AND allcomplete=:allcomplete AND text LIKE :keyword",
            "params"    => array(":uid" => $uid, ":pid" => "", ":allcomplete" => $complete, ":keyword" => "%$keyword%")
        ));
        return $cTasks;
    }

    public function handleCalendar($taskid)
    {
        $task = $this->fetchByPk($taskid);
        $data = array("taskid" => $taskid, "subject" => $task["text"] . Ibos::lang("From task"), "starttime" => strtotime($task["date"]), "endtime" => strtotime($task["date"]), "isalldayevent" => 1, "lock" => 1, "uid" => $task["uid"], "uptime" => time(), "upuid" => $task["upuid"], "category" => 1);
        return $data;
    }

    public function updateCalendar($id, $complete)
    {
        $schedule = Calendars::model()->fetchByAttributes(array("taskid" => $id));

        if ($complete) {
            $st = TIMESTAMP - 3600;
            $et = TIMESTAMP;

            if (!empty($schedule)) {
                Calendars::model()->modify($schedule["calendarid"], array("status" => 1, "starttime" => $st, "endtime" => $et, "isalldayevent" => 0));
            } else {
                $calendar = $this->handleCompTaskCalendar($id, $st, $et);
                Calendars::model()->add($calendar);
            }
        } elseif (!empty($schedule)) {
            Calendars::model()->modify($schedule["calendarid"], array("status" => 3));
        }
    }

    public function handleCompTaskCalendar($taskid, $st, $et)
    {
        $task = $this->fetchByPk($taskid);
        $data = array("taskid" => $taskid, "subject" => $task["text"] . Ibos::lang("From task"), "starttime" => $st, "endtime" => $et, "isalldayevent" => 0, "lock" => 0, "uid" => $task["uid"], "uptime" => time(), "upuid" => $task["upuid"], "category" => 1);
        return $data;
    }
}
