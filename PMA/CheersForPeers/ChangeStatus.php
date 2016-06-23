<?php

	include '../../wp-config.php';
	include '../PmaDb.class.php';
	
	$recognitionId = $_GET['id'];
	$statusId = $_GET['status'];
	$preview=$_GET['preview'];	

	$dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);

	
	$sql = 'call sp_updateRecognitionStatus("' . $recognitionId . '","' . $statusId . '")';   
    $dbo->query($sql);

	$rows = $dbo->resultset();
	$dbo->query($sql);
	$row = $dbo->singleRowResult();
	
	if($preview!="true"){
        $str .= $row['status']; 
		echo $str;
	}
	

