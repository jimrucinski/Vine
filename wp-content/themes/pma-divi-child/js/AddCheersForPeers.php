<?php
include '../../wp-config.php';
    $dateSubmitted="";
    $cheerFor="";
    $cheerReason="";
    $submittedBy="";
    
    $dateSumbitted=date("m/d/Y");
    $id=date("Ymdhms");


    $cheerFor=$_POST['selected_users'];
    $cheerReason=$_POST['txtCheers'];
    $submittedBy=$_POST['txtSubmittedBy'];
    $cheerReason=nl2br($cheerReason);    
    
    $cheerForString="";
    if($cheerFor){
    foreach($cheerFor as $c){
        $cheerForString .= $c . ", ";
    }
    $cheerForString=rtrim($cheerForString,", ");
    }

	echo json_encode($cheerFor);
	die();
	
    $file = "cheers.xml";
    $doc = new DOMDocument("1.0","UTF-8");
    $doc->load($file);
    $root = $doc->documentElement;
    $newCheer = $doc->createElement("cheer");
    $att = $doc->createAttribute("id");
    $att->value= $id;
    $newCheer->appendChild($att);
    $ds = $doc->createElement("dateSubmitted",(string)$dateSumbitted);
    $newCheer->appendChild($ds);
    $cf = $doc->createElement("cheerFor",$cheerForString);
    $newCheer->appendChild($cf);
    
    //$cdata = $doc->createCDATASection(htmlentities($cheerReason));
    $cr = $doc->createElement("cheerReason",  htmlentities($cheerReason));
    //$cr->appendChild($cdata);
    $newCheer->appendChild($cr);
  
    /*
    $cr = $doc->createCDATASection($cheerReason);
    $cr=$doc->createElement("cheerReason",$cr);
    $reason=$doc->createElement(("cheerReason"));
    $reason->appendChild($cr);
    $newCheer->appendChild($reason);
     */
    
    $sb = $doc->createElement("submittedBy",$submittedBy);
	$newCheer->appendChild($sb);
    header( 'Location: http://vinedev.pma.com/teams/human-resources/' );
    
    $root->appendChild($newCheer);

    $doc->save($file);
    //$redirectLocation='/teams/human-resources/';
	wp_redirect($_POST['_wp_http_referer']);
        
        
        $body = '<p><strong>A new Work Request was added for you.</strong></p>';
$body .= '<p>' . $tix->request_title . '<br/>' .$requestDesc .  '</p>';

//sendMail('New Work Request', $body . $wrUrl , $addresses, true );
