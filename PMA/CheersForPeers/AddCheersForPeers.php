<?php
	include '../../wp-config.php';
	include '../PmaDb.class.php';
	include './PmaRecognition.class.php';
    $dateSubmitted="";
    $cheerFor="";
    $cheerReason="";
    $submittedBy="";
	$cheerType="";
	$cheerTitle="";
	
	$root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
	$current_user = wp_get_current_user();

    $dateSubmitted=date("m/d/Y");
    $id= time().'-'.mt_rand();

    $cheerFor=$_POST['selected_users'];
    $cheerReason=$_POST['txtCheers'];
    $submittedBy=$_POST['txtSubmittedBy'];
    $cheerReason=nl2br($cheerReason);  
	$cheerType=$_POST['recognitionProgram'];		
	$cheerTitle = $_POST['txtRecognitionTitle'];

	$cheerSqlArray = array();
    if($cheerFor){
    foreach($cheerFor as $c){
		$cheerSqlArray[] = '('. mysql_real_escape_string($c).', "'.$id.'")';
    }

	$cheerQueryString=implode(',',$cheerSqlArray) . ';';

    }

	$rec = new PmaRecognition();
	$rec->id=$id;
	$rec->recogniton_text=$cheerReason;
	$rec->recognition_type=$cheerType;
	$rec->submitted_by=$submittedBy;
	$rec->submit_date=$dateSubmitted;
	$rec->recognition_title=$cheerTitle;
	$rec->recognition_people=$cheerQueryString;

	//echo '<br/>rec id= ' . $rec->id . '<br/>' . $rec->recogniton_text . '<br/>' . $rec->recognition_type . '<br/>' . $rec->submitted_by . //'<br/>' . $rec->submit_date . '<br/>people from class = ' . $rec->recognition_people . '<br/>';
	
	//echo $rec->recognition_people;
	//die();

try{
	$dbo = PmaPdoDb::getInstance();
	$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
	$dbo->addRecognition($rec);
	
	$body = '<p><strong>A new recognition was added</strong></p>';
	$body .= '<p>' . $rec->recognition_title . '<br/>' . $rec->recognition_text .  '</p>';

	$wrUrl = $root . 'PMA/CheersForPeers/RecognitionCert.php?recId=' . $rec->id;
	$addresses = array();
	$addresses[] =RECOGNITION_ADMIN;//pulled from the wp_config 
	$addresses[] = $current_user->user_email;
	
	sendMail('New Employee Recognition', $body . $wrUrl , $addresses, true );

	
	header( 'Location: /teams/human-resources/' );
	}
	catch(Exception $exp)
	{
		echo "EXCEPTION:<br/>" . $exp.message;
	}

  
