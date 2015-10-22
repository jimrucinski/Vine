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
if(isset($_REQUEST['workrequestid']))
    {
    $wid = $_GET["workrequestid"];

    }
    else
    {
        return "<h4>There was no ID passed to the page, therefore no records found.</h4>";
    }
$sql = 'call sp_getTicketLog(' . $wid .',@tixDesc)';

$dbo = PmaPdoDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
$dbo->query($sql);
$log = $dbo->resultset();
$dbo->query("select @tixDesc");
$tixDesc = $dbo->resultset();
//echo $tixDesc[0][0];
//die();
?>

<html>
    <head>
        <style type="text/css">
            body{
                font-family: calibri;
            }
            .description{margin-bottom:2em;font-style: oblique;}
            .logEntry{display:block;}
            
        </style>
            
        
    </head>
    <body>
        <?php
        if(!empty($log)){           
        $str ='<div class="log">';
        foreach($tixDesc as $tix)
        {
            $str .= '<strong>Request Description<br/></strong><div class="description">' . nl2br(htmlentities($tix['@tixDesc'])) . '</div>';
        }
        foreach($log as $l){
            $str .='<div style="margin-bottom:1em;"><strong>' . $l['tstamp'] . ': ' . $l["user"] . '<br/></strong><span>' . str_replace("\\n","<br/>", $l["ticket_comment"]) . '</span></div>';
        }
        $str .= '</div>';
    }
    else{
        $str .='<div id="log" style="text-align:center;">no log found</div>';
    }
    echo $str;
        ?>
    </body>
</html>