<?php


require( ABSPATH  . 'PMA/PmaDb.class.php');
require( ABSPATH . WPINC . '/class-phpmailer.php');
require( ABSPATH . WPINC . '/class-smtp.php');
add_action('after_setup_theme', 'remove_admin_bar');


/*Hide the WP Black Admin bar for anyone who is not an admin*/
function remove_admin_bar() {

if (!current_user_can('editor') && !current_user_can('administrator') && !is_admin()) {
  show_admin_bar(false);
}
}




/* password_protected function added by Jim Rucinski to ensure that users are logged in before they view content*/
function password_protected() {
	if ( !is_user_logged_in() ){
		auth_redirect();
        }
}
add_action('template_redirect', 'password_protected');
add_action('do_feed', 'password_protected');

add_filter( 'auth_redirect_scheme', 'front_end_auth_redirect' );
function front_end_auth_redirect(){
    return 'logged_in';
}
/*end custom code by Jim Rucinski*/

if (!isset($content_width)) {
    $content_width = 1080;
}

/*end custom code by Jim Rucinski*/

function pma_divi_child_scripts(){
    wp_enqueue_script('extrapma.js', get_stylesheet_directory_uri() . '/js/pma.js', array( 'jquery' ),'',true);
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-form');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'); 
    
   
}
add_action( 'wp_enqueue_scripts','pma_divi_child_scripts' );

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );



function disallowedFileType(){
  $str = '<script type="text/javascript">var my_var =[';;
  
  $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);

    $sql = 'call sp_getDisallowedFileTypes()';
   
    $dbo->query($sql);
    $rows = $dbo->resultset();
    foreach ($rows as $row){
        $str .= '"'. $row['extension'] . '",';
    }
    $str .= '];</script>';
  
  return $str;
}
add_shortcode('PMA-DisallowedFileTypes','disallowedFileType');

function enqueue_parent_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

function pmaAddBreadcrumbs(){
    global $post;
    if($post->post_parent == true){
        $anc = get_post_ancestors($post->ID);
        $anc = array_reverse($anc);
        
    $str = '<ul class="BreadCrumbs">';
    foreach ( $anc as $ancestor ) {
        $str .= '<li><a href="'. get_permalink($ancestor) . '" title="' . get_the_title($ancestor) . '">' . get_the_title($ancestor) . '</a></li>';
    }
    $str .= '<li><a href="#">' . get_the_title() . '</a></li>';
    $str .= '</ul>';
    return $str;
    }
}
add_shortcode('PMA-Breadcrumbs','pmaAddBreadcrumbs');

function pmaInsertTitle($title){
	extract(shortcode_atts(array(
	'title' => 'value'
			), $title));

	$str = '<h2 style="letter-spacing:.15em">';
	$str .= $title;
	$str.='</h2>';
	return $str;
}
add_shortcode('PMA-Title','pmaInsertTitle');


function pmaInsertChildren() {
   global $post;
   return '<h4>Related Items</h4><ul style="list-style-type: none">'.wp_list_pages('echo=0&depth=0&title_li=&child_of='.$post->ID).'</ul>';
}
add_shortcode('PMA-Children', 'pmaInsertChildren');

function remove_admin_bar_links() {
	
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
	if(!current_user_can('manage_options')){
	$wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
	$wp_admin_bar->remove_menu('wporg');            // Remove the WordPress.org link
	$wp_admin_bar->remove_menu('documentation');    // Remove the WordPress documentation link
	$wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
	$wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
	//$wp_admin_bar->remove_menu('site-name');        // Remove the site name menu
	$wp_admin_bar->remove_menu('view-site');        // Remove the view site link
	$wp_admin_bar->remove_menu('updates');          // Remove the updates link
	$wp_admin_bar->remove_menu('comments');         // Remove the comments link
	$wp_admin_bar->remove_menu('new-content');      // Remove the content link
	$wp_admin_bar->remove_menu('w3tc');             // If you use w3 total cache remove the performance link
	//$wp_admin_bar->remove_menu('my-account');       // Remove the user details tab
		}
}

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

