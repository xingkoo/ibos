<?php

class CDbCommand extends CComponent
{
	/**
	 * @var array the parameters (name=>value) to be bound to the current query.
	 * @since 1.1.6
	 */
	public $params = array();
	private $_connection;
	private $_text;
	private $_statement;
	private $_paramLog = array();
	private $_query;
	private $_fetchMode = array(PDO::FETCH_ASSOC);

	public function __construct(CDbConnection $connection, $query = NULL)
	{
		$this->_connection = $connection;

		if (is_array($query)) {
			foreach ($query as $name => $value ) {
				$this->$name = $value;
			}
		}
		else {
			$this->setText($query);
		}
	}

	public function __sleep()
	{
		$this->_statement = NULL;
		return array_keys(get_object_vars($this));
	}

	public function setFetchMode($mode)
	{
		$params = func_get_args();
		$this->_fetchMode = $params;
		return $this;
	}

	public function reset()
	{
		$this->_text = NULL;
		$this->_query = NULL;
		$this->_statement = NULL;
		$this->_paramLog = array();
		$this->params = array();
		return $this;
	}

	public function getText()
	{
		if (($this->_text == "") && !empty($this->_query)) {
			$this->setText($this->buildQuery($this->_query));
		}

		return $this->_text;
	}

	public function setText($value)
	{
		if (($this->_connection->tablePrefix !== NULL) && ($value != "")) {
			$this->_text = preg_replace("/{{(.*?)}}/", $this->_connection->tablePrefix . "\1", $value);
		}
		else {
			$this->_text = $value;
		}

		$this->cancel();
		return $this;
	}

	public function getConnection()
	{
		return $this->_connection;
	}

	public function getPdoStatement()
	{
		return $this->_statement;
	}

	public function prepare()
	{
		if ($this->_statement == NULL) {
			try {
				$this->_statement = $this->getConnection()->getPdoInstance($this->getText())->prepare($this->getText());
				$this->_paramLog = array();
			}
			catch (Exception $e) {
				Yii::log("Error in preparing SQL: " . $this->getText(), CLogger::LEVEL_ERROR, "system.db.CDbCommand");
				$errorInfo = ($e instanceof PDOException ? $e->errorInfo : NULL);
				throw new CDbException(Yii::t("yii", "CDbCommand failed to prepare the SQL statement: {error}", array("{error}" => $e->getMessage())), (int) $e->getCode(), $errorInfo);
			}
		}
	}

	public function cancel()
	{
		$this->_statement = NULL;
	}

	public function bindParam($name, &$value, $dataType = NULL, $length = NULL, $driverOptions = NULL)
	{
		$this->prepare();

		if ($dataType === NULL) {
			$this->_statement->bindParam($name, $value, $this->_connection->getPdoType(gettype($value)));
		}
		else if ($length === NULL) {
			$this->_statement->bindParam($name, $value, $dataType);
		}
		else if ($driverOptions === NULL) {
			$this->_statement->bindParam($name, $value, $dataType, $length);
		}
		else {
			$this->_statement->bindParam($name, $value, $dataType, $length, $driverOptions);
		}

		$this->_paramLog[$name] = &$value;
		return $this;
	}

	public function bindValue($name, $value, $dataType = NULL)
	{
		$this->prepare();

		if ($dataType === NULL) {
			$this->_statement->bindValue($name, $value, $this->_connection->getPdoType(gettype($value)));
		}
		else {
			$this->_statement->bindValue($name, $value, $dataType);
		}

		$this->_paramLog[$name] = $value;
		return $this;
	}

	public function bindValues($values)
	{
		$this->prepare();

		foreach ($values as $name => $value ) {
			$this->_statement->bindValue($name, $value, $this->_connection->getPdoType(gettype($value)));
			$this->_paramLog[$name] = $value;
		}

		return $this;
	}

