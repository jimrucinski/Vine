<?php

    $invoice = wpdm_query_var('invoice','txt')?wpdm_query_var('invoice','txt'):'';
    if($invoice!=''){
    $oorder = new Order();
    $order = $oorder->GetOrder($invoice);
    if($order->uid!=0) $invoice = '';
    }     
?>




<?php if(isset($_SESSION['reg_warning'])&&$_SESSION['reg_warning']!=''): ?>  <br>
<div class="col-md-12" >
<div class="alert alert-warning" align="center" style="font-size:10pt;">
<?php echo $_SESSION['reg_warning']; unset($_SESSION['reg_warning']); ?>
</div>
</div>

<?php endif; ?>
<?php if(isset($_SESSION['sccs_msg'])&&$_SESSION['sccs_msg']!=''): ?><br>
<div class="col-md-12" >
<div class="alert alert-success" align="center" style="font-size:10pt;">
<?php echo $_SESSION['sccs_msg'];  unset($_SESSION['sccs_msg']); ?>
</div>
</div>

<?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <?php include("wpdm-login-form.php"); ?>
        </div>

<div class="col-md-6">
<?php include("wpdm-reg-form.php"); ?>
</div>

</div>
<?php if(isset($_REQUEST['reseted'])): ?>
<div class="row">
<div class="col-md-12">
<div class="alert alert-success"><?php echo $_COOKIE['global_success'];?></div>
</div>
</div>
<?php unset($_COOKIE['global_success']); endif; ?>
