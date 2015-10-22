<div style="max-width: 300px;margin: 0 auto">
<?php if(is_user_logged_in()){

    do_action("wpdm_user_logged_in","<div class='alert alert-success'>".__("You are already logged in.","wpdmpro")." <a href='".wp_logout_url()."'>".__("Logout","wpdmpro")."</a></div>");

} else { ?>

<form name="loginform" id="loginform" action="" method="post" class="login-form" style="margin: 0">

<input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />
    <div class="panel panel-default">
        <div class="panel-heading"><b><?php _e("LogIn","wpdmpro");?></b></div>
        <div class="panel-body">
<?php global $wp_query; if(isset($_SESSION['login_error'])&&$_SESSION['login_error']!='') {  ?>
<div class="error alert alert-danger" >
<b><?php _e('Login Failed!','wpdmpro'); ?></b><br/>
<?php echo preg_replace("/<a.*?<\/a>\?/i","",$_SESSION['login_error']); $_SESSION['login_error']=''; ?>
</div>
<?php } ?>
            <p class="login-username"> 
                <label for="user_login"><?php _e('Username','wpdmpro'); ?></label> 
                <input type="text" name="login[log]" id="user_login" class="form-control input required text" value="" size="20" tabindex="38" />
            </p> 
            <p class="login-password"> 
                <label for="user_pass"><?php _e('Password','wpdmpro'); ?></label> 
                <input type="password" name="login[pwd]" id="user_pass" class="form-control input required password" value="" size="20" tabindex="39" />
            </p>

            <?php do_action("wpdm_login_form"); ?>
            <?php do_action("login_form"); ?>
            
            <p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="39" /> <?php _e('Remember Me','wpdmpro'); ?></label></p> 
            <p class="login-submit"> 
                <input type="submit" name="wp-submit" id="wp-submit" value="Log In" tabindex="40" class="btn btn-primary" /> 
                <input type="hidden" name="redirect_to" value="<?php echo $redirect; ?>" />
                
            </p>
            </div><div class="panel-footer">
            <?php _e('Forgot Password?','wpdmpro'); ?> <a href="<?php echo site_url('/wp-login.php?action=lostpassword'); ?>"><?php _e('Request New Password.','wpdmpro'); ?></a>
        </div>
            </div>
</form>


<script language="JavaScript">
<!--
  jQuery(function(){       
      jQuery('#loginform').validate({
            highlight: function(label) {
            jQuery(label).closest('.control-group').addClass('error');
            },
             success: function(label) {
            label
            .addClass('valid')
            .closest('.control-group').addClass('success');
            }
      });
  });
//-->
</script>

<?php } ?></div>