	public function execute($params = array())
	{
		if ($this->_connection->enableParamLogging && (($pars = array_merge($this->_paramLog, $params)) !== array())) {
			$p = array();

			foreach ($pars as $name => $value ) {
				$p[$name] = $name . "=" . var_export($value, true);
			}

			$par = ". Bound with " . implode(", ", $p);
		}
		else {
			$par = "";
		}

		Yii::trace("Executing SQL: " . $this->getText() . $par, "system.db.CDbCommand");

		try {
			if ($this->_connection->enableProfiling) {
				Yii::beginProfile("system.db.CDbCommand.execute(" . $this->getText() . $par . ")", "system.db.CDbCommand.execute");
			}

			$this->prepare();

			if ($params === array()) {
				$this->_statement->execute();
			}
			else {
				$this->_statement->execute($params);
			}

			$n = $this->_statement->rowCount();

			if ($this->_connection->enableProfiling) {
				Yii::endProfile("system.db.CDbCommand.execute(" . $this->getText() . $par . ")", "system.db.CDbCommand.execute");
			}

			return $n;
		}
		catch (Exception $e) {
			if ($this->_connection->enableProfiling) {
				Yii::endProfile("system.db.CDbCommand.execute(" . $this->getText() . $par . ")", "system.db.CDbCommand.execute");
			}

			$errorInfo = ($e instanceof PDOException ? $e->errorInfo : NULL);
			$message = $e->getMessage();
			Yii::log(Yii::t("yii", "CDbCommand::execute() failed: {error}. The SQL statement executed was: {sql}.", array("{error}" => $message, "{sql}" => $this->getText() . $par)), CLogger::LEVEL_ERROR, "system.db.CDbCommand");

			if (YII_DEBUG) {
				$message .= ". The SQL statement executed was: " . $this->getText() . $par;
			}

			throw new CDbException(Yii::t("yii", "CDbCommand failed to execute the SQL statement: {error}", array("{error}" => $message)), (int) $e->getCode(), $errorInfo);
		}
	}

	public function query($params = array())
	{
		return $this->queryInternal("", 0, $params);
	}

	public function queryAll($fetchAssociative = true, $params = array())
	{
		return $this->queryInternal("fetchAll", $fetchAssociative ? $this->_fetchMode : PDO::FETCH_NUM, $params);
	}

	public function queryRow($fetchAssociative = true, $params = array())
	{
		return $this->queryInternal("fetch", $fetchAssociative ? $this->_fetchMode : PDO::FETCH_NUM, $params);
	}

	public function queryScalar($params = array())
	{
		$result = $this->queryInternal("fetchColumn", 0, $params);
		if (is_resource($result) && (get_resource_type($result) === "stream")) {
			return stream_get_contents($result);
		}
		else {
			return $result;
		}
	}

	public function queryColumn($params = array())
	{
		return $this->queryInternal("fetchAll", array(PDO::FETCH_COLUMN, 0), $params);
	}

	private function queryInternal($method, $mode, $params = array())
	{
		$params = array_merge($this->params, $params);
		if ($this->_connection->enableParamLogging && (($pars = array_merge($this->_paramLog, $params)) !== array())) {
			$p = array();

			foreach ($pars as $name => $value ) {
				$p[$name] = $name . "=" . var_export($value, true);
			}

			$par = ". Bound with " . implode(", ", $p);
		}
		else {
			$par = "";
		}

		Yii::trace("Querying SQL: " . $this->getText() . $par, "system.db.CDbCommand");
		if ((0 < $this->_connection->queryCachingCount) && ($method !== "") && (0 < $this->_connection->queryCachingDuration) && ($this->_connection->queryCacheID !== false) && (($cache = Yii::app()->getComponent($this->_connection->queryCacheID)) !== NULL)) {
			$this->_connection->queryCachingCount--;
			$cacheKey = "yii:dbquery" . $this->_connection->connectionString . ":" . $this->_connection->username;
			$cacheKey .= ":" . $this->getText() . ":" . serialize(array_merge($this->_paramLog, $params));

			if (($result = $cache->get($cacheKey)) !== false) {
				Yii::trace("Query result found in cache", "system.db.CDbCommand");
				return $result[0];
			}
		}

		try {
			if ($this->_connection->enableProfiling) {
				Yii::beginProfile("system.db.CDbCommand.query(" . $this->getText() . $par . ")", "system.db.CDbCommand.query");
			}

			$this->prepare();

			if ($params === array()) {
				$this->_statement->execute();
			}
			else {
				$this->_statement->execute($params);
			}

			if ($method === "") {
				$result = new CDbDataReader($this);
			}
			else {
				$mode = (array) $mode;
				call_user_func_array(array($this->_statement, "setFetchMode"), $mode);
				$result = $this->_statement->{$method}();
				$this->_statement->closeCursor();
			}

			if ($this->_connection->enableProfiling) {
				Yii::endProfile("system.db.CDbCommand.query(" . $this->getText() . $par . ")", "system.db.CDbCommand.query");
			}

			if (isset($cache) && isset($cacheKey)) {
				$cache->set($cacheKey, array($result), $this->_connection->queryCachingDuration, $this->_connection->queryCachingDependency);
			}

			return $result;
		}
		catch (Exception $e) {
			if ($this->_connection->enableProfiling) {
				Yii::endProfile("system.db.CDbCommand.query(" . $this->getText() . $par . ")", "system.db.CDbCommand.query");
			}

			$errorInfo = ($e instanceof PDOException ? $e->errorInfo : NULL);
			$message = $e->getMessage();
			Yii::log(Yii::t("yii", "CDbCommand::{method}() failed: {error}. The SQL statement executed was: {sql}.", array("{method}" => $method, "{error}" => $message, "{sql}" => $this->getText() . $par)), CLogger::LEVEL_ERROR, "system.db.CDbCommand");

			if (YII_DEBUG) {
				$message .= ". The SQL statement executed was: " . $this->getText() . $par;
			}

			throw new CDbException(Yii::t("yii", "CDbCommand failed to execute the SQL statement: {error}", array("{error}" => $message)), (int) $e->getCode(), $errorInfo);
		}
	}

