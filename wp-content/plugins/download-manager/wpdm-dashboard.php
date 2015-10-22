<link rel="stylesheet" href="<?php echo plugins_url('download-manager/bootstrap/css/bootstrap.css'); ?>" />   
<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400' rel='stylesheet' type='text/css'>
<style type="text/css">
.wpdm-pro *{
    font-family: 'Open Sans', sans-serif;     
    font-weight: 300;
}
.wpdm-pro .alert,
.wpdm-pro th,
.wpdm-pro td{
    font-size: 14px;
}
.wpdm-pro h3{
    font-size: 12pt;
  
    font-weight: bold;
    margin: 0px;
    margin-bottom: 10px;
    line-height: normal;     
}
</style>
<div class="wrap">
    <div class="icon32" id="icon-file-manager"><br></div>
<h2>WPDM Dashboard</h2>
<?php global $wpdb; ?>
<br><br>  
  
<div class="wpdm-pro">

<div class="container-fluid">

<div class="row-fluid">
<div class="alert alert-info" align="center">New version of WordPress Download Manager Pro is availble now</div>
</div>
<div class="row-fluid">

<div class="span6">
<div class="thumbnail">
<h3 align="center" class="font-effect-outline">Summery</h3>
<table class="table table-bordered table-striped" style="margin-bottom: 0px;">
<tr><td>Total Packages</td><th><?php echo $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_files");?></th></tr>
<tr><td>Total Downloads</td><th><?php echo $wpdb->get_var("select sum(download_count) from {$wpdb->prefix}ahm_files");?></th></tr>
<tr><td>Total Categories</td><th><?php echo count(maybe_unserialize(get_option('_fm_categories'))); ?></th></tr>
<tr><td>Total Subscribers</td><th><?php echo count($wpdb->get_results("select count(email) from {$wpdb->prefix}ahm_emails group by email"));?></th></tr>
<tr><td>Subscribed Today</td><th><?php $s = strtotime(date("Y-m-d 0:0:0")); $e = time(); echo count($wpdb->get_results("select count(email) from {$wpdb->prefix}ahm_emails where date > $s and date < $e group by email"));?></th></tr>
</table>
</div>
</div>
<div class="span6">
<div class="thumbnail">
<h3 align="center" class="font-effect-outline">Send Download</h3> 
    <form class="form-horizontal">
    <div class="control-group span6">     
    <input type="text" id="inputEmail" placeholder="Emails Separated By Comma">     
    </div>
    <div class="control-group span6">    
    <input type="password" id="inputPassword" placeholder="Password">     
    </div>
    <div class="control-group">
    <div class="controls">
    <label class="checkbox">
    <input type="checkbox"> Remember me
    </label>
    <button type="submit" class="btn">Sign in</button>
    </div>
    </div>
    </form> 
</div>
</div>
</div>   <br>
<div class="row-fluid">
<div class="span12">
<div class="thumbnail">
<h3 align="center" class="font-effect-outline">Recently Downloaded</h3> 
<table class="table table-bordered table-striped" style="margin-bottom: 0px;">
<?php 
$data = $wpdb->get_results("select f.*,s.timestamp,s.ip from {$wpdb->prefix}ahm_files f, {$wpdb->prefix}ahm_download_stats s where f.id=s.pid order by `s`.`timestamp`  desc limit 0,5");       

foreach($data as $d):
?>
<tr><td><?php echo $d->title; ?></td><td><?php echo date('Y-m-d H:i:s A', $d->timestamp); ?></td><td><?php echo $d->ip; ?><td><div class="button-group"><a class="button button-small" href='#'>View</a><a class="button button-small" href='#'>Edit</a></div></td></tr>
<?php endforeach; ?>
</table>
</div>
</div>
</div>

</div>




</div>

  
 
<br class="clear">

</div>

 