function pmaCreateSeachForm($atts){
    
    $a =shortcode_atts(['deptid' => ''
                        ], $atts
	) ;
    
    $str='<ul class="WR_Search"><li><label for="StatusType">Status</label>';
    $str .= do_shortcode("[PMA-RequestSelectBox sp='sp_getStatusTypes' id=1 nme='StatusType' keepvalue='true' blank='0']");
    $str .= '</li><li><label for="agent">Agent</label>';
    $str .= do_shortcode("[PMA-RequestSelectBox sp='sp_getTicketAgents' id='" .$a['deptid'] ."' nme='agent' keepvalue='true' blank='1']");
    $str .= '</li><li><label for="commentSearch">Resolution / Log Search</label>';
    $str .= '<input type="text" value="' . filter_input(INPUT_GET,'commentSearch',FILTER_SANITIZE_STRING) . '" id="commentSearch" name="commentSearch" style="width:200px;" />';
    $str .= '</li><li><label/><input  type="submit" value="submit"  />';
    $str .='</li></ul>';
    return $str;
}
add_shortcode('PMA-SearchForm','pmaCreateSeachForm');
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
                    $str .="request title";
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
       
    $a =shortcode_atts(['sp' => '',
			'id' => '',
			'nme' => '',
                        'keepvalue' => '',
                        'blank' => '',
                        'size' =>''
                        ], $atts
	) ;
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
    $rows;

if($a['sp'] !=''){
    $sql = 'call ' . $a['sp'] . "(" . $a['id'] . ")";
   
    $dbo->query($sql);
    $rows = $dbo->resultset();
}
    $str = "<select style='min-width:200px;'" . ($a['size']>0 ? " multiple='multiple' name='" . $a['nme'] . "[]' " : "name='" . $a['nme'] . "'") . "  id='" . $a['nme'] . "' size='" .$a['size'] ."'>";
    

    if($a['blank']==1 || $a['blank']=='1'){
        $str.= "<option/>";
    }
    //used to keep the selected item in the status dropdown set to user selection
    $selectedStatusId=null;
    if($a['nme'] == 'StatusType' && filter_input(INPUT_GET,'StatusType',FILTER_SANITIZE_STRING)!=null){
    $selectedStatusId = filter_input(INPUT_GET,'StatusType',FILTER_SANITIZE_STRING);
    }
    //used to keep the selected item in the agent dropdown set to user selection
    $selectedAgentId=null;
    if($a['nme'] == 'agent' && filter_input(INPUT_GET,'agent',FILTER_SANITIZE_STRING)!=null){
    $selectedAgentId = filter_input(INPUT_GET,'agent',FILTER_SANITIZE_STRING);
    }
    
    foreach ($rows as $row){
        $str .= "<option  value='{$row['val']}' ";        
        //if($a['keepvalue'] == true &&  $row['val']==$selectedStatusId){
        if(($selectedStatusId!=null && $row['val']==$selectedStatusId) || $row['val'] == $a['keepvalue'] ){
            
            $str .= " selected ";
        }
        if(($selectedAgentId!=null && $row['val']==$selectedAgentId) || $row['val'] == $a['keepvalue'] ){
            
            $str .= " selected ";
        }
        $str .= ">{$row['txt']}</option>";
    }
    $str .= "</select>";
    return $str;
}
add_shortcode('PMA-RequestSelectBox','pmaCreateRequestTypeDropdown');

function determinePaging($totRecs, $atts)
{
    $numPages =$totRecs / $atts[7];
    $str = '<div>';
    for($i=1;$i<=$numPages;$i++){
        $str .= createPageLink($i, $atts);
    }
    if($totRecs % $atts[7] != 0){
        $str .= createPageLink($i, $atts);
//$str .= '<span class="pagination"><a href="' . $pageUrl . '?sort=' . $atts[0] . '&ad=' . $atts[1] . '&StatusType=' . $atts[3] . '&agent=' . $atts[4] . '&commentSearch=' . $atts[5] .'&pge=' .$i . '">'  . $i  . '</a></span>';
    }
        $str .= '</div>';
    return $str;
   // a href='{$curUrl}?sort=tix.last_name&ad={$results[2]}&StatusType={$results[3]}&agent={$results[4]}&commentSearch={$results[5]}&page={$results[8]}'
}