	public function buildQuery($query)
	{
		$sql = (!empty($query["distinct"]) ? "SELECT DISTINCT" : "SELECT");
		$sql .= " " . (!empty($query["select"]) ? $query["select"] : "*");

		if (!empty($query["from"])) {
			$sql .= "\nFROM " . $query["from"];
		}
		else {
			throw new CDbException(Yii::t("yii", "The DB query must contain the \"from\" portion."));
		}

		if (!empty($query["join"])) {
			$sql .= "\n" . (is_array($query["join"]) ? implode("\n", $query["join"]) : $query["join"]);
		}

		if (!empty($query["where"])) {
			$sql .= "\nWHERE " . $query["where"];
		}

		if (!empty($query["group"])) {
			$sql .= "\nGROUP BY " . $query["group"];
		}

		if (!empty($query["having"])) {
			$sql .= "\nHAVING " . $query["having"];
		}

		if (!empty($query["union"])) {
			$sql .= "\nUNION (\n" . (is_array($query["union"]) ? implode("\n) UNION (\n", $query["union"]) : $query["union"]) . ")";
		}

		if (!empty($query["order"])) {
			$sql .= "\nORDER BY " . $query["order"];
		}

		$limit = (isset($query["limit"]) ? (int) $query["limit"] : -1);
		$offset = (isset($query["offset"]) ? (int) $query["offset"] : -1);
		if ((0 <= $limit) || (0 < $offset)) {
			$sql = $this->_connection->getCommandBuilder()->applyLimit($sql, $limit, $offset);
		}

		return $sql;
	}

