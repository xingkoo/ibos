<?php

class CDbConnection extends CApplicationComponent
{
	/**
	 * @var string The Data Source Name, or DSN, contains the information required to connect to the database.
	 * @see http://www.php.net/manual/en/function.PDO-construct.php
	 *
	 * Note that if you're using GBK or BIG5 then it's highly recommended to
	 * update to PHP 5.3.6+ and to specify charset via DSN like
	 * 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
	 */
	public $connectionString;
	/**
	 * @var string the username for establishing DB connection. Defaults to empty string.
	 */
	public $username = "";
	/**
	 * @var string the password for establishing DB connection. Defaults to empty string.
	 */
	public $password = "";
	/**
	 * @var integer number of seconds that table metadata can remain valid in cache.
	 * Use 0 or negative value to indicate not caching schema.
	 * If greater than 0 and the primary cache is enabled, the table metadata will be cached.
	 * @see schemaCachingExclude
	 */
	public $schemaCachingDuration = 0;
	/**
	 * @var array list of tables whose metadata should NOT be cached. Defaults to empty array.
	 * @see schemaCachingDuration
	 */
	public $schemaCachingExclude = array();
	/**
	 * @var string the ID of the cache application component that is used to cache the table metadata.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching table metadata.
	 */
	public $schemaCacheID = "cache";
	/**
	 * @var integer number of seconds that query results can remain valid in cache.
	 * Use 0 or negative value to indicate not caching query results (the default behavior).
	 *
	 * In order to enable query caching, this property must be a positive
	 * integer and {@link queryCacheID} must point to a valid cache component ID.
	 *
	 * The method {@link cache()} is provided as a convenient way of setting this property
	 * and {@link queryCachingDependency} on the fly.
	 *
	 * @see cache
	 * @see queryCachingDependency
	 * @see queryCacheID
	 * @since 1.1.7
	 */
	public $queryCachingDuration = 0;
	/**
	 * @var CCacheDependency the dependency that will be used when saving query results into cache.
	 * @see queryCachingDuration
	 * @since 1.1.7
	 */
	public $queryCachingDependency;
	/**
	 * @var integer the number of SQL statements that need to be cached next.
	 * If this is 0, then even if query caching is enabled, no query will be cached.
	 * Note that each time after executing a SQL statement (whether executed on DB server or fetched from
	 * query cache), this property will be reduced by 1 until 0.
	 * @since 1.1.7
	 */
	public $queryCachingCount = 0;
	/**
	 * @var string the ID of the cache application component that is used for query caching.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable query caching.
	 * @since 1.1.7
	 */
	public $queryCacheID = "cache";
	/**
	 * @var boolean whether the database connection should be automatically established
	 * the component is being initialized. Defaults to true. Note, this property is only
	 * effective when the CDbConnection object is used as an application component.
	 */
	public $autoConnect = true;
	/**
	 * @var string the charset used for database connection. The property is only used
	 * for MySQL and PostgreSQL databases. Defaults to null, meaning using default charset
	 * as specified by the database.
	 *
	 * Note that if you're using GBK or BIG5 then it's highly recommended to
	 * update to PHP 5.3.6+ and to specify charset via DSN like
	 * 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
	 */
	public $charset;
	/**
	 * @var boolean whether to turn on prepare emulation. Defaults to false, meaning PDO
	 * will use the native prepare support if available. For some databases (such as MySQL),
	 * this may need to be set true so that PDO can emulate the prepare support to bypass
	 * the buggy native prepare support. Note, this property is only effective for PHP 5.1.3 or above.
	 * The default value is null, which will not change the ATTR_EMULATE_PREPARES value of PDO.
	 */
	public $emulatePrepare;
	/**
	 * @var boolean whether to log the values that are bound to a prepare SQL statement.
	 * Defaults to false. During development, you may consider setting this property to true
	 * so that parameter values bound to SQL statements are logged for debugging purpose.
	 * You should be aware that logging parameter values could be expensive and have significant
	 * impact on the performance of your application.
	 */
	public $enableParamLogging = false;
	/**
	 * @var boolean whether to enable profiling the SQL statements being executed.
	 * Defaults to false. This should be mainly enabled and used during development
	 * to find out the bottleneck of SQL executions.
	 */
	public $enableProfiling = false;
	/**
	 * @var string the default prefix for table names. Defaults to null, meaning no table prefix.
	 * By setting this property, any token like '{{tableName}}' in {@link CDbCommand::text} will
	 * be replaced by 'prefixTableName', where 'prefix' refers to this property value.
	 * @since 1.1.0
	 */
	public $tablePrefix;
	/**
	 * @var array list of SQL statements that should be executed right after the DB connection is established.
	 * @since 1.1.1
	 */
	public $initSQLs;
	/**
	 * @var array mapping between PDO driver and schema class name.
	 * A schema class can be specified using path alias.
	 * @since 1.1.6
	 */
	public $driverMap = array("pgsql" => "CPgsqlSchema", "mysqli" => "CMysqlSchema", "mysql" => "CMysqlSchema", "sqlite" => "CSqliteSchema", "sqlite2" => "CSqliteSchema", "mssql" => "CMssqlSchema", "dblib" => "CMssqlSchema", "sqlsrv" => "CMssqlSchema", "oci" => "COciSchema");
	/**
	 * @var string Custom PDO wrapper class.
	 * @since 1.1.8
	 */
	public $pdoClass = "PDO";
	private $_attributes = array();
	private $_active = false;
	private $_pdo;
	private $_transaction;
	private $_schema;
	private $_pdo_master;
	private $_pdo_slave;

