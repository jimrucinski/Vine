<?php
global $wpdb, $current_user;
$limit = 20;
get_currentuserinfo(); 
$_GET['paged'] = isset($_GET['paged'])?$_GET['paged']:1;
$start = isset($_GET['paged'])?(($_GET['paged']-1)*$limit):0;
$field = isset($_GET['sfield'])?$_GET['sfield']:'id';
$ord = isset($_GET['sorder'])?$_GET['sorder']:'desc';
$pid = isset($_GET['pid'])?(int)$_GET['pid']:0;
if(isset($_GET['pid'])) $cond = " and e.pid=$pid";
if(isset($_GET['uniq'])) $group = " group by e.pid";
$res = $wpdb->get_results("select e.*,f.post_title as title from {$wpdb->prefix}ahm_emails e,{$wpdb->prefix}posts f where e.pid=f.ID order by {$field} {$ord} limit $start, $limit",ARRAY_A);
$total = $wpdb->get_var("select count(*) as t from {$wpdb->prefix}ahm_emails");
 
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
    #cb{
        text-align: center;
    }
.w3eden #subtbl_wrapper td{
    border-width: 1px !important;
    border-bottom: 0 !important;
}
.w3eden #subtbl_wrapper{
    margin: 10px !important;
}

    #subtbl{
        margin-bottom: 10px !important;
    }

</style>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/download-manager/bootstrap/css/bootstrap.css');?>" />
<!--<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.0/css/jquery.dataTables.css" />-->
<!--<script src="//cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>-->
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
    <li class="active"><a id="basic" href="edit.php?post_type=wpdmpro&page=emails"><?php echo __('Emails','wpdmpro'); ?></a></li>
    <li><a id="basic" href="edit.php?post_type=wpdmpro&page=emails&task=template"><?php echo __('Email Template','wpdmpro'); ?></a></li>        
    </ul>
<br/>
<form method="post" action="edit.php?post_type=wpdmpro&page=emails&task=delete" id="posts-filter">


<div class="clear"></div>

<table id="subtbl" cellspacing="0" class="table table-striped" style="margin:0 !important;border-bottom:0;">
    <thead>
    <tr>
    <th style="width: 50px" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
    <th style="width:50px" style="" class="manage-column column-id"  scope="col"><?php echo __('ID','wpdmpro'); ?></th>
    <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __('Email','wpdmpro'); ?></th>
    <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __('Name','wpdmpro'); ?></th>
    <th style="" class="manage-column column-media" id="filename" scope="col"><?php echo __('Package Name','wpdmpro'); ?></th>
    <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __('Date','wpdmpro'); ?></th>    
    <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __('Action','wpdmpro'); ?></th>
    </tr>
    </thead>

    <tfoot>
    <tr>
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>         
    <th style="width:50px" style="" class="manage-column column-id"  scope="col"><?php echo __('ID','wpdmpro'); ?></th>
    <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __('Email','wpdmpro'); ?></th>
    <th style="" class="manage-column column-media" id="email" scope="col"><?php echo __('Name','wpdmpro'); ?></th>
    <th style="" class="manage-column column-media" id="filename" scope="col"><?php echo __('Package Name','wpdmpro'); ?></th>
    <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __('Date','wpdmpro'); ?></th>
    <th style="" class="manage-column column-password" id="author" scope="col"><?php echo __('Action','wpdmpro'); ?></th>
    </tr>
    </tfoot>

    <tbody class="list:post" id="the-list">
    <?php foreach($res as $row) { 
                   
        ?>
    <tr valign="top" class="author-self status-inherit" id="post-<?php echo $row[id]; ?>">

                <th class="check-column text-center" style="padding: 5px 0px !important;" scope="row"><input type="checkbox" value="<?php echo $row['id']; ?>" name="id[]"></th>
                <td scope="row">
                <?php echo $row['id']; ?>
                </td>
                <td scope="row"><?php echo $row['email']; ?></td>
                <td scope="row"><?php $cd = unserialize($row['custom_data']); if($cd) foreach($cd as $k=>$v): echo $v; endforeach; ?></td>
                
                <td class="media column-media">
                <a href='edit.php?post_type=wpdmpro&page=emails&pid=<?php echo $row['pid']; ?>'><?php echo $row['title']; ?></a>
                </td>
                <td class="author column-author"><?php echo date("Y-m-d H:i",$row['date']); ?></td>                
                <td class="author column-author"><?php echo $row['request_status']==2?"<a href='#'>Send Download Link</a>":"Link Sent"; ?></td>

     </tr>
     <?php } ?>
    </tbody>
</table>
                    
<?php
$cp = $_GET['paged']?$_GET['paged']:1;
$page_links = paginate_links( array(
    'base' => add_query_arg( 'paged', '%#%' ),
    'format' => '',
    'prev_text' => __('&laquo;'),
    'next_text' => __('&raquo;'),
    'total' => ceil($total/$limit),
    'current' => $cp
));


?>



    <div class="panel-footer">
        <nobr>
            <input type="submit" class="button-secondary action submitdelete" id="doaction" value="<?php echo __('Delete Selected','wpdmpro'); ?>">
            <?php if(isset($_REQUEST['q'])) { ?>
                <input type="button" class="button-secondary action" onclick="location.href='admin.php?page=file-manager'" value="<?php echo __('Reset Search','wpdmpro'); ?>">
            <?php } ?>
        </nobr>
        <div class="pull-right">

            <?php  if ( $page_links ) { ?>
                <div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
                        number_format_i18n( ( $_GET['paged'] - 1 ) * $limit + 1 ),
                        number_format_i18n( min( $_GET['paged'] * $limit, $total ) ),
                        number_format_i18n( $total ),
                        $page_links
                    ); echo $page_links_text; ?></div>
            <?php }  ?>

        </div><div style="clear: both"></div>
    </div>
</form>
</div>
</div>

<script language="JavaScript">
<!--
  jQuery(function(){

//     jQuery('#subtbl').dataTable({"aoColumns": [
//             { "bSearchable": false, "bSortable": false },
//             null,
//             null,
//             null,
//             null,
//             null,
//         { "bSearchable": false, "bSortable": false }
//
//     ]});
  });
//-->
</script> 