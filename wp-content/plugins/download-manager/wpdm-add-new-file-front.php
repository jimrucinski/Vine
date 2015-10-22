
<?php
if(wpdm_query_var('id','num'))
$post = get_post(wpdm_query_var('id','num'));
else {
$post = new stdClass();
$post->ID = 0;
$post->post_title = '';
$post->post_content = '';
}
?>

 <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/download-manager/css/chosen.css'); ?>" />
<style>
    .cat-panel ul,
    .cat-panel label,
    .cat-panel li{
        padding: 0;
        margin: 0;
        font-size: 9pt;
    }
    .cat-panel ul{
        margin-left: 20px;
    }
    .cat-panel > ul{
        padding-top: 10px;
    }
</style>
<div class="wpdm-front"><br>
<form id="wpdm-pf" action="" method="post">
<div class="row">

    <div class="col-md-8">


<input type="hidden" id="act" name="act" value="<?php echo wpdm_query_var('task', 'txt')=='edit-package'?'_ep_wpdm':'_ap_wpdm'; ?>" />

<input type="hidden" name="id" id="id" value="<?php echo wpdm_query_var('id', 'num'); ?>" />
<div class="form-group">
<input id="title" class="form-control input-lg"  placeholder="Enter title here" type="text" value="<?php echo isset($post->post_title)?$post->post_title:''; ?>" name="pack[post_title]" /><br/>
</div>
<div  class="form-group">
<?php $cont = isset($post)?$post->post_content:''; wp_editor(stripslashes($cont),'post_content',array('textarea_name'=>'pack[post_content]')); ?>
</div>

        <div class="panel panel-default" id="package-settings-section">
            <div class="panel-heading"><b>Attached Files</b></div>
            <div class="panel-body">
                <?php
                require_once dirname(__FILE__)."/tpls/metaboxes/attached-files-front.php";
                ?>
            </div>
        </div>

        <div class="panel panel-default" id="package-settings-section">
            <div class="panel-heading"><b>Package Settings</b></div>
            <div class="panel-body">
                <?php
                require_once dirname(__FILE__)."/tpls/metaboxes/package-settings-front.php";
                ?>
            </div>
        </div>


</div>
<div class="col-md-4">

    <div class="panel panel-default" id="package-settings-section">
        <div class="panel-heading"><b>Attach Files</b></div>
        <div class="panel-body">
            <?php
            require_once dirname(__FILE__)."/tpls/metaboxes/attach-file-front.php";
            ?>
        </div>
    </div>

    <div class="panel panel-default" id="package-settings-section">
        <div class="panel-heading"><b>Categories</b></div>
        <div class="panel-body cat-panel">
            <?php
            $term_list = wp_get_post_terms($post->ID, 'wpdmcategory', array("fields" => "all"));

            function wpdm_categories_checkboxed_tree($parent = 0, $selected = array()){
                $categories = get_terms( 'wpdmcategory' , array('hide_empty'=>0,'parent'=>$parent));
                $checked = "";
                foreach($categories as $category){
                    if($selected){
                        foreach($selected as $ptype){
                            if($ptype->term_id==$category->term_id){$checked="checked='checked'";break;}else $checked="";
                        }
                    }
                    echo '<li><label><input type="checkbox" '.$checked.' name="cats[]" value="'.$category->term_id.'"> '.$category->name.' </label>';
                    echo "<ul>";
                    wpdm_categories_checkboxed_tree($category->term_id, $selected);
                    echo "</ul>";
                    echo "</li>";
                }
            }

            echo "<ul class='ptypes'>";
            wpdm_categories_checkboxed_tree(0, $term_list);
            echo "</ul>";
            ?>
        </div>
    </div>


<div class="panel panel-default">
<div class="panel-heading"><b><?php _e('Featured Image','wpdmpro'); ?></b></div>
<div class="inside">
<div id="img"><?php if(has_post_thumbnail(wpdm_query_var('id','num'))):
        $thumbnail_id = get_post_thumbnail_id(wpdm_query_var('id','num'));
        $thumbnail_url = wp_get_attachment_image_src($thumbnail_id,'full', true);
        ?>
