<?php

function wpdm_dir_tree(){
    $root = '';
    if(!isset($_GET['task'])||$_GET['task']!='wpdm_dir_tree') return;
    $_POST['dir'] = urldecode($_POST['dir']);
    if( file_exists( $_POST['dir'])) {
	    $files = scandir( $_POST['dir']);
	    natcasesort($files);        
	    if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		    echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		    // All dirs
		    foreach( $files as $file ) {
			    if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
				    echo "<li class=\"directory collapsed\"><a id=\"".uniqid()."\" href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
			    }
		    }
		    // All files
		    foreach( $files as $file ) {
			    if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
				    $ext = preg_replace('/^.*\./', '', $file);
				    echo "<li class=\"file ext_$ext\"><a id=\"".uniqid()."\" href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
			    }
		    }
		    echo "</ul>";	
	    }
    }    
}

function wpdm_file_browser(){
    //if($_GET['task']!='wpdm_file_browser') return;
    ?>
    <script type="text/javascript" src="<?php echo plugins_url().'/download-manager/js/jqueryFileTree.js';?>"></script>
    <link rel="stylesheet" href="<?php echo plugins_url().'/download-manager/css/jqueryFileTree.css';?>" />
    <style type="text/css">.jqueryFileTree li{line-height: 20px;}</style>
    <!--<div class="wrap">
    <div class="icon32" id="icon-categories"><br></div>
    <h2>Browse Files</h2>-->
    <div id="tree" style="height: 200px;overflow:auto"></div>
    <script language="JavaScript">
    <!--
      jQuery( function() {
            jQuery('#tree').fileTree({
                root: '<?php echo get_option('_wpdm_file_browser_root',$_SERVER['DOCUMENT_ROOT']); ?>/',
                script: 'admin.php?task=wpdm_dir_tree',
                expandSpeed: 1000,
                collapseSpeed: 1000,
                multiFolder: false
            }, function(file, id) {
                var sfilename = file.split('/');
                var filename = sfilename[sfilename.length-1];                
                if(confirm('Add this file?')){
                     var ID = id;
                    //jQuery('#currentfiles table.widefat').append("<tr id='"+ID+"' class='cfile'><td><input type='hidden' id='in_"+ID+"' name='file[files][]' value='"+file+"' /><img id='del_"+ID+"' src='<?php echo plugins_url(); ?>/download-manager/images/minus.png' rel='del' align=left /></td><td>"+file+"</td><td width='40%'><input style='width:99%' type='text' name='file[fileinfo]["+file+"][title]' value='"+filename+"' onclick='this.select()'></td><td><input size='10' type='text' id='indpass_"+ID+"' name='file[fileinfo]["+file+"][password]' value=''> <img style='cursor: pointer;float: right;margin-top: -3px' class='genpass' onclick=\"return generatepass('indpass_"+ID+"')\" title='Generate Password' src=\"<?php echo plugins_url('download-manager/images/generate-pass.png'); ?>\" /></td></tr>");
                    jQuery('#wpdm-files').dataTable().fnAddData( [
                                                        "<input type='hidden' id='in_"+ID+"' name='file[files][]' value='"+file+"' /><img id='del_"+ID+"' src='<?php echo plugins_url(); ?>/download-manager/images/minus.png' rel='del' align=left />",
                                                        file,
                                                        "<input style='width:99%' type='text' name='file[fileinfo]["+file+"][title]' value='"+filename+"' onclick='this.select()'>",                                                        
                                                        "<input size='10' type='text' id='indpass_"+ID+"' name='file[fileinfo]["+file+"][password]' value=''> <img style='cursor: pointer;float: right;margin-top: -3px' class='genpass' onclick=\"return generatepass('indpass_"+ID+"')\" title='Generate Password' src=\"<?php echo plugins_url('download-manager/images/generate-pass.png'); ?>\" />"
                                                        ] );
                    jQuery('#wpdm-files tbody tr:last-child').attr('id',ID).addClass('cfile');
                    
                    jQuery("#wpdm-files tbody").sortable();                     
    
                            jQuery('#'+ID).fadeIn();
                            jQuery('#del_'+ID).click(function(){
                                if(jQuery(this).attr('rel')=='del'){
                                jQuery('#'+ID).removeClass('cfile').addClass('dfile');
                                jQuery('#in_'+ID).attr('name','del[]');
                                jQuery(this).attr('rel','undo').attr('src','<?php echo plugins_url(); ?>/download-manager/images/add.png').attr('title','Undo Delete');
                                } else if(jQuery(this).attr('rel')=='undo'){
                                jQuery('#'+ID).removeClass('dfile').addClass('cfile');
                                jQuery('#in_'+ID).attr('name','file[files][]');
                                jQuery(this).attr('rel','del').attr('src','<?php echo plugins_url(); ?>/download-manager/images/minus.png').attr('title','Delete File');
                                }
                                
                                
                            });
                    
                    
                }
                //jQuery('#serverfiles').append('<li><label><input checked=checked type="checkbox" value="'+file+'" name="imports[]" class="role"> &nbsp; '+filename+'</label></li>');                
            });
                        
      });
    //-->
    </script>    
    <!--</div> -->
    <?php
   // die();
}

function wpmp_file_browser_metabox(){
    ?>
    
    <div class="postbox " id="action">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span><?php echo __('Add file(s) from server','wpdmpro'); ?></span></h3>
<div class="inside" style="height: 200px;overflow: auto;">
      
<?php wpdm_file_browser(); ?>

<ul id="serverfiles">



 


</ul>   
 <div class="clear"></div>
</div>
</div>
    
    <?php
}

if(is_admin()){
     
    //add_action("init","wpdm_file_browser");
    add_action("init","wpdm_dir_tree");
    add_action("add_new_file_sidebar","wpmp_file_browser_metabox");
}


