<?php 
/*
include ('wp-config.php');
include ('./PMA/pma-support-ticket-data.php');
include ('./PMA/table.class.php');
include ('./PMA/PmaTicket.class.php');
include ('./PMA/PmaRequestTypes.class.php');

$dbo = PmaDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
//Get form data
$fName = $_POST["txtFname"];
$lName = $_POST["txtLname"];
$email = $_POST["txtEmail"];
$requestTitle = $_POST["txtRequestTitle"];
$requestDesc = $_POST["txtRequestDesc"];
$dueDate = strtotime($_POST["txtDueDate"]);
 * */


try{
	
    
    
	echo 'boo';
	//$tix = new PmaTicketObj();
        
        //$data = array("first_name"=>$fName,"last_name"=>$lName,"email"=>$email,"request_title"=>$requestTitle,"request_desc"=>$requestDesc);
        //$tix->bind($data);
        //$tix->store();
        
     
        
	//$tix->set_first_name($fName);
	//$tix->set_last_name($lName);
	//$tix->set_email($email);
	//$tix->set_request_title($requestTitle);
	//$tix->set_request_desc($requestDesc);
       
	
        
	//echo "last name ==" . $tix->get_last_name() ."<br/><br/>";

	//$db = new PmaDb(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
	
	//if($db->insertTicket($tix))
	//{
	//	echo "it worked";
	//}
	//else{
        //		echo "it failed";
	//}
	
	//$recs = $db->query('SELECT request_title FROM pma_tickets');
	
	//while($row = mysqli_fetch_assoc($recs)){//mysql_fetch_assoc reads one row at a time and converts to an array.
	//	echo $row["request_title"] . "<br/>";
	//}
	//while($row = mysqli_fetch_assoc($recs))
	//{
	//	echo $row["request_title"];
	//}
	
	//foreach($recs as $row)
	//{
	//	list($title) = @array_values($row);
	//	echo $title . "dd<br/>";
	//}
	
	
	//while($recs = $db->query('SELECT * FROM pma_tickets')){
	//	foreach ($recs as $column => $value)
	//	{
	//		echo ($value);
	//	}
	//}
	
	//mysqli_report(MYSQLI_REPORT_STRICT);//enable messages from mysqli object
	//$mysqli = new mysqli(DB_HOST,DB_USER, DB_PASSWORD, xDB_NAME); loo
	
	
	//$conn = pmaConnectToDb(); 
	
	
	//if(mysqli_connect_errno()){
	//	echo 'goo';
	//	throw new Exception($mysqli->error);
	//}
	
	
	/*
	if($stmt = $conn -> prepare("INSERT INTO pma_tickets(first_name, last_name, email, request_title, request_desc, due_date) Values ?,?,?,?,?,?")){
		echo ('hey now');
		$stmt -> bind_param("sssssd",$fName,$lName,$email,$requestTitle,$requestDesc,$dueDate);
		$stmt -> execute();
		$stmt -> close();
	}
	else {
		echo ('failed ' .$stmt);
	}
	$conn ->close();
	*/
	/*
//connect to the database defined in the wp_config file
$conn = mysqli_connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
if(!$conn){
	die('Problem in database connection: ' . mysql_error());	
}

//insert data
$query = "INSERT INTO pma_tickets (first_name,last_name, email, request_title,request_desc,due_date) VALUES ('" . $fName . "','" .$lName ."','" .$email . "','" . $requestTitle . "','" . $requestDesc . "','" . date('Y-m-d',$dueDate) . "')";

$sqlResult = mysqli_query($conn, $query);

if(!$sqlResult)
{
	throw new Exception($conn->error);
}
echo $query . "<br/>";
echo 'record added successfully!';
	  //echo 'here i am' . $name . '<br/>';
      //echo 'Username: ' . $current_user->user_login . "\n";
      //echo 'User email: ' . $current_user->user_email . "\n";
      //echo 'User first name: ' . $current_user->user_firstname . "\n";
      //echo 'User last name: ' . $current_user->user_lastname . "\n";
      //echo 'User display name: ' . $current_user->display_name . "\n";
      //echo 'User ID: ' . $current_user->ID . "\n";
*/
}
catch(Exception $e)
{
echo "ERROR: " . $e->getMessage();
}
?>