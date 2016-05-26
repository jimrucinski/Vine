<?php
include './PmaDb.class.php';
include '../wp-config.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(isset($_GET['id'])){
    $id=intval(filter_input(INPUT_GET,'id'));
    
    try{
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
    
    $attachment = 'call sp_getFileAttachment(' .$id . ')';

    $dbo->query($attachment);
    $fas =  $dbo->resultset();

    if(!empty($fas)){
    //$row = mysqli_fetch_assoc($result);
 
                // Print headers
                header("Content-Type: ". $fas[0]['mime']);
                header("Content-Length: ". $fas[0]['size']);
                header("Content-Disposition: attachment; filename=". $fas[0]['fileName']);
 
                
                while(@ob_end_clean());//this line is needed if server has output buffering on. Without this line all files besides PDF are corrupt on download.
                // Print data
                echo $fas[0]['fileData'];
    }
    }
    catch(Exception $exp)
    {
        echo "EXCEPTION:<br/>" . $exp.message;
    }
}
