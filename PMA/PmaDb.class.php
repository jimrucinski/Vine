<?php
if(!class_exists('PmaPdoDb')){
	
	class PmaPdoDb{            
            protected static $db;            
            private $host;
            private $user;
            private $pass;
            private $dbName;            
            private static $instance = "";            
            private $connection;
            private $results;
            private $numRows;
            private $stmt;
            
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
            try{
                $dsn = 'mysql:host=' . $host .';dbname=' . $dbName ;
                self::$db = new PDO($dsn, $user, $password);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                
            } catch (PDOException $ex) {
                echo "connection error: " . $ex->getMessage();
            }
        }
        public function editTicket($tix, $cmt, $fileUp){
            try{       
                
         
                $this->stmt = self::$db->prepare('CALL sp_editTicket(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $this->stmt->bindParam(1,$tix->id);
                $this->stmt->bindParam(2,$tix->agent);
                $this->stmt->bindParam(3,$tix->status);
                $this->stmt->bindParam(4,$tix->request_type);  
                $this->stmt->bindParam(5,$tix->request_title);
                $this->stmt->bindParam(6,$cmt);
                $this->stmt->bindParam(7,$tix->logged_in_user);
                $this->stmt->bindParam(8,$tix->resolution);
                if($fileUp != NULL){
                    $this->stmt->bindParam(9,$fileUp->fileName);
                    $this->stmt->bindParam(10,$fileUp->mime);
                    $this->stmt->bindParam(11,$fileUp->size);
                    //echo 'size = ' . $fileUp->size;
                    //die();
                    $this->stmt->bindParam(12,$fileUp->fileData);   
                }
                else
                { 
                    $this->stmt->bindParam(9,$null);
                    $this->stmt->bindParam(10,$null);
                    $this->stmt->bindParam(11,$null);
                    $this->stmt->bindParam(12,$null); 
                }
                $this->stmt->bindParam(13,$tix->due_date);
                $this->stmt->bindParam(14,$tix->charge_code);
                $this->stmt->bindParam(15,$tix->material_to_office_services);
                $this->stmt->bindParam(16,$tix->quantity);
                $this->stmt->bindParam(17,$tix->envelope_type);
           
                $this->stmt->execute();
            }
            catch(PDOException $e){
                echo $e->getMessage();
            }
            
        }
        public function addSupportFile($tickid, $fileUp)
        {
            $out=0;
            try{
                $this->stmt = self::$db->prepare('CALL sp_insertSupportingFile(?,?,?,?,?)');
                $this->stmt->bindParam(1,$tickid);
                $this->stmt->bindParam(2,$fileUp->fileName);
                $this->stmt->bindParam(3,$fileUp->mime);
                $this->stmt->bindParam(4,$fileUp->size);
                $this->stmt->bindParam(5,$fileUp->fileData);      
 
                return $this->stmt->fetchALL(PDO::FETCH_ASSOC);   
                
            } catch(PDOException $e){
                echo $e->getMessage();
                //die();
            }    
        }
        public function addTicket($tix, $fileUp){
            $null = NULL;
           try{
               //echo
                $this->stmt = self::$db->prepare('CALL sp_insertPmaTicket(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $this->stmt->bindParam(1,$tix->first_name);
                $this->stmt->bindParam(2,$tix->last_name);
                $this->stmt->bindParam(3,$tix->email);
                $this->stmt->bindParam(4,$tix->request_title);
                $this->stmt->bindParam(5,$tix->request_desc);
                $this->stmt->bindParam(6,$tix->due_date);
                $this->stmt->bindParam(7,$tix->request_type);
                $this->stmt->bindParam(8,$tix->agent);   
                $this->stmt->bindParam(9,$tix->department);
                $this->stmt->bindParam(10,$tix->logged_in_user);   
                $this->stmt->bindParam(15,$tix->charge_code);
                $this->stmt->bindParam(16,$tix->material_to_office_services);
                $this->stmt->bindParam(17,$tix->quantity);
                $this->stmt->bindParam(18,$tix->envelope_type);
                if($fileUp != NULL){
                    
                   // foreach ($fileUp as $file) {
                   //     $this->stmt->bindParam(11,$file->fileName);
                   //     $this->stmt->bindParam(12,$file->mime);
                   //     $this->stmt->bindParam(13,$file->size);
                   //     $this->stmt->bindParam(14,$file->fileData);
                   // }
                    $this->stmt->bindParam(11,$fileUp->fileName);
                    $this->stmt->bindParam(12,$fileUp->mime);
                    $this->stmt->bindParam(13,$fileUp->size);
                    $this->stmt->bindParam(14,$fileUp->fileData);   
                }
                else
                { 
                    $this->stmt->bindParam(11,$null);
                    $this->stmt->bindParam(12,$null);
                    $this->stmt->bindParam(13,$null);
                    $this->stmt->bindParam(14,$null); 
                }
               //die();
                return $this->stmt->fetchALL(PDO::FETCH_ASSOC);    
           }
           catch(PDOException $e){
            echo $e->getMessage();
            //die();
            }           
        }
        
		public function addRecognition($rec){
			
           try{
                $this->stmt = self::$db->prepare('CALL sp_insertRecognition(?,?,?,?,?,?,?)');				
                $this->stmt->bindParam(1,$rec->id);				
                $this->stmt->bindParam(2,$rec->submit_date);				
                $this->stmt->bindParam(3,$rec->submitted_by);				
                $this->stmt->bindParam(4,$rec->recognition_type);				
                $this->stmt->bindParam(5,$rec->recogniton_text);				
                $this->stmt->bindParam(6,$rec->recognition_people);
				$this->stmt->bindParam(7,$rec->recognition_title);
                $this->stmt->execute();
           }
           catch(PDOException $e){
			$e->getMessage();

            }           
        }
		
		
        public function query($sql){
            $this->stmt = self::$db->prepare($sql);
        }

        public function queryForObjs($sql){
            
            $this->stmt = self::$db->prepare($sql);
            $this->stmt->execute();
            $this->stmt->setFetchMode(PDO::FETCH_CLASS, 'PmaTix');
            
            return $this->stmt->fetchAll();            
        }
        public function execute(){
            //$this->stmt->setFetchMode(PDO::FETCH_CLASS,'PmaTix');
            return $this->stmt->execute();
        }
        public function resultset(){
            $this->execute();
            return $this->stmt->fetchALL(PDO::FETCH_ASSOC);
        }
		public function singleRowResult(){
			$this->execute();
            return $this->stmt->fetch(PDO::FETCH_ASSOC);
		}
        public function resultsetForObjs(){
            $this->execute();
            $this->stmt->fetchALL(PDO::FETCH_CLASS, 'PmaTix');
            return $this->stmt->fetchAll();
        }
        public function DoQuery($sql){
          $this->results = self::query($sql);
          //foreach(self::$db->query($sql) as $row){
          //          print_r($row);
          //}
        }
        public function DoQueryToObject($sql){
            $sql->setFetchMode(PDO::FETCH_CLASS,'PmaTix');
            while($r = $query->fetch()){
                echo $r->summary, '<br>';
                echo '<pre>' . print_r($r) . '</pre>';
            }
        }
        public function Bind($param, $value, $type = null){
            
        }
        
    }
}
?>