<?php
include ('wp-config.php');
include ('./PMA/pma-support-ticket-data.php');
include ('./PMA/PmaTable.class.php');
include ('./PMA/PmaTicket.class.php');
include ('./PMA/PmaRequestTypes.class.php');
include ('./PMA/PmaDisplayTable.class.php');



$dbo = PmaDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);

$tix = new PmaTicketObj();
$tix->load();
$tix->testing();
//the following lines allow you to update the record
//$data = array("first_name"=>"Foghorn","last_name"=>"Leghorn");
//$tix->bind($data);
//$tix->store();

//echo("{$tix->first_name}  {$tix->last_name}");
//echo "<br/>" . $tix->request_desc ."<br/><br/><br/>";

//$rs = $tix->load();
//echo "<br/>HERE: " . $rs . "</br>";

//$reqtypes=new PmaRequestTypes();
//$reqtypes->load('1');

//echo("{$reqtypes->request_type}");



$tble = new PmaDisplayTable();
echo "<br/><br/>here it is " . $tble->createTable();