<?php
	class DB {
		private $dbHost = 'localhost';
		private $dbUsername = 'root';
		private $dbPassword = '';
		private $dbName = 'test';
		public $db;

		public function __construct() {
			if (!isset($this->db)) {
				try {
					$conn = new mysqli($this->dbHost,$this->dbUsername,$this->dbPassword,$this->dbName);
					$this->db = $conn;
				} catch (Exception $e) {
					die('Failed to connect with MySQL: '.$e->getMessage());
				}
			}
		}

		/**
		 * Returns rows from the database based on the conditions
		 * @param string name of the table
		 * @param array select, where, order_by, limit and return_type condition
		 */
		public function getRows($table, $conditions = array()) {
			$sql = 'SELECT ';
			$sql .= array_key_exists("select", $conditions) ? $conditions['select'] : '*';
			$sql .= ' FROM '.$table;
			if (array_key_exists("where", $conditions)) {
				$sql .= ' WHERE ';
				$i = 0;
				foreach ($conditions['where'] as $key => $value) {
					$pre = ($i > 0) ? ' AND ' : '';
					$sql .= $pre.$key." = '".$value."'";
					$i++;
				}
			}

			if (array_key_exists("order_by", $conditions)) {
				$sql .= ' ORDER BY '.$conditions['order_by'];
			}

			if (array_key_exists("start", $conditions) && array_key_exists("limit", $conditions)) {
				$sql .= ' LIMIT '.$conditions['start'].','.$conditions['limit'];
			} elseif (!array_key_exists("start", $conditions) && array_key_exists("limit", $conditions)) {
				$sql .= ' LIMIT '.$conditions['limit'];
			}

			$query = $this->db->query($sql);

			if (array_key_exists("return_type", $conditions) && $conditions['return_type'] != 'all') {
				switch ($conditions['return_type']) {
					case 'count':
						$data = $this->db->affected_rows;
						break;
					case 'single':
						$data = $query->fetch_assoc();
						break;
					default:
						$data = '';
				}
			} else {
				if ($this->db->affected_rows > 0) {
					while ($row = $query->fetch_assoc()) {
						$data[] = $row;
					}
					$query->free();
				}
			}
			return !empty($data) ? $data : false;
		}

		/**
		 * Insert data into the database
		 * @param string name of the table
		 * @param array the data for inserting into the table
		 * @param array the data type for data binding
		 */
		public function insert($table, $data, $type) {
			if (!empty($data) && is_array($data)) {
				$columnString = '';
				$valueString = '';
				$types = '';
				$i = 0;

				if (!array_key_exists('created', $data)) {
					$data['created'] = date('Y-m-d H:i:s');
					$type['created'] = 's';
				}
				if (!array_key_exists('modified', $data)) {
					$data['modified'] = date('Y-m-d H:i:s');
					$type['modified'] = 's';
				}

				$columnString = implode(',', array_keys($data));
				//$valueString = ":".implode(',:', array_keys($data));
				$types = implode('', array_values($type));
				$params[] = $types;
				$n = 0;
				foreach ($type as $key => $val)
				{
					if ($n > 0)	$valueString .= ',';
					$valueString .= '?';
					$n++;
				}
				$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
				$query = $this->db->prepare($sql);
				foreach ($data as $key => $val) {
					$val = htmlspecialchars(strip_tags($val));
					$params[] = $val;
				}
				call_user_func_array(array($query, 'bind_param'), $this->refValues($params));
				// $query->bind_param($types, $params);
				$insert = $query->execute();
				if ($insert) {
					$data['id'] = $this->db->insert_id;
					return $data;
				} else {
					return false;
				}
				// return $sql;
			} else {
				return false;
			}
		}

		/**
		 * Update data into the database
		 * @param string name of the table
		 * @param array the data for updating into the table
		 * @param array where condition on updating data
		 */
		public function update($table, $data, $conditions) {
			if (!empty($data) && is_array($data)) {
				$colvalSet = '';
				$whereSql = '';
				$i = 0;
				if (!array_key_exists('modified', $data)) {
					$data['modified'] = date('Y-m-d H:i:s');
				}
				foreach ($data as $key => $val) {
					$pre = ($i > 0) ? ', ' : '';
					$val = htmlspecialchars(strip_tags($val));
					$colvalSet .= $pre.$key."='".$val."'";
					$i++;
				}
				if (!empty($conditions) && is_array($conditions)) {
					$whereSql .= ' WHERE ';
					$i = 0;
					foreach ($conditions as $key => $value) {
						$pre = ($i > 0) ? ' AND ' : '';
						$whereSql .= $pre.$key." = '".$value."'";
						$i++;
					}
				}
				$sql = "UPDATE ".$table." SET ".$colvalSet.$whereSql;
				// $query = $this->db->prepare($sql);
				$update = $this->db->query($sql);
				return $update ? $this->db->affected_rows : false;
			} else {
				return false;
			}
		}

		/**
		 * Delete data into the database
		 * @param string name of the table
		 * @param array where condition on deleting data
		 */
		public function delete($table, $conditions) {
			$whereSql = '';
			if (!empty($conditions) && is_array($conditions)) {
				$whereSql = ' WHERE ';
				$i = 0;
				foreach ($conditions as $key => $value) {
					$pre = ($i > 0) ? ' AND ' : '';
					$whereSql .= $pre.$key." = '".$value."'";
					$i++;
				}
			}
			$sql = "DELETE FROM ".$table.$whereSql;
			$delete = $this->db->query($sql);
			return $delete ? $delete : false;
		}

		private function refValues($arr) {
			if (strnatcmp(phpversion(), '5.3') >= 0) {
				$refs = array();
				foreach ($arr as $key => $value)
					$refs[$key] = &$arr[$key];
				return $refs;
			}
			return $arr;
		}
	}
?>