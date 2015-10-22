<?php

    $etpl = get_option('_wpdm_etpl');  
    if(isset($_GET['loadetpl'])&&file_exists(dirname(__FILE__).'/email-templates/'.esc_attr($_GET['loadetpl']))){
        $etpl['body'] = file_get_contents(dirname(__FILE__).'/email-templates/'.esc_attr($_GET['loadetpl'])); 
    }

?>
<style type="text/css">
    #wphead{
        border-bottom:0px;
    }
    #screen-meta-links{
        display: none;
    }
    .wrap{
        margin: 0px;
        padding: 0px;
    }
    #wpbody{
        margin-left: -19px;
    }
</style>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/download-manager/bootstrap/css/bootstrap.css');?>" />

<style>

    input{
        padding: 7px;
    }
    #wphead{
        border-bottom:0px;
    }
    #screen-meta-links{
        display: none;
    }
    .wrap{
        margin: 0px;
        padding: 0px;
    }
    #wpbody{
        margin-left: -19px;
    }
    select{
        min-width: 150px;
    }

    .wpdm-loading {
        background: url('<?php  echo plugins_url('download-manager/images/wpdm-settings.png'); ?>') center center no-repeat;
        width: 16px;
        height: 16px;
        /*border-bottom: 2px solid #2a2dcb;*/
        /*border-left: 2px solid #ffffff;*/
        /*border-right: 2px solid #c30;*/
        /*border-top: 2px solid #3dd269;*/
        /*border-radius: 100%;*/

    }

    .w3eden .btn{
        border-radius: 0.2em !important;
    }

    .w3eden .nav-pills a{
        background: #f5f5f5;
    }

    .w3eden .form-control,
    .w3eden .nav-pills a{
        border-radius: 0.2em !important;
        box-shadow: none !important;
        font-size: 9pt !important;
    }

    .wpdm-spin{
        -webkit-animation: spin 2s infinite linear;
        -moz-animation: spin 2s infinite linear;
        -ms-animation: spin 2s infinite linear;
        -o-animation: spin 2s infinite linear;
        animation: spin 2s infinite linear;
    }

    @keyframes "spin" {
        from {
            -webkit-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(359deg);
            -moz-transform: rotate(359deg);
            -o-transform: rotate(359deg);
            -ms-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-moz-keyframes spin {
        from {
            -moz-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -moz-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-webkit-keyframes "spin" {
        from {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-ms-keyframes "spin" {
        from {
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -ms-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-o-keyframes "spin" {
        from {
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -o-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    .panel-heading h3.h{
        font-size: 11pt;
        font-weight: 700;
        margin: 0;
        padding: 5px 10px;
        font-family: 'Open Sans';
    }

    .panel-heading .btn.btn-primary{

        border-radius: 3px;
        border:1px solid rgba(255,255,255,0.8);
        -webkit-transition: all 400ms ease-in-out;
        -moz-transition: all 400ms ease-in-out;
        -o-transition: all 400ms ease-in-out;
        transition: all 400ms ease-in-out;
    }

    .panel-heading .btn.btn-primary:hover{

        border-radius: 3px;
        border:1px solid rgba(255,255,255,1);

    }

    .alert-info {
        background-color: #DFECF7 !important;
        border-color: #B0D1EC !important;
    }

    ul.nav li a:active,
    ul.nav li a:focus,
    ul.nav li a{
        outline: none !important;
    }

    .w3eden .nav-pills li.active a,
    .btn-primary,
    .w3eden .panel-primary > .panel-heading{
        background-image: linear-gradient(to bottom, #2081D5 0px, #1B6CB2 100%) !important;
    }
    .w3eden .panel-default > .panel-heading {
        background-image: linear-gradient(to bottom, #F5F5F5 0px, #E1E1E1 100%);
        background-repeat: repeat-x;
    }
    .w3eden thead{
        background: #dddddd;
    }


</style>
<div class="wrap w3eden">
    <div class="panel panel-primary" style="margin: 30px">
        <div class="panel-heading">
            <b style="font-size: 12pt;line-height:28px"><i class="fa fa-users"></i> &nbsp; <?php echo __("Subscribers", "wpdmpro"); ?></b>
           <a style="margin-left: 10px" id="basic" href="edit.php?post_type=wpdmpro&page=emails&task=export" class="btn btn-sm btn-primary pull-right"><?php echo __('Export All','wpdmpro'); ?></a>
           <a id="basic" href="edit.php?post_type=wpdmpro&page=emails&task=export&uniq=1" class="btn btn-sm btn-primary pull-right"><?php echo __('Export Unique Emails','wpdmpro'); ?></a>&nbsp;

        </div>

        <ul id="tabs" class="nav nav-tabs" style="padding: 10px 10px 0 10px;background: #f5f5f5">
            <li><a id="basic" href="edit.php?post_type=wpdmpro&page=emails"><?php echo __('Emails','wpdmpro'); ?></a></li>
            <li class="active"><a id="basic" href="edit.php?post_type=wpdmpro&page=emails&task=template"><?php echo __('Email Template','wpdmpro'); ?></a></li>
            </ul>

 


           
<form method="post" action="" id="posts-filter" style="padding: 20px;">
<input name="task" value="save-etpl" type="hidden" />
<div style="margin-bottom: 10px;padding-bottom: 10px;border-bottom: 1px solid #eeeeee;">
<b>Load Template:</b> 
 
<select id="xtpl" class="form-control input-sm" style="display: inline !important;width: 300px">
<?php
$xtpls = scandir(dirname(__FILE__).'/email-templates/');

foreach($xtpls as $xtpl){
    $tmp = explode('.', $xtpl);
    if(end($tmp)=='html')
    echo "<option value='{$xtpl}'>{$xtpl}</option>";
}
 
?>
</select>
<input type="button" value="Load" class="btn btn-info btn-sm" onclick="location.href='edit.php?post_type=wpdmpro&page=emails&task=template&loadetpl='+jQuery('#xtpl').val();">
 </div>
Subject:
<input  class="form-control input-lg" type="text" value="<?php echo isset($etpl['subject'])?htmlentities(stripcslashes($etpl['subject'])):''; ?>" placeholder="Subject" name="et[subject]" /><br/><br/>
<b>Template:</b>
<div id="poststuff" class="postarea" contentEditable="true" style="border-radius: 3px;border: 1px solid #ccc;padding:10px;">
<?php echo htmlspecialchars_decode(stripslashes($etpl['body'])); //,'et[body]','body', false, false); ?>                
</div>
<input type="hidden" name="et[body]" value="" id="mbd" />
<input type="hidden" value="0" id="rst" />
<br/>
<b>Variables:</b><br/>
<code>[download_url]</code> - Download URL<Br/>
<code>[title]</code> - Package Title<Br/>
<code>Double click on image to change it</code>
<br/>From Mail:
<input class="form-control input-lg" type="text" value="<?php echo isset($etpl['frommail'])?$etpl['frommail']:''; ?>" placeholder="From Mail" name="et[frommail]" /><br/><br/>
From Name:
<input class="form-control input-lg" type="text" value="<?php echo isset($etpl['fromname'])?htmlentities(stripcslashes($etpl['fromname'])):''; ?>" placeholder="From Name" name="et[fromname]" /><br/><br/>

<input type="submit" class="btn btn-primary button-large" value="Save Template"  style="margin-top: 10px;">
</form>
<br class="clear">

</div>
</div>

 <script language="JavaScript">
 <!--
   jQuery(function(){
       jQuery('#rst').val(0);
       jQuery('#posts-filter').submit(function(){
           if(jQuery('#rst').val()==1) return true;      
           jQuery('#mbd').val(jQuery('#poststuff').html());           
           jQuery('#rst').val(1);
           jQuery('#posts-filter').submit();  
           //if(jQuery('#rst').val()==0) return false;      
       });
       
       jQuery('#poststuff img').dblclick(function() {                            
                var ob = jQuery(this);
                tb_show('', '<?php echo admin_url('media-upload.php?type=image&TB_iframe=1&width=640&height=551'); ?>');
                window.send_to_editor = function(html) {           
                  var imgurl = jQuery('img',"<p>"+html+"</p>").attr('src');                     
                  jQuery(ob).attr("src",imgurl).css("max-width","100%").css("max-height","100%");
                  tb_remove();
                  }
                return false;
            });
 
       
   });
 //-->
 </script>