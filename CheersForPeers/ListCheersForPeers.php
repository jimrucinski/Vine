<?php
$xmlDoc = new DOMDocument();
$xmlDoc->load("cheers.xml");
$xslDoc = new DOMDocument();
$xslDoc->load("CheersForPeersGrid.xsl");
$year=$_GET["year"];

//find the cheer for the given ID
$xpathVar = new DOMXPath($xmlDoc);

$queryResult=$xpathVar->query('//cheers/cheer[starts-with(@id,"'. $year . '")]');
if($queryResult->length >0){
//create a new xml document and add the results of thw query to the document.
$doc = new DOMDocument();
foreach($queryResult as $q){
    $doc->appendChild($doc->importNode($q,true));
}
//display the cheer in the certificate
$orderby='cheerFor';
$proc = new XSLTProcessor();
$proc->importStylesheet($xslDoc);
$proc->registerPHPFunctions();
$proc->setParameter('','orderby',$orderby);
echo $proc->transformToXml($doc);
}
else{
    echo "no records found";
}


