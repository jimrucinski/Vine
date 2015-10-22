<?php


if(!class_exists('PmaDb')){
	
	class PmaDb{
            private $host;
            private $user;
            private $pass;
            private $dbName;
            
            private static $instance = "";
            
            private $connection;
            private $results;
            private $numRows;

        private function __construct(){			
		}
                //singleton method of ensuring one connection open at a time.
                //minimizes memory use and chances of multiple connections.
                static function getInstance()
                {
                    if(!self::$instance){//if there is no instance of itself
                      self::$instance = new self(); //create one 
                    }
                    return self::$instance;
                }
		
		function connect($host, $user, $password, $dbName){
                    $this -> user = $user;
                    $this -> pass = $password;
                    $this -> dbName =$dbName;
                    $this -> host = $host;                    
                    $this->connection = mysqli_connect($this->host, $this->user, $this->pass, $this->dbName);
		}
                
                public function doQuery($sql){
                    $this->results=mysqli_query($this->connection, $sql);
                    $this->numRows=$this->results->num_rows;   
                }
                
                public function loadObjectList(){
                    $obj = "No Results";
                    if($this->results)
                    {                        
                       $obj = mysqli_fetch_assoc($this->results); 
                    }
                    return $obj;
                }
		
                public function getNumRows()
                {
                    return $this->numRows;
                }
                public function getResults()
                {
                    return $this->results;
                }
/*
		public function insertTicket(PmaTicket $tix)
		{

			$ret = false;
			echo $tix->get_first_name();
			
			//$db = $this->connect();
			
			$one= $tix->get_first_name();
			$two = $tix->get_last_name();
			$three = $tix->get_email();
			$four = $tix->get_request_title();
			$five = $tix->get_request_desc();
			
			if($stmt = $db->prepare("INSERT INTO pma_tickets(first_name, last_name, email, request_title, request_desc) Values (?,?,?,?,?)"))
			{
				echo $stmt->param_count;
				$stmt -> bind_param("sssss", $one,$two,$three,$four,$five);
				if($stmt -> execute()){
					$ret = true;
				$stmt -> close();
				
				}
			}	
			
			
			return $ret;
		}
		public function query($query){
			$db = $this->connect();
			$rs = $db->query($query);			
			return $rs;
		}
		
		*/
	}
	
}
/*
require('wp-config.php');

function pmaConnectToDb(){
try{

	static $c;
	
	//try to connect to database is connection doesn't already exist
	if(!isset($c))
	{
		echo 'now here';
		mysqli_report(MYSQLI_REPORT_STRICT);//enable messages from mysqli object
		$c = new mysqli(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
		if(mysqli_connect_errno()){
			throw new Exception($mysqli->error);
		}
	}
	
}
	catch(mysqli_sql_exception $e){
		throw $e;
	}
	return $c;
}*/
?>