function createPageLink($pageNum, $atts){
    global $wp;
    $pageUrl =home_url() . '/' . $wp->request;
    $str = "";
    $curPage = (!isset($_REQUEST['pge']))?1:$_GET['pge'];  
    if($curPage == $pageNum){
        $str= $pageNum;
    }
 else {
        $str .= '<span class="pagination"><a href="' . $pageUrl . '?sort=' . $atts[0] . '&ad=' . $atts[1] . '&StatusType=' . $atts[3] . '&agent=' . $atts[4] . '&commentSearch=' . $atts[5] .'&pge=' .$pageNum . '">'  . $pageNum  . '</a></span>';

    }
    return $str;
}

function determineTixQuery(){

    $sortby = (isset($_REQUEST['sort']))? $_GET['sort']:null;
    $ad = (isset($_REQUEST['ad']))? $_GET['ad']:null;
    $reverse = ($ad=='asc' || $ad==''?'desc':'asc'); 
    $statustype =(!isset($_REQUEST['StatusType']))? '-1':$_GET['StatusType'];
    $agentId = (!isset($_REQUEST['agent']) || $_REQUEST['agent']=='')?-1:$_GET['agent'];
    $cmtSearch = (!isset($_REQUEST['commentSearch']))?'':trim($_GET['commentSearch']);    
    $recLimit = 50;
    $recOffSet = (!isset($_REQUEST['pge']) || $_GET['pge']==1 || $_GET['pge']==0)?0:((($_GET['pge']-1)*$recLimit));
    $pagenum = (!isset($_REQUEST['pge']))?0:$_GET['pge'];    
    return array($sortby, $ad, $reverse, $statustype, $agentId, $cmtSearch, $recOffSet, $recLimit, $pagenum);
}

function pmaAllWorkRequestsTable($atts){
    $results = determineTixQuery();
    if($results[3] != null){
    global $wp;
    $curUrl = rtrim(home_url(add_query_arg(array(),$wp->request)),"/");
        $a =shortcode_atts( array(
                        'id' => '',
			'sortby' => '',
                        'ad' =>''
		), $atts
	) ;
    
    $totalRecords=0;
    $sql = 'call sp_getTicketsInOrder(' .$a['id'] . ',' . $results[3] . ',' . $results[4] . ',' . ($results[5]==''?"''":"'".$results[5]."'") .  ',"' . ($results[0]=='' ? $a['sortby'] : $results[0]) . '","'  . ($results[1]== ''?$a['ad']:$results[1]) . '",' . $results[6] . ',' . $results[7] . ',@outrecs)';
//echo $sql . ";";
//die();
   
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
    $rows = $dbo->queryForObjs($sql);
    $tots = $dbo->queryForObjs("select @outrecs");//get the output parameter of the proc for the total number of records sans the LIMIT/OFFSET
    $numRecs = $tots[0][0];
    
    $lateCss = "";
    $str = $numRecs . ' records found.';
    $str .= "<table class='responsive-stacked-table' cellpadding='0' cellspacing='0' id='PmaWorkRequestsTable'><thead><tr><th>id</th><th><a href='{$curUrl}?sort=tix.last_name&ad={$results[2]}&StatusType={$results[3]}&agent={$results[4]}&commentSearch={$results[5]}&pge={$results[8]}'>requestor</a></th><th width='33%'>title</th><th><a href='{$curUrl}?sort=tix.due_date&ad={$results[2]}&StatusType={$results[3]}&agent={$results[4]}&commentSearch={$results[5]}&pge={$results[8]}'>due date<a/></th><th><a href='{$curUrl}?sort=ta.last_name&ad={$results[2]}&StatusType={$results[3]}&agent={$results[4]}&commentSearch={$results[5]}&pge={$results[8]}'>assigned</a></th><th><a href='{$curUrl}?sort=st.status&ad={$results[2]}&StatusType={$results[3]}&agent={$results[4]}&commentSearch={$results[5]}&pge={$results[8]}'>status</a></th><th><a href='{$curUrl}?sort=tix.date_submitted&ad={$results[2]}&StatusType={$results[3]}&agent={$results[4]}&commentSearch={$results[5]}&pge={$results[8]}'>date submitted</a></th></tr></thead>";

    if($numRecs > 0){
    foreach($rows as $row){
        $lateCss = "";
        if($row['status_id']!= 3 && $row['is_old']!=null){
            $lateCss = 'overdue';
        }
        $str .= '<tr><td data-label="ID">' . $row['id'] . '</td><td data-label="requestor">' . $row['requestor'] . '</td><td data-label="title"><a target="WorkRequest" href="./edit-work-request/?workrequestid=' . $row['id'] . '">' . stripslashes($row['request_title']) . '</a></td><td data-label="due date" class="' . $lateCss .'">' . $row['due_date'] . '</td><td data-label="assigned">' . $row['assignedTo'] . '</td><td data-label="status">' . $row['status'] . '</td><td data-label="date submitted">' . $row['date_submitted'] . '</td></tr>';        
        if($results[5] != ''){
            $str .= '<tr><td colspan="7">' . str_ireplace($results[5], '<span class="highlight">' . $results[5] . '</span>',str_replace("\\n","<br/>",$row["comments"])) . '</td></tr>';
        }   
    }
    /*Pagination */
    $str .= '</tr>';
    $str .= '<tr><td colspan="7" align="center">' . determinePaging($numRecs, $results) . '</td></tr>';
    }
    else
    {
        $str.='<tr><td colspan="7">no records found</td></tr>';
    }
    $str .= '</table>';
    
    
    
    return $str;
    }
}
add_shortcode('PMA-AllWorkRequestsTable','pmaAllWorkRequestsTable');

