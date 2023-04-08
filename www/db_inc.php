<?
	class DB {
		var $hosts = "localhost";
		var $users = "user_id";
		var $passwd = "user_pw";
		var $dbname = "db_name";
		var $connect = '';

        function __construct() {
            $this->connect = mysqli_connect($this->hosts, $this->users, $this->passwd);
            mysqli_select_db($this->connect, $this->dbname);
            mysqli_query($this->connect, "set names 'utf8mb4'");
            mysqli_query($this->connect, "SET SESSION wait_timeout = 100");
            mysqli_query($this->connect, "SET SESSION interactive_timeout = 100");
        }

		function db_query($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$result = mysqli_query($this->connect, $query);
			if(!$result){
				echo $query."<br/>".mysqli_error($this->connect);
				exit;
			}

			return $result;
		}

		function insert_id() {
			$id = mysqli_insert_id($this->connect);

			return $id;
		}

		function fetch_query($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$return = mysqli_fetch_array($this->db_query($query));

			return $return;
		}

		function fetch_assoc($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$return = mysqli_fetch_assoc($this->db_query($query));

			return $return;
		}

		function count_query($query,$debug=0) {
			$query_ex = explode("from", $query);
			$query = "select count(*) as cnt from ".$query_ex[1];
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$data = $this->fetch_query($query);
			return $data['cnt'];
		}

		function select_rows($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$cnt = mysqli_num_rows($this->db_query($query));

			return $cnt;
		}

		function select_query($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$result = $this->db_query($query);
			while ($data = mysqli_fetch_array($result)) $return[] = $data;

			return $return;
		}

		function select_assoc($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$result = $this->db_query($query);
			while ($data = mysqli_fetch_assoc($result)) $return[] = $data;

			return $return;
		}

		function select_query_row($query,$debug=0) {
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$result = $this->db_query($query);
			while ($data = mysqli_fetch_row($result)) $return[] = $data;

			return $return;
		}

		function insert_query($table, $arr, $debug=0) {
			array_walk($arr, "set_sql_quote");

			$query = "insert into ".$table." (".implode(",", array_keys($arr)).") value (".implode(",", array_values($arr)).")";
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$result = $this->db_query($query);

			return $result;
		}

		function update_query($table, $arr, $where_query, $debug=0) {
			array_walk($arr, "set_sql_quote");
			$arr_val = array();
			foreach($arr as $key => $val) {
				$arr_val[] = $key." = ".$val;
			}
			$query = "update ".$table." set ".implode(', ', $arr_val)." where ".$where_query;
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			//echo "<script>alert('".$val."');</script>";
			$result = $this->db_query($query);
			return $result;
		}

		function del_query($table,$query,$debug=0) {
			$query = "delete from ".$table." where ".$query;
			//echo $query;
			if($debug=="1") {
				echo $query."<br/>";
				exit;
			}
			$result = $this->db_query($query);

			return $result;
		}

		function close($connect=0) {
			if(!$this->free()) {
				return false;
			}

			if(!$connect) {
				$connect = $this->connect;
			}

			if($connect) {
				if(!@mysqli_close($connect)) {
					return false;
				}
			}

			$this->remove();

			return true;
		}

		function free($result=0) {
			if($result) {
				@mysqli_free_result($result);
			}

			return true;
		}

		function remove() {
			unset($this->hosts);
			unset($this->users);
			unset($this->passwd);
			unset($this->dbname);
			unset($this->connect);
		}
	}

	if(!function_exists("sql_quote")) {
		function sql_quote($strSql) {
			$return = "'".str_replace("'", "\'", stripslashes($strSql))."'";

			return $return;
		}
	}

	if(!function_exists("set_sql_quote")) {
		function set_sql_quote(&$val, $key) {
			$val = trim($val);

			if(strtolower($val) == "now()" || strtolower(substr($val, 0, 13)) == "from_unixtime" || strtolower(substr($val, 0, 8)) == "date_add" || $key == "num_file") {
				$val = $val;
			} else {
				$val = sql_quote($val);
			}
		}
	}

	$DB = new DB();
?>