<?php
$xmlDoc = new DOMDocument();
$xmlDoc->load("cheers.xml");
$xslDoc = new DOMDocument();
$xslDoc->load("CheersForPeersGrid.xsl");

//display the cheer in the certificate
$proc = new XSLTProcessor();
$proc->importStylesheet($xslDoc);
echo $proc->transformToXml($xmlDoc);
