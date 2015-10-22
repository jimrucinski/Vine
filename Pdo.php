<?php
//put connection stuff in it's own connect file
include ('wp-config.php');
$dsn = 'mysql:host=' . DB_HOST .';dbname=' . DB_NAME ;
//print_r(PDO::getAvailableDrivers());
try{
    $handler = new PDO($dsn, DB_USER, DB_PASSWORD);
    $handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}
catch(PDOException $e){
   echo $e->getMessage();
   die();
    
}
//put class in it's own class file
class PmaTix
{
    public $date_submitted,$due_date,$email,$first_name,$last_name,$request_title,$request_desc,
        $summary;
    
    public function __construct(){
        $this->summary = "{$this->first_name} {$this->last_name} put in the ticket on: {$this->date_submitted}";
    }
    
}
//Getting row count
$query = $handler->query('select * from pma_tickets');
if($query->rowCount()){
    echo $query->rowCount() . '<br/>';
    while($r=$query->fetch(PDO::FETCH_OBJ))
    {
        echo $r->first_name . ' ' . $r->last_name . '<br/>';
        
    }
}
else{
    echo 'no results';
}


//using prepared statment to protect against SQL injection
$first_name ='Road';
$last_name ='Runner';
$request_title = 'Beep Beep';
$request_desc ="Insert sound of fast moving road runner here!";
/*        
$sql ="INSERT into pma_tickets (first_name, last_name, date_submitted, request_title, request_desc)values(:first_name,:last_name, NOW(), :request_title,:request_desc)";
$query = $handler->prepare($sql);//prepare the SQL / Validate it
$query->execute(array(
    ':first_name'=>$first_name,
    ':last_name'=>$last_name,
    ':request_title'=>$request_title,
    ':request_desc'=>$request_desc    
));//build an array to insert the data into the SQL 
*/


//alternate way of binding the data in prepared statement
/*
$sql = $sql ="INSERT into pma_tickets (first_name, last_name, date_submitted, request_title, request_desc)values(?,?, NOW(), ?,?)";
$query = $handler->prepare($sql);//prepare the SQL / Validate it
$query->execute(array($first_name, $last_name, $request_title, $request_desc));
echo $handler->lastInsertId(); //get the id of the last inserted record.
 */

$query = $handler->query('select * from pma_tickets');

 //fetch all records into a class
$query->setFetchMode(PDO::FETCH_CLASS,'PmaTix');
while($r = $query->fetch()){
    echo $r->summary, '<br>';
    echo '<pre>' . print_r($r) . '</pre>';
}

/*
 //see if records exist without loop
$results= $query->fetchAll(PDO::FETCH_ASSOC);
if(count($results)){
    echo 'records found';
}
else{
    echo 'no records found';
}
*/
/*
 //return data as anonymous objects allows dot notation to get data
while($r = $query->fetch(PDO::FETCH_OBJ) ){
    echo $r->first_name . '<br/>';
}
*/
/*
while($r =$query->fetch(PDO::FETCH_NUM)){
    
    echo '<pre>' , print_r($r), '</pre>';
}
*/
/*
while($r =$query->fetch(PDO::FETCH_ASSOC)){
    
    echo '<pre>' , print_r($r), '</pre>';
}
 */


