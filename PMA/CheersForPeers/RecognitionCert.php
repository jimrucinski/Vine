<?php
include '../../wp-config.php';
include '../PmaDb.class.php';


if(isset($_GET['submit'])) 
{

	$statusId = 2;	
	$recId = $_GET['recId'];
	$dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
	
	$sql = 'call sp_updateRecognitionStatus("' . $recId . '","' . $statusId . '")';   
	$dbo->query($sql);
	//echo('call sp_updateRecognitionStatus("' . $recognitionId . '","' . $statusId . '")');
	
	$rows = $dbo->resultset();
	$str="";
    foreach ($rows as $row){
        $str .= $row['status'];
    }


}


$certificateTitle="";

$recId = $_GET['recId'];
$statusId = $_GET['status'];

$dbo = PmaPdoDb::getInstance();
$dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
$sql = 'call sp_getRecognition("' . $recId . '")';   
$dbo->query($sql);
$row = $dbo->singleRowResult();


switch($row['recognitionType']){
	case 1:
		$certificateTitle="PMA Cheers for Peers ";
		break;
	case 2:
		$certificateTitle="Employee of the Month";
		break;
	case 3:
		$certificateTitle="Supervisor Award THINGY";
		break;
	case 4:
		$certificateTitle="Core. Compentency THINGY";
		break;
	default:
		$certificateTitle = "Unknown Award Type";
}
?>

 <HTML>
      <HEAD>
          <style>
             #master{display:block;margin-left:auto;margin-right:auto;padding:2em 2em 2em 2em;min-height:580px;width:780px;
              border:groove 10px #cccccc;background-image:url('images/cheer-4-peers.png');background-position:right bottom; background-repeat:no-repeat;
			  }
              h1{font-family:Perpetua;font-size:2.75em;position:relative;left:-10%;top:.05em;letter-spacing:3px;white-space:nowrap;}
              h2{padding-top:0px;margin-top:0px;}
              #receive{border:ridge 3px #d1d19d;padding:.2em;margin-right:1em;background: linear-gradient(#ffffc9, #ffffff);}
              #cheer{margin-left:30%;}
              #claim{padding:.5em;line-height:1.2em;background:#ddffb3;background:rgba(223,255,181,0.3);border-radius: 15px;border:solid 1px #000000;}              
              ul{margin:0;padding:0px;}
              li{display:inline;font-size:large;}
              li label{font-weight:bolder;}
              p{font-size:large; border:1 solid #000;}             
			  
			  #watermarkText{
				  position: absolute;
				  font-family:stencil regular, calibri, verdana, arial;
				  opacity:0.10;
				  width:100%;
				  margin-top:15%;
				  margin-left:auto;
				  margin-right:auto;
				  font-size:150px;
				  text-align:center;
				  -webkit-transform: rotate(330deg); /* Chrome, Safari, Opera */	
				  border:none;			  
				  transform: rotate(330deg);
				  z-index:1;}
			 }
		  </style>
		  <link rel="stylesheet" href="./CertificatePrint.css" media="print"/>
		  <script language="JavaScript">
		  function changeRecStatus(id, status, preview){

	if(id=="")
		return;
	else{
		if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                //document.getElementById(id).innerHTML = xmlhttp.responseText;
				document.location.reload();
            }
        };
        xmlhttp.open("GET","ChangeStatus.php?id="+id+"&status="+status+"&preview="+preview,true);
		xmlhttp.send();
	}
}
		  </script>
        <TITLE></TITLE>
      </HEAD>
      <BODY>
 
	<?php if($row['recognitionStatus'] != 2){?>
	<input type="button"  name="button" text="click this" onclick="changeRecStatus('<?php echo $recId?>','2','true')" value='approve now' style="background-color:#6666ff;text-transform:capitalize;font-size:large;color:#ffff00;padding:.25em 0 .25em 0;border-radius:25px;display:block;position:relative;text-align:center;width:25%;margin-left:auto;margin-right:auto;margin-bottom:2em;"/>
	<input type="hidden" name='recId' value='<?php echo($recId) ?>'/>
	
		<div id="watermarkText"><span style="border:solid 5px #000;border-radius:25px;padding:5px;">PENDING</span></div>
	<?php } ?>
		
          <div id="master">
			
            <div style="height:4em;background-color:#9de872;width:80%;float:right;">
                <h1><?echo $certificateTitle?></h1>
            </div>     
			<div style="position:relative;margin-top:15%;width:100%;">	
		
		<ul style="display:table;width:100%;">
		
		<li style="display:table-cell;width:20%;height:100%;">
		<div id="receive">
			<ul>
				<li>
					<label>Date:</label>
				</li>
				<li>
					  <? echo($row['submitDate'])?>
				</li>                    
			</ul>
			<br/><br/>
			<ul>
				<li><label>Individual or Group Receiving Cheers:</label></li>
			</ul>
				<? echo($row['recognitionPeople'])?>
				 
		</div>
	
		</li>
		<li style="display:table-cell;width:50%;">
		 <div id="cheers">
                <h2>Why are they being cheered on?</h2>
                <div id="claim">
                    <? echo(stripslashes($row['recognitionText']))?>
              </div>
			  <p style="margin-bottom:3em;">
                    <strong>Submitted By: </strong><? echo($row['submittedBy'])?>
                </p>
			  </div>
		</li>
		</ul>
		
            </div>   
<div style="background-color:purple;position:relative;bottom:0px;">
            
			</div>            
          </div>        
      </BODY>
    </HTML>