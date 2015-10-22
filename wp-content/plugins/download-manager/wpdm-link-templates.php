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

    .btn-primary {
        background-color: #2081D5;
        background-image: linear-gradient(to bottom, #2081D5 0px, #1B6CB2 100%);
        background-repeat: repeat-x;
        border-color: #1D76C3 #1B6CB2 #134B7C !important;
        color: #FFFFFF;
    }

    .panel-heading .btn.btn-primary{

        border-radius: 3px;
        border:1px solid rgba(255,255,255,0.8) !important;
        -webkit-transition: all 400ms ease-in-out;
        -moz-transition: all 400ms ease-in-out;
        -o-transition: all 400ms ease-in-out;
        transition: all 400ms ease-in-out;
    }

    .panel-heading .btn.btn-primary:hover{

        border-radius: 3px;
        border:1px solid rgba(255,255,255,1) !important;

    }
    .btn-info {
        background-color: #5AA2D3 !important;
        background-image: linear-gradient(to bottom, #5AA2D3 0px, #3A90CA 100%) !important;
        background-repeat: repeat-x;
        border-color: #4A99CF #3A90CA #2A6E9D !important;
        color: #FFFFFF;
    }

    .btn-danger {
        background-color: #DE090B !important;
        background-image: linear-gradient(to bottom, #DE090B 0px, #B70709 100%) !important;
        background-repeat: repeat-x;
        border-color: #CA080A #B70709 #7C0506 !important;
        color: #FFFFFF;
    }

    .btn-success {
        background-color: #5D9C22 !important;
        background-image: linear-gradient(to bottom, #5D9C22 0px, #497B1B 100%) !important;
        background-repeat: repeat-x;
        border-color: #538B1E #497B1B #2B4810 !important;
        color: #FFFFFF;
    }

    .btn-default {
        background-color: #FFFFFF;
        background-image: linear-gradient(to bottom, #FFFFFF 0px, #EBEBEB 100%) !important;
        background-repeat: repeat-x;
        border-color: #EBEBEB #E0E0E0 #C2C2C2 !important;
        color: #555555;
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


</style>
  <style>
      thead{
          background: #dddddd;
      }
.w3eden .btn-xs{
    min-width: 60px;
}
input[type=text],textarea{
    width:500px;
    padding:5px;
}

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
img{
    max-width: 100%;
}



</style>

<div class="wrap w3eden">

<div class="panel panel-primary" style="margin: 30px">
<div class="panel-heading">
<b style="font-size: 12pt;line-height:28px"><i class="fa fa-magic"></i> &nbsp; <?php echo __("Templates", "wpdmpro"); ?></b>
    <div class="pull-right">
<a href="edit.php?post_type=wpdmpro&page=templates&_type=page&task=NewTemplate" class="btn btn-sm btn-default"><i class="fa fa-plus"></i> <?php echo __("Create Page Template", "wpdmpro"); ?></a> <a href="edit.php?post_type=wpdmpro&page=templates&_type=link&task=NewTemplate" class="btn btn-sm btn-default"><i class="fa fa-plus"></i> <?php echo __("Create Link Template", "wpdmpro"); ?></a>
    </div>
    <div style="clear: both"></div>
</div>
    <ul id="tabs" class="nav nav-tabs" style="padding: 10px 10px 0 10px;background: #f5f5f5">
    <li <?php if(!isset($_GET['_type'])||$_GET['_type']=='link'){ ?>class="active"<?php } ?>><a href="edit.php?post_type=wpdmpro&page=templates&_type=link" id="basic">Link Templates</a></li>
    <li <?php if(isset($_GET['_type'])&&$_GET['_type']=='page'){ ?>class="active"<?php } ?>><a href="edit.php?post_type=wpdmpro&page=templates&_type=page" id="basic">Page Templates</a></li>
    </ul>
<div class="tab-content" style="padding-top: 15px;">
<blockquote  class="alert alert-info" style="margin: 0 10px 10px 10px">
<?php echo __("Pre-designed templates can't be deleted or edited from this section. But you can clone any of them and edit as your own. If you seriously want to edit any pre-designed template you have to edit those directly edting php files at /download-manager/templates/ dir","wpdmpro"); ?>
</blockquote>
<div>
<table cellspacing="0" class="table">
    <thead>
    <tr>
    <th style="width: 50%" class="manage-column column-media" id="media" scope="col"><?php echo __("Template Name", "wpdmpro"); ?></th>
    <th style="width: 300px" class="manage-column column-media" id="tid" scope="col"><?php echo __("Template ID", "wpdmpro"); ?></th>
    <th style="width: 300px" class="manage-column column-media" id="tid" scope="col"><?php echo __("Actions", "wpdmpro"); ?></th>
    </tr>
    </thead>

    <tfoot>
    <tr>
    <th style="" class="manage-column column-media" id="media" scope="col">Template Name</th>    
    <th style="" class="manage-column column-media" id="tid" scope="col">Template ID</th>
    <th class="manage-column column-media" id="tid" scope="col">Actions</th>
    </tr>
    </tfoot>
    <tbody class="list:post" id="the-list">
    <?php 
    $ttype = isset($_GET['_type'])?$_GET['_type']:'link';
    $ctpls = scandir(WPDM_BASE_DIR.'/templates/');
    array_shift($ctpls);
    array_shift($ctpls);
    $ptpls = $ctpls;
     
    foreach($ctpls as $ctpl){  
      $tmpdata = file_get_contents(WPDM_BASE_DIR.'/templates/'.$ctpl);
      $regx = $ttype=='link'?"/WPDM[\s]+Link[\s]+Template[\s]*:([^\-\->]+)/":"/WPDM[\s]+Template[\s]*:([^\-\->]+)/";
      if(preg_match($regx,$tmpdata, $matches)){
    ?>
     
    <tr valign="top" class="author-self status-inherit" id="post-8">
                <td class="column-icon media-icon" style="text-align: left;">                                     
                    <b><?php echo $matches[1]; ?></b>
                 
                    </td>
                <td>
                <input class="form-control input-sm" type="text" readonly="readonly" onclick="this.select()" value="<?php echo str_replace(".php","",$ctpl); ?>" style="width: 200px;text-align: center;font-weight: bold; background: #fff;cursor: alias"/>
                </td>
        <td>
            <a href="edit.php?post_type=wpdmpro&page=templates&_type=<?php echo $ttype; ?>&task=NewTemplate&clone=<?php echo $ctpl; ?>" class="btn btn-xs btn-primary"><i class="fa fa-copy"></i> Clone</a>
            <a data-toggle="modal" href="#" data-href="admin-ajax.php?action=template_preview&template=<?php echo $ctpl; ?>" data-target="#preview-modal" rel="<?php echo $ctpl; ?>" class="template_preview btn btn-xs btn-success"><i class="fa fa-desktop"></i> Preview</a>

        </td>
                
     
     </tr>
    <?php    
    }  
    }
    if($templates = maybe_unserialize(get_option("_fm_{$ttype}_templates",true))){    
    if(is_array($templates)){
    foreach($templates as $id=>$template) {  ?>
    <tr valign="top" class="author-self status-inherit" id="post-8">
                <td class="column-icon media-icon" style="text-align: left;">                
                    <a title="Edit" href="edit.php?post_type=wpdmpro&page=templates&_type=<?php echo $ttype; ?>&task=EditTemplate&tplid=<?php echo $id; ?>">
                    <b><?php echo $template['title']?></b>
                    </a>
                </td>
                <td>
                <input class="form-control input-sm" type="text" readonly="readonly" onclick="this.select()" value="<?php echo $id; ?>" style="width: 200px;text-align: center;font-weight: bold; background: #fff;cursor: alias"/>
                </td>
        <td>
            <a href="edit.php?post_type=wpdmpro&page=templates&_type=<?php echo $ttype; ?>&task=EditTemplate&tplid=<?php echo $id; ?>" class="btn btn-xs btn-info"><i class="fa fa-pencil"></i> <?php echo __("Edit", "wpdmpro"); ?></a>
            <a data-toggle="modal" href="#" data-href="admin-ajax.php?action=template_preview&template=<?php echo $id; ?>" data-target="#preview-modal" rel="<?php echo $id; ?>" class="template_preview btn btn-xs btn-success"><i class="fa fa-desktop"></i> <?php echo __("Preview", "wpdmpro"); ?></a>
            <a href="edit.php?post_type=wpdmpro&page=templates&_type=<?php echo $ttype; ?>&task=DeleteTemplate&tplid=<?php echo $id?>" onclick="return showNotice.warn();" class="submitdelete btn btn-xs btn-danger"><i class="fa fa-trash-o"></i> <?php echo __("Delete", "wpdmpro"); ?></a>
        </td>
                
     
     </tr>
     <?php }}} ?>
    </tbody>
</table>

</div>
    </div>
    </div>


    <div class="modal fade" id="preview-modal" tabindex="-1" role="dialog" aria-labelledby="preview" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Template Preview</h4>
                </div>
                <div class="modal-body" id="preview-area">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>



<script>



    jQuery(function(){
        jQuery('.template_preview').click(function(){
            jQuery('#preview-area').html("<i class='fa fa-spin fa-spinner'></i> Loading Preview...").load(jQuery(this).attr('data-href'));
        });
    });

</script>
</div>


 
