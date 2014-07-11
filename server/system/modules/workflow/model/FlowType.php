<?php

class FlowType extends ICModel
{
    const GUIDE_PROCESS = "1,2,3";

    protected $allowCache = true;

    public static function model($className = "FlowType")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_type}}";
    }

    public function getIsAssociated($ids)
    {
        $criteria = array("select" => "1", "condition" => sprintf("FIND_IN_SET(formid,'%s')", $ids));
        $result = $this->fetch($criteria);
        return $result ? true : false;
    }

    public function getGuideInfo($flowId)
    {
        $info = array();
        $flow = $this->fetchByPk($flowId);
        $info["title"] = $flow["name"];
        $info["finished"] = $flow["guideprocess"];

        if (strcmp($flow["guideprocess"], self::GUIDE_PROCESS) == 0) {
            $passExam = $this->examFlow($flowId);

            if ($passExam) {
                $info["status"] = "success";
                $info["current"] = 0;
                return $info;
            } else {
                $info["status"] = "error";
            }
        } else {
            $info["status"] = "warning";
        }

        $guideProcessArr = explode(",", $flow["guideprocess"]);
        $allProcessArr = explode(",", self::GUIDE_PROCESS);
        $diff = array_diff($allProcessArr, $guideProcessArr);
        $current = array_shift($diff);
        $info["current"] = intval($current);
        return $info;
    }

    public function examFlow($flowId, $showDetail = false)
    {
        $processExits = FlowProcess::model()->countByAttributes(array("flowid" => $flowId));

        if ($processExits) {
            $checkProcessUser = FlowProcess::model()->checkProcessUserByFlowId($flowId);
            $checkProcessCirculating = FlowProcess::model()->checkProcessCirculatingByFlowId($flowId);
            $checkWritable = FlowProcess::model()->checkWritableFieldByFlowId($flowId);
        } else {
            $checkProcessUser = $checkProcessCirculating = $checkWritable = false;
        }

        $checkCicrulating = true;

        if (!empty($checkProcessCirculating)) {
            foreach ($checkProcessCirculating as $cp) {
                if (!empty($cp["error"])) {
                    $checkCicrulating = false;
                    break;
                }
            }
        }

        if ($showDetail) {
            return array("processExists" => $processExits, "user" => $checkProcessUser, "circulating" => $checkProcessCirculating, "checkCicrulating" => $checkCicrulating, "writable" => $checkWritable);
        } else {
            $pass = false;

            if ($processExits) {
                if (empty($checkProcessUser) && $checkCicrulating && empty($checkWritable)) {
                    $pass = true;
                }
            }

            return $pass;
        }
    }

    public function fetchFreeOtherByFlowID($flowID)
    {
        $flow = $this->fetchByPk($flowID);

        if (!empty($flow)) {
            return intval($flow["freeother"]);
        } else {
            return 0;
        }
    }

    public function fetchFormIDByFlowID($flowID)
    {
        $flow = $this->fetchByPk($flowID);

        if (!empty($flow)) {
            return intval($flow["formid"]);
        } else {
            return 0;
        }
    }

    public function fetchNameByFlowId($flowId)
    {
        $flow = $this->fetchByPk($flowId);

        if (!empty($flow)) {
            return $flow["name"];
        } else {
            return "";
        }
    }

    public function fetchDeptIDByFlowID($flowID)
    {
        $flow = $this->fetchByPk($flowID);

        if (!empty($flow)) {
            return intval($flow["deptid"]);
        } else {
            return null;
        }
    }

    public function fetchAllByCatID($catID)
    {
        $criteria = array(
            "condition" => "catid = :catid",
            "params"    => array(":catid" => $catID),
            "order"     => "sort"
        );
        return $this->fetchAll($criteria);
    }

    public function fetchAllFreePermission()
    {
        $criteria = array("select" => "flowid,newuser", "condition" => "type = 2", "order" => "flowid");
        return $this->fetchAll($criteria);
    }

    public function fetchAllFlow()
    {
        $list = Ibos::app()->db->createCommand()->select("ft.*,fc.catid,fc.name as catname,fc.sort")->from("{{flow_type}} ft")->leftJoin("{{flow_category}} fc", "ft.catid = fc.catid")->order("fc.sort,ft.sort")->queryAll();
        return $list;
    }

    public function fetchAllOnOptlist($uid, $filter = true)
    {
        $temp = array();
        $list = $this->fetchAllFlow();

        if (!empty($list)) {
            while (list(, $type) = each($list)) {
                if (!$filter || WfCommonUtil::checkDeptPurv($uid, $type["deptid"])) {
                    $data = array("id" => $type["flowid"], "text" => $type["name"]);

                    if (!isset($temp[$type["catid"]])) {
                        $temp[$type["catid"]]["text"] = $type["catname"];
                        $temp[$type["catid"]]["children"] = array();
                    }

                    $temp[$type["catid"]]["children"][] = $data;
                }
            }
        }

        $result = array_merge(array(), $temp);
        return $result;
    }

    public function fetchAllFlowIDByFormID($formID)
    {
        $criteria = array("select" => "flowid", "condition" => sprintf("formid = %d", $formID));
        return $this->fetchAll($criteria);
    }

    public function fetchAllAssociatedFlowIDByFormID($formIDs)
    {
        $ids = (is_array($formIDs) ? implode(",", $formIDs) : $formIDs);
        $criteria = array("select" => "flowid", "condition" => sprintf("FIND_IN_SET(formid,'%s')", $ids));
        $result = $this->fetchAll($criteria);
        return ConvertUtil::getSubByKey($result, "flowid");
    }

    public function fetchAllByList($uid, $condition = "", $offset = 0, $limit = 10)
    {
        $list = Ibos::app()->db->createCommand()->select("ft.*,fft.formname")->from("{{flow_type}} ft")->leftJoin("{{flow_form_type}} fft", "ft.formid = fft.formid")->where($condition)->order("ft.sort,ft.flowid")->offset(intval($offset))->limit(intval($limit))->queryAll();
        $return = array();

        foreach ($list as $value) {
            if (WfCommonUtil::checkDeptPurv($uid, $value["deptid"], $value["catid"])) {
                $value["formname"] = StringUtil::filterCleanHtml($value["formname"]);
                $value["flowcount"] = FlowRun::model()->countAllByFlowId($value["flowid"]);
                $value["delcount"] = FlowRun::model()->countDelFlowByFlowId($value["flowid"]);
                $return[] = $value;
            }
        }

        return (array) $return;
    }

    public function countByList($condition = "")
    {
        $count = Ibos::app()->db->createCommand()->select("count(flowid)")->from("{{flow_type}} ft")->leftJoin("{{flow_form_type}} fft", "ft.formid = fft.formid")->where($condition)->queryScalar();
        return (int) $count;
    }

    public function delFlow($flowIds)
    {
        $ids = (is_array($flowIds) ? $flowIds : explode(",", $flowIds));
        $sqlCondition = sprintf("FIND_IN_SET(flowid,'%s')", implode(",", $ids));
        $this->clearFlow($ids);
        $uid = Ibos::app()->user->uid;

        foreach ($ids as $id) {
            $flow = $this->fetchByPk($id);
            $content = Ibos::lang("Del flow", "workflow.default", array("{flowName}" => $flow["name"]));
            FlowManageLog::model()->log($id, $flow["name"], $uid, 3, $content);
        }

        $delFlow = $this->deleteByPk($ids);
        FlowProcess::model()->deleteAll($sqlCondition);
        FlowRule::model()->deleteAll($sqlCondition);
        FlowPermission::model()->deleteAll($sqlCondition);

        if ($delFlow) {
            return true;
        } else {
            return false;
        }
    }

    public function clearFlow($flowIds)
    {
        $ids = (is_array($flowIds) ? $flowIds : explode(",", $flowIds));
        $count = 0;

        foreach ($ids as $id) {
            $flow = $this->fetchByPk($id);

            if (!empty($flow)) {
                $runs = FlowRun::model()->fetchAllByFlowId($id);

                if (!empty($runs)) {
                    $runIds = ConvertUtil::getSubByKey($runs, "runid");
                    WfHandleUtil::destroy($runIds);
                    $table = sprintf("{{flow_data_%d}}", $id);

                    if (WfCommonUtil::tableExists($table)) {
                        Ibos::app()->db->createCommand()->dropTable($table);
                    }

                    FlowRun::model()->deleteAllByAttributes(array("flowid" => $id));
                    $content = Ibos::lang("Clear flow", "workflow.default", array("{flowName}" => $flow["name"]));
                    FlowManageLog::model()->log($id, $flow["name"], Ibos::app()->user->uid, 3, $content);
                    $count++;
                }
            }
        }

        return $count;
    }
}
