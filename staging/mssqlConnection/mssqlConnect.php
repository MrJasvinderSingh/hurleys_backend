<?php
class mssqlConnect{
	private $servername;
	private $dbName;
	private $user;
	private $password;
	public $conn;
	
	function __construct(){
		$this->servername="DESKTOP-S3N90HL";
		$this->dbName="test";
		$this->user="anviamdb";
		$this->password="anviam123";
		$this->conn = sqlsrv_connect($this->servername, array("database"=>$this->dbName, "UID"=>$this->user,"PWD"=>$this->password));
		if (!$this->conn) {
			echo '<pre>';die( print_r( sqlsrv_errors(), true));
		}
	}
	
	public function insertData($data,$table){
		if ($this->conn) {
			if(count($data) > 0){
				$vals = '';
				foreach($data as $val){
					$vals .= '?,';
				}
				$values = trim($vals,',');
				$sql = "INSERT INTO $table VALUES ($values)";
				$stmt = sqlsrv_query( $this->conn, $sql, $data);
				if( $stmt === false ) {
					return json_encode(sqlsrv_errors());
				}
				return true;
			} else{
				return false;
			}
		} else{
			return json_encode(sqlsrv_errors());
		}
	}
	
	public function updateData($data,$id,$table){
		if ($this->conn) {
			if($id == ''){
				return "id field is required";
			}
			if($data != ''){
				//data should be in form:
				//$data = "SET name = 'thakur', email='vthakur@anviam.cokmm', password='asdf'"
				$sql = "update $table $data where id = $id";
				$stmt = sqlsrv_query( $this->conn, $sql);
				if( $stmt === false ) {
					return json_encode(sqlsrv_errors());
				}
				return true;
			} else{
				return 'Something Went wrong!';
			}
		} else{
			return json_encode(sqlsrv_errors());
		}
	}
	
	public function getData($table,$where=''){
		if ($this->conn) {
			//$where = " where id = '1'"
			$sql = "SELECT * from $table $where";
			
			$stmt = sqlsrv_query( $this->conn, $sql);
			$arr = array();
			if( $stmt === false ) {
				return json_encode( sqlsrv_errors());
			}
			while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
				array_push($arr,$row);
			}
			//print_r($arr);die('asdf');
			return $arr;			
		} else{
			return json_encode(sqlsrv_errors());
		}
	}
}
//$obj = new mssqlConnect();
//echo '<pre>';print_r($obj);
//$table = '[dbo].[user]';
//$params = array("Vikash1", "vthakur1@anviam.com","123456");
//echo $obj->insertData($params,$table);
//$data = "SET name = 'thakur', email='vthakur@anviam.cokmm', password='32432'";
//$id = 2;
//echo $obj->updateData($data,$id,$table);

//$getdata = $obj->getData($table);

//print_r($getdata);die;
?>