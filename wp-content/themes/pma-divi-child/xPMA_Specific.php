
<?php 

//include ABSPATH . PMAINC . '/PmaDb.class.php';
require( ABSPATH  . 'PMA/PmaDb.class.php');
require( ABSPATH . WPINC . '/class-phpmailer.php');
require( ABSPATH . WPINC . '/class-smtp.php');
function pmaCurrentUserInformation()
{
	global $current_user;
	$str = pmaCreateInputBox('first_name',$current_user->user_firstname);
	$str .= pmaCreateInputBox('last_name',$current_user->user_lastname);
	$str .= pmaCreateInputBox('email',$current_user->user_email);
	return $str;
	//return  $current_user->user_email;
}
add_shortcode('PMA-UserInfo','pmaCurrentUserInformation');

function pmaCreateInputBox($id, $value)
{
	$str='<li><label>';
	switch($id)
	{
		case "first_name":
			$str .="first name";
			break;
		case "last_name":
			$str .="last name";
			break;
		case "email":
			$str .="email";
			break;
                case "requestTitle":
                    $str .="requestTitle";
                    break;
			
	}
	$str .='</label>';
	$str .= '<input type="text" name="' . $id . '" id="' . $id . '" value="' . stripslashes($value) . '"></input></li>';
	return $str;
}
/*
 * sp = stored procedure to call
 * id = identity value for stored proc
 * nme = name of the created select box
 * keepvalue= selected item
 * blank = include blank option at top. omit or set to zero to NOT have a blank <option> at top of select box.
 */
function pmaCreateRequestTypeDropdown($atts){
       
    $a =shortcode_atts( array(
			'sp' => '',
			'id' => '',
			'nme' => '',
                        'keepvalue' => '',
                        'blank' => ''
		), $atts
	) ;
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);

    $sql = 'call ' . $a['sp'] . "(" . $a['id'] . ")";
   
    $dbo->query($sql);
    $rows = $dbo->resultset();
    $str = "<select name='" . $a['nme'] . "'>";
    

    if($a['blank']==1 || $a['blank']=='1'){
        $str.= "<option/>";
    }
    
    foreach ($rows as $row){
        $str .= "<option  value='{$row['val']}' ";        
        if($a['keepvalue'] == $row['val']){
            $str .= " selected ";
        }
        $str .= ">{$row['txt']}</option>";
    }
    $str .= "</select>";
    return $str;
}
add_shortcode('PMA-RequestSelectBox','pmaCreateRequestTypeDropdown');

function determineTixQuery(){
    
    global $wp;
    $sortby = (isset($_REQUEST['sort']))? $_GET['sort']:null;
    $ad = (isset($_REQUEST['ad']))? $_GET['ad']:null;
    $reverse = ($ad=='asc' || $ad==''?'desc':'asc'); 
    $statustype =(!isset($_REQUEST['StatusType']))? 0:$_GET['StatusType'];
    return array($sortby, $ad, $reverse, $statustype);
}

function pmaAllWorkRequestsTable($atts){
    $results = determineTixQuery();
    global $wp;
    $curUrl = rtrim(home_url(add_query_arg(array(),$wp->request)),"/");
    $str= "<table cellpadding='0' cellspacing='0' id='PmaWorkRequestsTable'><tr><th>id</th><th><a href='{$curUrl}?sort=tix.last_name&ad={$results[2]}&StatusType={$results[3]}'>requestor</a></th><th>title</th><th><a href='{$curUrl}?sort=tix.due_date&ad={$results[2]}&StatusType={$results[3]}'>due date<a/></th><th><a href='{$curUrl}?sort=ta.last_name&ad={$results[2]}&StatusType={$results[3]}'>assigned</a></th><th><a href='{$curUrl}?sort=st.status&ad={$results[2]}&StatusType={$results[3]}'>status</a></th><th><a href='{$curUrl}?sort=tix.date_submitted&ad={$results[2]}&StatusType={$results[3]}'>date submitted</a></th></tr>";
    $a =shortcode_atts( array(
                        'id' => '',
			'sortby' => '',
                        'ad' =>'',
		), $atts
	) ;
    
        
    $sql = 'call sp_getTicketsInOrder(' .$a['id'] . ',' . $results[3] .  ',"' . ($results[0]=='' ? $a['sortby'] : $results[0]) . '","'  . ($results[1]== ''?$a['ad']:$results[1]) . '")';
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
    $rows = $dbo->queryForObjs($sql);
    if(count($rows) > 0){
    foreach($rows as $row){
        $str .= '<tr><td>' . $row['id'] . '</td><td>' . $row['requestor'] . '</td><td><a href="./edit-work-request/?workrequestid=' . $row['id'] . '">' . stripslashes($row['request_title']) . '</a></td><td>' . $row['due_date'] . '</td><td>' . $row['assignedTo'] . '</td><td>' . $row['status'] . '</td><td>' . $row['date_submitted'] . '</td></tr>';        
           
    }
    }
    else
    {
        $str.='<tr><td colspan="7">no records found</td></tr>';
    }
    $str .= '</table>';
    
    return $str;
}
add_shortcode('PMA-AllWorkRequestsTable','pmaAllWorkRequestsTable');