<p><img src="<?php  echo $thumbnail_url[0]; ?>" width="240" alt="preview" /><input type="hidden" name="file[preview]" value="<?php  echo $thumbnail_url[0]; ?>" id="fpvw" /></p>
        <a href="#"  id="rmvp" class="text-danger">Remove Featured Image</a>
<?php else: ?>
<a href="#" id="wpdm-featured-image"><?php _e('Add Featured Image','wpdmpro'); ?></a>
        <input type="hidden" name="file[preview]" value="" id="fpvw" />
    <?php endif; ?>
</div>
<!-- <input type="file" name="preview" /> -->
 <div class="clear"></div>
</div>
</div>
 











<div class="panel panel-primary " id="form-action">
    <div class="panel-heading">
        <b>Actions</b>
    </div>
<div class="panel-body">

<label><input type="checkbox" <?php if(isset($post->post_status)) checked($post->post_status,'draft'); ?> value="draft" name="status"> Save as Draft</label><br/><br/>


 <button type="button" value="Back" tabindex="9" class="btn btn-inverse  backbtn" onclick="location.href='<?php the_permalink(); ?>'"  name="addmeta" id="addmetasub">Back</button>


<button type="submit" accesskey="p" tabindex="5" id="publish" class="btn btn-success" name="publish"><span class="wpdm-spinner"></span> <?php echo $_GET['task']=='EditPackage'?'Update Package':'Create Package'; ?></button>

</div>
</div>

</div>
</div>

</form>

</div>


<script type="text/javascript" src="<?php echo plugins_url('/download-manager/js/chosen.jquery.min.js'); ?>"></script>
      <script type="text/javascript">
      
      jQuery(document).ready(function() {

        jQuery('select').chosen();
        jQuery('span.infoicon').css({color:'transparent',width:'16px',height:'16px',cursor:'pointer'}).tooltip({placement:'right',html:true});
        jQuery('span.infoicon').tooltip({placement:'right'});
        jQuery('.nopro').click(function(){
            if(this.checked) jQuery('.wpdmlock').removeAttr('checked');
        });
        
        jQuery('.wpdmlock').click(function(){      
         
            if(this.checked) {   
            jQuery('#'+jQuery(this).attr('rel')).slideDown();
            jQuery('.nopro').removeAttr('checked');
            } else {
            jQuery('#'+jQuery(this).attr('rel')).slideUp();    
            }
        });
          
       // jQuery( "#pdate" ).datepicker({dateFormat:'yy-mm-dd'});
       // jQuery( "#udate" ).datepicker({dateFormat:'yy-mm-dd'});
        
        jQuery('#wpdm-pf').submit(function(){
             var editor = tinymce.get('post_content');
             editor.save();
             jQuery('#wpdm-pf .wpdm-spinner').addClass('wpdm-spin');
             jQuery('#publish').attr('disabled','disabled');
             jQuery('#wpdm-pf').ajaxSubmit({
                 //dataType: 'json',
                 beforeSubmit: function() { jQuery('#sving').fadeIn(); },
                 success: function(res) {  jQuery('#sving').fadeOut(); jQuery('#nxt').slideDown();
                     if(res.result=='_ap_wpdm') {
                         location.href = "<?php the_permalink(); echo get_option('permalink_structure')?'?':'&'; ?>task=edit-package&id="+res.id;
                         jQuery('#wpdm-pf').prepend('<div class="alert alert-success">Package Created Successfully. Opening Edit Window ... <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a></div>');
                     }
                     else {
                     jQuery('#wpdm-pf .wpdm-spinner').removeClass('wpdm-spin');
                     jQuery('#publish').removeAttr('disabled');
                     jQuery('#wpdm-pf').prepend('<div class="alert alert-success">Package Updated Successfully <a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a></div>');
                     }
                 }
                 
                 
             });
             return false;
        });


       
      });
      
      jQuery('#upload-main-preview').click(function() {           
            tb_show('', '<?php echo admin_url('media-upload.php?type=image&TB_iframe=1&width=640&height=551'); ?>');
            window.send_to_editor = function(html) {           
              var imgurl = jQuery('img',"<p>"+html+"</p>").attr('src');                     
              jQuery('#img').html("<img src='"+imgurl+"' style='max-width:100%'/><input type='hidden' name='file[preview]' value='"+imgurl+"' >");
              tb_remove();
              }
            return false;
        });

     
 

      </script>
      
      <?php
 
?>
 