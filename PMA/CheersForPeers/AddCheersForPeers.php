<?php

    $dateSubmitted="";
    $cheerFor="";
    $cheerReason="";
    $submittedBy="";
    
    $dateSumbitted=date("m/d/Y");
    $id=date("Ymdhms");


    $cheerFor=$_POST['txtCheersFor'];
    $cheerReason=$_POST['txtCheers'];
    $submittedBy=$_POST['txtSubmittedBy'];
    $cheerReason=nl2br($cheerReason);
        
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
    $cf = $doc->createElement("cheerFor",$cheerFor);
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
    
    $root->appendChild($newCheer);

    $doc->save($file);
    
    wp_redirect(home_url());