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


$addresses = array();
$addresses[] ='jrucinski@pma.com'; //work request IT admin set in wp-config
$addresses[] ='jimrucinski@msn.com'; //work request IT admin set in wp-config

sendMail('email testng', '<p>test of siteground email'  , $addresses );
echo('here');