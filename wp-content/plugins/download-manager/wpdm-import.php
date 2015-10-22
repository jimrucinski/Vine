<div class="wrap">
    <div class="icon32" id="icon-import-file"><br></div>
<h2>Bulk Import</h2>
<script type="text/javascript" src="<?php echo plugins_url().'/download-manager/js/jqueryFileTree.js';?>"></script>
<link rel="stylesheet" href="<?php echo plugins_url().'/download-manager/css/jqueryFileTree.css';?>" />
<link rel="stylesheet" href="<?php echo plugins_url('/download-manager/css/chosen.css'); ?>" />
<script language="JavaScript" src="<?php echo plugins_url('/download-manager/js/chosen.jquery.min.js'); ?>"></script> 
<script language="JavaScript" src="<?php echo plugins_url('/download-manager/js/jquery.cookie.js'); ?>"></script> 
<style type="text/css">.jqueryFileTree li{line-height: 20px;}</style>

<table><tr>
<td valign="top">
        <br/>
        
<form action="admin.php?task=wpdm-import-csv" method="post" enctype="multipart/form-data">
<b>Import form CSV File:</b>
<input type="file" name="csv" />
<input type="submit" value="Import CSV" class="button button-primary" />
</form>
<code>Download sample csv file: <a href="<?php echo plugins_url('/download-manager/sample.csv'); ?>">sample.csv</a></code>
<div noshade="noshade" size="1" style="border-bottom: 1px solid #ccc;">&nbsp;</div>
<br>        
<b>Select Dir:</b>
    <div id="dtree" style="width: 280px;height: 350px;overflow: auto;border: 1px solid #ccc;border-radius:5px;padding:5px;"></div>    
    <div id="path">
    <form method="post">
    <b>Selected Dir Path:</b>    
    <input type="text" name="wpdm_importdir" value="<?php echo get_option('wpdm_importdir'); ?>" id="pathd" size="50" /> 
    <input type="submit" id="slctdir" value="Set as Import Dir" class="button-primary">
    </form>
    </div>    
    <script language="JavaScript">
    <!--
      jQuery(document).ready( function() {
            jQuery('#dtree').fileTree({
                root: '<?php echo get_option('_wpdm_file_browser_root',$_SERVER['DOCUMENT_ROOT']); ?>/',
                script: 'admin.php?task=wpdm_odir_tree',
                expandSpeed: 1000,
                folderEvent: 'click',
                collapseSpeed: 1000,
                multiFolder: false
            }, function(file) {
                alert(file);
                var sfilename = file.split('/');
                var filename = sfilename[sfilename.length-1];
                jQuery('#serverfiles').append('<li><label><input checked=checked type="checkbox" value="'+file+'" name="imports[]" class="role"> &nbsp; '+filename+'</label></li>');
                tb_remove();
            });
            
            jQuery('#TB_ajaxContent').css('width','630px').css('height','90%');
             
      });
      function odirpath(a){
          jQuery('#pathd').val(a.rel);
      }
      
      jQuery('#slctdir').click(function(){
          jQuery('#srvdir').val(jQuery('#pathd').val());
          //jQuery('#currentfiles table').load('admin.php?task=wpdm_fetch_dir&dir='+jQuery('#pathd').val());
          tb_remove();
      });
    //-->
    </script>    
    
</td>
<td valign="top">    <?php $wpdmimported = isset($_COOKIE['wpdmimported'])?explode(",", $_COOKIE['wpdmimported']):array(); ?>
<form action="" method="post">
<table><tr> 
    <td>    
        <select name="cats" id="cats" multiple="multiple" style="width:400px;max-width: 40%;" data-placeholder="Assign Categories">
            <?php $terms = get_terms('wpdmcategory','hide_empty=0'); print_r($terms); 
                  foreach($terms as $term){
                      echo "<option value='{$term->term_id}'>{$term->name}</option>";
                  }   
            ?>
        </select>
        <select name="access" id="access" style="width:400px;max-width: 40%;" multiple="multiple" data-placeholder="Allow Access to Role(s)">
	<?php
	
	 
	?>
	
            <option value="guest" selected="selected"> All Visitors</option>
    <?php
    global $wp_roles;
    $roles = array_reverse($wp_roles->role_names);
    foreach( $roles as $role => $name ) { 
	
	
	 
	
	?>
    <option value="<?php echo $role; ?>" > <?php echo $name; ?></option>
    <?php } ?>
    </select>
   </td><td valign="top">
    <input type="button" id="idel" value="Import Selected Files" class="button button-large button-primary" ></td>