function pmaGetWorkRequest($atts){
    //global $wp;
    $a =shortcode_atts(['deptid' => ''
                        ], $atts
	) ;
    
    
    global $current_user;
    //handle file delete request
    if(isset($_REQUEST['SupportFile'])){
        try{
            get_currentuserinfo();
            $curUser = $current_user->user_login;
            $currentWID = $_REQUEST['workrequestid'];
            $delId = $_REQUEST['SupportFile']; 
            $sql = 'call sp_deleteSupportFile(' . $currentWID . ',' .$delId . ',"' . $curUser . '")';
            $dbo = PmaPdoDb::getInstance();
            $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
            $dbo->query($sql);
            $dbo->execute();
            //$rs = $dbo->execute(); 
        }
        catch(Exception $exp)
        {
            echo "EXCEPTION:<br/>" . $exp.message;
            die();
        }
    }
    if(isset($_REQUEST['workrequestid']))
    {
    $wid = filter_input(INPUT_GET,"workrequestid");
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
    //session_start();
    $_SESSION['OriginalTicketVals'] = $rows;
    $str=getTinyMceScript();     
    //$str = do_shortcode("[PMA-Breadcrumbs]") . '<div style="float:right"><font class="ChangeLogHeader">Change Log</font></div>';
    $str .= '<h4>' . $rows[0]['request_title'] .'</h4>';  
    
    $str .= '<script type="text/javascript">function checkFiles(){var F = document.getElementById("upload_file");if("files" in F){var s=0;for (var i = 0; i < F.files.length; i++){var file = F.files[i];s+=file.size;}};if(s > 8388608){alert("Total File Size is Greater Than Allowable 8 Meg Limit");return false;}else{return true;}}</script>';
    $str .= '<form action="../../../../PMA/EditTicket.php" method="POST" name="EditTicket" id="EditTicket" enctype="multipart/form-data">';
    $str .= '<div style="float:left;width:60%;"><fieldset style="position:relative;width:95%;" ><ul class="PmaSupportTicket">';
    foreach($rows as $row){
        $str .= '<li><label>Name</label>';
        $str .=  $row['first_name'] . ' ' . $row['last_name'] . '</li><li><label>email</label><a href="mailto://' . $row['email'] . '">' . $row['email'] . '</a></li><li><label>date submitted</label>' . $row['date_submitted']  . '</li>';
        if($row['status']==3)
        {
            $str .= '<li><label>date complete</label>' . $row['date_complete'] . '</li>';
            if($row['total_days']>0){

               $str .= '<li><label>days to complete</label>' . $row['total_days'] . '</li>';
            }
            else {           
            $str .= '<li><label>hours to complete</label>' . $row['total_hours'] . '</li>';}
        }
        $str .= '<li><label>due date</label><input class="required" id="due_date"   name="due_date" type="text" value="' . $row['due_date'] . '" /></li>';
        
        $str .= '<li><label id="agent_label">assigned to</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getTicketAgents' id=" . $a['deptid'] . " nme='agent' keepvalue=" . $row['agent']. " blank=1)]");  '</li>';
        $str .= '<li><label>status</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getStatusTypesForEdit' id=" . $a['deptid'] . " nme='status' keepvalue=" . $row['status'] . ']'); '</li>';
        $str .= '<li><label>request type</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getDeptRequestTypes' id=" . $a['deptid'] . " nme='requestType' keepvalue=" . $row['request_type'] . " blank=0]");  '</li>';
        $str .= pmaCreateInputBox('requestTitle',  $row['request_title']);
        $str .= '<li><label>description</label><span class="tixDesc">' . htmlspecialchars_decode($row['request_desc']) . '</span></li>';        
        $str .= '<li><label  id="upload_file_label">support files</label><input type="file" id="upload_file" name="upload_file[]" multiple=""/></li>';
        $str .= ($row['resolution'] !='' ? '<li><label id="resolution_label">resolution</label><p style="display:inline-block;">' . nl2br($row['resolution']) .'</p></li>':'');
        $str .= '<li><label id="comment_label">comment</label><textarea id="comments" name="comments" rows="3" style="width:98%;"></textarea>';        
        $str .= '<li><label>&nbsp;</label><input type="submit" value="submit" id="go" name="go" /></li>';
    }
    $str .= '</ul></fieldset></div></div>';
    $str .= '</form>';

    //start Change Log 
    $ticketlog = 'call sp_getTicketLog(' . $wid .',@tixDesc)';
    $dbo->query($ticketlog);
    $log = $dbo->resultset();
    $str .='<div id="rightPanel">';
    if(!empty($log)){
        $str .= '<a target ="new" href="../../../../PMA/PrintLog.php?workrequestid=' . $wid . '">print log</a><br/>';         
        $str .='<div class="log">';
        foreach($log as $l){
            $str .='<div><strong>' . $l["tstamp"] . ': ' . $l["user"] . '</strong><br/>' . str_replace("\\n","<br/>",$l["ticket_comment"]) . '</div>';
        }
        $str .= '</div>';
    }
    else{
        $str .='<div class="log" style="background-color:#666666;color:#ffffff;text-align:center;">no log found</div>';
    }
    $str .='</div>';
    //start File Attachments
    $attachments = 'call sp_getTicketFileAttachments(' .$wid . ')';
    $dbo->query($attachments);
    $fas =  $dbo->resultset();


    if(!empty($fas[0])){
        $str .= '<div id="supportTix"><font class="ChangeLogHeader" style="text-align:left;">Support Documents</font>';
        $str .='<div id="supportFiles">';
        foreach($fas as $fa){
            $str .=  '<div style="margin: 10px;"><a onclick="return confirm(\'Are you sure you want to delete the selected support document: ' . $fa['fileName'] . '?\');" href=".?workrequestid=' . $wid . '&SupportFile=' . $fa['id'] .'"><img style="margin-right:1em;border-right: .5px solid #ebebeb;" title="Delete ' . $fa['fileName'] . '"  src="' . get_option("siteurl") .'/PMA/images/delete-icon.png"/></a><a title="Download ' . $fa['fileName'] . '" href="' . get_option("siteurl") .'/PMA/PmaGetFile.php?id=' . $fa['id'] . '">';
           $str .= getIconForMimeType($fa['mime']);
           $str .= $fa['fileName'] . '</a></div>';
        }
        $str .='</div>';
        
    }
   $str .= '</div>';
    return $str;
}
add_shortcode('PMA-GetWorkRequest', 'pmaGetWorkRequest');
   