	public function __construct($dsn = "", $username = "", $password = "")
	{
		$dsn = $dsn_m = $this->getSaeDBConn("m");
		$username = SAE_MYSQL_USER;
		$password = SAE_MYSQL_PASS;
		$this->connectionString = $dsn;
		$this->username = $username;
		$this->password = $password;
	}

	public function getSaePdoInstance($isRead = NULL)
	{
		return $isRead ? $this->_pdo_slave : $this->_pdo_master;
	}

	public function isReadOperation($sql)
	{
		return preg_match("/^\s*(SELECT|SHOW|DESCRIBE|PRAGMA)/i", $sql);
	}

	public function getSaeDBConn($type = "s")
	{
		$db_user = SAE_MYSQL_USER;
		$db_password = SAE_MYSQL_PASS;
		$db_host_m = SAE_MYSQL_HOST_M;
		$db_host_s = SAE_MYSQL_HOST_S;
		$db_port = SAE_MYSQL_PORT;
		$db_name = SAE_MYSQL_DB;
		$dsn["m"] = "mysql:host=" . $db_host_m . ";port=" . $db_port . ";dbname=" . $db_name;
		$dsn["s"] = "mysql:host=" . $db_host_s . ";port=" . $db_port . ";dbname=" . $db_name;
		if (empty($this->username) || 1) {
			$this->username = $db_user;
		}

		if (empty($this->password) || 1) {
			$this->password = $db_password;
		}

		$connectionString = ($dsn[$type] ? $dsn[$type] : $dsn["s"]);

		if (empty($this->connectionString)) {
			$this->connectionString = $connectionString;
		}

		return $connectionString;
	}

	public function __sleep()
	{
		$this->close();
		return array_keys(get_object_vars($this));
	}

	static public function getAvailableDrivers()
	{
		return PDO::getAvailableDrivers();
	}

	public function init()
	{
		parent::init();

		if ($this->autoConnect) {
			$this->setActive(true);
		}
	}

	public function getActive()
	{
		return $this->_active;
	}

	public function setActive($value)
	{
		if ($value != $this->_active) {
			if ($value) {
				$this->open();
			}
			else {
				$this->close();
			}
		}
	}

	public function cache($duration, $dependency = NULL, $queryCount = 1)
	{
		$this->queryCachingDuration = $duration;
		$this->queryCachingDependency = $dependency;
		$this->queryCachingCount = $queryCount;
		return $this;
	}

	protected function open()
	{
		if ($this->_pdo === NULL) {
			if (empty($this->connectionString)) {
				throw new CDbException("CDbConnection.connectionString cannot be empty.");
			}

			try {
				Yii::trace("Opening DB connection", "system.db.CDbConnection");
				$dsn = $this->getSaeDBConn("m");
				$this->connectionString = $dsn;
				$this->_pdo = $this->createPdoInstance();
				$this->initConnection($this->_pdo);
				$this->_pdo_master = $this->_pdo;
				$dsn = $this->getSaeDBConn("s");
				$this->connectionString = $dsn;
				$this->_pdo_slave = $this->createPdoInstance();
				$this->initConnection($this->_pdo_slave);
				$this->_active = true;
			}
			catch (PDOException $e) {
				if (YII_DEBUG) {
					throw new CDbException("CDbConnection failed to open the DB connection: " . $e->getMessage(), (int) $e->getCode(), $e->errorInfo);
				}
				else {
					Yii::log($e->getMessage(), CLogger::LEVEL_ERROR, "exception.CDbException");
					throw new CDbException("CDbConnection failed to open the DB connection.", (int) $e->getCode(), $e->errorInfo);
				}
			}
		}
	}

	protected function close()
	{
		Yii::trace("Closing DB connection", "system.db.CDbConnection");
		$this->_pdo = NULL;
		$this->_active = false;
		$this->_schema = NULL;
	}

	protected function createPdoInstance()
	{
		$pdoClass = $this->pdoClass;

		if (($pos = strpos($this->connectionString, ":")) !== false) {
			$driver = strtolower(substr($this->connectionString, 0, $pos));
			if (($driver === "mssql") || ($driver === "dblib")) {
				$pdoClass = "CMssqlPdoAdapter";
			}
			else if ($driver === "sqlsrv") {
				$pdoClass = "CMssqlSqlsrvPdoAdapter";
			}
		}

		return new $pdoClass($this->connectionString, $this->username, $this->password, $this->_attributes);
	}

	protected function initConnection($pdo)
	{
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if (($this->emulatePrepare !== NULL) && constant("PDO::ATTR_EMULATE_PREPARES")) {
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
		}

		if ($this->charset !== NULL) {
			$driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));

