


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
            <b style="font-size: 12pt;line-height:28px"><i class="fa fa-bar-chart-o"></i> &nbsp; <?php echo __('Download Statistics','wpdmpro'); ?></b>

        </div>
        <ul id="tabs" class="nav nav-tabs" style="padding: 10px 10px 0 10px;background: #f5f5f5">
            <li <?php if((!isset($_GET['type']))&&!isset($_GET['task'])){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats'><?php echo __('Monthly Stats','wpdmpro'); ?></a></li>
            <li <?php if(isset($_GET['type'])&&$_GET['type']=='pvdpu'){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats&type=pvdpu'><?php echo __('Package vs Date','wpdmpro'); ?></a></li>
            <li <?php if(isset($_GET['type'])&&$_GET['type']=='pvupd'){ ?>class="active"<?php } ?>><a href='edit.php?post_type=wpdmpro&page=wpdm-stats&type=pvupd'><?php echo __('Package vs User','wpdmpro'); ?></a></li>
        </ul>
        <div class="tab-content" style="padding: 15px;">
<?php 

$type = isset($_GET['type'])?"stats/{$_GET['type']}.php":"stats/current-month.php";

include($type);

?>
</div>
</div>