</tr></table>    
  <table cellspacing="0" class="widefat fixed">
    <thead>
      <tr>
        <th width="20" class="check-column"><input type="checkbox" class="multicheck"></th>
        <th >File name</th>
        <th >Title</th>        
        <th >Description</th>        
        <th width=100>Password</th>
        <th width=100>Size</th>
         
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th width="20" class="check-column"><input type="checkbox" class="multicheck"></th>
        <th >File name</th>
        <th >Title</th>        
        <th >Description</th>        
        <th  width=100>Password</th>
        <th width=100>Size</th>
         
      </tr>
    </tfoot>
    <tbody id="the-list" class="list:post">
      <?php 
	$k = 0;
    $limit = 50;
    $total = isset($fileinfo)?count($fileinfo):0;
    $p = isset($_GET['paged'])?$_GET['paged']:1;
	$s = ($p-1)*$limit;
    $max = $s+$limit;
    if($max>$total) $max = $total;
	for($index=$s; $index<$max; $index++): $value = $fileinfo[$index]; $tmptitle = ucwords(str_replace(array("-","_",".")," ",$value['name'])); ?>
      <tr for="file-<?php echo $index; ?>" valign="top" class="importfilelist" id="<?php echo $index; ?>">
        <th   class=" check-column" style="padding-bottom: 0px;"><input type="checkbox" rel="<?php echo $index; ?>" id="file-<?php echo $index; ?>" class="checkbox dim" value="<?php echo $value['name'] ?>"></th>
        <td><label for="file-<?php echo $index; ?>"><strong><?php echo $value['name'] ?></strong></label> <?php if(in_array($value['name'],$wpdmimported)) echo '<span style="margin-left:10px;background:#E2FFE5;color:#000;font-size:11px;font-family:\'Courier New\';padding:2px 7px;">imported</span>'; ?></td>
        <td><input size="20" type="text" id="title<?php echo $index; ?>" name="file[<?php echo $index; ?>][title]" value="<?php echo $tmptitle; ?>"></td>
        <td><input size="40" type="text" id="desc<?php echo $index; ?>" name="file[<?php echo $index; ?>][description]"></td>
        <td><input size="10" type="text" id="password<?php echo $index; ?>" name="file[<?php echo $index; ?>][password]"></td>         
        <td>
		<?php echo number_format(@filesize(get_option('wpdm_importdir').$value['name'])/(1024*1024),4); ?> MB
		</td>
         
      </tr>
      <?php
	  
	  $k++;
	  endfor; ?>
	  
	  
	  
	  
    </tbody>
  </table>
  <?php
  
$page_links = paginate_links( array(
    'base' => add_query_arg( 'paged', '%#%' ),
    'format' => '',
    'prev_text' => __('&laquo;'),
    'next_text' => __('&raquo;'),
    'total' => ceil($total/$limit),
    'current' => $p
));


?>

<div id="ajax-response"></div>

<div class="tablenav">

<?php if ( $page_links ) { ?>
<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
    number_format_i18n( ( $_GET['paged'] - 1 ) * $limit + 1 ),
    number_format_i18n( min( $_GET['paged'] * $limit, $total ) ),
    number_format_i18n( $total ),
    $page_links
); echo $page_links_text; ?></div>
<?php } ?>

 
</div>
   
    <input type="button" id="idel1" value="Import Selected Files" class="button button-large button-primary" >
   
</form>     
</td></tr></table>
<script type="text/javascript">
  
     jQuery('#idel,#idel1').click(function(){
         jQuery('.dim').each(function(){
             if(this.checked)
             dimport(jQuery(this).attr('rel'),jQuery(this).val());
         });
     });
     
     function dimport(id,file){
       var wpdmimported = [];  
       jQuery('#'+id).fadeTo('slow', 0.4);        
       jQuery.post(ajaxurl,{action:'wpdm_dimport',fname:file, title:jQuery('#title'+id).val(),password:jQuery('#password'+id).val(),access:jQuery('#access').val(),description:jQuery('#desc'+id).val(),category:jQuery('#cats').val()},function(res){
          jQuery('#'+id).fadeOut().remove(); 
          wpdmimported = jQuery.cookie('wpdmimported');
          wpdmimported = wpdmimported + "," + file;          
          jQuery.cookie('wpdmimported',wpdmimported,{expires:360});
       })
     }
     jQuery('select').chosen({});
     </script>
</div>