function pmaGetOfficeServicesRequest(){
    //global $wp;
    global $current_user;
    //handle file delete request
    /*if(isset($_REQUEST['SupportFile'])){
        try{
            get_currentuserinfo();
            $curUser = $current_user->user_login;
            $currentWID = $_REQUEST['workrequestid'];
            $delId = $_REQUEST['SupportFile']; 
            $sql = 'call sp_deleteSupportFile(' . $currentWID . ',' .$delId . ',"' . $curUser . '")';
            $dbo = PmaPdoDb::getInstance();
            $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);
            $dbo->query($sql);
            $dbo->execute();
            //$rs = $dbo->execute(); 
        }
        catch(Exception $exp)
        {
            echo "EXCEPTION:<br/>" . $exp.message;
            die();
        }
    }*/
    if(isset($_REQUEST['workrequestid']))
    {
    $wid = filter_input(INPUT_GET,"workrequestid");
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
    //session_start();
    $_SESSION['OriginalTicketVals'] = $rows;
    $str=getTinyMceScript();     
    //$str = do_shortcode("[PMA-Breadcrumbs]") . '<div style="float:right"><font class="ChangeLogHeader">Change Log</font></div>';
    $str .= '<h4>' . $rows[0]['request_title'] .'</h4>';  
    
    $str .= '<script type="text/javascript">function checkFiles(){var F = document.getElementById("upload_file");if("files" in F){var s=0;for (var i = 0; i < F.files.length; i++){var file = F.files[i];s+=file.size;}};if(s > 8388608){alert("Total File Size is Greater Than Allowable 8 Meg Limit");return false;}else{return true;}}</script>';
    $str .= '<form action="../../../../PMA/EditTicket.php" method="POST" name="EditTicket" id="EditTicket" enctype="multipart/form-data">';
    $str .= '<div style="float:left;width:60%;"><fieldset style="position:relative;width:95%;" ><ul class="PmaSupportTicket">';
    foreach($rows as $row){
        $str .= '<li><label>Name</label>';
        $str .=  $row['first_name'] . ' ' . $row['last_name'] . '</li><li><label>email</label><a href="mailto://' . $row['email'] . '">' . $row['email'] . '</a></li><li><label>date submitted</label>' . $row['date_submitted']  . '</li>';

        if($row['status']==3)
        {
            $str .= '<li><label>date complete</label>' . $row['date_complete'] . '</li>';
            if($row['total_days']>0){

               $str .= '<li><label>days to complete</label>' . $row['total_days'] . '</li>';
            }
            else {           
            $str .= '<li><label>hours to complete</label>' . $row['total_hours'] . '</li>';}
        }
        $str .= '<li><label id="agent_label">assigned to</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getTicketAgents' id=4 nme='agent' keepvalue=" . $row['agent']. " blank=0)]");  '</li>';
        
        
        
        $str .= '<li><label>due date</label><input class="required" id="due_date"   name="due_date" type="text" value="' . $row['due_date'] . '" /></li>';
       
        $str .= '<li><label>materials to O.S.</label><input class="required" id="material_to_office_services"   name="material_to_office_services" type="text" value="' . $row['material_to_office_services'] . '" /></li>';
        
        $str .= '<li><label>charge code</label><input class="required" id="charge_code"   name="charge_code" type="text" value="' . $row['charge_code'] . '" /></li>';
        $str .= '<li><label>quantity</label><input class="required" id="quantity"   name="quantity" type="text" value="' . $row['quantity'] . '" /></li>';
        $str .= '<li><label>request type</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getDeptRequestTypes' id=4 nme='requestType' keepvalue=" . $row['request_type'] . " blank=0]");  '</li>';
        $str .= '<li><label>envelope type</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getEnvelopeTypesForEdit' id=1 nme='envelope_type' keepvalue=" . $row['envelope_type'] . ']'); '</li>';
        $str .= '<li><label>status</label>' . do_shortcode("[PMA-RequestSelectBox sp='sp_getStatusTypesForEdit' id=1 nme='status' keepvalue=" . $row['status'] . ']'); '</li>';
        $str .= pmaCreateInputBox('requestTitle',  $row['request_title']);
        $str .= '<li><label>description</label><span class="tixDesc">' . htmlspecialchars_decode($row['request_desc']) . '</span></li>';        
        //$str .= '<li><label  id="upload_file_label">support files</label><input type="file" id="upload_file" name="upload_file[]" multiple=""/></li>';
        $str .= ($row['resolution'] !='' ? '<li><label id="resolution_label">resolution</label><p style="display:inline-block;">' . nl2br($row['resolution']) .'</p></li>':'');
        $str .= '<li><label id="comment_label">comment</label><textarea id="comments" name="comments" rows="3" style="width:98%;"></textarea>';        
        $str .= '<li><label>&nbsp;</label><input type="submit" value="submit" id="go" name="go" /></li>';
    }
    $str .= '</ul></fieldset></div></div>';
    $str .= '</form>';

    //start Change Log 
    $ticketlog = 'call sp_getTicketLog(' . $wid .',@tixDesc)';
    $dbo->query($ticketlog);
    $log = $dbo->resultset();
    $str .='<div id="rightPanel">';
    if(!empty($log)){
        $str .= '<a target ="new" href="../../../../PMA/PrintLog.php?workrequestid=' . $wid . '">print log</a><br/>';         
        $str .='<div class="log">';
        foreach($log as $l){
            $str .='<div><strong>' . $l["tstamp"] . ': ' . $l["user"] . '</strong><br/>' . str_replace("\\n","<br/>",$l["ticket_comment"]) . '</div>';
        }
        $str .= '</div>';
    }
    else{
        $str .='<div class="log" style="background-color:#666666;color:#ffffff;text-align:center;">no log found</div>';
    }
    $str .='</div>';
    //start File Attachments
    $attachments = 'call sp_getTicketFileAttachments(' .$wid . ')';
    $dbo->query($attachments);
    $fas =  $dbo->resultset();


    if(!empty($fas[0])){
        $str .= '<div id="supportTix"><font class="ChangeLogHeader" style="text-align:left;">Support Documents</font>';
        $str .='<div id="supportFiles">';
        foreach($fas as $fa){
            $str .=  '<div style="margin: 10px;"><a onclick="return confirm(\'Are you sure you want to delete the selected support document: ' . $fa['fileName'] . '?\');" href=".?workrequestid=' . $wid . '&SupportFile=' . $fa['id'] .'"><img style="margin-right:1em;border-right: .5px solid #ebebeb;" title="Delete ' . $fa['fileName'] . '"  src="' . get_option("siteurl") .'/PMA/images/delete-icon.png"/></a><a title="Download ' . $fa['fileName'] . '" href="' . get_option("siteurl") .'/PMA/PmaGetFile.php?id=' . $fa['id'] . '">';
           $str .= getIconForMimeType($fa['mime']);
           $str .= $fa['fileName'] . '</a></div>';
        }
        $str .='</div>';
        
    }
   $str .= '</div>';
 return $str;
}
add_shortcode('PMA-GetOfficeServicesRequest', 'pmaGetOfficeServicesRequest');

