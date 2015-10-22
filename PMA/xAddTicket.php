<?php
include './PmaDb.class.php';
include './PmaTix.class.php';
include '../wp-config.php';
include 'PmaSupportFile.class.php';

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//using the STRIPSLASHES function because escape slashes are being added on insert. Can't get magic quotes to turn off. 
$fName = filter_input(INPUT_POST,"first_name");
$lName = filter_input(INPUT_POST,"last_name");
$email = filter_input(INPUT_POST,"email");
$requestTitle = stripslashes(filter_input(INPUT_POST,"request_title"));
$requestDesc = stripslashes(filter_input(INPUT_POST,"request_desc"));
$dueDate =filter_input(INPUT_POST,"due_date");
$requestType = filter_input(INPUT_POST,"request_type");
$agent = filter_input(INPUT_POST,"agent");
$department = filter_input(INPUT_POST,"department");
$logged_in_user = $current_user->user_login;
$fileUpload = NULL;
$fileArray = NULL;

if(count(array_filter($_FILES['upload_file']['name']))>0){
//if(null !== filter_input(INPUT_POST,"upload_file")) {
    $fileArray = new ArrayObject();
    foreach($_FILES['upload_file']['tmp_name'] as $key => $tmp_name){
        $fileUpload = new PmaSupportFile();
        $fileUpload->fileName = $_FILES['upload_file']['name'][$key];
        $fileUpload->mime = $_FILES['upload_file']['type'][$key]; 
        $fileUpload->size = $_FILES['upload_file']['size'][$key]; 
        $fileUpload->fileData =  file_get_contents($_FILES['upload_file']['tmp_name'][$key]); //file_get_contents function used to read binary into a string
        $fileArray->append($fileUpload);
    }    
}
$tix = new PmaTix();
$tix->first_name = $fName;
$tix->last_name=$lName;
$tix->email=$email;
$tix->request_title=$requestTitle;
$tix->request_desc=$requestDesc;
$tix->due_date =   $dueDate;
$tix->request_type=$requestType;
$tix->agent = $agent;
$tix->department = $department;
$tix->logged_in_user = $logged_in_user;

try{
$dbo = PmaPdoDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
$dbo->addTicket($tix, $fileArray);

$requestId = $dbo->resultset();

if(count($fileArray)!=0){
foreach($fileArray as $file){
   $dbo->addSupportFile($requestId[0]['RequestId'], $file);
   
   $fId = $dbo->execute();
}
}
if($agent != null){
$sql = 'call sp_getEmail(' .$agent . ')';
$dbo->query($sql);
$agentEmail = $dbo->resultset();



$addresses = array();
$addresses[] =$agentEmail[0]['email'];

}
$wrUrl = '<a href="' . get_page_link(get_page_by_title('Edit Work Request')->ID) . '?workrequestid=' . $requestId[0]['RequestId'] . '">Work Request #' . $requestId[0]['RequestId'] .'</a>';
$addresses[] =WR_IT_Admin; //work request IT admin set in wp-config

$body = '<p><strong>A new Work Request was added for you.</strong></p>';
$body .= '<p>' . $tix->request_title . '<br/>' . nl2br($requestDesc) .  '</p>';

sendMail('New Work Request', $body . $wrUrl , $addresses, true );

wp_safe_redirect(get_page_link(get_page_by_title('Review IT Work Requests')->ID));
}
catch(Exception $exp)
{
    echo "EXCEPTION:<br/>" . $exp.message;
}

