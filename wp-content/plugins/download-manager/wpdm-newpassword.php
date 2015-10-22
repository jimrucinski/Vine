<div align="left">
<?php global $wp_query; if($_SESSION['login_error']!=''&&$wp_query->query_vars['wpedentask']=='login') {  ?>
<blockquote class="error" style="width: 260px;text-align: left;">
<b>Login Failed!</b><br/>
<?php echo $_SESSION['login_error']; ?>
</blockquote>   
<?php } ?>

<form name="loginform" id="loginform" action="<?php the_permalink(); ?>login/" method="post" class="login-form"> 

<input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />
<h1 class="header-1 entry-title">Login</h1>
<div class="stripe"></div>
            <p class="login-username"> 
                <label for="user_login">Username</label> 
                <input type="text" name="login[log]" id="user_login" class="input" value="" size="20" tabindex="10" /> 
            </p> 
            <p class="login-password"> 
                <label for="user_pass">Password</label> 
                <input type="password" name="login[pwd]" id="user_pass" class="input" value="" size="20" tabindex="20" /> 
            </p> 
            
            <p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90" /> Remember Me</label></p> 
            <p class="login-submit"> 
                <input type="submit" name="wp-submit" id="wp-submit" value="Log In" tabindex="100" class="btn btn-primary" /> 
                <input type="hidden" name="redirect_to" value="<?php the_permalink(); ?>" /> 
            </p> 
</form>   
<h1 class="header-1 entry-title">Forgot Password?</h1>
<div class="stripe"></div>
<form method="post" action="<?php the_permalink(); ?>" id="lostpasswordform" name="lostpasswordform" class="well form-inline">
<input type="hidden" name="minimaxtask" value="remindpass" />
   
    <p>
        <label>Username or E-mail:<br></label>
        <input type="text" tabindex="10" size="20" value="" class="input" id="user_login" name="user_login"> <input type="submit" tabindex="100" value="Remind Me!" class="btn btn-blue" id="wp-submit" name="wp-submit">
        
    </p>
    <input type="hidden" value="" name="redirect_to">
     
</form>
</div>