function getIconForMimeType($mime)
{
    $str='<img src="' . get_option("siteurl") .'/PMA/images/';
    switch($mime){
                case "application/vnd.ms-excel";
                case "application/vnd.openxmlformats-officedocument.spre";
                    $str .= 'excel.png"/>';
                    break;
                case "application/pdf":
                    $str .= 'pdf.png"/>';
                    break;
                case "application/vnd.openxmlformats-officedocument.word":
                    $str .= 'word.png"/>';
                    break;
                case "application/x-zip-compressed":
                    $str .= 'zip.png"/>';
                    break;
                case "text/plain":
                    $str .= 'text.png"/>';
                    break;
                case "image/jpeg";
                case "image/gif";
                case "image/png";
                    $str .= 'image.png"/>';
                    break;
                case "text/csv":
                    $str .= 'excel.png"/>';
                    break;
                case "application/octet-stream":
                    $str .= 'binary.png"/>';
                    break;
                default:
                    $str .= 'default.png"/>';
                    break;
            }
            return $str;
}

function sendMail($Subject, $Msg, $ToAddress, $NotifyAdmin, $FromName = NULL)
{
	$smail = new PHPMailer();
	$smail->CharSet = 'UTF-8';
    $smail->IsSMTP();
	$smail->IsHTML(true);	
    $smail->Host =MailHost;
    $smail->SMTPDebug = 0;
	$smail->SMTPSecure = "none";
    $smail->SMTPAuth = MailHostSmtpAuth;    
    $smail->Username = MailHostUsername;
    $smail->Password=MailHostPassword;
    $smail->From=MailFromAddress;
    $smail->FromName=($FromName != NULL)?$FromName:MailFromName;
    foreach($ToAddress as $value)
    {
         $smail->addAddress($value,$value);        
    }
    if($NotifyAdmin){

    $smail->addCC(WR_IT_Admin);
    }
    $smail->Subject=$Subject;
    $smail->msgHTML($Msg);

    try{
        if(!$smail->Send())
        {
        echo "Mailer Error: " . $smail->ErrorInfo;
        die();
        }
        
    } catch (phpmailerException $ex) {
       // echo $ex->errorMessage();
    }
}

