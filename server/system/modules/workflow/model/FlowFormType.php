<?php

class FlowFormType extends ICModel
{
    protected $allowCache = true;

    public static function model($className = "FlowFormType")
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return "{{flow_form_type}}";
    }

    public function quickAdd($formName, $catId)
    {
        $data = array("formname" => StringUtil::filterCleanHtml($formName), "catid" => intval($catId), "printmodel" => "", "printmodelshort" => "", "script" => "", "css" => "");
        return $this->add($data, true);
    }

    public function getNextItemID($formID)
    {
        $criteria = array("select" => "itemmax", "condition" => "formid = " . intval($formID));
        $res = $this->fetch($criteria);
        $maxItem = (isset($res["itemmax"]) ? $res["itemmax"] + 1 : 1);
        $this->modify($formID, array("itemmax" => $maxItem));
        return $maxItem;
    }

    public function countByCondition($condition = "")
    {
        return (int) $this->count($condition);
    }

    public function fetchFormNameByFormId($formId)
    {
        $form = $this->fetchByPk(intval($formId));
        return $form ? $form["formname"] : "";
    }

    public function fetchAllOnOptListByUid($uid, $filter = true)
    {
        $temp = array();
        $list = $this->fetchAllForm();

        if (!empty($list)) {
            while (list(, $form) = each($list)) {
                if (!$filter || WfCommonUtil::checkDeptPurv($uid, $form["deptid"])) {
                    $data = array("id" => $form["formid"], "text" => $form["formname"]);

                    if (!$form["catid"]) {
                        $form["catid"] = 0;
                        $form["catname"] = "未分类";
                    }

                    if (!isset($temp[$form["catid"]])) {
                        $temp[$form["catid"]]["text"] = $form["catname"];
                        $temp[$form["catid"]]["children"] = array();
                    }

                    $temp[$form["catid"]]["children"][] = $data;
                }
            }
        }

        $result = array_merge(array(), $temp);
        return $result;
    }

    public function fetchAllForm()
    {
        $list = Ibos::app()->db->createCommand()->select("fft.*,fc.catid,fc.name as catname,fc.sort")->from("{{flow_form_type}} fft")->leftJoin("{{flow_category}} fc", "fft.catid = fc.catid")->order("fc.sort")->queryAll();
        return $list;
    }

    public function fetchAllByList($condition = "", $offset = 0, $limit = 10)
    {
        $list = Ibos::app()->db->createCommand()->select("GROUP_CONCAT(ft.`name` SEPARATOR ',') AS flow,ff.formid,ff.catid,ff.deptid,ff.formname")->from($this->tableName() . " ff")->leftJoin("{{flow_type}} ft", "ff.formid = ft.formid")->where($condition)->group("ff.formid")->limit($limit)->offset($offset)->queryAll();
        return $list;
    }

    public function del($ids)
    {
        $ids = (is_array($ids) ? $ids : explode(",", $ids));

        foreach ($ids as $id) {
            $this->deleteByPk($id);
            FlowFormVersion::model()->deleteAllByAttributes(array("formid" => $id));
        }
    }
}
