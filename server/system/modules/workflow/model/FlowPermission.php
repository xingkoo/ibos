<?php

class FlowPermission extends ICModel
{
    private $_typeLangMap = array("Flow purv all", "Flow purv manager", "Flow purv monitoring", "Flow purv search", "Flow purv edit", "Flow purv review");
    private $_scopeLangMap = array("selforg" => "Flow scope selforg", "alldept" => "Flow scope alldept", "selfdept" => "Flow scope selfdept", "selfdeptall" => "Flow scope selfdeptall", "custom" => "Flow scope custom");

    public static function model($className = "FlowPermission")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_permission}}";
    }

    public function fetchPermission($uid, $flowId, $type = array(0, 1, 2, 3))
    {
        $user = User::model()->fetchByUid($uid);
        $condition = sprintf("FIND_IN_SET(type,'%s') AND flowid = %d", implode(",", $type), $flowId);
        $per = false;
        $result = Ibos::app()->db->createCommand()->select("*")->from($this->tableName())->where($condition)->queryAll();

        foreach ($result as $rs) {
            if (($rs["deptid"] == "alldept") || StringUtil::findIn($user["uid"], $rs["uid"]) || StringUtil::findIn($user["alldeptid"], $rs["deptid"]) || StringUtil::findIn($user["allposid"], $rs["positionid"])) {
                $per = $rs["type"];
            }
        }

        return $per !== false ? intval($per) : false;
    }

    public function fetchAllByPer($per = array(0, 1, 2, 3))
    {
        $permissions = implode(",", $per);
        return $this->fetchAll(sprintf("FIND_IN_SET(type,'%s')", $permissions));
    }

    public function fetchAllListByFlowId($flowId)
    {
        $list = $this->fetchAllByFlowId($flowId);

        foreach ($list as &$per) {
            $per["userName"] = (!empty($per["uid"]) ? User::model()->fetchRealnamesByUids($per["uid"]) : "");

            if (!empty($per["deptid"])) {
                if ($per["deptid"] == "alldept") {
                    $per["deptName"] = "全体部门";
                } else {
                    $per["deptName"] = Department::model()->fetchDeptNameByDeptId($per["deptid"]);
                }
            } else {
                $per["deptName"] = "";
            }

            $per["posName"] = (!empty($per["positionid"]) ? Position::model()->fetchPosNameByPosId($per["positionid"]) : "");
            $per["typeName"] = Ibos::lang($this->_typeLangMap[$per["type"]], "workflow.default");

            if (array_key_exists($per["scope"], $this->_scopeLangMap)) {
                $per["scopeName"] = Ibos::lang($this->_scopeLangMap[$per["scope"]], "workflow.default");
            } else {
                $per["scopeName"] = Department::model()->fetchDeptNameByDeptId($per["scope"]);
            }
        }

        return $list;
    }

    public function fetchAllByFlowId($flowId)
    {
        return $this->fetchAllByAttributes(array("flowid" => intval($flowId)));
    }
}
