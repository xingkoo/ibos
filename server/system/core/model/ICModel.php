<?php

class ICModel extends CActiveRecord
{
    /**
     * 是否允许缓存
     * @var mixed 
     */
    protected $allowCache;
    /**
     * 缓存生命周期
     * @var mixed 
     */
    protected $cacheLife;

    public function init()
    {
        $cacheLife = ($this->cacheLife !== null ? $this->cacheLife : null);
        if ($cacheLife && CacheUtil::check()) {
            $this->cacheLife = $cacheLife;
            $this->allowCache = true;
        }
    }

    public function fetch($condition = "", $params = array())
    {
        $result = array();
        $record = $this->find($condition, $params);

        if (!empty($record)) {
            $result = $record->attributes;
        }

        return $result;
    }

    public function fetchByPk($pk)
    {
        $record = $this->fetchCache($pk);

        if ($record === false) {
            $object = $this->findByPk($pk);

            if (is_object($object)) {
                $record = $object->attributes;

                if ($this->getIsAllowCache()) {
                    CacheUtil::set($this->getCacheKey($pk), $record, $this->cacheLife);
                }
            } else {
                $record = null;
            }
        }

        return $record;
    }

    public function fetchByAttributes($attributes, $condition = "", $params = array())
    {
        $result = array();
        $record = $this->findByAttributes($attributes, $condition, $params);

        if (!empty($record)) {
            $result = $record->attributes;
        }

        return $result;
    }

    public function fetchAll($condition = "", $params = array())
    {
        $result = array();
        $records = $this->findAll($condition, $params);

        if (!empty($records)) {
            foreach ($records as $record) {
                $result[] = $record->attributes;
            }
        }

        return $result;
    }

    public function fetchAllByAttributes($attributes, $condition = "", $params = array())
    {
        $result = array();
        $records = $this->findAllByAttributes($attributes, $condition, $params);

        if (!empty($records)) {
            foreach ($records as $record) {
                $result[] = $record->attributes;
            }
        }

        return $result;
    }

    public function fetchAllSortByPk($pk, $condition = "", $params = array())
    {
        $result = array();
        $records = $this->findAll($condition, $params);

        if (!empty($records)) {
            foreach ($records as $record) {
                $row = $record->attributes;
                $result[$row[$pk]] = $row;
            }
        }

        return $result;
    }

    public function fetchAllByPk($pks)
    {
        $record = $this->fetchCaches($pks);
        if (($record === false) || (count($pks) != count($record))) {
            if (is_array($record) && !empty($record)) {
                $pks = array_diff($pks, array_keys($record));
            }

            if ($record === false) {
                $record = array();
            }

            if (!empty($pks)) {
                $records = $this->findAllByPk(array_merge($pks));

                if (!empty($records)) {
                    foreach ($records as $rec) {
                        $pk = $rec->getPrimaryKey();
                        $record[$pk] = $rec->attributes;

                        if ($this->getIsAllowCache()) {
                            CacheUtil::set($this->getCacheKey($pk), $rec->attributes, $this->cacheLife);
                        }
                    }
                }
            }
        }

        return $record;
    }

    public function add($attributes, $returnNewId = false, $replace = false)
    {
        $attrs = $this->getAttributes();
        $schema = $this->getTableSchema();

        foreach ($attrs as $attr => $val) {
            if (isset($attributes[$attr])) {
                $this->setAttribute($attr, $attributes[$attr]);
            } else {
                $column = $schema->getColumn($attr);

                if (!is_null($column)) {
                    if ($column->isPrimaryKey) {
                        continue;
                    }

                    $this->setAttribute($attr, (string) $column->defaultValue);
                }
            }
        }

        if ($replace) {
            if ($this->refresh()) {
                $this->setIsNewRecord(false);
            } else {
                $this->setIsNewRecord(true);
            }
        } else {
            $this->setIsNewRecord(true);
        }

        $status = $this->save();
        $lastInsert = $this->getPrimaryKey();
        $this->setOldPrimaryKey(null);
        $this->setPrimaryKey(null);

        if ($returnNewId) {
            return $lastInsert;
        } else {
            return $status;
        }
    }

    public function modify($pk, $attributes)
    {
        if ($this->beforeSave()) {
            $result = $this->updateByPk($pk, $attributes);
            return $result;
        }
    }

    public function remove($pk)
    {
        assert("isset(\$pk)");
        $this->setPrimaryKey($pk);
        $result = $this->delete();
        return $result;
    }

    public function getIsAllowCache()
    {
        return (bool) $this->allowCache;
    }

    public function getMaxId($pk = "id")
    {
        $result = 0;
        $record = $this->find(array("select" => "COUNT($pk) as $pk"));

        if (!empty($record)) {
            $result = intval($record->$pk);
        }

        return $result;
    }

    public function updateByPk($pk, $attributes, $condition = "", $params = array())
    {
        if ($this->getIsAllowCache()) {
            $pk = (is_array($pk) ? $pk : explode(",", $pk));

            foreach ($pk as $id) {
                $key = $this->getCacheKey($id);
                CacheUtil::rm($key);
            }
        }

        $counter = parent::updateByPk($pk, $attributes, $condition, $params);
        $this->afterSave();
        return $counter;
    }

    public function updateAll($attributes, $condition = "", $params = array())
    {
        $counter = parent::updateAll($attributes, $condition, $params);
        $this->afterSave();
        return $counter;
    }

    public function deleteByPk($pk, $condition = "", $params = array())
    {
        if ($this->getIsAllowCache()) {
            $ids = (is_array($pk) ? $pk : explode(",", $pk));

            foreach ($ids as $id) {
                if (!empty($id)) {
                    CacheUtil::rm($this->getCacheKey($id));
                }
            }
        }

        return parent::deleteByPk($pk, $condition, $params);
    }

    public function create($data = "")
    {
        if (empty($data)) {
            $data = $_POST;
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (empty($data) || !is_array($data)) {
            throw new DataException(Ibos::t("Data type invalid", "error"));
        }

        $fields = $this->getAttributes();

        if (isset($fields)) {
            foreach ($data as $key => $val) {
                if (!array_key_exists($key, $fields)) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    protected function beforeDelete()
    {
        if ($this->getIsAllowCache()) {
            $key = $this->getCacheKey();
            CacheUtil::rm($key);
        }

        return parent::beforeDelete();
    }

    protected function fetchCache($pk)
    {
        $resource = false;

        if ($this->getIsAllowCache()) {
            $resource = CacheUtil::get($this->getCacheKey($pk));
        }

        return $resource;
    }

    protected function fetchCaches($pks)
    {
        $return = array();

        if ($this->getIsAllowCache()) {
            foreach ($pks as $pk) {
                $data = CacheUtil::get($this->getCacheKey($pk));

                if ($data !== false) {
                    $return[$pk] = $data;
                }
            }
        }

        return !empty($return) ? $return : false;
    }

    protected function getModelClass()
    {
        $modelClass = get_class($this);
        return $modelClass;
    }

    protected function getCacheKey($pk = "")
    {
        $modelClass = $this->getModelClass();

        if (empty($pk)) {
            $modelPk = $this->getPrimaryKey();

            if (!$modelPk) {
                throw new DbException(Ibos::lang("Cache must have a primary key", "error"));
            }

            $pk = $modelPk;
        }

        $key = strtolower($modelClass) . "_" . $pk;
        return $key;
    }
}