			if (in_array($driver, array("pgsql", "mysql", "mysqli"))) {
				$pdo->exec("SET NAMES " . $pdo->quote($this->charset));
			}
		}

		if ($this->initSQLs !== NULL) {
			foreach ($this->initSQLs as $sql ) {
				$pdo->exec($sql);
			}
		}
	}

	public function getPdoInstance($query = NULL)
	{
		$isRead = self::isReadOperation($query);
		$pdo = self::getSaePdoInstance($isRead);
		return $pdo;
	}

	public function createCommand($query = NULL)
	{
		$this->setActive(true);
		return new CDbCommand($this, $query);
	}

	public function getCurrentTransaction()
	{
		if ($this->_transaction !== NULL) {
			if ($this->_transaction->getActive()) {
				return $this->_transaction;
			}
		}

		return NULL;
	}

	public function beginTransaction()
	{
		Yii::trace("Starting transaction", "system.db.CDbConnection");
		$this->setActive(true);
		$this->_pdo->beginTransaction();
		return $this->_transaction = new CDbTransaction($this);
	}

	public function getSchema()
	{
		if ($this->_schema !== NULL) {
			return $this->_schema;
		}
		else {
			$driver = $this->getDriverName();

			if (isset($this->driverMap[$driver])) {
				return $this->_schema = Yii::createComponent($this->driverMap[$driver], $this);
			}
			else {
				throw new CDbException(Yii::t("yii", "CDbConnection does not support reading schema for {driver} database.", array("{driver}" => $driver)));
			}
		}
	}

	public function getCommandBuilder()
	{
		return $this->getSchema()->getCommandBuilder();
	}

	public function getLastInsertID($sequenceName = "")
	{
		$this->setActive(true);
		return $this->_pdo->lastInsertId($sequenceName);
	}

	public function quoteValue($str)
	{
		if (is_int($str) || is_float($str)) {
			return $str;
		}

		$this->setActive(true);

		if (($value = $this->_pdo->quote($str)) !== false) {
			return $value;
		}
		else {
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
		}
	}

	public function quoteTableName($name)
	{
		return $this->getSchema()->quoteTableName($name);
	}

	public function quoteColumnName($name)
	{
		return $this->getSchema()->quoteColumnName($name);
	}

	public function getPdoType($type)
	{
		static $map = array("boolean" => PDO::PARAM_BOOL, "integer" => PDO::PARAM_INT, "string" => PDO::PARAM_STR, "resource" => PDO::PARAM_LOB, "NULL" => PDO::PARAM_NULL);
		return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
	}

	public function getColumnCase()
	{
		return $this->getAttribute(PDO::ATTR_CASE);
	}

	public function setColumnCase($value)
	{
		$this->setAttribute(PDO::ATTR_CASE, $value);
	}

	public function getNullConversion()
	{
		return $this->getAttribute(PDO::ATTR_ORACLE_NULLS);
	}

	public function setNullConversion($value)
	{
		$this->setAttribute(PDO::ATTR_ORACLE_NULLS, $value);
	}

	public function getAutoCommit()
	{
		return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
	}

	public function setAutoCommit($value)
	{
		$this->setAttribute(PDO::ATTR_AUTOCOMMIT, $value);
	}

	public function getPersistent()
	{
		return $this->getAttribute(PDO::ATTR_PERSISTENT);
	}

	public function setPersistent($value)
	{
		return $this->setAttribute(PDO::ATTR_PERSISTENT, $value);
	}

	public function getDriverName()
	{
		if (($pos = strpos($this->connectionString, ":")) !== false) {
			return strtolower(substr($this->connectionString, 0, $pos));
		}
	}

	public function getClientVersion()
	{
		return $this->getAttribute(PDO::ATTR_CLIENT_VERSION);
	}

	public function getConnectionStatus()
	{
		return $this->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}

	public function getPrefetch()
	{
		return $this->getAttribute(PDO::ATTR_PREFETCH);
	}

	public function getServerInfo()
	{
		return $this->getAttribute(PDO::ATTR_SERVER_INFO);
	}

	public function getServerVersion()
	{
		return $this->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	public function getTimeout()
	{
		return $this->getAttribute(PDO::ATTR_TIMEOUT);
	}

	public function getAttribute($name)
	{
		$this->setActive(true);
		return $this->_pdo->getAttribute($name);
	}

	public function setAttribute($name, $value)
	{
		if ($this->_pdo instanceof PDO) {
			$this->_pdo->setAttribute($name, $value);
		}
		else {
			$this->_attributes[$name] = $value;
		}
	}

	public function getAttributes()
	{
		return $this->_attributes;
	}

	public function setAttributes($values)
	{
		foreach ($values as $name => $value ) {
			$this->_attributes[$name] = $value;
		}
	}

	public function getStats()
	{
		$logger = Yii::getLogger();
		$timings = $logger->getProfilingResults(NULL, "system.db.CDbCommand.query");
		$count = count($timings);
		$time = array_sum($timings);
		$timings = $logger->getProfilingResults(NULL, "system.db.CDbCommand.execute");
		$count += count($timings);
		$time += array_sum($timings);
		return array($count, $time);
	}
}


?>
