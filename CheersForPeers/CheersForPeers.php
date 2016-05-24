<?php

$xmlDoc = new DOMDocument();
$xmlDoc->load("cheers.xml");
$xslDoc = new DOMDocument();
$xslDoc->load("CheersForPeers.xsl");
$id=$_GET["cheerid"];

//find the cheer for the given ID
$xpathVar = new DOMXPath($xmlDoc);
$queryResult=$xpathVar->query('//cheers/cheer[@id="'. $id . '"]');

if($queryResult->length ==1){
//create a new xml document and add the results of thw query to the document.
$doc = new DOMDocument();
foreach($queryResult as $q){
    $doc->appendChild($doc->importNode($q,true));
}
//display the cheer in the certificate
$proc = new XSLTProcessor();
$proc->importStylesheet($xslDoc);
echo $proc->transformToXml($doc);
}
 else {
     echo "no records found for given ID.";
 }
