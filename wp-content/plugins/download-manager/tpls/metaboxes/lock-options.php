<div id="lock-options"  class="tab-pane">
    <?php echo __('You can use one or more of following methods to lock your package download:','wpdmpro'); ?>
    <br/>
    <br/>
    <div class="wpdm-accordion" style="border: 1px solid #ccc;padding-bottom:1px">

        <h3><input type="checkbox" class="wpdmlock" rel='password' name="file[password_lock]" <?php if(get_post_meta($post->ID,'__wpdm_password_lock', true)=='1') echo "checked=checked"; ?> value="1"><?php echo __('Enable Password Lock','wpdmpro'); ?></h3>
        <div  id="password" class="fwpdmlock" <?php if(get_post_meta($post->ID,'__wpdm_password_lock', true)!='1') echo "style='display:none'"; ?> >
            <table width="100%"  cellpadding="0" cellspacing="0">
                <tr id="password_row">
                    <td><?php echo __('Password:','wpdmpro'); ?></td>
                    <td><input size="10" style="width: 200px" type="text" name="file[password]" id="pps_z" value="<?php echo get_post_meta($post->ID,'__wpdm_password', true); ?>" /><span class="info infoicon" title="You can use single or multiple password<br/>for a package. If you are using multiple password then<br/>separate each password by []. example [password1][password2]">&nbsp;</span> <img style="float: right;margin-top: -3px" class="genpass"  title='Generate Password' onclick="return generatepass('pps_z')" src="<?php echo plugins_url('download-manager/images/generate-pass.png'); ?>" alt="" /></td>
                </tr>
                <tr id="password_usage_row">
                    <td><?php echo __('PW Usage Limit:','wpdmpro'); ?></td>
                    <td><input size="10" style="width: 80px" type="text" name="file[password_usage_limit]" value="<?php echo get_post_meta($post->ID,'__wpdm_password_usage_limit', true); ?>" /> / <?php echo __('password','wpdmpro'); ?> <span class="info infoicon" title="<?php echo __('Password will expire after it exceed this usage limit','wpdmpro'); ?>">&nbsp;</span></td>
                </tr>
                <tr id="password_usage_row">
                    <td colspan="2"><label><input type="checkbox" name="file[password_usage]" value="0" /> <?php echo __('Reset Password Usage Count','wpdmpro'); ?></label></td>
                     </td>
                </tr>
            </table>
        </div>
        <h3><input type="checkbox" rel="linkedin" class="wpdmlock" name="file[linkedin_lock]" <?php if(get_post_meta($post->ID,'__wpdm_linkedin_lock', true)=='1') echo "checked=checked"; ?> value="1"><?php echo __('LinkedIn Share Lock','wpdmpro'); ?></h3>
        <div id="linkedin" class="frm fwpdmlock" <?php if(get_post_meta($post->ID,'__wpdm_linkedin_lock', true)!='1') echo "style='display:none'"; ?> >
            <table width="100%"  cellpadding="0" cellspacing="0" >
                <tr>
                    <td><?php echo __('Custom linkedin share message:','wpdmpro'); ?>
                        </br><textarea style="width: 100%" name="file[linkedin_message]"><?php echo get_post_meta($post->ID,'__wpdm_linkedin_message', true) ?></textarea>
                        URL to share (keep empty for current page url):
                        </br><input style="width: 100%" type="text" name="file[linkedin_url]" value="<?php echo get_post_meta($post->ID,'__wpdm_linkedin_url', true) ?>" />
                    </td>
                </tr>
            </table>
        </div>
        <h3><input type="checkbox" rel="tweeter" class="wpdmlock" name="file[tweet_lock]" <?php if(get_post_meta($post->ID,'__wpdm_tweet_lock', true)=='1') echo "checked=checked"; ?> value="1"><?php echo __('Tweet Lock','wpdmpro'); ?></h3>
        <div id="tweeter" class="frm fwpdmlock" <?php if(get_post_meta($post->ID,'__wpdm_tweet_lock', true)!='1') echo "style='display:none'"; ?> >
            <table width="100%"  cellpadding="0" cellspacing="0" >
                <tr>
                    <td><?php echo __('Custom tweet message:','wpdmpro'); ?>
                        </br><textarea style="width: 100%" type="text" name="file[tweet_message]"><?php echo get_post_meta($post->ID,'__wpdm_tweet_message', true) ?></textarea></td>
                </tr>
            </table>
        </div>
        <h3><input type="checkbox" rel="gplusone" class="wpdmlock" name="file[gplusone_lock]" <?php if(get_post_meta($post->ID,'__wpdm_gplusone_lock', true)=='1') echo "checked=checked"; ?> value="1"><?php echo __('Enable Google +1 Lock','wpdmpro'); ?></h3>
        <div id="gplusone" class="frm fwpdmlock" <?php if(get_post_meta($post->ID,'__wpdm_gplusone_lock', true)!='1') echo "style='display:none'"; ?> >
            <table width="100%"  cellpadding="0" cellspacing="0" >
                <tr>
                    <td width="90px"><?php echo __('URL for +1:','wpdmpro'); ?></td>
                    <td><input size="10" style="width: 200px" type="text" name="file[google_plus_1]" value="<?php echo get_post_meta($post->ID,'__wpdm_google_plus_1', true) ?>" /></td>
                </tr>
            </table>
        </div>
        <h3><input type="checkbox" rel="facebooklike" class="wpdmlock" name="file[facebooklike_lock]" <?php if(get_post_meta($post->ID,'__wpdm_facebooklike_lock', true)=='1') echo "checked=checked"; ?> value="1"><?php echo __('Enable Facebook Like Lock','wpdmpro'); ?></h3>
        <div id="facebooklike" class="frm fwpdmlock" <?php if(get_post_meta($post->ID,'__wpdm_facebooklike_lock', true)!=1) echo "style='display:none;'"; ?> >
            <table  width="100%" cellpadding="0" cellspacing="0">
                <?php if(get_option('_wpdm_facebook_app_id')=='') echo "<tr><td colspan=2>You have to add a Facebook appID <a href='admin.php?page=file-manager/settings#fbappid'>here</a></td></tr>"; ?>
                <tr>
                    <td width="90px"><?php echo __('URL to Like:','wpdmpro'); ?></td>
                    <td><input size="10" style="width: 200px" type="text" name="file[facebook_like]" value="<?php echo get_post_meta($post->ID,'__wpdm_facebook_like', true) ?>" /></td>
                </tr>
            </table>
        </div>
        <h3><input type="checkbox" rel="email" class="wpdmlock" name="file[email_lock]" <?php if(get_post_meta($post->ID,'__wpdm_email_lock', true)=='1') echo "checked=checked"; ?> value="1"><?php echo __('Enable Email Lock','wpdmpro'); ?> </h3>
        <div id="email" class="frm fwpdmlock"  <?php if(get_post_meta($post->ID,'__wpdm_email_lock', true)!='1') echo "style='display:none'"; ?> >
            <table  cellpadding="0" cellspacing="0" width="100%">
                <tr><td>
                        <?php if(isset($post->ID)) do_action('wpdm_custom_form_field',$post->ID); ?>
                    </td>
                </tr>
                <tr><td>

                        <?php echo __('Will ask for email (and checked custom data) before download','wpdmpro'); ?><br/>
                  </td></tr>
            </table>
        </div>
        <?php do_action('wpdm_download_lock_option',$post); ?>
    </div>
    <div class="clear"></div>
</div>