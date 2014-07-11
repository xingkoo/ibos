<?php

class ICCategory
{
    /**
     * 分类表
     * @var string 
     */
    protected $_category = "";
    /**
     * 相关联的模块表
     * @var string
     */
    protected $_related = "";
    /**
     * 配置数组
     * @var array 
     * <pre>
     * array(
     *     'index' => 'catid',
     *     'parent' => 'pid',
     *     'name' => 'name',
     *     'sort' => 'sort',
     * );
     * </pre>
     */
    protected $_setting = array();
    /**
     * 默认分类表字段值
     * @var type 
     */
    protected $_default = array("index" => "catid", "parent" => "pid", "name" => "name", "sort" => "sort");

    public function __construct($category, $related = "", $setting = array())
    {
        $this->setCategory($category);
        $this->setRelated($related);
        $this->setSetting($setting);
        $this->init();
    }

    public function __get($name)
    {
        if (isset($this->_setting[strtolower($name)])) {
            return $this->_setting[$name];
        }

        throw new CException(Ibos::t("yii", "Property \"{class}.{property}\" is not defined.", array("{class}" => get_class($this), "{property}" => $name)));
    }

    public function __isset($name)
    {
        if (isset($this->_setting[strtolower($name)])) {
            return $this->_setting[$name] !== null;
        }

        return false;
    }

    public function setCategory($category)
    {
        $this->_category = null;

        if (class_exists($category)) {
            $this->_category = new $category();
        } else {
            throw new NotFoundException(Ibos::lang("Cannot find class", "error", array("{class}" => $category)));
        }
    }

    public function setRelated($related = "")
    {
        $this->_related = null;

        if (!empty($related)) {
            $this->_related = (class_exists($related) ? new $related() : null);
        }
    }

    public function setSetting($setting = array())
    {
        $this->_setting = CMap::mergeArray($this->_default, $setting);
    }

    public function add($pid, $name)
    {
        $sort = $this->sort;
        $parent = $this->parent;
        $catName = $this->name;
        $cond = array("select" => $sort, "order" => "`$sort` DESC");
        $sortRecord = $this->_category->fetch($cond);

        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord["sort"];
        }

        $newSortId = $sortId + 1;
        $status = $this->_category->add(array($sort => $newSortId, $parent => $pid, $catName => $name), true);
        $this->afterAdd();
        return $status;
    }

    public function delete($catid)
    {
        $clear = false;
        $ids = $this->fetchAllSubId($catid);
        $idStr = implode(",", array_unique(explode(",", trim($ids, ","))));

        if (empty($idStr)) {
            $idStr = $catid;
        } else {
            $idStr .= "," . $catid;
        }

        if (!is_null($this->_related)) {
            $count = $this->_related->count("`$this->index` IN ($idStr)");
            !$count && ($clear = true);
        } else {
            $clear = true;
        }

        if ($clear) {
            $status = $this->_category->deleteAll("FIND_IN_SET($this->index,'$idStr')");
            $this->afterDelete();
            return $status;
        } else {
            return false;
        }
    }

    public function edit($catid, $pid, $name)
    {
        $status = $this->_category->modify($catid, array($this->parent => $pid, $this->name => $name));
        $this->afterEdit();
        return $status;
    }

    public function move($action, $catid, $pid)
    {
        $sort = $this->sort;
        $parent = $this->parent;
        $index = $this->index;
        $sortRecord = $this->_category->fetch(array("select" => $sort, "condition" => "$index = '$catid'"));

        if (empty($sortRecord)) {
            $sortId = 0;
        } else {
            $sortId = $sortRecord["sort"];
        }

        if ($action == "moveup") {
            $where = " `$parent` = $pid AND $sort < $sortId ORDER BY `$sort` DESC";
        } elseif ($action == "movedown") {
            $where = " $parent = $pid AND $sort > $sortId ORDER BY `$parent` ASC";
        } else {
            $where = " 1 ";
        }

        $record = $this->_category->fetch(array("select" => "$index,$sort", "condition" => $where));

        if (!empty($record)) {
            $nextCatid = $record[$index];
            $nextSort = $record[$sort];
            $this->_category->modify($nextCatid, array($sort => $sortId));
            $this->_category->modify($catid, array($sort => $nextSort));
            $this->afterEdit();
            return true;
        }

        return false;
    }

    public function getData($condition = "")
    {
        $result = array();
        $sort = $this->sort;
        $index = $this->index;
        $data = $this->_category->fetchAll(array("condition" => $condition, "order" => "$sort ASC"));

        foreach ($data as $row) {
            $catid = $row[$index];
            $result[$catid] = $row;
        }

        return $result;
    }

    public function getAjaxCategory($data = array())
    {
        $return = array();
        $counter = 0;

        foreach ($data as $row) {
            $tmp = array();
            if (($counter == 0) && ($row[$this->parent] == "0")) {
                $tmp["open"] = 1;
                $counter++;
            }

            $tmp["id"] = $row[$this->index];
            $tmp["pId"] = $row[$this->parent];
            $tmp["name"] = $row[$this->name];
            $return[] = array_merge($tmp, $row);
        }

        return $return;
    }

    protected function init()
    {
        return true;
    }

    protected function afterEdit()
    {
    }

    protected function afterAdd()
    {
    }

    protected function afterDelete()
    {
    }

    protected function fetchAllParentId($catid)
    {
        $condition = "`$this->index` = $catid";
        $field = $this->parent;
        $idStr = "";
        $count = $this->_category->count($condition);

        if (0 < $count) {
            $record = $this->_category->fetchAll(array("select" => $field, "condition" => $condition));

            if (!empty($record)) {
                foreach ($record as $row) {
                    $idStr .= $row[$field] . "," . $this->fetchAllParentId($row[$field]);
                }
            }
        }

        return $idStr;
    }

    protected function fetchAllSubId($catid)
    {
        $condition = "`$this->parent` = $catid";
        $field = $this->index;
        $idStr = "";
        $count = $this->_category->count($condition);

        if (0 < $count) {
            $record = $this->_category->fetchAll(array("select" => $field, "condition" => $condition));

            if (!empty($record)) {
                foreach ($record as $row) {
                    $idStr .= $row[$field] . "," . $this->fetchAllSubId($row[$field]);
                }
            }
        }

        return $idStr;
    }
}