function pmaGetWorkRequest(){
    global $wp;

    //handle file delete request
    if(isset($_REQUEST['SupportFile'])){
        try{
            $curUser = $current_user->user_login;
            $currentWID = $_REQUEST['workrequestid'];
            $delId = $_REQUEST['SupportFile'];
            $sql = 'call sp_deleteSupportFile(' . $currentWID . ',' .$delId . ',"' . $curUser . '")';
            $dbo = PmaPdoDb::getInstance();
            $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
            $dbo->query($sql);
            $rs = $dbo->execute(); 
        }
        catch(Exception $exp)
        {
            echo "EXCEPTION:<br/>" . $exp.message;
            die();
        }
    }
    if(isset($_REQUEST['workrequestid']))
    {
    $wid = $_GET["workrequestid"];
    }
    else
    {
        return "<h4>There was no ID passed to the page, therefore no records found.</h4>";
    }
    
    $sql = 'call sp_getWorkRequest(' .$wid . ')';
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
    $rows = $dbo->queryForObjs($sql);

    //store orginal values in session
    session_start();
    $_SESSION['OriginalTicketVals'] = $rows;
    $str = '<div style="float:right"><font class="ChangeLogHeader">Change Log</font></div>';
    $str .= '<h4>Work Request: '. $wid . '</h4>';  
    
    //echo strlen($rows[0]['ticketlog']);
    //die();
    
   // if(!empty($rows[0]['ticketlog'])){
   // $str .='<div><div id="log" style="">' . $rows[0]['ticketlog'] . '</div></div>';    
   // }
   // else{
   //     $str .='<div id="log" style="">no log found</div>';
   // }
    $str .= '<script type="text/javascript">function checkFiles(){var F = document.getElementById("upload_file");if("files" in F){var s=0;for (var i = 0; i < F.files.length; i++){var file = F.files[i];s+=file.size;}};if(s > 8388608){alert("Total File Size is Greater Than Allowable 8 Meg Limit");return false;}else{return true;}}</script>';
    $str .= '<form onsubmit="return checkFiles();" action="../../../PMA/EditTicket.php" method="POST" name="EditTicket" id="EditTicket" enctype="multipart/form-data">';
    $str .= '<div style="float:left;width:60%;"><fieldset style="position:relative;width:100%;" ><ul class="PmaSupportTicket">';
    foreach($rows as $row){
        $str .= '<li><label>Name</label>';
        $str .=  $row['first_name'] . ' ' . $row['last_name'] . '</li><li><label>email</label><a href="mailto://' . $row['email'] . '">' . $row['email'] . '</a></li><li><label>date submitted</label>' . $row['date_submitted']  . '</li>';
        $str .= '<li><label>due date</label>' . $row['due_date'] .'</li>';
        $str .= '<li><label>assigned to</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getTicketAgents' id=1 nme='agent' keepvalue=" . $row['agent']. " emptyTopValue=false)]");  '</li>';
        $str .= '<li><label>status</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getStatusTypesForEdit' id=1 nme='status' keepvalue=" . $row['status'] . ']'); '</li>';
        $str .= '<li><label>request type</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getDeptRequestTypes' id=1 nme='requestType' keepvalue=" . $row['request_type'] . " emptyTopValue=false)]");  '</li>';
        $str .= pmaCreateInputBox('requestTitle',  $row['request_title']);
        $str .= '<li><label>description</label><p style="display:inline-block;">' .$row['request_desc'] . '</p></li>';
        $str .= '<li><label>support files</label><input type="file" id="upload_file" name="upload_file[]" multiple=""/></li>';
        $str .= '<li><label>comment</label><textarea id="comments" name="comments" rows="3" style="width:500px;"></textarea>';
        $str .= '<li><label>&nbsp;</label><input type="submit" value="submit" id="go" name="go" /></li>';
    }
    $str .= '</ul></fieldset></div></div>';
    $str .= '</form>';

    //start Change Log 
    $ticketlog = 'call sp_getTicketLog(' . $wid .')';
    $dbo->query($ticketlog);
    $log = $dbo->resultset();
    
    if(!empty($log)){
        $str .= '<a target ="new" href="../../../PMA/PrintLog.php?workrequestid=' . $wid . '">print log</a><br/>';         
        $str .='<div class="log">';
        foreach($log as $l){
            $str .='<div><strong>' . $l[tstamp] . ': ' . $l["user"] . '</strong><br/>' . $l["ticket_comment"] . '</div>';
        }
        $str .= '</div>';
    }
    else{
        $str .='<div class="log" style="background-color:#666666;color:#ffffff;text-align:center;">no log found</div>';
    }
    
    //start File Attachments
    $attachments = 'call sp_getTicketFileAttachments(' .$wid . ')';
    $dbo->query($attachments);
    $fas =  $dbo->resultset();
    //echo 'it = ' . $fas->fetchColumn();
    if(!empty($fas)){
        $str .= '<div id="supportTix"><font class="ChangeLogHeader" style="text-align:left;">Support Documents</font>';
        foreach($fas as $fa){
            $str .=  '<div id="supportFiles"><a onclick="return confirm(\'Are you sure you want to delete the selected support document: ' . $fa['fileName'] . '?\');" href=".?workrequestid=' . $wid . '&SupportFile=' . $fa['id'] .'"><img title="Delete ' . $fa['fileName'] . '"  src="' . get_option("siteurl") .'/PMA/delete-icon.png"/></a><a title="Download ' . $fa['fileName'] . '" href="' . get_option("siteurl") .'/PMA/PmaGetFile.php?id=' . $fa['id'] . '">' . $fa['fileName'] . '</a></div>';
        }
        $str .= '</div>';
    }
   
    return $str;
}
add_shortcode('PMA-GetWorkRequest', 'pmaGetWorkRequest');
        
function sendMail($Subject, $Msg, $ToAddress)
{
    
    $smail = new PHPMailer();
    $smail->IsSMTP();
    $smail->SMTPDebug=2;
    //$smail->SMTPAuth = false;
    //$smail->Host ='PMAEX10.pma.com';
    //$smail->Username = 'jrucinski@pma.com';
    //$smail->Password='Ch@s1ng!';

    //$smail->SMTPSecure = 'tls';
    //$smail->Host = "10.0.0.210:25"; 
    $smail->Host=MailHost;
    ////$smail->Port=25;
    //$smail->Username='jimrucinski@gmail.com';
    //$smail->Password='C@sh1t2013';
    //$smail->From='jrucinski@pma.com';
    //$smail->FromName='PMA Work Request System';
    $smail->From=MailFromAddress;
    $smail->FromName=MailFromName;
    foreach($ToAddress as $value)
    {
        $smail->addAddress($value);
    }
    $smail->Subject=$Subject;
    $smail->msgHTML($Msg);

    try{
        if(!$smail->Send())
        {
        echo "Mailer Error: " . $smail->ErrorInfo;
        }
        else
        {
        echo "Message has been sent";
        }
    } catch (Exception $ex) {
        echo $ex.message;
    }
  

    
}

