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
        
    $file = "cheers.xml";
    $doc = new DOMDocument();
    $doc->load($file);
    
   $fragment=$doc->createDocumentFragment();
   $fragment->appendXML("<cheer id='{$id}'><dateSubmitted>{$dateSumbitted}</dateSubmitted><cheerFor>{$cheerFor}</cheerFor><cheerReason>{$cheerReason}</cheerReason><submittedBy>{$submittedBy}</submittedBy></cheer>");
   $doc->documentElement->appendChild($fragment);
   $doc->save($file);
   
    
    
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