function email_subscribers($post_ID){
    $post = get_post($post_ID);
    if($post->post_date == $post->post_modified ){//run only on new post creation
    $sql = 'call sp_getSubscriberEmails()';
    $dbo = PmaPdoDb::getInstance();
    $dbo->connect(DB_HOST,DB_USER, DB_PASSWORD, DB_NAME);

    $dbo->query($sql);
    $rows = $dbo->resultset();
    $emails = array();
	$emails[] = VinePostingEmailNode;
    //foreach ($rows as $row){
    //     $emails[] = $row['user_email'];
    // }
    
     
    // $author = get_user_by('id', $post->post_author );
     //echo ucwords($post->post_title) . '<br/>';
    // echo 'by: ' . $author->display_name  . '<br/>';
   $category = get_the_category($post);
   $emailCats = "";
   foreach($category as $cat){
       $emailCats .= '[ ' . $cat->cat_name . ' ]';
   }
     $vineImg = '<img src="http://www.pma.com/~/media/pma-images/grapevineEmailImage.png" style="float:left;" alt="PMA Grapevine" />';
     //$vineImg = '<img src="http://jxr980:8020/vinedev/wp-content/uploads/2015/07/transgrape2_small.png" style="float:left; alt="PMA Grapevine"  />';
     $emailTitle = 'New Vine Post: ' . ucwords($post->post_title);
     $postLink = '<p><a href="' . get_permalink($post_ID) . '">Read more of this post</a></>';
     
     $msg = '<table><tr><td>' . $vineImg . '</td><td style="font-family:calibri; width:120px;valign:middle;text-align:left;"><H3>' . ucwords($post->post_title) . '</H3><h5>' . $emailCats . '</h5></td></tr>';
     $msg .= '<tr><td style="font-family:calibri;" colspan="2">' . get_excerpt_by_id($post) . $postLink . '</td></tr></table>';
    //echo $post->post_type;  
    //echo $msg;
     //echo $post->post_date . '<br/>' . $post->post_modified . '<br/>';
     //die();
     sendMail($emailTitle, $msg, $emails,false,'PMA Grapevine');
    return $post_ID;
    }
}
add_action('publish_post','email_subscribers');


