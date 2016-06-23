<?php
include './PmaDb.class.php';
include './PmaTix.class.php';
include '../wp-config.php';
include 'PmaSupportFile.class.php';

$body="this is a test from cron";
$wrUrl="http://www.cnn.com";
$addresses[]='jrucinski@pma.com';
sendMail('New CRON Message', $body . $wrUrl , $addresses, false );
