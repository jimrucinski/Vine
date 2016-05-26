<?php

if (isset($_POST['lsr-submit']))
    {
    createFile('testing.xml');
    }

function createFile($xml_file)
{
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
    
    
    
    
    
    
    
   //$doc=simplexml_load_file($file);
//$doc->load($file);
   
    //$cheerReason=nl2br($cheerReason);
    
   /*$fragment=$doc->createDocumentFragment();
   $fragment->appendXML("<cheer id='{$id}'><dateSubmitted>{$dateSumbitted}</dateSubmitted><cheerFor>{$cheerFor}</cheerFor><cheerReason>{$cheerReason}</cheerReason><submittedBy>{$submittedBy}</submittedBy></cheer>");
   $doc->documentElement->appendChild($fragment);*/
    /*
    $newCheer = $doc->addChild('cheer');
    $newCheer->addChild('dateSubmitted',$dateSumbitted);
    $newCheer->addChild('cheerFor',$cheerFor);
    $newCheer->addChild('cheerReason',$cheerReason);
    $newCheer->addChild('submittedBy',$submittedBy);
    file_put_contents($file,$doc->asXML());
        */

//$doc->save($file);
   
    
    
}




function addRoot(&$xml)
{
    $xml->appendChild($xml->createElement("entry"));
}

?>
<form name="Cheers4Peers" action="CheersForPeersNomination.php" method="post">
    
    
    <ul>
        <li>
            <label for="txtCheersFor">Individual / Group to Cheer</label>
            <input type="text" name="txtCheersFor">
        </li>
        <li>
            <label for="txtCheers">Why are they being cheered on?</label>
            <textarea name="txtCheers" id="txtCheers" cols="50" rows="10"></textarea>
        </li>
        <li>
            <label for="txtsubmittedBy">Submitted By:</label>
            <input type="text" name="txtSubmittedBy">
        </li>
        <li>
            <input type="submit" name="lsr-submit" value="Submit">
        </li>
    </ul>

</form>