	public function select($columns = "*", $option = "")
	{
		if (is_string($columns) && (strpos($columns, "(") !== false)) {
			$this->_query["select"] = $columns;
		}
		else {
			if (!is_array($columns)) {
				$columns = preg_split("/\s*,\s*/", trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			}

			foreach ($columns as $i => $column ) {
				if (is_object($column)) {
					$columns[$i] = (string) $column;
				}
				else if (strpos($column, "(") === false) {
					if (preg_match("/^(.*?)(?i:\s+as\s+|\s+)(.*)$/", $column, $matches)) {
						$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . " AS " . $this->_connection->quoteColumnName($matches[2]);
					}
					else {
						$columns[$i] = $this->_connection->quoteColumnName($column);
					}
				}
			}

			$this->_query["select"] = implode(", ", $columns);
		}

		if ($option != "") {
			$this->_query["select"] = $option . " " . $this->_query["select"];
		}

		return $this;
	}

	public function getSelect()
	{
		return isset($this->_query["select"]) ? $this->_query["select"] : "";
	}

	public function setSelect($value)
	{
		$this->select($value);
	}

	public function selectDistinct($columns = "*")
	{
		$this->_query["distinct"] = true;
		return $this->select($columns);
	}

	public function getDistinct()
	{
		return isset($this->_query["distinct"]) ? $this->_query["distinct"] : false;
	}

	public function setDistinct($value)
	{
		$this->_query["distinct"] = $value;
	}

	public function from($tables)
	{
		if (is_string($tables) && (strpos($tables, "(") !== false)) {
			$this->_query["from"] = $tables;
		}
		else {
			if (!is_array($tables)) {
				$tables = preg_split("/\s*,\s*/", trim($tables), -1, PREG_SPLIT_NO_EMPTY);
			}

			foreach ($tables as $i => $table ) {
				if (strpos($table, "(") === false) {
					if (preg_match("/^(.*?)(?i:\s+as\s+|\s+)(.*)$/", $table, $matches)) {
						$tables[$i] = $this->_connection->quoteTableName($matches[1]) . " " . $this->_connection->quoteTableName($matches[2]);
					}
					else {
						$tables[$i] = $this->_connection->quoteTableName($table);
					}
				}
			}

			$this->_query["from"] = implode(", ", $tables);
		}

		return $this;
	}

	public function getFrom()
	{
		return isset($this->_query["from"]) ? $this->_query["from"] : "";
	}

	public function setFrom($value)
	{
		$this->from($value);
	}

	public function where($conditions, $params = array())
	{
		$this->_query["where"] = $this->processConditions($conditions);

		foreach ($params as $name => $value ) {
			$this->params[$name] = $value;
		}

		return $this;
	}

	public function andWhere($conditions, $params = array())
	{
		if (isset($this->_query["where"])) {
			$this->_query["where"] = $this->processConditions(array("AND", $this->_query["where"], $conditions));
		}
		else {
			$this->_query["where"] = $this->processConditions($conditions);
		}

		foreach ($params as $name => $value ) {
			$this->params[$name] = $value;
		}

		return $this;
	}

	public function orWhere($conditions, $params = array())
	{
		if (isset($this->_query["where"])) {
			$this->_query["where"] = $this->processConditions(array("OR", $this->_query["where"], $conditions));
		}
		else {
			$this->_query["where"] = $this->processConditions($conditions);
		}

		foreach ($params as $name => $value ) {
			$this->params[$name] = $value;
		}

		return $this;
	}

	public function getWhere()
	{
		return isset($this->_query["where"]) ? $this->_query["where"] : "";
	}

	public function setWhere($value)
	{
		$this->where($value);
	}

	public function join($table, $conditions, $params = array())
	{
		return $this->joinInternal("join", $table, $conditions, $params);
	}

	public function getJoin()
	{
		return isset($this->_query["join"]) ? $this->_query["join"] : "";
	}

	public function setJoin($value)
	{
		$this->_query["join"] = $value;
	}

	public function leftJoin($table, $conditions, $params = array())
	{
		return $this->joinInternal("left join", $table, $conditions, $params);
	}

	public function rightJoin($table, $conditions, $params = array())
	{
		return $this->joinInternal("right join", $table, $conditions, $params);
	}

	public function crossJoin($table)
	{
		return $this->joinInternal("cross join", $table);
	}

	public function naturalJoin($table)
	{
		return $this->joinInternal("natural join", $table);
	}

	public function group($columns)
	{
		if (is_string($columns) && (strpos($columns, "(") !== false)) {
			$this->_query["group"] = $columns;
		}
		else {
			if (!is_array($columns)) {
				$columns = preg_split("/\s*,\s*/", trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			}

			foreach ($columns as $i => $column ) {
				if (is_object($column)) {
					$columns[$i] = (string) $column;
				}
				else if (strpos($column, "(") === false) {
					$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}

			$this->_query["group"] = implode(", ", $columns);
		}

		return $this;
	}

	public function getGroup()
	{
		return isset($this->_query["group"]) ? $this->_query["group"] : "";
	}

	public function setGroup($value)
	{
		$this->group($value);
	}

	public function having($conditions, $params = array())
	{
		$this->_query["having"] = $this->processConditions($conditions);

		foreach ($params as $name => $value ) {
			$this->params[$name] = $value;
		}

		return $this;
	}

	public function getHaving()
	{
		return isset($this->_query["having"]) ? $this->_query["having"] : "";
	}

	public function setHaving($value)
	{
		$this->having($value);
	}

	public function order($columns)
	{
		if (is_string($columns) && (strpos($columns, "(") !== false)) {
			$this->_query["order"] = $columns;
		}
		else {
			if (!is_array($columns)) {
				$columns = preg_split("/\s*,\s*/", trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			}

			foreach ($columns as $i => $column ) {
				if (is_object($column)) {
					$columns[$i] = (string) $column;
				}
				else if (strpos($column, "(") === false) {
					if (preg_match("/^(.*?)\s+(asc|desc)$/i", $column, $matches)) {
						$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . " " . strtoupper($matches[2]);
					}
					else {
						$columns[$i] = $this->_connection->quoteColumnName($column);
					}
				}
			}

			$this->_query["order"] = implode(", ", $columns);
		}

		return $this;
	}

	public function getOrder()
	{
		return isset($this->_query["order"]) ? $this->_query["order"] : "";
	}

	public function setOrder($value)
	{
		$this->order($value);
	}

	public function limit($limit, $offset = NULL)
	{
		$this->_query["limit"] = (int) $limit;

		if ($offset !== NULL) {
			$this->offset($offset);
		}

		return $this;
	}

	public function getLimit()
	{
		return isset($this->_query["limit"]) ? $this->_query["limit"] : -1;
	}

	public function setLimit($value)
	{
		$this->limit($value);
	}

	public function offset($offset)
	{
		$this->_query["offset"] = (int) $offset;
		return $this;
	}

	public function getOffset()
	{
		return isset($this->_query["offset"]) ? $this->_query["offset"] : -1;
	}

	public function setOffset($value)
	{
		$this->offset($value);
	}

	public function union($sql)
	{
		if (isset($this->_query["union"]) && is_string($this->_query["union"])) {
			$this->_query["union"] = array($this->_query["union"]);
		}

		$this->_query["union"][] = $sql;
		return $this;
	}

	public function getUnion()
	{
		return isset($this->_query["union"]) ? $this->_query["union"] : "";
	}

	public function setUnion($value)
	{
		$this->_query["union"] = $value;
	}

	public function insert($table, $columns)
	{
		$params = array();
		$names = array();
		$placeholders = array();

		foreach ($columns as $name => $value ) {
			$names[] = $this->_connection->quoteColumnName($name);

			if ($value instanceof CDbExpression) {
				$placeholders[] = $value->expression;

				foreach ($value->params as $n => $v ) {
					$params[$n] = $v;
				}
			}
			else {
				$placeholders[] = ":" . $name;
				$params[":" . $name] = $value;
			}
		}

		$sql = "INSERT INTO " . $this->_connection->quoteTableName($table) . " (" . implode(", ", $names) . ") VALUES (" . implode(", ", $placeholders) . ")";
		return $this->setText($sql)->execute($params);
	}

	public function update($table, $columns, $conditions = "", $params = array())
	{
		$lines = array();

		foreach ($columns as $name => $value ) {
			if ($value instanceof CDbExpression) {
				$lines[] = $this->_connection->quoteColumnName($name) . "=" . $value->expression;

				foreach ($value->params as $n => $v ) {
					$params[$n] = $v;
				}
			}
			else {
				$lines[] = $this->_connection->quoteColumnName($name) . "=:" . $name;
				$params[":" . $name] = $value;
			}
		}

		$sql = "UPDATE " . $this->_connection->quoteTableName($table) . " SET " . implode(", ", $lines);

		if (($where = $this->processConditions($conditions)) != "") {
			$sql .= " WHERE " . $where;
		}

		return $this->setText($sql)->execute($params);
	}

	public function delete($table, $conditions = "", $params = array())
	{
		$sql = "DELETE FROM " . $this->_connection->quoteTableName($table);

		if (($where = $this->processConditions($conditions)) != "") {
			$sql .= " WHERE " . $where;
		}

		return $this->setText($sql)->execute($params);
	}

	public function createTable($table, $columns, $options = NULL)
	{
		return $this->setText($this->getConnection()->getSchema()->createTable($table, $columns, $options))->execute();
	}

	public function renameTable($table, $newName)
	{
		return $this->setText($this->getConnection()->getSchema()->renameTable($table, $newName))->execute();
	}

	public function dropTable($table)
	{
		return $this->setText($this->getConnection()->getSchema()->dropTable($table))->execute();
	}

	public function truncateTable($table)
	{
		$schema = $this->getConnection()->getSchema();
		$n = $this->setText($schema->truncateTable($table))->execute();

		if (strncasecmp($this->getConnection()->getDriverName(), "sqlite", 6) === 0) {
			$schema->resetSequence($schema->getTable($table));
		}

		return $n;
	}

	public function addColumn($table, $column, $type)
	{
		return $this->setText($this->getConnection()->getSchema()->addColumn($table, $column, $type))->execute();
	}

	public function dropColumn($table, $column)
	{
		return $this->setText($this->getConnection()->getSchema()->dropColumn($table, $column))->execute();
	}

	public function renameColumn($table, $name, $newName)
	{
		return $this->setText($this->getConnection()->getSchema()->renameColumn($table, $name, $newName))->execute();
	}

	public function alterColumn($table, $column, $type)
	{
		return $this->setText($this->getConnection()->getSchema()->alterColumn($table, $column, $type))->execute();
	}

	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = NULL, $update = NULL)
	{
		return $this->setText($this->getConnection()->getSchema()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update))->execute();
	}

	public function dropForeignKey($name, $table)
	{
		return $this->setText($this->getConnection()->getSchema()->dropForeignKey($name, $table))->execute();
	}

	public function createIndex($name, $table, $column, $unique = false)
	{
		return $this->setText($this->getConnection()->getSchema()->createIndex($name, $table, $column, $unique))->execute();
	}

	public function dropIndex($name, $table)
	{
		return $this->setText($this->getConnection()->getSchema()->dropIndex($name, $table))->execute();
	}

	private function processConditions($conditions)
	{
		if (!is_array($conditions)) {
			return $conditions;
		}
		else if ($conditions === array()) {
			return "";
		}

		$n = count($conditions);
		$operator = strtoupper($conditions[0]);
		if (($operator === "OR") || ($operator === "AND")) {
			$parts = array();

			for ($i = 1; $i < $n; ++$i) {
				$condition = $this->processConditions($conditions[$i]);

				if ($condition !== "") {
					$parts[] = "(" . $condition . ")";
				}
			}

			return $parts === array() ? "" : implode(" " . $operator . " ", $parts);
		}

		if (!isset($conditions[1]) && isset($conditions[2])) {
			return "";
		}

		$column = $conditions[1];

		if (strpos($column, "(") === false) {
			$column = $this->_connection->quoteColumnName($column);
		}

		$values = $conditions[2];

		if (!is_array($values)) {
			$values = array($values);
		}

		if (($operator === "IN") || ($operator === "NOT IN")) {
			if ($values === array()) {
				return $operator === "IN" ? "0=1" : "";
			}

			foreach ($values as $i => $value ) {
				if (is_string($value)) {
					$values[$i] = $this->_connection->quoteValue($value);
				}
				else {
					$values[$i] = (string) $value;
				}
			}

			return $column . " " . $operator . " (" . implode(", ", $values) . ")";
		}

		if (($operator === "LIKE") || ($operator === "NOT LIKE") || ($operator === "OR LIKE") || ($operator === "OR NOT LIKE")) {
			if ($values === array()) {
				return ($operator === "LIKE") || ($operator === "OR LIKE") ? "0=1" : "";
			}

			if (($operator === "LIKE") || ($operator === "NOT LIKE")) {
				$andor = " AND ";
			}
			else {
				$andor = " OR ";
				$operator = ($operator === "OR LIKE" ? "LIKE" : "NOT LIKE");
			}

			$expressions = array();

			foreach ($values as $value ) {
				$expressions[] = $column . " " . $operator . " " . $this->_connection->quoteValue($value);
			}

			return implode($andor, $expressions);
		}

		throw new CDbException(Yii::t("yii", "Unknown operator \"{operator}\".", array("{operator}" => $operator)));
	}

	private function joinInternal($type, $table, $conditions = "", $params = array())
	{
		if (strpos($table, "(") === false) {
			if (preg_match("/^(.*?)(?i:\s+as\s+|\s+)(.*)$/", $table, $matches)) {
				$table = $this->_connection->quoteTableName($matches[1]) . " " . $this->_connection->quoteTableName($matches[2]);
			}
			else {
				$table = $this->_connection->quoteTableName($table);
			}
		}

		$conditions = $this->processConditions($conditions);

		if ($conditions != "") {
			$conditions = " ON " . $conditions;
		}

		if (isset($this->_query["join"]) && is_string($this->_query["join"])) {
			$this->_query["join"] = array($this->_query["join"]);
		}

		$this->_query["join"][] = strtoupper($type) . " " . $table . $conditions;

		foreach ($params as $name => $value ) {
			$this->params[$name] = $value;
		}

		return $this;
	}

	public function addPrimaryKey($name, $table, $columns)
	{
		return $this->setText($this->getConnection()->getSchema()->addPrimaryKey($name, $table, $columns))->execute();
	}

	public function dropPrimaryKey($name, $table)
	{
		return $this->setText($this->getConnection()->getSchema()->dropPrimaryKey($name, $table))->execute();
	}
}


?>