function get_excerpt_by_id($the_post){
$suppliedExcerpt = $the_post->post_excerpt;
if($suppliedExcerpt != null){
    $the_excerpt =$suppliedExcerpt;    
}
else
{
$the_excerpt = nl2br($the_post->post_content); //Gets post_content to be used as a basis for the excerpt
$excerpt_length = 50; //Sets excerpt length by word count
$the_excerpt = strip_shortcodes($the_excerpt); 
$the_excerpt = strip_tags($the_excerpt,'<br>');
$words = explode(' ', $the_excerpt, $excerpt_length + 1);
if(count($words) > $excerpt_length) :
array_pop($words);
array_push($words, "&hellip;");
$the_excerpt = implode(' ', $words);
endif;
$the_excerpt = $the_excerpt ;
}
return $the_excerpt;
}



/*add logout to menu KEEP THIS FUNCTION AT THE END OF THIS FILE!!!!!!! ELSE THE MENU GETS SCREWED UP*/
add_filter('wp_nav_menu_items', 'add_login_logout_link', 10, 2); 
function add_login_logout_link($items, $args) {
    ob_start();
    wp_loginout('index.php');
    $loginoutlink = ob_get_contents();
    ob_end_clean();
    $items .= '<li>'. $loginoutlink .'</li>';
    return $items;
    }
    /*include javascript for use of TinyMCE rich text editor*/
function getTinyMceScript(){

    $root = get_site_url() .  "/wp-content/themes/pma-divi-child/js/tinymce/tinymce.min.js";    
    $str = "<script src='" . $root . "'></script><script>tinymce.init({selector:'textarea',plugins: [
        'link   anchor'
    ],
    contextmenu: 'link image inserttable | cell row column deletetable',
    menubar:false,
    statusbar:false,    
    toolbar: ' bold italic |  bullist numlist  | link unlink image'});</script>"; 
    return $str;
}
add_shortcode('PMA-GetTinyMceScript','getTinyMceScript');
?>