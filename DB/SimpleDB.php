<?php

/**
 * Description of SimpleDB
 *
 * @author atrichkov
 */

namespace EF\DB;

class SimpleDB extends Builder {

	protected $connection = 'default';
	private $db = null;
	private $stmt = null;
	private $sql = null;
	private $params = null;

	public function __construct($connection = null) {
		if ($connection instanceof \PDO) {
			$this->db = $connection;
		} else if ($connection != null) {
			$this->db = \EF\App::getInstance()->getDBConnection($connection);
			$this->connection = $connection;
			$connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		} else {
			$this->db = \EF\App::getInstance()->getDBConnection($this->connection);
		}
	}

	/*
	 * 
	 * @param type $sql
	 * @param type $params
	 * @param type $pdoOptions
	 */
	public function prepare($sql, $params = array(), $pdoOptions = array()) {
		$this->stmt = $this->db->prepare($sql, $pdoOptions);
		$this->params = $params;
		$this->sql = $sql;
		
		return $this;
	}

	public function filters($filters = array()) {
		if (is_array($filters)) {
			foreach ($filters as $k => $v) {
				if ($k) {
					$conditions .= ' AND ' . $k . ' = :' . $k;
				}
			}
		}

		if ($conditions) {
			$sqlWhere = $conditions;
		}
		
		return $sqlWhere;
	}

	public function execute($params = array()) { // опципнални параметри засега няма да се вземат в предвид при builder-а
		if ($params) {
			$this->params = $params;
		}
		if ($this->_query) {
			$this->prepare($this->buildQuery(), $this->_params);
		}
		
//		try {
//			$this->stmt->execute($this->params);
//		} catch (\Exception $e) {
//			throw new Exception('Could not connect to database');
//		}

		if (!$this->stmt->execute($this->params)) {
			var_dump($this->sql);
			echo '<br />';
			echo '<br />';
			echo '<br />';
			var_dump($this->params);
			echo '<br />';
			echo '<br />';
			echo '<br />';
			
			$debugSql = $this->sql;
			foreach($this->params as $k => $v) {
				if (is_string($v)) {
					$debugSql = str_replace(':' . $k, '\'' . $v . '\'', $debugSql);
				} else {
					$debugSql = str_replace(':' . $k, $v, $debugSql);
				}
				
			}
			
//			if (strpos($debugSql, 'CALL') !== FALSE) {
//				$debugSql = $sqlParts[0] . ' (' . implode(',', $params) . ')';
//			} else {
//				$debugSql = $sqlParts[0] . ' VALUES ' . $sqlParts[1];
//			}
			echo $debugSql;

//			echo ($sqlParts[0] . 'VALUES (' . implode($this->params, ',') . ')');
//			var_dump(implode($this->params, ','));
//            $filename = "/var/www/Personal/lab/log.html";
//		echo $this->sql;
		if ($this->logSql) {
			// TODO
		}
//		$this->stmt->debugDumpParams();
		throw new \Exception('SQL error in query: ' . $this->sql, 400);
//            print_r($this->db->errorInfo());
//TODO            CHECK THIS
//            throw new \Exception($this->db->errorInfo(), 500);
		}
		
		return $this;
	}

	public function fetchAllAssoc() {
		return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function fetchRowAssoc() {
		return $this->stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function fetchAllNum() {
		return $this->stmt->fetchAll(\PDO::FETCH_NUM);
	}

	public function fetchRowNum() {
		return $this->stmt->fetch(\PDO::FETCH_NUM);
	}

	public function fetchAllObj() {
		return $this->stmt->fetchAll(\PDO::FETCH_OBJ);
	}

	public function fetchRowObj() {
		return $this->stmt->fetch(\PDO::FETCH_OBJ);
	}

	public function fetchAllColumn($column) {
		return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
	}

	public function fetchRowColumn($column) {
		return $this->stmt->fetch(\PDO::FETCH_COLUMN, $column);
	}

	public function fetchAllClass($class) {
		return $this->stmt->fetchAll(\PDO::FETCH_CLASS, $class);
	}

	public function fetchRowClass($class) {
		return $this->stmt->fetch(\PDO::FETCH_CLASS, $class);
	}

	public function getLastInsertId() {
		return $this->db->lastInsertId();
	}

	public function getAffectedRows() {
		return $this->stmt->rowCount();
	}

	public function getSTMT() {
		return $this->stmt;
	}

	public function getConnection() {
		return $this->connection;
	}

}

?>
