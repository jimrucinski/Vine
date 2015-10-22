<?php

function wpdm_indv_dls($id, $pack){    
    $idvdl = $_POST['wpdm_individual_download']?$_POST['wpdm_individual_download']:0;
    update_wpdm_meta($id,'individual_download',$idvdl);
}

function wpdm_url_protect($id, $pack){    
    $idvdl = $_POST['wpdm_url_protect']?$_POST['wpdm_url_protect']:0;
    update_wpdm_meta($id,'url_protect',$idvdl);
}


function wpdm_meta_update($id, $pack){            
    //deleteall_wpdm_meta($id);
    if(!isset($_POST['password_lock'])) delete_wpdm_meta($id,'password_lock');        
    if(!isset($_POST['gplusone_lock'])) delete_wpdm_meta($id,'gplusone_lock');
    if(!isset($_POST['email_lock'])) delete_wpdm_meta($id,'email_lock');
    if(!isset($_POST['facebooklike_lock'])) delete_wpdm_meta($id,'facebooklike_lock');
    if(!isset($_POST['tweet_lock'])) delete_wpdm_meta($id,'tweet_lock');
    if(!isset($_POST['linkedin_lock'])) delete_wpdm_meta($id,'linkedin_lock');
    update_wpdm_meta($id,'wpdm_download_limit_per_user',$_POST['wpdm_download_limit_per_user']);
     
    if(is_array($_POST['wpdm_meta'])){
    foreach($_POST['wpdm_meta'] as $meta=>$value){
    if(strpos($meta,'_date')) $value = strtotime($value);   
    update_wpdm_meta($id,$meta,$value);
    }}
}

function wpdm_preview_slider($file){
    $k = 1;
    if( $file['slider-previews']!='' ) return $file;
    $file['additional_previews'] = get_wpdm_meta($file[id],'more_previews');         
    $img = "<img style='' id='more_previews_{$k}' title='' class='more_previews' src='".plugins_url()."/download-manager/timthumb.php?w=".get_option('_wpdm_pthumb_w')."&h=".get_option('_wpdm_pthumb_h')."&zc=1&src={$file[preview]}'/>\n";
    $tmb = "<a href='#more_previews_{$k}' class='spt'><img title='' src='".plugins_url()."/download-manager/timthumb.php?w=".get_option('_wpdm_athumb_w').'&h='.get_option('_wpdm_athumb_h')."&zc=1&src={$file[preview]}'/></a>\n";
    if($file['additional_previews']){
        foreach($file['additional_previews'] as $p){
            ++$k;
            $img .= "<img style='display:none;' id='more_previews_{$k}' class='more_previews' title='' src='".plugins_url().'/download-manager/timthumb.php?w='.get_option('_wpdm_pthumb_w').'&h='.get_option('_wpdm_pthumb_h').'&zc=1&src=wp-content/plugins/download-manager/preview/'.$p."'/>\n";
            $tmb .= "<a href='#more_previews_{$k}' class='spt'><img id='more_previews_{$k}' title='' src='".plugins_url().'/download-manager/timthumb.php?w='.get_option('_wpdm_athumb_w').'&h='.get_option('_wpdm_athumb_h').'&zc=1&src=wp-content/plugins/download-manager/preview/'.$p."'/></a>\n";
        }}
        
    $file['slider-previews'] = "<div class='slider' style='margin-bottom:10px;'>".$img."<div style='clear:both'></div></div><div style='clear:both'></div><div class='tmbs'>$tmb</div>";
    
    
    return $file;
    
}

function preview_slider_js(){
    ?>
    <style type="text/css">
    .tmbs img{        
        padding:3px !important;margin:2.5px;border:1px slid #ccc; display: inline-table;
    }
    .more_previews{
        border:1px solid #ccc;
    }
    .pack_stats{
        padding:10px;
        border:1px solid #ccc;
        text-align: left !important;
        width: 50%;
    } 
    .pack_stats table{
        width:100%;
    }
    .pack_stats table td{
        border-bottom:1px solid #ccc;
    }
    .pack_stats table td:last{
        border-bottom:0px solid #ccc;
        
    }
    </style>
    <script language="JavaScript">
<!--
  
  jQuery(function(){
      
    jQuery('.spt').on('click',function(){        
        jQuery('.more_previews').css('position','absolute').fadeOut();
        jQuery(jQuery(this).attr('href')).css('position','absolute').fadeIn().css('position','static');
        return false;
    });
      
  });

//-->
</script>
    
    <?php
}
 

/**
* actions    
*/
//add_action("after_add_package","wpdm_indv_dls",999,2);
//add_action("after_update_package","wpdm_indv_dls",999,2);
//add_action("after_add_package","wpdm_url_protect",999,2);
//add_action("after_update_package","wpdm_url_protect",999,2);
//add_action("after_add_package","wpdm_meta_update",999,2);
//add_action("after_update_package","wpdm_meta_update",999,2);
//add_filter("wdm_before_fetch_template","wpdm_preview_slider");
//add_action("wp_head","preview_slider_js",999);
