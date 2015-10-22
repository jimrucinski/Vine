<?php
include './PmaDb.class.php';
include './PmaTix.class.php';
include '../wp-config.php';
include 'PmaSupportFile.class.php';

$agent = filter_input(INPUT_POST,"agent");
$status = filter_input(INPUT_POST,"status");
$dueDate = filter_input(INPUT_POST,"due_date");
$requestType = filter_input(INPUT_POST,"requestType");
$requestTitle = filter_input(INPUT_POST,"requestTitle");
//$cmt = filter_input(INPUT_POST,"comments");
$cmt = strip_tags(html_entity_decode(filter_input(INPUT_POST,"comments")),'<br><br/><p><a><strong><em><ul><ol><li>');
$fileUpload = NULL;
$fileArray = NULL;
$submissionEmail;
$sendUpdateEmail = false;
$updatedAgent = null;

$log="";

if(!isset($_SESSION)){
    session_start();
}
if(isset($_SESSION['OriginalTicketVals'])){
    $rows = $_SESSION['OriginalTicketVals'];//orignal ticket values
}



$dbo = PmaPdoDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);  

$submitterEmail=$rows[0]['email'];


if($agent != $rows[0]['agent']){
    $sql = 'call sp_getAgent(' .$agent . ')';      
    $dbo->query($sql);
    $nme = $dbo->resultset();   
    $updatedAgent=$nme[0]['email'];
    $log .= 'Current agent' . $rows[0]['current_agent_full_name'] . ' changed to ' . $nme[0]['full_name'] . '\n' ;   
    $sendUpdateEmail = true;
    
}
if($status != $rows[0]['status']){
    $sql = 'call sp_getStatus(' .$status . ')';
    $dbo->query($sql);
    $nme = $dbo->resultset();   
    $log .= 'Status '. $rows[0]['current_status_name'] . ' changed to ' . $nme[0]['status'] . '\n' ; 

    //check if complete
    if($status == 3){
    $sql = 'call sp_getTicketSubmissionEmail(' .$rows[0]['id'] . ')';
    $dbo->query($sql);
    $submissionEmail = $dbo->resultset(); 
    }    
}

if($dueDate != $rows[0]['due_date']){
    $log .= 'Due Date ' . date('m-d-Y', strtotime($rows[0]['due_date'])) . ' changed to ' . $dueDate . '\n';
}
else{
    $dueDate = date('m-d-Y',strtotime($dueDate));
}
;
if($requestType != $rows[0]['request_type']){
    $sql = 'call sp_getRequestType(1,' .$requestType . ')';
    $dbo->query($sql);
    $nme = $dbo->resultset();   
    $log .= 'Request type ' . $rows[0]['current_request_type_name'] . ' changed to ' . $nme[0]['request_type'] . '\n' ; 
}
if($requestTitle != $rows[0]['request_title']){
    $log .= 'Request title ' . $rows[0]['request_title'] .  ' changed to ' . $requestTitle . '\n' ; 
}
if(!empty($cmt)){
    $log .=  $cmt ;
}


$tix = new PmaTix();
$tix->agent = $agent;
$tix->id = $rows[0]['id'];
$tix->status = $status;
$tix->due_date = $dueDate;
$tix->request_type = $requestType;
$tix->request_desc= $rows[0]['request_desc'];
$tix->request_title = $requestTitle;
$tix->logged_in_user = $current_user->user_login;
$tix->resolution = ($status == 3?$cmt:'');


//$sql = 'call sp_getEmail(' .$agent . ')';
//$dbo->query($sql);
//$agentEmail = $dbo->resultset();

if(count(array_filter($_FILES['upload_file']['name']))>0){
    $fileArray = new ArrayObject();
    foreach($_FILES['upload_file']['tmp_name'] as $key => $tmp_name){
        
        if($_FILES['upload_file']['error'][$key] == 4)
        {
            continue;
        }
        else{
        $fileUpload = new PmaSupportFile();
        $fileUpload->fileName = $_FILES['upload_file']['name'][$key];
        $fileUpload->mime = $_FILES['upload_file']['type'][$key]; 
        $fileUpload->size = $_FILES['upload_file']['size'][$key]; 
        $fileUpload->fileData =  file_get_contents($_FILES['upload_file']['tmp_name'][$key]); //file_get_contents function used to read binary into a string
        //echo $fileUpload->fileName;
        $fileArray->append($fileUpload);
        //$log .=  '<div class="logEntry">Support File added: ' .$fileUpload->fileName . '</div>';
        }
    }
}


try{


//add suppport files

if(count($fileArray)!=0){
    foreach($fileArray as $file){
        
    $safety = 'call sp_isFileSafe("' . $file->fileName .'")';
    $dbo->query($safety);
    $safetyResult = $dbo->resultset();    
    
    /*hoop jumping because the output parameter doesn't work in the stored procedure ... this is a MySql bug*/
    if($safetyResult[0]["IsSafe"] != 1){   
        $dbo->addSupportFile($tix->id, $file);   
        $fId = $dbo->execute();
        $log .=  'Support File added: ' . $file->fileName . '\n';
        }
    else{
        $log .=  'Support NOT File added: ' . $file->fileName . '.\nNot a valid file type for uploading.\n';
    }
    
    }
}
$addresses = array();
if($updatedAgent != null){
    $addresses[] =$updatedAgent;
}

$dbo->editTicket($tix, $log,null); 
$subject = 'IT Work Request: ' . $tix->id;
$wrUrl = '<a href="' . get_page_link(get_page_by_title('Edit Work Request')->ID) . '?workrequestid=' . $tix->id . '">Work Request #' . $tix->id .'</a>';
if($status == 3){    

    $body ='<p>The following work request has been closed: ' . $wrUrl;
    $body .='<p>' . $tix->request_title . '<br/>' . $rows[0]["request_desc"] .  '</p>';
    $addresses[]=$submitterEmail;
//$addresses[]=$submissionEmail[0]['email'];
    sendMail($subject, $body , $addresses, false );
}
else if($sendUpdateEmail == true){

    $body ='<p>The following work request has been assigned to you: ' . $wrUrl . '</p>';
    $body='<p><strong>' . $tix->request_title . '</strong><br/>' . $rows[0]["request_desc"] .  '</p>';
    //$addresses[]=$submissionEmail[0]['email'];
    sendMail($subject, $body , $addresses, false );
}

//$addresses[] ='jrucinski@pma.com'; //work request IT admin set in wp-config
//$addresses = array();
//$addresses[] =$agentEmail[0]['email'];
//$addresses[] =WR_IT_Admin; //work request IT admin set in wp-config
//sendMail('Work Request', '<strong> and wrap your cords the right way</strong><p>blah ditty blah blah blah</p>', $addresses );
($status==3?wp_redirect(home_url() . '/information-technology/review-it-work-requests/'):wp_safe_redirect( wp_get_referer()));
//wp_safe_redirect( wp_get_referer());
exit;
}
catch(Exception $exp)
{
    echo "EXCEPTION:<br/>" . $exp.message;
}
session_destroy();

