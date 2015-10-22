<?php
global $wpdm_message, $btnclass;

function wpdm_print_cat_dropdown()
{
    echo "<option value=''>" . __('Top Level Category', 'wpdmpro') . "</option>";
    wpdm_cat_dropdown_tree('', 0, '');
    die();
}

/**
 * Setup wpdm pro custom post type and taxonomy
 */
function wpdm_common_actions()
{
    $labels = array(
        'name' => __('Downloads', 'wpdmpro'),
        'singular_name' => __('Package', 'wpdmpro'),
        'add_new' => __('Add New', 'wpdmpro'),
        'add_new_item' => __('Add New Package', 'wpdmpro'),
        'edit_item' => __('Edit Package', 'wpdmpro'),
        'new_item' => __('New Package', 'wpdmpro'),
        'all_items' => __('All Packages', 'wpdmpro'),
        'view_item' => __('View Package', 'wpdmpro'),
        'search_items' => __('Search Packages', 'wpdmpro'),
        'not_found' => __('No Package Found', 'wpdmpro'),
        'not_found_in_trash' => __('No Packages found in Trash', 'wpdmpro'),
        'parent_item_colon' => '',
        'menu_name' => __('Downloads', 'wpdmpro')

    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'query_var' => true,
        'rewrite' => array('slug' => sanitize_title(get_option('__wpdm_purl_base', 'download')), 'with_front' => (bool)get_option('__wpdm_purl_with_front', false)), //get_option('__wpdm_purl_base','download')
        'capability_type' => 'post',
        'has_archive' => (get_option('__wpdm_has_archive', false)==false?false:sanitize_title(get_option('__wpdm_archive_page_slug', 'all-downloads'))),
        'hierarchical' => false,

        'taxonomies' => array('post_tag'),
        'menu_icon' => 'dashicons-download',
        'exclude_from_search' => (bool)get_option('__wpdm_exclude_from_search', false),
        //'menu_icon' => plugins_url('/download-manager/images/download-manager-16.png'),
        'supports' => array('title', 'editor', 'publicize', 'excerpt', 'custom-fields', 'thumbnail', 'tags', 'comments','author')

    );
    register_post_type('wpdmpro', $args);


    $labels = array(
        'name' => __('Categories', 'wpdmpro'),
        'singular_name' => __('Category', 'wpdmpro'),
        'search_items' => __('Search Categories', 'wpdmpro'),
        'all_items' => __('All Categories', 'wpdmpro'),
        'parent_item' => __('Parent Category', 'wpdmpro'),
        'parent_item_colon' => __('Parent Category:', 'wpdmpro'),
        'edit_item' => __('Edit Category', 'wpdmpro'),
        'update_item' => __('Update Category', 'wpdmpro'),
        'add_new_item' => __('Add New Category', 'wpdmpro'),
        'new_item_name' => __('New Category Name', 'wpdmpro'),
        'menu_name' => __('Categories', 'wpdmpro'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => sanitize_title(get_option('__wpdm_curl_base', 'download-category'))),
    );

    register_taxonomy('wpdmcategory', array('wpdmpro'), $args);


}

/**
 * Download contents as a file
 * @param $filename
 * @param $content
 */
function wpdm_download_data($filename, $content)
{
    @ob_end_clean();
    header("Content-Description: File Transfer");
    header("Content-Type: text/plain");
    header("Content-disposition: attachment;filename=\"$filename\"");
    header("Content-Transfer-Encoding: text/plain");
    header("Content-Length: " . strlen($content));
    echo $content;
}


/**
 * Cache remote file to local directory and return local file path
 * @param mixed $url
 * @param mixed $filename
 * @return string $path
 */
function wpdm_cache_remote_file($url, $filename = '')
{
    $filename = $filename ? $filename : end($tmp = explode('/', $url));
    $path = WPDM_CACHE_DIR . $filename;
    $fp = fopen($path, 'w');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    $data = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $path;
}

/**
 * @usage Download Given File
 * @param $filepath
 * @param $filename
 * @param int $speed
 * @param int $resume_support
 * @param array $extras
 */
function wpdm_download_file($filepath, $filename, $speed = 0, $resume_support = 1, $extras = array())
{

//dd($_SERVER);

    if (isset($extras['package']))
        $package = $extras['package'];
    $mdata = wp_check_filetype($filename);
    $content_type = $mdata['type'];

    $buffer = $speed ? $speed : 1024;

    //$buffer *= 1024; // in byte

    $bandwidth = 0;

    @ini_set('zlib.output_compression', 'Off');
    @set_time_limit(0);
    @session_cache_limiter('none');
    @ob_end_clean();
    @session_write_close();
    //@ob_clean();
    if (strpos($filepath, '://'))
        $filepath = wpdm_cache_remote_file($filepath, $filename);

    if (file_exists($filepath))
        $fsize = filesize($filepath);
    else
        $fsize = 0;


    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Robots: none");
    header("Content-type: $content_type");
    header("Content-disposition: attachment;filename=\"{$filename}\"");
    header("Content-Transfer-Encoding: binary");

    if(isset($_REQUEST['play']) && strpos($_SERVER['HTTP_USER_AGENT'],"Safari")){
        header("Content-Length: " . $fsize);
        readfile($filepath);
        die();
    }


    $file = fopen($filepath, "rb");

    //check if http_range is sent by browser (or download manager)
    if (isset($_SERVER['HTTP_RANGE']) && $fsize > 0) {
        list($bytes, $http_range) = explode("=", $_SERVER['HTTP_RANGE']);
        $set_pointer = intval(array_shift($tmp = explode('-', $http_range)));

        $new_length = $fsize - $set_pointer;

        header("Accept-Ranges: bytes");
        header("HTTP/1.1 206 Partial Content");

        header("Content-Length: $new_length");
        header("Content-Range: bytes $http_range$fsize/$fsize");

        fseek($file, $set_pointer);

    } else {
        header("Content-Length: " . $fsize);
    }

    $packet = 1;

    if ($file) {
        while (!(connection_aborted() || connection_status() == 1) && $fsize > 0) {
            if ($fsize > $buffer)
                echo fread($file, $buffer);
            else
                echo fread($file, $fsize);
            flush();
            $fsize -= $buffer;
            $bandwidth += $buffer;
            if ($speed > 0 && ($bandwidth > $speed * $packet * 1024)) {
                sleep(1);
                $packet++;
            }


        }
        $package['downloaded_file_size'] = $fsize;
        //add_action('wpdm_download_completed', $package);
        @fclose($file);
    }

}


function wpdm_stream_file($filepath){
    die("OK");
    readfile($filepath);
    die();
}


/**
 * @usage Check multi user satus ! This functions deprecated from wpdm pro 4.0.0
 * @param string $cond
 * @return bool|string
 */
function wpdm_multi_user($cond = '')
{
    global $current_user;
    get_currentuserinfo();
    $ismu = get_option('wpdm_multi_user') == 1 && !$current_user->caps['administrator'] ? true : false;
    return $ismu && $cond ? $cond : $ismu;
}


/**
 * @usage Generate downlad link of a package
 * @param $package
 * @param int $embed
 * @param array $extras
 * @return string
 */
function DownloadLink(&$package, $embed = 0, $extras = array())
{
    global $wpdb, $current_user, $wpdm_download_icon, $wpdm_download_lock_icon, $btnclass;
    extract($extras);
    $data = '';
    get_currentuserinfo();

    $package['link_url'] = home_url('/?download=1&');
    $package['link_label'] = !isset($package['link_label']) || $package['link_label'] == '' ? __("Download", "wpdmpro") : $package['link_label'];

    //Change link label using a button image
    $package['link_label'] = apply_filters('wpdm_button_image', $package['link_label'], $package);


    $package['download_url'] = wpdm_download_url($package);
    if (wpdm_is_download_limit_exceed($package['ID'])) {
        $package['download_url'] = '#';
        $package['link_label'] = __msg('DOWNLOAD_LIMIT_EXCEED');
    }
    if (isset($package['expire_date']) && $package['expire_date'] != "" && strtotime($package['expire_date']) < time()) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download was expired on', 'wpdmpro') . " " . date_i18n(get_option('date_format')." h:i A", strtotime($package['expire_date']));
        $package['download_link'] = "<a href='#'>{$package['link_label']}</a>";
        return "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$package['link_label']}</div>";
    }

    if (isset($package['publish_date']) && $package['publish_date'] !='' && strtotime($package['publish_date']) > time()) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download will be available from ', 'wpdmpro') . " " . date_i18n(get_option('date_format')." h:i A", strtotime($package['publish_date']));
        $package['download_link'] = "<a href='#'>{$package['link_label']}</a>";
        return "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$package['link_label']}</div>";
    }

    $link_label = isset($package['link_label']) ? $package['link_label'] : __('Download', 'wpdmpro');

    $package['access'] = @maybe_unserialize($package['access']);
    $access = array();

    /*
     * Category Access Settings Disabled Temporarily
     *
    $categories = maybe_unserialize(get_option("_fm_categories"));
    $cats = @maybe_unserialize($package['category']);
    $access = array();
    if (is_array($cats)) {
        foreach ($cats as $c) {
            if (!is_array($categories[$c]['access'])) $categories[$c]['access'] = array();
            foreach ($categories[$c]['access'] as $ac) {
                $access[] = $ac;
            }
        }
    }*/
    if (count($access) > 0) {
        foreach ($access as $role) {
            if (!@in_array($role, $package['access']))
                $package['access'][] = $role;
        }
    }
    if ($package['download_url'] != '#')
        $package['download_link'] = "<a class='wpdm-download-link wpdm-download-locked {$btnclass}' rel='noindex nofollow' href='{$package['download_url']}'><i class='$wpdm_download_icon'></i>{$link_label}</a>";
    else
        $package['download_link'] = "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$link_label}</div>";
    $caps = array_keys($current_user->caps);
    $role = array_shift($caps);

    $matched = (is_array(@maybe_unserialize($package['access'])) && is_user_logged_in())?array_intersect($current_user->roles, @maybe_unserialize($package['access'])):array();

    $skiplink = 0;
    if (is_user_logged_in() && count($matched) <= 0 && !@in_array('guest', @maybe_unserialize($package['access']))) {
        $package['download_url'] = "#";
        $package['download_link'] = $package['download_link_extended'] = stripslashes(get_option('wpdm_permission_msg'));
        $package = apply_filters('download_link', $package);
        if (get_option('_wpdm_hide_all', 0) == 1) { $package['download_link'] = $package['download_link_extended'] = 'blocked'; }
        return $package['download_link'];
    }
    if (!@in_array('guest', @maybe_unserialize($package['access'])) && !is_user_logged_in()) {

        $loginform = wpdm_loginform();
        if (get_option('_wpdm_hide_all', 0) == 1) return 'loginform';
        $package['download_url'] = home_url('/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        $package['download_link'] = stripcslashes(str_replace("[loginform]", $loginform, get_option('wpdm_login_msg')));
        return get_option('__wpdm_login_form', 0) == 1 ? $loginform : $package['download_link'];

    }

    $package = apply_filters('download_link', $package);

    $unqid = uniqid();
    if (!isset($package['quota']) || (isset($package['quota']) && $package['quota'] > 0 && $package['quota'] > $package['download_count']) || $package['quota'] == 0) {
        $lock = 0;

        if (isset($package['password_lock']) && $package['password'] != '') {
            $lock = 'locked';
            $data = '
         <div class="panel panel-default">
            <div class="panel-heading">
    Enter Correct Password to Download
  </div>
  <div class="panel-body">
        <div id="msg_' . $package['ID'] . '" style="display:none;">processing...</div>
        <form id="wpdmdlf_' . $unqid . '_' . $package['ID'] . '" method=post action="' . home_url('/') . '" style="margin-bottom:0px;">
        <input type=hidden name="id" value="' . $package['ID'] . '" />
        <input type=hidden name="dataType" value="json" />
        <input type=hidden name="execute" value="wpdm_getlink" />
        <input type=hidden name="action" value="wpdm_ajax_call" />
        ';

            $data .= '
        <input type="password"  class="form-control" placeholder="Enter Password" size="10" id="password_' . $unqid . '_' . $package['ID'] . '" name="password" />
        <input style="margin:5px 0" id="wpdm_submit_' . $unqid . '_' . $package['ID'] . '" style="padding:6px 10px;font-size:10pt" class="wpdm_submit btn btn-warning" type="submit" value="' . __('Submit', 'wpdmpro') . '" />

        </form>        
        
        <script type="text/javascript">
        jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").submit(function(){
            var ctz = new Date().getMilliseconds();
            jQuery(this).removeClass("wpdm_submit").addClass("wpdm_submit_wait");
            jQuery(this).ajaxSubmit({
            url: "'.home_url('/?nocache=').'" + ctz,
            success: function(res){

                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();
                jQuery("#msg_' . $package['ID'] . '").html("verifying...").css("cursor","pointer").show().click(function(){ jQuery(this).hide();jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show(); });
                if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                location.href=res.downloadurl;
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").html("<a style=\'color:#ffffff !important\' class=\'btn btn-success\' href=\'"+res.downloadurl+"\'>Download</a>");
                jQuery("#msg_' . $package['ID'] . '").html("processing...").hide();
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show();
                } else {
                    jQuery("#msg_' . $package['ID'] . '").html(""+res.error);
                }
        }
            });
        return false;
        });
        </script></div></div>
         
        ';
        }


        //if(get_wpdm_meta($package['ID'],'email_lock')=='1'&&$data!='')   $data .= '<div style="margin:5px 0px;border-bottom:1px solid #eee"></div>';


        $sociallock = "";

        if (isset($package['email_lock'])) {
            $data .= wpdm_email_lock_form($package);
            $lock = 'locked';
        }

        if (isset($package['linkedin_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_linkedin_share($package, true);

        }
        if (isset($package['gplusone_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_plus1st_google_plus_one($package, true);

        }

        if (isset($package['tweet_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_tweet_button($package, true);

        }

        if (isset($package['facebooklike_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_facebook_like_button($package , true);

        }

        $extralocks = '';
        $extralocks = apply_filters("wpdm_download_lock", $extralocks, $package);

        if (is_array($extralocks) && $extralocks['lock'] === 'locked') {

            if(isset($extralocks['type']) && $extralocks['type'] == 'social')
                $sociallock .= $extralocks['html'];
            else
                $data .= $extralocks['html'];

            $lock = 'locked';
        }

        if($sociallock!=""){
            $data .= "<div class='panel panel-default'><div class='panel-heading'>".__("Like or Share to Download","wpdmpro")."</div><div class='panel-body wpdm-social-locks'>{$sociallock}</div></div>";
        }

        if ($lock === 'locked') {
            $popstyle = isset($popstyle) && in_array($popstyle, array('modal', 'pop-over')) ? $popstyle : 'pop-over';
            if ($embed == 1)
                $adata = "</strong><table class='table all-locks-table' style='border:0px'><tr><td style='padding:5px 0px;border:0px;'>" . $data . "</td></tr></table>";
            else {
                $adata = '<a href="#pkg_' . $package['ID'] . '" data-title="<button type=button id=\'close\' class=\'btn btn-link btn-xs pull-right po-close\' style=\'margin-top:-4px;margin-right:-10px\'><i class=\'fa fa-close text-danger\'></i></button> '.__('Download','wpdmpro').' ' . $package['title'] . '" class="wpdm-download-link wpdm-download-locked ' . $popstyle . ' ' . $btnclass . '"><i class=\'' . $wpdm_download_lock_icon . '\'></i>' . $package['link_label'] . '</a>';
                if ($popstyle == 'pop-over')
                    $adata .= '<div class="modal fade"><div class="row all-locks"  id="pkg_' . $package['ID'] . '">' . $data . '</div></div>';
                else
                    $adata .= '<div class="modal fade" id="pkg_' . $package['ID'] . '"> <div class="modal-header"><strong style="margin:0px;font-size:12pt">' . __('Download') . '</strong></div><div class="modal-body">' . $data . '</div><div class="modal-footer">' . __('Please take any of the actions above to start download') . '</div></div>';
            }

            $data = $adata;
        }
        if ($lock !== 'locked') {

            $data = $package['download_link'];


        }
    } else {
        $data = __("Download limit exceeded!",'wpdmpro');
    }
    //return str_replace(array("\r","\n"),"",$data);
    return $data;

}
function wpdm_get_download_link($id, $embed = 0, $extras = array())
{
    global $wpdb, $current_user, $wpdm_download_icon, $wpdm_download_lock_icon, $btnclass;
    $package = get_post($id, ARRAY_A);
    $package = array_merge($package, wpdm_custom_data($id));
    $data = '';
    get_currentuserinfo();

    $package['link_url'] = home_url('/?download=1&');
    $package['link_label'] = !isset($package['link_label']) || $package['link_label'] == '' ? __("Download", "wpdmpro") : $package['link_label'];

    //Change link label using a button image
    $package['link_label'] = apply_filters('wpdm_button_image', $package['link_label'], $package);


    $package['download_url'] = wpdm_download_url($package);
    if (wpdm_is_download_limit_exceed($package['ID'])) {
        $package['download_url'] = '#';
        $package['link_label'] = __msg('DOWNLOAD_LIMIT_EXCEED');
    }
    if (isset($package['expire_date']) && $package['expire_date'] != "" && strtotime($package['expire_date']) < time()) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download was expired on', 'wpdmpro') . " " . date_i18n(get_option('date_format')." h:i A", strtotime($package['expire_date']));
        $package['download_link'] = "<a href='#'>{$package['link_label']}</a>";
        return "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$package['link_label']}</div>";
    }

    if (isset($package['publish_date']) && $package['publish_date'] !='' && strtotime($package['publish_date']) > time()) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download will be available from ', 'wpdmpro') . " " . date_i18n(get_option('date_format')." h:i A", strtotime($package['publish_date']));
        $package['download_link'] = "<a href='#'>{$package['link_label']}</a>";
        return "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$package['link_label']}</div>";
    }

    $link_label = isset($package['link_label']) ? $package['link_label'] : __('Download', 'wpdmpro');

    $package['access'] = @maybe_unserialize($package['access']);
    $access = array();

    /*
     * Category Access Settings Disabled Temporarily
     *
    $categories = maybe_unserialize(get_option("_fm_categories"));
    $cats = @maybe_unserialize($package['category']);
    $access = array();
    if (is_array($cats)) {
        foreach ($cats as $c) {
            if (!is_array($categories[$c]['access'])) $categories[$c]['access'] = array();
            foreach ($categories[$c]['access'] as $ac) {
                $access[] = $ac;
            }
        }
    }*/
    if (count($access) > 0) {
        foreach ($access as $role) {
            if (!@in_array($role, $package['access']))
                $package['access'][] = $role;
        }
    }
    if ($package['download_url'] != '#')
        $package['download_link'] = "<a class='wpdm-download-link wpdm-download-locked {$btnclass}' rel='noindex nofollow' href='{$package['download_url']}'><i class='$wpdm_download_icon'></i>{$link_label}</a>";
    else
        $package['download_link'] = "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$link_label}</div>";
    $caps = array_keys($current_user->caps);
    $role = array_shift($caps);

    $matched = (is_array(@maybe_unserialize($package['access'])) && is_user_logged_in())?array_intersect($current_user->roles, @maybe_unserialize($package['access'])):array();

    $skiplink = 0;
    if (is_user_logged_in() && count($matched) <= 0 && !@in_array('guest', @maybe_unserialize($package['access']))) {
        $package['download_url'] = "#";
        $package['download_link'] = $package['download_link_extended'] = stripslashes(get_option('wpdm_permission_msg'));
        $package = apply_filters('download_link', $package);
        if (get_option('_wpdm_hide_all', 0) == 1) { $package['download_link'] = $package['download_link_extended'] = 'blocked'; }
        return $package['download_link'];
    }
    if (!@in_array('guest', @maybe_unserialize($package['access'])) && !is_user_logged_in()) {

        $loginform = wp_login_form(array('echo' => 0));
        if (get_option('_wpdm_hide_all', 0) == 1) return 'loginform';
        $loginform = '<a class="wpdm-download-link wpdm-download-login ' . $btnclass . '" href="#wpdm-login-form" data-toggle="modal"><i class=\'glyphicon glyphicon-lock\'></i>' . __('Login', 'wpdmpro') . '</a><div id="wpdm-login-form" class="modal fade">' . $loginform . "</div>";
        $package['download_url'] = home_url('/wp-login.php?redirect_to=' . urlencode($_SERVER['REQUEST_URI']));
        $package['download_link'] = stripcslashes(str_replace("[loginform]", $loginform, get_option('wpdm_login_msg')));
        return get_option('__wpdm_login_form', 0) == 1 ? $loginform : $package['download_link'];

    }

    $package = apply_filters('download_link', $package);

    $unqid = uniqid();
    if (!isset($package['quota']) || (isset($package['quota']) && $package['quota'] > 0 && $package['quota'] > $package['download_count']) || $package['quota'] == 0) {
        $lock = 0;

        if (isset($package['password_lock']) && $package['password'] != '') {
            $lock = 'locked';
            $data = '
         <div class="panel panel-default">
            <div class="panel-heading">
    Enter Correct Password to Download
  </div>
  <div class="panel-body">
        <div id="msg_' . $package['ID'] . '" style="display:none;">processing...</div>
        <form id="wpdmdlf_' . $unqid . '_' . $package['ID'] . '" method=post action="' . home_url('/') . '" style="margin-bottom:0px;">
        <input type=hidden name="id" value="' . $package['ID'] . '" />
        <input type=hidden name="dataType" value="json" />
        <input type=hidden name="execute" value="wpdm_getlink" />
        <input type=hidden name="action" value="wpdm_ajax_call" />
        ';

            $data .= '
        <input type="password"  class="form-control" placeholder="Enter Password" size="10" id="password_' . $unqid . '_' . $package['ID'] . '" name="password" />
        <input style="margin:5px 0" id="wpdm_submit_' . $unqid . '_' . $package['ID'] . '" style="padding:6px 10px;font-size:10pt" class="wpdm_submit btn btn-warning" type="submit" value="' . __('Submit', 'wpdmpro') . '" />

        </form>

        <script type="text/javascript">
        jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").submit(function(){
            var ctz = new Date().getMilliseconds();
            jQuery(this).removeClass("wpdm_submit").addClass("wpdm_submit_wait");
            jQuery(this).ajaxSubmit({
            url: "'.home_url('/?nocache=').'" + ctz,
            success: function(res){

                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();
                jQuery("#msg_' . $package['ID'] . '").html("verifying...").css("cursor","pointer").show().click(function(){ jQuery(this).hide();jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show(); });
                if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                location.href=res.downloadurl;
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").html("<a style=\'color:#ffffff !important\' class=\'btn btn-success\' href=\'"+res.downloadurl+"\'>Download</a>");
                jQuery("#msg_' . $package['ID'] . '").html("processing...").hide();
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show();
                } else {
                    jQuery("#msg_' . $package['ID'] . '").html(""+res.error);
                }
        }
            });
        return false;
        });
        </script></div></div>

        ';
        }


        //if(get_wpdm_meta($package['ID'],'email_lock')=='1'&&$data!='')   $data .= '<div style="margin:5px 0px;border-bottom:1px solid #eee"></div>';


        $sociallock = "";

        if (isset($package['email_lock'])) {
            $data .= wpdm_email_lock_form($package);
            $lock = 'locked';
        }

        if (isset($package['linkedin_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_linkedin_share($package, true);

        }
        if (isset($package['gplusone_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_plus1st_google_plus_one($package, true);

        }

        if (isset($package['tweet_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_tweet_button($package, true);

        }

        if (isset($package['facebooklike_lock'])) {
            $lock = 'locked';
            $sociallock .= wpdm_facebook_like_button($package , true);

        }

        $extralocks = '';
        $extralocks = apply_filters("wpdm_download_lock", $extralocks, $package);

        if (is_array($extralocks) && $extralocks['lock'] === 'locked') {

            if(isset($extralocks['type']) && $extralocks['type'] == 'social')
                $sociallock .= $extralocks['html'];
            else
                $data .= $extralocks['html'];

            $lock = 'locked';
        }

        if($sociallock!=""){
            $data .= "<div class='panel panel-default'><div class='panel-heading'>".__("Like or Share to Download","wpdmpro")."</div><div class='panel-body wpdm-social-locks'>{$sociallock}</div></div>";
        }

        if ($lock === 'locked') {
            $popstyle = isset($popstyle) && in_array($popstyle, array('modal', 'pop-over')) ? $popstyle : 'pop-over';
            if ($embed == 1)
                $adata = "</strong><table class='table all-locks-table' style='border:0px'><tr><td style='padding:5px 0px;border:0px;'>" . $data . "</td></tr></table>";
            else {
                $adata = '<a href="#pkg_' . $package['ID'] . '" data-title="Download ' . $package['title'] . '" class="wpdm-download-link wpdm-download-locked ' . $popstyle . ' ' . $btnclass . '"><i class=\'' . $wpdm_download_lock_icon . '\'></i>' . $package['link_label'] . '</a>';
                if ($popstyle == 'pop-over')
                    $adata .= '<div class="modal fade"><div class="row all-locks"  id="pkg_' . $package['ID'] . '">' . $data . '</div></div>';
                else
                    $adata .= '<div class="modal fade" id="pkg_' . $package['ID'] . '"> <div class="modal-header"><strong style="margin:0px;font-size:12pt">' . __('Download') . '</strong></div><div class="modal-body">' . $data . '</div><div class="modal-footer">' . __('Please take any of the actions above to start download') . '</div></div>';
            }

            $data = $adata;
        }
        if ($lock !== 'locked') {

            $data = $package['download_link'];


        }
    } else {
        $data = "Download limit exceeded!";
    }
    //return str_replace(array("\r","\n"),"",$data);
    return $data;

}

global $gp1c;


function wpdm_email_lock_form($package)
{

    $data = '<div class="alert alert-danger">Email Lock Is Not Enabled for This Download!</div>';
    if (isset($package['email_lock']) && $package['email_lock'] == '1') {

        $lock = 'locked';
        $unqid = uniqid();
        $btitle = isset($package['email_heading']) ? $package['email_heading'] : __('Subscribe to download', 'wpdmpro');
        $intro = isset($package['email_intro']) ? "<p>" . $package['email_intro'] . "</p>" : '';
        $data = '
                 <div class="panel panel-default">
            <div class="panel-heading">
    ' . $btitle . '
  </div>
  <div class="panel-body">
        ' . $intro . '
        <div id="emsg_' . $package['ID'] . '" style="display:none;">processing...</div>
        <form id="wpdmdlf_' . $unqid . '_' . $package['ID'] . '" method=post action="' . home_url('/') . '" style="font-weight:normal;font-size:12px;padding:0px;margin:0px">
        <input type=hidden name="id" value="' . $package['ID'] . '" />
        <input type=hidden name="dataType" value="json" />
        <input type=hidden name="execute" value="wpdm_getlink" />
        <input type=hidden name="verify" value="email" />
        <input type=hidden name="action" value="wpdm_ajax_call" />
        ';
        $data .= apply_filters('wpdm_render_custom_form_fields', $package['ID']);
        $data .= '
        <div class="input-group">
        <input type="text" class="form-control group-item" placeholder="Enter Email" size="20" id="email_' . $unqid . '_' . $package['ID'] . '" name="email" style="margin:5px 0" />
        <span class="input-group-btn">
        <button id="wpdm_submit_' . $unqid . '_' . $package['ID'] . '" class="wpdm_submit btn btn-success  group-item"  type=submit>Subscribe</button>
      </span>
    </div>


        </form>        
        
        <script type="text/javascript">
        jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").submit(function(){
            var paramObj = {};        
            jQuery("#emsg_' . $package['ID'] . '").html("processing...").show(); 
            jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();    
            jQuery.each(jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").serializeArray(), function(_, kv) {
              paramObj[kv.name] = kv.value;
            });
            var ctz = new Date().getMilliseconds();
            jQuery(this).removeClass("wpdm_submit").addClass("wpdm_submit_wait");
            jQuery(this).ajaxSubmit({
            url: "'.home_url('/?nocache=').'" + ctz,
            success:function(res){
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();
                jQuery("#emsg_' . $package['ID'] . '").html("verifying...").css("cursor","pointer").show().click(function(){ jQuery(this).hide();jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show(); });
                if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                location.href=res.downloadurl;
                jQuery("#emsg_' . $package['ID'] . '").html(res.msg);
                jQuery("#pkg_' . $package['ID'] . ' .modal-body").html("<div style=\'padding:10px;text-align:center\'><a style=\'color:#fff !important\' class=\'btn btn-success\' href=\'"+res.downloadurl+"\'>Download</a></div>").fadeIn();
                } else {
                    jQuery("#emsg_' . $package['ID'] . '").html(""+res.error);
                }

            }})
            /*
            jQuery.post("' . home_url('/') . '",paramObj,function(res){        
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();            
                jQuery("#emsg_' . $package['ID'] . '").html("verifying...").css("cursor","pointer").show().click(function(){ jQuery(this).hide();jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show(); });            
                if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                location.href=res.downloadurl;
                jQuery("#emsg_' . $package['ID'] . '").html(res.msg);
                jQuery("#pkg_' . $package['ID'] . ' .modal-body").html("<div style=\'padding:10px;text-align:center\'><a style=\'color:#fff !important\' class=\'btn btn-success\' href=\'"+res.downloadurl+"\'>Download</a></div>").fadeIn();
                } else {             
                    jQuery("#emsg_' . $package['ID'] . '").html(""+res.error);                     
                } 
        });*/
        return false;
        });
        </script>
         </div></div>
        ';
    }
    return $data;
}

global $tbc;

function wpdm_tweet_button($package, $buttononly = false)
{
    global $tbc;

    $tbc++;
    $var = md5('tl_visitor.' . $_SERVER['REMOTE_ADDR'] . '.' . $tbc . '.' . md5(get_permalink($package['ID'])));

    $tweet_message = $package['tweet_message'];

    //$href = $href?$href:get_permalink(get_the_ID());
    $tmpid = uniqid();
    //update_post_meta(get_the_ID(),$var,$package['download_url']);
    $force = rtrim(base64_encode("unlocked|" . date("Ymdh")), '=');
    if (isset($_COOKIE[$var]) && $_COOKIE[$var] == 1)
        return $package['download_url'];
    else
        $data = '<div id="tweet_content_' . $package['ID'] . '" class="locked_ct pull-left"><a href="https://twitter.com/share?text=' . $tweet_message . '" class="twitter-share-button" data-via="webmaniac">Tweet</a></div><div style="clear:both"></div>';
    $req = home_url('/?pid=' . $package['ID'] . '&var=' . $var);
    $home = home_url('/');
    $btitle = isset($package['tweet_heading']) ? $package['tweet_heading'] : __('Tweet to download', 'wpdmpro');
    $intro = isset($package['tweet_intro']) ? "<p>" . $package['tweet_intro'] . "</p>" : '';
    $html = <<<DATA
                  
                <div class="panel panel-default">
            <div class="panel-heading">
    {$btitle}
  </div>
  <div class="panel-body" id="in_{$tmpid}">

                <div id="tl_$tbc" style="max-width:100%;overflow:hidden">
                {$intro}<Br/>
                $data
                </div>
               
               
                <script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>
                <script type="text/javascript">

                if(typeof twttr !== 'undefined'){
                twttr.ready(function (twttr) {
                   
                    twttr.events.bind('tweet', function(event) {
                        document.log(event);
                        var data = {unlock_key : '<?php echo base64_encode(session_id());?>'};
                        var ctz = new Date().getMilliseconds();

                        jQuery.cookie('unlocked_{$package['ID']}',1); 
                        jQuery.post("{$home}?nocache="+ctz,{id:{$package['ID']},dataType:'json',execute:'wpdm_getlink',force:'$force',social:'t',action:'wpdm_ajax_call'},function(res){
                            if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                            location.href=res.downloadurl;
                            jQuery('#in_{$tmpid}').html('<div style="padding:10px;text-align:center;"><a style="color:#fff" class="btn btn-success" href="'+res.downloadurl+'">Download</a></div>');
                            } else {             
                                jQuery("#msg_{$package['ID']}").html(""+res.error);                                
                            } 
                    }, "json").error(function(xhr, ajaxOptions, thrownError) {

                        });
                    });
                   
                });}
                 
                </script>
                
           </div></div>

DATA;

    if($buttononly==true)
        $html = <<<DATA


  <div class="labell" id="in_{$tmpid}">


                $data


                <script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>
                <script type="text/javascript">
                var ctz = new Date().getMilliseconds();

                if(typeof twttr !== 'undefined'){
                twttr.ready(function (twttr) {

                    twttr.events.bind('tweet', function(event) {

                        var data = {unlock_key : '<?php echo base64_encode(session_id());?>'};
                        var ctz = new Date().getMilliseconds();
                        jQuery.cookie('unlocked_{$package['ID']}',1);
                        jQuery.post("{$home}?nocache="+ctz,{id:{$package['ID']},dataType:'json',execute:'wpdm_getlink',force:'$force',social:'t',action:'wpdm_ajax_call'},function(res){
                            if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                            location.href=res.downloadurl;
                            jQuery('#in_{$tmpid}').html('<div style="padding:10px;text-align:center;"><a style="color:#fff" class="btn btn-success" href="'+res.downloadurl+'">Download</a></div>');
                            } else {
                                jQuery("#msg_{$package['ID']}").html(""+res.error);
                            }
                    }, "json").error(function(xhr, ajaxOptions, thrownError) {

                        });
                    });

                });}

                </script>

           </div>

DATA;
    return $html;
}

$lis = 0;
function wpdm_linkedin_share($package, $buttononly = false)
{
    global $lis;

    $lis++;
    $var = md5('li_visitor.' . $_SERVER['REMOTE_ADDR'] . '.' . $lis . '.' . md5(get_permalink($package['ID'])));
    $tmpid = uniqid();
    $href = $package['linkedin_url'];
    $href = $href ? $href : get_permalink($package['ID']);

    //update_post_meta(get_the_ID(),$var,$package['download_url']);
    $force = str_replace("=", "", base64_encode("unlocked|" . date("Ymdh")));
    if (isset($_COOKIE[$var]) && $_COOKIE[$var] == 1)
        return "<a href='{$package['download_url']}'>Download</a>";
    else
        $data = '<script src="//platform.linkedin.com/in.js" type="text/javascript"></script><script type="IN/Share" data-url="' . $href . '" data-counter="right" data-onSuccess="wpdm_linkedin_unlock_' . $lis . '"></script>';
    $req = home_url('/?pid=' . $package['ID'] . '&var=' . $var);
    $home = home_url('/');
    $btitle = isset($package['linkedin_heading']) ? $package['linkedin_heading'] : __('Share on Linkedin to download', 'wpdmpro');
    $intro = isset($package['linkedin_intro']) ? "<p>" . $package['linkedin_intro'] . "</p>" : '';
    $html = <<<DATA
                
                 <div class="panel panel-default">
            <div class="panel-heading">
    {$btitle}
  </div>
  <div class="panel-body" id="ll_{$tmpid}">
                <div id="lin_$lis" style="max-width:100%;overflow:hidden">
                {$intro}<br/>
                $data
                </div>
               
               
                <script type="text/javascript">
                  (function() {
                    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                    po.src = 'https://apis.google.com/js/plusone.js';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                  })();

                  function wpdm_linkedin_unlock_$lis(){                         
                        jQuery.cookie('unlocked_{$package['ID']}',1);
                        var ctz = new Date().getMilliseconds();
                        jQuery.post("{$home}?nocache="+ctz,{id:{$package['ID']},dataType:'json',execute:'wpdm_getlink',force:'$force',social:'l',action:'wpdm_ajax_call'},function(res){
                            if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                            location.href=res.downloadurl;
                            jQuery('#ll_{$tmpid}').html('<a class="btn btn-success" href="'+res.downloadurl+'">Download</a>');
                            } else {             
                                jQuery("#lin_$lis").html(""+res.error);                                
                            } 
                    }, "json");
                      
                  
                  }
                  
                </script></div></div>
                
                

DATA;

    if($buttononly==true)
        $html = <<<DATA


  <div class='labell' id="ll_{$tmpid}">
                    $data

                <script type="text/javascript">
                  (function() {
                    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                    po.src = 'https://apis.google.com/js/plusone.js';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                  })();

                  function wpdm_linkedin_unlock_$lis(){
                        jQuery.cookie('unlocked_{$package['ID']}',1);
                        var ctz = new Date().getMilliseconds();
                        jQuery.post("{$home}?nocache="+ctz,{id:{$package['ID']},dataType:'json',execute:'wpdm_getlink',force:'$force',social:'l',action:'wpdm_ajax_call'},function(res){
                            if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                            location.href=res.downloadurl;
                            jQuery('#ll_{$tmpid}').html('<a class="btn btn-success" href="'+res.downloadurl+'">Download</a>');
                            } else {
                                /*jQuery("#ll_{$tmpid}").append(""+res.error);*/
                            }
                    }, "json");


                  }

                </script></div>



DATA;

    return $html;
}


function wpdm_plus1st_google_plus_one($package, $buttononly = false)
{
    global $gp1c;

    $gp1c++;
    $var = md5('visitor.' . $_SERVER['REMOTE_ADDR'] . '.' . $gp1c . '.' . md5(get_permalink($package['ID'])));

    $href = $package['google_plus_1'];

    $href = $href ? $href : get_permalink($package['ID']);

    //update_post_meta(get_the_ID(),$var,$package['download_url']);
    $force = str_replace("=", "", base64_encode("unlocked|" . date("Ymdh")));
    if (isset($_COOKIE[$var]) && $_COOKIE[$var] == 1)
        return $package['download_url'];
    else
        $data = '<div class="g-plusone" data-size="medium" data-href="' . $href . '" data-callback="wpdm_plus1st_unlock_' . $gp1c . '"></div>';
    $req = home_url('/?pid=' . $package['ID'] . '&var=' . $var);
    $home = home_url('/');
    $btitle = isset($package['gplus_heading']) ? $package['gplus_heading'] : __('Google +1 to download', 'wpdmpro');
    $intro = isset($package['gplus_intro']) ? "<p>" . $package['gplus_intro'] . "<p>" : '';
    $html = <<<DATA
                
               <div class="panel panel-default">
            <div class="panel-heading">
    {$btitle}
  </div>
  <div class="panel-body">
                <div id="plus_$gp1c" style="max-width:100%;overflow:hidden">
                {$intro}<br/>
                $data
                </div>
               
               <!-- Place this tag where you want the +1 button to render. -->
<div class="g-plusone" data-size="small" data-callback="oki" data-href="http://google.com"></div>


                <script type="text/javascript">
                  (function() {
                    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                    po.src = 'https://apis.google.com/js/platform.js';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                  })();
                  
                  function wpdm_plus1st_unlock_$gp1c(plusone){
                  console.log(plusone);
                        if(plusone.state!='on') { jQuery.cookie('unlocked_{$package['ID']}',null); return; }
                        jQuery.cookie('unlocked_{$package['ID']}',1);
                        var ctz = new Date().getMilliseconds();
                        jQuery.post("{$home}?nocache="+ctz,{id:{$package['ID']},dataType:'json',execute:'wpdm_getlink',force:'$force',social:'g',action:'wpdm_ajax_call'},function(res){
                            if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                            location.href=res.downloadurl;
                            jQuery('#pkg_{$package['ID']}').html('<a style="color:#000" href="'+res.downloadurl+'">Download</a>');
                            } else {             
                                jQuery("#msg_{$package['ID']}").html(""+res.error);                                
                            } 
                    }, "json");
                      
                  
                  }
                  
                </script></div></div>
                
                

DATA;

    if($buttononly==true)
        $html = <<<DATA

                <div id="plus_$gp1c" class="labell">

                $data



                <script type="text/javascript">
                  (function() {
                    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                    po.src = 'https://apis.google.com/js/plusone.js';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                  })();

                  function wpdm_plus1st_unlock_$gp1c(plusone){
                        if(plusone.state!='on') { jQuery.cookie('unlocked_{$package['ID']}',null); return; }
                        jQuery.cookie('unlocked_{$package['ID']}',1);
                        var ctz = new Date().getMilliseconds();
                        jQuery.post("{$home}?nocache="+ctz,{id:{$package['ID']},dataType:'json',execute:'wpdm_getlink',force:'$force',social:'g',action:'wpdm_ajax_call'},function(res){
                            if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                            location.href=res.downloadurl;
                            jQuery('#pkg_{$package['ID']}').html('<a style="color:#000" href="'+res.downloadurl+'">Download</a>');
                            } else {
                                jQuery("#msg_{$package['ID']}").html(""+res.error);
                            }
                    }, "json");


                  }

                </script></div>



DATA;

    return $html;
}


function wpdm_facebook_like_footer()
{
    return;
    /* echo "<div id=\"fb-root\"></div>
           <style>.fb_edge_widget_with_comment span.fb_edge_comment_widget { display: none !important; }</style>     
           <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = \"//connect.facebook.net/en_US/all.js#xfbml=1&appId=""\";
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
          "; 
          
      echo '';*/
}


function wpdm_facebook_like_button($package, $buttononly = false)
{
    $url = $package['facebook_like'];
    $url = $url ? $url : get_permalink();
    $force = str_replace("=", "", base64_encode("unlocked|" . date("Ymdh")));
    //return '<div class="fb-like" data-href="'.$url.'#'.$package['ID'].'" data-send="false" data-width="300" data-show-faces="false" data-font="arial"></div>';
    $unlockurl = home_url("/?id={$package['ID']}&execute=wpdm_getlink&force={$force}&social=f");
    $btitle = isset($package['facebook_heading']) ? $package['facebook_heading'] : __('Like on FB to Download', 'wpdmpro');
    $intro = isset($package['facebook_intro']) ? "<p>" . $package['facebook_intro'] . "</p>" : '';

    if($buttononly==true){
        return '

  <div class="labell">

     <div id="fb-root"></div>
     <div style="display:none" id="' . strtolower(str_replace(array("://", "/", "."), "", $url)) . '" >' . $package['ID'] . '</div>
     <script>
     var ctz = new Date().getMilliseconds();
            var siteurl = "' . home_url('/?nocache=') . '"+ctz,force="' . $force . '", appid="' . get_option('_wpdm_facebook_app_id', 0) . '";
            window.fbAsyncInit = function() {
                /*FB.init({
                    appId: \'' . get_option('_wpdm_facebook_app_id', 0) . '\',
                    cookie: true
                });*/

                FB.Event.subscribe(\'edge.create\', function(href) {
                    var id = href.replace(/[^0-9a-zA-Z-]/g,"");
                    id = id.toLowerCase();
                      var pkgid = jQuery(\'#\'+id).html();
                      jQuery.cookie(\'unlocked_\'+pkgid,1);

                      jQuery.post(siteurl,{id:pkgid,dataType:\'json\',execute:\'wpdm_getlink\',force:force,social:\'f\',action:\'wpdm_ajax_call\'},function(res){
                                            if(res.downloadurl!=\'\'&&res.downloadurl!=\'undefined\'&&res!=\'undefined\') {
                                            location.href=res.downloadurl;
                                            jQuery(\'#pkg_\'+pkgid).html(\'<a style="color:#000" href="\'+res.downloadurl+\'">Download</a>\');
                                            /*jQuery.cookie(\'liked_' . str_replace(array("://", "/", "."), "", $url) . '\',res.downloadurl,{expires:30});*/
                                            } else {
                                                jQuery(\'#msg_\'+pkgid).html(\'\'+res.error);
                                            }
                                    });
                      return false;
                });
            };

            (function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id; //js.async = true;
              js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=' . get_option('_wpdm_facebook_app_id', 0) . '";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, \'script\', \'facebook-jssdk\'));
     </script>
     <div class="fb-like" data-href="' . $url . '" data-send="false" data-width="100" data-show-faces="false" data-layout="button_count" data-font="arial"></div>

     <style>.fb_edge_widget_with_comment{ max-height:20px !important; overflow:hidden !important;}</style>
     </div>

     ';
    }

    return '
            <div class="panel panel-default">
            <div class="panel-heading">
    ' . $btitle . '
  </div>
  <div class="panel-body">

' . $intro . '<br/>
     <div id="fb-root"></div>
     <div style="display:none" id="' . str_replace(array("://", "/", "."), "", $url) . '" >' . $package['ID'] . '</div>
     <script>
            var siteurl = "' . home_url('/') . '",force="' . $force . '", appid="' . get_option('_wpdm_facebook_app_id', 0) . '";
            window.fbAsyncInit = function() {
                /*FB.init({
                    appId: \'' . get_option('_wpdm_facebook_app_id', 0) . '\',
                    cookie: true
                });*/

                FB.Event.subscribe(\'edge.create\', function(href) {
                    var id = href.replace(/[^0-9a-z-]/g,"");
                      var pkgid = jQuery(\'#\'+id).html();
                      jQuery.cookie(\'unlocked_\'+pkgid,1);

                      jQuery.post(siteurl,{id:pkgid,dataType:\'json\',execute:\'wpdm_getlink\',force:force,social:\'f\',action:\'wpdm_ajax_call\'},function(res){
                                            if(res.downloadurl!=\'\'&&res.downloadurl!=\'undefined\'&&res!=\'undefined\') {
                                            location.href=res.downloadurl;
                                            jQuery(\'#pkg_\'+pkgid).html(\'<a style="color:#000" href="\'+res.downloadurl+\'">Download</a>\');
                                            /*jQuery.cookie(\'liked_' . str_replace(array("://", "/", "."), "", $url) . '\',res.downloadurl,{expires:30});*/
                                            } else {
                                                jQuery(\'#msg_\'+pkgid).html(\'\'+res.error);
                                            }
                                    });
                      return false;
                });
            };

            (function(d, s, id) {
              var js, fjs = d.getElementsByTagName(s)[0];
              if (d.getElementById(id)) return;
              js = d.createElement(s); js.id = id; //js.async = true;
              js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=' . get_option('_wpdm_facebook_app_id', 0) . '";
              fjs.parentNode.insertBefore(js, fjs);
            }(document, \'script\', \'facebook-jssdk\'));
     </script>
     <div class="fb-like" data-href="' . $url . '" data-send="false" data-width="100" data-show-faces="false" data-layout="button_count" data-font="arial"></div>

     <style>.fb_edge_widget_with_comment{ max-height:20px !important; overflow:hidden !important;}</style>
     </div>

</div>
     ';

}


function wpdm_email_button($package, $icononly = false)
{
    $label = $icononly ? "" : "Email Link";
    $data = '
                    <a href="#" class="btn" onclick="jQuery(\'#epkg_' . $package['ID'] . '\').slideToggle();return false;"><i class="icon icon-email"></i>' . $label . '</a> 
                    <div class="download_page download_link"  style="z-index:99999;display:none;position:absolute;" id="epkg_' . $package['ID'] . '">' . wpdm_email_lock_form($package) . '</div>';
    return $data;
}


/**
 * @usage Generate Direct Download Button
 * @param $package
 * @param bool $icononly
 * @return string
 */
function wpdm_ddl_button($package, $icononly = false)
{
    global $wpdb, $current_user;
    $label = $icononly ? "" : "Download Now";
    //print_r($package);     
    $download_url = home_url("/?file={$package['ID']}");
    return "<a class='wpdm-gh-button wpdm-gh-icon arrowdown wpdm-gh-big' rel='noindex nofollow' href='$download_url'>$label</a>";

}

/**
 * @usage Verify Email Address
 * @param $email
 * @return bool
 */
function wpdm_verify_email($email){
    $dns_verify = get_option('__wpdm_verify_dns',0);
    $blocked_domains = explode("\n",str_replace("\r","",get_option('__wpdm_blocked_domains','')));
    $blocked_emails = explode("\n",str_replace("\r","",get_option('__wpdm_blocked_emails','')));
    $eparts = explode("@", $email);
    $domain = $eparts[1];
    if(!is_email($email)) return false;
    if(in_array($email, $blocked_emails)) return false;
    if(in_array($domain, $blocked_domains)) return false;
    if($dns_verify && !checkdnsrr($domain, 'MX')) return false;
    return true;
}


/**
 * return download link after verifying password
 * data format: json
 */
function wpdm_getlink()
{
    global $wpdb;
    if (!isset($_POST['id'])) return;
    $id = (int)$_POST['id'];
    $password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
    $file = get_post($id, ARRAY_A);
    $file['ID'] = $file['ID'];
    $file = wpdm_setup_package_data($file);
    $key = uniqid();
    $file1 = $file;
    // and( password='$password' or password like '%[$password]%')
    $plock = isset($file['password_lock']) ? $file['password_lock'] : 0;

    $data = array('error' => '', 'downloadurl' => '');
    if (isset($_POST['verify']) && $_POST['verify'] == 'email' && $file['email_lock'] == 1) {
        if (wpdm_verify_email($_POST['email'])) {
            $subject = "Your Download Link";
            $site = get_option('blogname');

            add_post_meta($file['ID'], "__wpdmkey_".$key, 3);
            //file_put_contents(WPDM_CACHE_DIR.'wpdm_'.$key,"3");
            $download_url = wpdm_download_url($file, "_wpdmkey={$key}");
            $cff = isset($_POST['custom_form_field']) ? $_POST['custom_form_field'] : array();
            $wpdb->insert("{$wpdb->prefix}ahm_emails", array('email' => $_POST['email'], 'pid' => $file['ID'], 'date' => time(), 'custom_data' => serialize($cff)));
            $eml = get_option('_wpdm_etpl');
            $eml['fromname'] = isset($eml['fromname']) ? $eml['fromname'] : get_bloginfo('name');
            $eml['frommail'] = isset($eml['frommail']) ? $eml['frommail'] : get_bloginfo('admin_email');
            $eml['subject'] = isset($eml['subject']) ? $eml['subject'] : 'Download ' . $file['post_title'];

            $headers = 'From: ' . $eml['fromname'] . ' <' . $eml['frommail'] . '>' . "\r\nContent-type: text/html\r\n";
            $file = wpdm_setup_package_data($file);
            $file['download_url'] = $download_url; //Custom Download URL for email lock
            $keys = array();
            foreach ($file as $key => $value) {
                $_key = "[$key]";
                $tdata[$_key] = $value;
            }
            $tdata["[site_url]"] = home_url('/');
            $tdata["[site_name]"] = get_bloginfo('sitename');
            $tdata["[download_url]"] = $download_url;
            $tdata["unsaved:///"] = "";
            $tdata["[date]"] = date(get_option('date_format'), time());

            $message = $eml['body'];

            foreach ($tdata as $skey => $svalue) {
                if(!is_array($svalue)) {
                    $message = str_replace(strval($skey), strval($svalue), $message);
                    $eml['subject'] = str_replace(strval($skey), strval($svalue), $eml['subject']);
                }
            }

            //do something before sending download link
            do_action("wpdm_before_email_download_link", $_POST, $file);

            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>'.__("Welcome Message","wpdmpro").'</title></head><body>' . stripslashes($message) . '</body></html>';
            wp_mail($_POST['email'], stripcslashes($eml['subject']), stripcslashes($message), $headers);
            $idl = isset($file['email_lock_idl']) ? $file['email_lock_idl'] : 0;
            if ($idl != 1) {
                $data['downloadurl'] = "";
                $data['error'] = '<div class="alert alert-success">' . __('Download link sent to your email!', 'wpdmpro') . '</div>';
            } else {
                $data['downloadurl'] = $file['download_url'];
                $data['error'] = '';
                $data['msg'] = '<div class="alert alert-success">' . __('Download link also sent to your email!', 'wpdmpro') . '</div>';
            }
            header('HTTP/1.0 200 OK');
            header("Content-type: application/json");
            echo json_encode($data);
            die();
        } else {
            $data['downloadurl'] = "";
            $data['error'] = '<div class="alert alert-danger">' . __('Invalid Email Address!', 'wpdmpro') . '</i>';
            header("Content-type: application/json");
            echo json_encode($data);
            die();
        }
    }

    if (isset($_POST['force']) && $_POST['force'] != '') {
        $vr = explode('|', base64_decode($_POST['force']));
        if ($vr[0] == 'unlocked') {
            $social = array('f' => 'wpdm_fb_likes', 'g' => 'wpdm_gplus1s', 't' => 'wpdm_tweets', 'l' => 'wpdm_lishare');
            if ($_POST['social'] && isset($social[$_POST['social']]))
                update_option($social[$_POST['social']], (int)get_option($social[$_POST['social']]) + 1);

            add_post_meta($file['ID'], "__wpdmkey_".$key, 3);
            $data['downloadurl'] = wpdm_download_url($file, "_wpdmkey={$key}");
            $adata = apply_filters("wpdmgetlink", $data, $file);
            $data = is_array($adata) ? $adata : $data;
            header("Content-type: application/json");
            die(json_encode($data));
        }

    }

    if ($plock == 1 && $password != $file['password'] && !strpos("__" . $file['password'], "[$password]")) {
        $data['error'] = 'Wrong Password!';
        $file = array();
    }
    if ($plock == 1 && $password == '') {
        $data['error'] = 'Wrong Password!';
        $file = array();
    }
    $ux = "";
    if ($plock == 1) {

        add_post_meta($file['ID'], "__wpdmkey_" .$key, 3);
    }

    if ($file['ID'] != '') {
        $pu = isset($file['password_usage']) && is_array($file['password_usage'])?$file['password_usage']:array();
        $pul = $file['password_usage_limit'];

        if (is_array($pu) && isset($pu[$password]) && $pu[$password] >= $pul && $pul > 0)
            $data['error'] = __msg('PASSWORD_LIMIT_EXCEED');
        else {
            if(!is_array($pu)) $pu = array();
            $pu[$password] = isset($pu[$password])?$pu[$password]+1:1;
            update_post_meta($file['ID'], '__wpdm_password_usage', $pu);
        }
    }
    if (isset($_COOKIE['unlocked_' . $file['ID']]) && $_COOKIE['unlocked_' . $file['ID']] == 1) {
        $data['error'] = '';
        $file = $file1;
    }

    if ($data['error'] == '') $data['downloadurl'] = wpdm_download_url($file, "_wpdmkey={$key}"); // home_url('/?downloadkey='.md5($file['files']).'&file='.$id.$ux);
    $adata = apply_filters("wpdmgetlink", $data, $file);
    $data = is_array($adata) ? $adata : $data;
    header("Content-type: application/json");
    die(json_encode($data));
}

function wpdm_download_periods() {

    if(get_post_type()!='wpdmpro') return;

    $xd = get_post_meta(get_the_ID(),'__wpdm_expire_date',true);
    $pd = get_post_meta(get_the_ID(),'__wpdm_publish_date',true);
    ?>
    <div class="misc-pub-section curtime misc-pub-curtime">
	<span id="timestamp">
	Download Available From:<Br/><input type="text" id="publish_date" autocomplete="off" size="30" value="<?php echo $pd; ?>" name="file[publish_date]"  style="padding: 4px;font-size: 10px">
    </span></div>
    <div class="misc-pub-section curtime misc-pub-curtime">
	<span id="timestamp">
	Download Expire on:<br/><input type="text" id="expire_date" autocomplete="off" size="30" value="<?php echo $xd; ?>" name="file[expire_date]"  style="padding: 4px;font-size: 10px">
    </span></div>
    <script>
        jQuery(function(){
            jQuery('#expire_date,#publish_date').datetimepicker({dateFormat:"yy-mm-dd", timeFormat: "hh:mm tt"});
        });
    </script>
<?php
}

/**
 * callback function for shortcode [wpdm_package id=pid]
 *
 * @param mixed $params
 * @return mixed
 */
function wpdm_package_link($params)
{
    extract($params);
    $postlink = site_url('/');
    if (isset($pagetemplate) && $pagetemplate == 1)
        return DownloadPageContent($id);
    $data = get_post($id, ARRAY_A);
    $data = wpdm_setup_package_data($data);

    if ($data['ID'] == '') {
        return '';
    }

    $templates = maybe_unserialize(get_option("_fm_link_templates", true));

    if(!isset($template) || $template=="" ) $template = $data['template'];

    if(isset($templates[$template]) && isset($templates[$template]['content'])) $template = $templates[$template]['content'];

    return "<div class='w3eden'>" . FetchTemplate($template, $data, 'link') . "</div>";
}


function wpdm_package_link_legacy($params)
{
    extract($params);
    $posts = get_posts(array("post_type"=>"wpdmpro","meta_key"=>"__wpdm_legacy_id","meta_value"=>$params['id']));
    $data = (array)$posts[0];
    if(!isset($data['ID'])) return "";
    $data = wpdm_setup_package_data($data);

    if ($data['ID'] == '') {
        return '';
    }

    $templates = maybe_unserialize(get_option("_fm_link_templates", true));

    if(!isset($template) || $template=="" ) $template = $data['template'];

    if(isset($template) && isset($templates[$template]) && isset($templates[$template]['content'])) $template = $templates[$template]['content'];


    return "<div class='w3eden'>" . FetchTemplate($template, $data, 'link') . "</div>";
}




/**
 * Parse shortcode
 *
 * @param mixed $content
 * @return mixed
 */
function wpdm_downloadable($content)
{
    if(defined('WPDM_THEME_SUPPORT')&&WPDM_THEME_SUPPORT==true) return $content;
    global $wpdb, $current_user, $post, $wp_query, $wpdm_package;
    if (isset($wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')]) && $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] != '')
        return wpdm_embed_category(array("id" => $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')]));
    $postlink = site_url('/');
    get_currentuserinfo();
    $permission_msg = get_option('wpdm_permission_msg') ? stripslashes(get_option('wpdm_permission_msg')) : "<div  style=\"background:url('" . get_option('siteurl') . "/wp-content/plugins/download-manager/images/lock.png') no-repeat;padding:3px 12px 12px 28px;font:bold 10pt verdana;color:#800000\">Sorry! You don't have suffient permission to download this file!</div>";
    $login_msg = get_option('wpdm_login_msg') ? stripcslashes(get_option('wpdm_login_msg')) : "<a href='" . get_option('siteurl') . "/wp-login.php'  style=\"background:url('" . get_option('siteurl') . "/wp-content/plugins/download-manager/images/lock.png') no-repeat;padding:3px 12px 12px 28px;font:bold 10pt verdana;\">Please login to access downloadables</a>";
    $user = new WP_User(null);
    if (isset($_GET[get_option('__wpdm_purl_base', 'download')]) && $_GET[get_option('__wpdm_purl_base', 'download')] != '' && $wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] == '')
        $wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] =  $_GET[get_option('__wpdm_purl_base', 'download')];
    $wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] = isset($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')]) ? urldecode($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')]) : '';

    if (is_singular('wpdmpro')) {
        if (get_option('_wpdm_custom_template') == 1) return $content;

        return DownloadPageContent();
    }

    return $content;


}


/**
 * @usage Count files in a package
 * @param $id
 * @return int
 */
function wpdm_package_filecount($id){
    $files = get_post_meta($id, '__wpdm_files', true);
    $files = maybe_unserialize($files);
    return count($files);

}

/**
 * @usage Calculate file size
 * @param $id
 * @return float|int|mixed|string
 */
function wpdm_package_size($id){
    $size = get_post_meta($id, '__wpdm_package_size', true);

    if($size!="") return $size;

    $files = maybe_unserialize(get_post_meta($id, '__wpdm_files', true));

    $size = 0;
    if (is_array($files)) {
        foreach ($files as $f) {
            $f = trim($f);
            if (file_exists($f))
                $size += @filesize($f);
            else
                $size += @filesize(UPLOAD_DIR . $f);
        }
    }

    update_post_meta($id, '__wpdm_package_size_b', $size);
    $size = $size / 1024;
    if ($size > 1024) $size = number_format($size / 1024, 2) . ' MB';
    else $size = number_format($size, 2) . ' KB';
    update_post_meta($id, '__wpdm_package_size', $size);
    return $size;
}

/**
 * @usage Returns icons for package file types
 * @param $id
 * @param bool $img
 * @return array|string
 */
function wpdm_package_filetypes($id, $img = true){

    $files = maybe_unserialize(get_post_meta($id, '__wpdm_files', true));
    $ext = array();
    if (is_array($files)) {
        foreach ($files as $f) {
            $f = trim($f);
            $f = explode(".", $f);
            $ext[] = end($f);
        }
    }

    $ext = array_unique($ext);
    $exico = '';
    foreach($ext as $exi){
        if(file_exists(dirname(__FILE__).'/file-type-icons/16x16/'.$exi.'.png'))
        $exico .= "<img alt='{$exi}' title='{$exi}' class='ttip' src='".plugins_url('download-manager/file-type-icons/16x16/'.$exi.'.png')."' /> ";
    }
    if($img) return $exico;
    return $ext;
}


/**
 * @usage Validate and sanitize input data
 * @param $var
 * @param array $params
 * @return int|null|string|void
 */
function wpdm_query_var($var, $params = array())
{
    $val = isset($_REQUEST[$var]) ? $_REQUEST[$var] : null;
    $validate = is_string($params) ? $params : '';
    $validate = is_array($params) && isset($params['validate']) ? $params['validate'] : $validate;

    switch ($validate) {
        case 'num':
            $val = intval($val);
            break;
        case 'html':

            break;
        default:
            $val = esc_attr($val);
            break;
    }

    return $val;
}

function wpdm_new_file_form_sc()
{

    global $wpdb, $current_user, $wp_query;
    wp_reset_query();
    $currentAccess = maybe_unserialize(get_option( '__wpdm_front_end_access', array()));

    if(!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in() )
        return "<div class='w3eden'><div class='alert alert-danger'>".wpautop(stripslashes(get_option( '__wpdm_front_end_access_blocked')))."</div></div>";

    $cond_uid = wpdm_multi_user("and uid='{$current_user->ID}'");
    $id = wpdm_query_var('ID');
    $task = wpdm_query_var('task');

    $tabs = array( //'sales' => array('label'=>'Sales','callback'=>'wpdm_sales_report')
    );
    $tabs = apply_filters('wpdm_frontend', $tabs);
    $burl = get_permalink();
    $sap = strpos($burl, '?') ? '&' : '?';
    ob_start();
    ?>
    <div class="w3eden">
    <ul id="tabs" class="nav nav-tabs" style="margin: 0px !important;padding: 0px;">
        <?php if (is_user_logged_in()) { ?>
            <li <?php if ($task == '' || $task == 'edit-package') { ?>class="active"<?php } ?> >
                <a href="<?php echo $burl; ?>">All Packages</a></li>
            <li <?php if ($task == 'addnew') { ?>class="active"<?php } ?> ><a
                    href="<?php echo $burl . $sap; ?>task=addnew">Create New Package</a></li>
            <?php foreach ($tabs as $tid => $tab): ?>
                <li <?php if ($task == $tid) { ?>class="active"<?php } ?> ><a
                        href="<?php echo $burl . $sap; ?>task=<?php echo $tid; ?>"><?php echo $tab['label']; ?></a></li>
            <?php endforeach; ?>
            <li <?php if ($task == 'editprofile') { ?>class="active"<?php } ?> ><a
                    href="<?php echo $burl . $sap; ?>task=editprofile">Edit
                    Profile</a></li>
            <li><a href="<?php echo $burl . $sap; ?>task=logout">Logout</a></li>
        <?php } else { ?>
            <li class="active"><a href="<?php echo $burl; ?>">Signup or Signin</a></li>
        <?php } ?>
    </ul><div class="tab-content">
    <?php

    if (is_user_logged_in()) {

        if ($task == 'addnew' || $task == 'edit-package')
            include('wpdm-add-new-file-front.php');
        else if ($task == 'editprofile')
            include('wpdm-edit-user-profile.php');
//        else if ($task != '' && $tabs[$task]['callback'] != '')
//            call_user_func($tabs[$task]['callback']);
        else if ($task != '' && $tabs[$task]['shortcode'] != '')
            do_shortcode($tabs[$task]['shortcode']);
        else
            include('wpdm-list-files-front.php');
    } else {

        include('wpdm-be-member.php');
    }
    echo '</div></div><script>jQuery(function($){ $("#tabs > li > a").click(function(){ location.href=this.href; });  });</script>';
    $data = ob_get_clean();
    
    return $data;
}

function wpdm_do_login()
{
    global $wp_query, $post, $wpdb;
    if (!isset($_POST['login'])) return;
    unset($_SESSION['login_error']);
    $creds = array();
    $creds['user_login'] = $_POST['login']['log'];
    $creds['user_password'] = $_POST['login']['pwd'];
    $creds['remember'] = isset($_POST['rememberme']) ? $_POST['rememberme'] : false;
    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $_SESSION['login_error'] = $user->get_error_message();
        header("location: " . $_SERVER['HTTP_REFERER']);
        die();
    } else {
        do_action('wp_login', $creds['user_login'], $user);
        header("location: " . $_POST['redirect_to']);
        die();
    }
}

function wpdm_do_register()
{
    global $wp_query, $wpdb;
    if (!isset($_POST['reg'])) return;
    extract($_POST['reg']);
    $_SESSION['tmp_reg_info'] = $_POST['reg'];
    $user_id = username_exists($user_login);
    $loginurl = $_POST['permalink'];
    if ($user_login == '') {
        $_SESSION['reg_error'] = __('Username is Empty!');
        header("location: " . $_POST['permalink']);
        die();
    }
    if (!isset($user_email) || !is_email($user_email)) {
        $_SESSION['reg_error'] = __('Invalid Email Address!');
        header("location: " . $_POST['permalink']);
        die();
    }

    if (!$user_id) {
        $user_id = email_exists($user_email);
        if (!$user_id) {
            $auto_login = isset($user_pass) && $user_pass!=''?1:0;
            $user_pass = isset($user_pass) && $user_pass!=''?$user_pass:wp_generate_password(12, false);

            $user_id = wp_create_user($user_login, $user_pass, $user_email);
            $display_name = isset($display_name)?$display_name:$user_id;
            $headers = "From: " . get_option('sitename') . " <" . get_option('admin_email') . ">\r\nContent-type: text/html\r\n";
            $message = file_get_contents(dirname(__FILE__) . '/templates/wpdm-new-user.html');
            $loginurl = $_POST['permalink'];
            $message = str_replace(array("[#support_email#]", "[#homeurl#]", "[#sitename#]", "[#loginurl#]", "[#name#]", "[#username#]", "[#password#]", "[#date#]"), array(get_option('admin_email'), site_url('/'), get_option('blogname'), $loginurl, $display_name, $user_login, $user_pass, date("M d, Y")), $message);

            if ($user_id) {
                wp_mail($user_email, "Welcome to " . get_option('sitename'), $message, $headers);

            }
            unset($_SESSION['guest_order']);
            unset($_SESSION['login_error']);
            unset($_SESSION['tmp_reg_info']);
            //if(!isset($_SESSION['reg_warning']))
            $creds['user_login'] = $user_login;
            $creds['user_password'] = $user_pass;
            $creds['remember'] = true;
            $_SESSION['sccs_msg'] = "Your account has been created successfully and login info sent to your mail address.";
            if($auto_login==1) {
                $_SESSION['sccs_msg'] = "Your account has been created successfully and login now.";
                wp_signon($creds);
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
            }
            header("location: " . $loginurl);
            die();
        } else {
            $_SESSION['reg_error'] = __('Email already exists.');
            $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];
            header("location: " . $loginurl);
            die();
        }
    } else {
        $_SESSION['reg_error'] = __('User already exists.');
        $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];
        header("location: " . $loginurl);
        die();
    }
    die();
}

function wpdm_update_profile()
{
    global $wp_query, $wpdb, $current_user;
    get_currentuserinfo();
    if (isset($_REQUEST['task']) && $_REQUEST['task'] == 'editprofile' && isset($_POST['profile'])) {
        extract($_POST);
        $error = 0;

        if ($password != $cpassword) {
            $_SESSION['member_error'][] = 'Password not matched';
            $error = 1;
        }
        if (!$error) {
            $profile['ID'] = $current_user->ID;
            if ($password != '')
                $profile['user_pass'] = $password;
            wp_update_user($profile);
            get_currentuserinfo();
            update_user_meta($current_user->ID, 'payment_account', $payment_account);
            $_SESSION['member_success'] = 'Profile data updated successfully.';
        }
        header("location: " . $_SERVER['HTTP_REFERER']);
        die();
    }
}


function wpdm_validate_newpass_sk()
{
    global $wp_query, $wpdb;
    if ($wp_query->query_vars['minimaxtask'] != 'new-password') return false;
    $reminder = sanitize_text_field($_REQUEST['u']);
    $userdata = $wpdb->get_row("select * from {$wpdb->prefix}users where user_login='$reminder' or user_email='$reminder'");
    $usk = get_user_meta($userdata->ID, 'remind_pass_sk', true);
    if ($usk != $_REQUEST['sk']) return false;
    return true;
}

function wpdm_update_password()
{
    global $wpdb;
    if (!isset($_POST['user_pass'])) return;
    $reminder = sanitize_text_field($_REQUEST['u']);
    $userdata = $wpdb->get_row("select * from {$wpdb->prefix}users where user_login='$reminder' or user_email='$reminder'");
    $usk = get_user_meta($userdata->ID, 'remind_pass_sk', true);
    if ($usk != $_REQUEST['sk']) return;
    $pid = uniqid();
    update_user_meta($userdata->ID, 'remind_pass_sk', $pid);
    wp_update_user(array('ID' => $userdata->ID, 'user_pass' => $_POST['user_pass']));
    header("location: " . home_url('/members/'));
    die();
}

function wpdm_do_logout()
{
    global $wp_query;
    if (isset($_GET['task']) && $_GET['task'] == 'logout') {
        wp_logout();
        header("location: " . home_url('/'));
        die();
    }
}


function wpdm_category($params)
{
    $params['order_field'] = isset($params['order_by'])?$params['order_by']:'publish_date';
    unset($params['order_by']);
    if (isset($params['item_per_page']) && !isset($params['items_per_page'])) $params['items_per_page'] = $params['item_per_page'];
    unset($params['item_per_page']);
    return wpdm_embed_category($params);

}

function wpdm_tag($params)
{
    $params['order_field'] = isset($params['order_by'])?$params['order_by']:'publish_date';
    $params['tag'] = 1;
    unset($params['order_by']);
    if (isset($params['item_per_page']) && !isset($params['items_per_page'])) $params['items_per_page'] = $params['item_per_page'];
    unset($params['item_per_page']);
    return wpdm_embed_category($params);

}

function wpdm_delete_emails()
{
    global $wpdb;
    $task = isset($_GET['task']) ? $_GET['task'] : '';
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    if ($task == 'delete' && $page == 'emails') {
        $ids = implode(",", $_POST['id']);
        $wpdb->query("delete from {$wpdb->prefix}ahm_emails where id in ($ids)");
        header("location: edit.php?post_type=wpdmpro&page=emails");
        die();
    }
}

function wpdm_export_emails()
{
    global $wpdb;
    $task = isset($_GET['task']) ? $_GET['task'] : '';
    if ($task == 'export' && isset($_GET['page']) && $_GET['page'] == 'emails') {
        $custom_fields = array();
        $csv = '';
        $custom_fields = apply_filters('wpdm_export_custom_form_fields', $custom_fields);
        $res = $wpdb->get_results("select e.* from {$wpdb->prefix}ahm_emails e order by id desc", ARRAY_A);
        if (isset($_GET['uniq']) && $_GET['uniq'] == 1)
            $res = $wpdb->get_results("select email,custom_data from {$wpdb->prefix}ahm_emails group by email", ARRAY_A);
        $csv .= "\"package\", \"email\", \"" . implode("\", \"", $custom_fields) . "\", \"date\"\r\n";
        foreach ($res as $row) {
            $data = array();
            $data['package'] = get_the_title($row['pid']);
            $data['email'] = $row['email'];
            $cf_data = unserialize($row['custom_data']);
            foreach ($custom_fields as $c) {
                $data[$c] = isset($cf_data[$c])?$cf_data[$c]:"";
            }
            $data['date'] = isset($row['date'])?date("Y-m-d H:i", $row['date']):"";
            $csv .= '"' . @implode('","', $data) . '"' . "\r\n";
        }
        header("Content-Description: File Transfer");
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"emails.csv\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($csv));
        echo $csv;
        die();
    }
}

function wpdm_save_email_template()
{
    if (isset($_POST['task']) && $_POST['task'] == 'save-etpl') {
        update_option('_wpdm_etpl', $_POST['et']);
        header("location: edit.php?post_type=wpdmpro&page=emails&task=template");
        die();
    }

}

function wpdm_emails()
{
    if (isset($_GET['task']) && $_GET['task'] == 'template')
        include("wpdm-emails-template.php");
    else
        include("wpdm-emails.php");
}


/**
 * Query wpdm packages
 *
 * @param mixed $args
 */
function wpdm_get_packages($args = array())
{
    global $wpdm_query, $wpdm_packages, $wp_query, $wpdb;
    extract($args);
    $order_field = isset($order_field) ? $order_field : 'create_date';
    $order = isset($order) ? $order : 'desc';
    $page = isset($page) ? $page : 1;
    $items_per_page = $items_per_page ? $items_per_page : 9;
    $start = ($page - 1) * $items_per_page;
    $wpdm_query['wpdm_category'] = $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] ? $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] : $args['wpdm_category'];
    if (!is_array($wpdm_query)) $wpdm_query = array();
    $wpdm_query = $wpdm_query + $args;
    $qry[] = 1;

    if ($wpdm_query['wpdm_category'] != '') $qry[] = "category like '%\"{$wpdm_query[wpdm_category]}\"%'";
    if ($wpdm_query['search'] != '') $qry[] = "title like '%{$wpdm_query[search]}%' or description like '%{$wpdm_query[search]}%'";
    $qry = implode(" and ", $qry);
    $wpdm_packages = $wpdb->get_results("select * from {$wpdb->prefix}ahm_files where $qry order by $order_field $order limit $start,$items_per_page", ARRAY_A);
    for ($index = 0; $index < count($wpdm_packages); $index++) {
        $wpdm_packages[$index]['files'] = unserialize($wpdm_packages[$index]['files']);
        $wpdm_packages[$index]['access'] = unserialize($wpdm_packages[$index]['access']);
        $wpdm_packages[$index]['category'] = unserialize($wpdm_packages[$index]['category']);
        $wpdm_packages[$index] = apply_filters('wpdm_data_init', $wpdm_packages[$index]);
        $wpdm_packages[$index] = wpdm_setup_package_data($wpdm_packages[$index]);

    }
    return $wpdm_packages;
}

function wpdm_count_packages($args = array())
{
    global $wpdm_query, $wpdm_packages, $wp_query, $wpdb;
    extract($args);
    $order_field = isset($order_field) ? $order_field : 'create_date';
    $order = isset($order) ? $order : 'desc';
    $start = isset($start) ? $start : 0;
    $items_per_page = $items_per_page ? $items_per_page : 9;
    $wpdm_query['wpdm_category'] = $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] ? $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] : $args['wpdm_category'];
    if (!is_array($wpdm_query)) $wpdm_query = array();
    $wpdm_query = $wpdm_query + $args;
    $qry[] = 1;

    if ($wpdm_query['wpdm_category'] != '') $qry[] = "category like '%\"{$wpdm_query[wpdm_category]}\"%'";
    if ($wpdm_query['search'] != '') $qry[] = "title like '%{$wpdm_query[search]}%' or description like '%{$wpdm_query[search]}%'";
    $qry = implode(" and ", $qry);
    return $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_files where $qry");
}


function wpdm_search_result($args = array())
{
    $total = wpdm_count_packages($args);
    $item_per_page = 10;
    $pages = ceil($total / $item_per_page);
    $page = $_GET['cp'] ? $_GET['cp'] : 1;
    $start = ($page - 1) * $item_per_page;
    $pag = new wpdm_pagination();
    $pag->changeClass('wpdm-ap-pag');
    $pag->items($total);
    $pag->limit($item_per_page);
    $pag->currentPage($page);
    $url = strpos($_SERVER['REQUEST_URI'], '?') ? $_SERVER['REQUEST_URI'] . '&' : $_SERVER['REQUEST_URI'] . '?';
    $url = preg_replace("/\&cp=[0-9]+/", "", $url);
    $pag->urlTemplate($url . "cp=[%PAGENO%]");
    $packages = wpdm_get_packages($args);

    foreach ($packages as $package) {
        if ($package['preview'] == '')
            $package['preview'] = "download-manager/preview/noimage.gif";
        $package['thumb'] = "<img class='wpdm-thumb' src='" . plugins_url() . '/download-manager/timthumb.php?w=' . get_option('_wpdm_thumb_w') . '&h=' . get_option('_wpdm_thumb_h') . '&zc=1&src=' . $package[preview] . "'/>";
        $package['thumb_page'] = "<img class='wpdm-thumb' src='" . plugins_url() . '/download-manager/timthumb.php?w=' . get_option('_wpdm_pthumb_w') . '&h=' . get_option('_wpdm_pthumb_h') . '&zc=1&src=' . $package[preview] . "'/>";
        $package['thumb_gallery'] = "<img class='wpdm-thumb' src='" . plugins_url() . '/download-manager/timthumb.php?w=' . get_option('_wpdm_athumb_w') . '&h=' . get_option('_wpdm_athumb_h') . '&zc=1&src=' . $package[preview] . "'/>";
        $package['thumb_widget'] = "<img class='wpdm-thumb' src='" . plugins_url() . '/download-manager/timthumb.php?w=' . get_option('_wpdm_wthumb_w') . '&h=' . get_option('_wpdm_wthumb_h') . '&zc=1&src=' . $package[preview] . "'/>";
        //if($package['icon']!='')
        //$package['icon'] = "<img class='wpdm_icon' align='left' src='".plugins_url()."/{$package['icon']}' />";            
        $package['download_url'] = wpdm_download_url($package);
        $package = apply_filters('wdm_pre_render_link', $package);
        echo FetchTemplate("link-template-search-result.php", $package, 'link');
    }
}


function wpdm_page_links($urltemplate, $total, $page = 1, $items_per_page = 10)
{
    if ($items_per_page <= 0) $items_per_page = 10;
    $page = $page ? $page : 1;
    $pages = ceil($total / $items_per_page);
    $start = ($page - 1) * $items_per_page;
    $pag = new wpdm_pagination();
    $pag->items($total);
    $pag->nextLabel(' <i class="icon icon-forward"></i> ');
    $pag->prevLabel(' <i class="icon icon-backward"></i> ');
    $pag->limit($items_per_page);
    $pag->urlTemplate($urltemplate);
    $pag->currentPage($page);
    return $pag->show();
}

function wpdm_get_preview_templates($type = 'link')
{
    if (!defined('WPDM_DEV_MODE') || WPDM_DEV_MODE == 0) return "";
    $ctpls = scandir(WPDM_BASE_DIR . '/templates/');
    array_shift($ctpls);
    array_shift($ctpls);
    $type = $type ? $type : 'link';
    $ptpls = $ctpls;

    ob_start();
    ?>
    <form action="" method="get" id="frm" class="alert alert-success">
        <strong>&nbsp;&nbsp;&nbsp;Dev Mode Enabled:</strong>
        <table style="margin: 0px;border:0px" cellpadding="5">
            <tr style="border: 0px">
                <td style="border: 0px">&nbsp;&nbsp;&nbsp;Select Template For Preview:</td>
                <td style="border: 0px">
                    <select name="wpdm_<?php echo $type; ?>_template" id="pge_tpl" onchange="jQuery('#frm').submit();">
                        <option value="<?php echo $type; ?>-template-default.php">Select</option>
                        <?php

                        $pattern = $type == 'link' ? "/WPDM[\s]+Link[\s]+Template[\s]*:([^\-\->]+)/" : "/WPDM[\s]+Template[\s]*:([^\-\->]+)/";
                        foreach ($ptpls as $ctpl) {
                            $tmpdata = file_get_contents(WPDM_BASE_DIR . '/templates/' . $ctpl);
                            if (preg_match($pattern, $tmpdata, $matches)) {

                                ?>
                                <option
                                    value="<?php echo $ctpl; ?>"  <?php echo $_GET['wpdm_' . $type . '_template'] == $ctpl ? 'selected=selected' : ''; ?>><?php echo $matches[1]; ?></option>
                            <?php
                            }
                        }

                        if ($templates = unserialize(get_option("_fm_{$type}_templates", true))) {
                            foreach ($templates as $id => $template) {
                                ?>
                                <option
                                    value="<?php echo $id; ?>"  <?php echo ($file['page_template'] == $id) ? ' selected=selected ' : ''; ?>><?php echo $template['title']; ?></option>
                            <?php
                            }
                        } ?>
                    </select>
                </td>
            </tr>
        </table>
    </form>
    <?php
    $data = ob_get_contents();
    ob_clean();
    return $data;
}

function wpdm_packages($params = array('items_per_page' => 10, 'title' => false, 'desc' => false, 'order_field' => 'date', 'order' => 'desc', 'paging' => false, 'toolbar' => 1, 'template' => '','cols'=>3, 'colspad'=>2, 'colsphone' => 1))
{
    extract($params);
    if(!isset($items_per_page)) $items_per_page = 10;
    if(!isset($template)) $template = 'link-template-panel';
    if(!isset($cols)) $cols = 3;
    if(!isset($colspad)) $colspad = 1;
    if(!isset($colsphone)) $colsphone = 1;
    if(!isset($toolbar)) $toolbar = 1;

    $cwd_class = "col-md-".(int)(12/$cols);
    $cwdsm_class = "col-sm-".(int)(12/$colspad);
    $cwdxs_class = "col-xs-".(int)(12/$colsphone);

    $id = trim($id, ", ");
    $cids = explode(",", $id);

    global $wpdb, $current_user, $post, $wp_query;

    $order_field = isset($order_field) ? $order_field : 'date';
    $order_field = isset($_GET['orderby']) ? $_GET['orderby'] : $order_field;
    $order = isset($order) ? $order : 'desc';
    $order = isset($_GET['order']) ? $_GET['order'] : $order;
    $cp = wpdm_query_var('cp','num');
    if(!$cp) $cp = 1;

    $params = array(
        'post_type' => 'wpdmpro',
        'paged' => $cp,
        'posts_per_page' => $items_per_page,
        'include_children' => false,
    );

    if (get_option('_wpdm_hide_all', 0) == 1) {
        $params['meta_query'] = array(
            array(
                'key' => '__wpdm_access',
                'value' => 'guest',
                'compare' => 'LIKE'
            )
        );
        if(is_user_logged_in()){
            global $current_user;
            $params['meta_query'][] = array(
                'key' => '__wpdm_access',
                'value' => $current_user->roles[0],
                'compare' => 'LIKE'
            );
            $params['meta_query']['relation'] = 'OR';
        }
    }

    $order_fields = array('__wpdm_download_count','__wpdm_view_count','__wpdm_package_size_b');
    if(!in_array( "__wpdm_".$order_field, $order_fields)) {
        $params['orderby'] = $order_field;
        $params['order'] = $order;
    } else {
        $params['orderby'] = 'meta_value_num';
        $params['meta_key'] = "__wpdm_".$order_field;
        $params['order'] = $order;
    }

    $params = apply_filters("wpdm_packages_query_params", $params);

    $packs = new WP_Query($params);

    $total = $packs->found_posts;

    $pages = ceil($total / $items_per_page);
    $page = isset($_GET['cp']) ? $_GET['cp'] : 1;
    $start = ($page - 1) * $items_per_page;

    if (!isset($paging) || intval($paging) == 1) {
        $pag = new wpdm_pagination();
        $pag->items($total);
        $pag->nextLabel(' &#9658; ');
        $pag->prevLabel(' &#9668; ');
        $pag->limit($items_per_page);
        $pag->currentPage($page);
    }

    $burl = get_permalink();
    $url = $_SERVER['REQUEST_URI']; //get_permalink();
    $url = strpos($url, '?') ? $url . '&' : $url . '?';
    $url = preg_replace("/[\&]*cp=[0-9]+[\&]*/", "", $url);
    $url = strpos($url, '?') ? $url . '&' : $url . '?';
    if (!isset($paging) || intval($paging) == 1)
        $pag->urlTemplate($url . "cp=[%PAGENO%]");


    $html = '';
    $templates = maybe_unserialize(get_option("_fm_link_templates", true));

    if(isset($templates[$template])) $template = $templates[$template]['content'];

    //if (file_exists(WPDM_BASE_DIR . '/templates/' . $category['link_template'])) $category['link_template'] = @file_get_contents(WPDM_BASE_DIR . '/templates/' . $category['link_template']);
    //else $category['link_template'] = $templates[$category['link_template']]['content'];
    //$link_temnplate = $category['link_template'] == '' ? $category['template_repeater'] : $category['link_template'];

    //if (isset($template) && $template) $link_temnplate = $template;
    //if (isset($template) && $templates[$template]['content'] != '') $link_temnplate = $templates[$template]['content'];

    //global $post;
    while($packs->have_posts()) { $packs->the_post();

        $pack = (array)$post;
        $repeater = "<div class='{$cwd_class} {$cwdsm_class} {$cwdxs_class}'>".FetchTemplate($template, $pack)."</div>";
        $html .=  $repeater;

    }
    wp_reset_query();

    $html = "<div class='row'>{$html}</div>";


    if (!isset($paging) || intval($paging) == 1)
        $pgn = "<div style='clear:both'></div>" . $pag->show() . "<div style='clear:both'></div>";
    else
        $pgn = "";
    global $post;

    $sap = get_option('permalink_structure') ? '?' : '&';
    $burl = $burl . $sap;
    if (isset($_GET['p']) && $_GET['p'] != '') $burl .= 'p=' . $_GET['p'] . '&';
    if (isset($_GET['src']) && $_GET['src'] != '') $burl .= 'src=' . $_GET['src'] . '&';
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'create_date';
    $order = ucfirst($order);

    $title = $title?"<h3>$title</h3>":"";

    return "<div class='w3eden'>" . $title . $desc  . $html  . $pgn . "<div style='clear:both'></div></div>";
}


function wpdm_embed_category($params = array('id' => '', 'items_per_page' => 10, 'title' => false, 'desc' => false, 'order_field' => 'create_date', 'order' => 'desc', 'paging' => false, 'toolbar' => 1, 'template' => '','cols'=>3, 'colspad'=>2, 'colsphone' => 1))
{
    extract($params);
    if(!isset($id)) return;
    if(!isset($items_per_page)) $items_per_page = 10;
    if(!isset($template)) $template = 'link-template-panel';
    if(!isset($cols)) $cols = 3;
    if(!isset($colspad)) $colspad = 2;
    if(!isset($colsphone)) $colsphone = 1;
    if(!isset($toolbar)) $toolbar = 1;
    $taxo = 'wpdmcategory';
    if(isset($tag) && $tag==1) $taxo = 'post_tag';
    $cwd_class = "col-md-".(int)(12/$cols);
    $cwdsm_class = "col-sm-".(int)(12/$colspad);
    $cwdxs_class = "col-xs-".(int)(12/$colsphone);

    $id = trim($id, ", ");
    $cids = explode(",", $id);

    global $wpdb, $current_user, $post, $wp_query;

    $order_field = isset($order_field) ? $order_field : 'publish_date';
    $order_field = isset($_GET['orderby']) ? $_GET['orderby'] : $order_field;
    $order = isset($order) ? $order : 'desc';
    $order = isset($_GET['order']) ? $_GET['order'] : $order;
    $cp = wpdm_query_var('cp','num');
    if(!$cp) $cp = 1;

    $params = array(
        'post_type' => 'wpdmpro',
        'paged' => $cp,
        'posts_per_page' => $items_per_page,
        'include_children' => false,
        'tax_query' => array(array(
            'taxonomy' => $taxo,
            'field' => 'slug',
            'terms' => $cids
        ))
    );

    if (get_option('_wpdm_hide_all', 0) == 1) {
        $params['meta_query'] = array(
            array(
            'key' => '__wpdm_access',
            'value' => 'guest',
            'compare' => 'LIKE'
            )
        );
        if(is_user_logged_in()){
            global $current_user;
            $params['meta_query'][] = array(
                'key' => '__wpdm_access',
                'value' => $current_user->roles[0],
                'compare' => 'LIKE'
            );
            $params['meta_query']['relation'] = 'OR';
        }
    }

    $params['orderby'] = $order_field;
    $params['order'] = $order;
    $params = apply_filters("wpdm_embed_category_query_params", $params);
    $packs = new WP_Query($params);

    $total = $packs->found_posts;

    $pages = ceil($total / $items_per_page);
    $page = isset($_GET['cp']) ? $_GET['cp'] : 1;
    $start = ($page - 1) * $items_per_page;

    if (!isset($paging) || $paging == 1) {
        $pag = new wpdm_pagination();
        $pag->items($total);
        $pag->nextLabel(' &#9658; ');
        $pag->prevLabel(' &#9668; ');
        $pag->limit($items_per_page);
        $pag->currentPage($page);
    }

    $burl = get_permalink();
    $url = $_SERVER['REQUEST_URI']; //get_permalink();
    $url = strpos($url, '?') ? $url . '&' : $url . '?';
    $url = preg_replace("/[\&]*cp=[0-9]+[\&]*/", "", $url);
    $url = strpos($url, '?') ? $url . '&' : $url . '?';
    if (!isset($paging) || $paging == 1)
        $pag->urlTemplate($url . "cp=[%PAGENO%]");


    $html = '';
    $templates = maybe_unserialize(get_option("_fm_link_templates", true));

    if(isset($templates[$template])) $template = $templates[$template]['content'];

    //if (file_exists(WPDM_BASE_DIR . '/templates/' . $category['link_template'])) $category['link_template'] = @file_get_contents(WPDM_BASE_DIR . '/templates/' . $category['link_template']);
    //else $category['link_template'] = $templates[$category['link_template']]['content'];
    //$link_temnplate = $category['link_template'] == '' ? $category['template_repeater'] : $category['link_template'];

    //if (isset($template) && $template) $link_temnplate = $template;
    //if (isset($template) && $templates[$template]['content'] != '') $link_temnplate = $templates[$template]['content'];

    global $post;
    while($packs->have_posts()) { $packs->the_post();

        $pack = (array)$post;
        $repeater = "<div class='{$cwd_class} {$cwdsm_class} {$cwdxs_class}'>".FetchTemplate($template, $pack)."</div>";
        $html .=  $repeater;

    }
    wp_reset_query();

    $html = "<div class='row'>{$html}</div>";
    $cname = array();
    foreach($cids as $cid){
        $cat = get_term_by('slug', $cid, $taxo);
        $cname[] = $cat->name;
    }
    $cats = implode(", ", $cname);
    //$category['title'] = stripcslashes($category['title']);
    //$category['content'] = stripcslashes($category['content']);
    $cimg = '';
    $desc = '';
    //if ($title == 1 && count($cids) == 1) $title = "<h3 style='margin:0px;font-size:11pt;line-height:normal'>$category[title]</h3>";
    //if (get_option('__wpdm_cat_img', 0) == 1) $cimg = "<img src='{$category[icon]}' />";
    //if ($desc == 1 && count($cids) == 1 || get_option('__wpdm_cat_desc', 0) == 1) $desc = wpautop($category['content']);


    $subcats = '';
    if (function_exists('wpdm_ap_categories') && $subcats == 1) {
        $schtml = wpdm_ap_categories(array('parent' => $id));
        if ($schtml != '') {
            $subcats = "<fieldset class='cat-page-tilte'><legend>" . __('Sub-Categories', 'wpdmpro') . "</legend>" . $schtml . "<div style='clear:both'></div></fieldset>" . "<fieldset class='cat-page-tilte'><legend>" . __('Downloads', 'wpdmpro') . "</legend>";
            $efs = '</fieldset>';
        }
    }

    if (!isset($paging) || $paging == 1)
        $pgn = "<div style='clear:both'></div>" . $pag->show() . "<div style='clear:both'></div>";
    else
        $pgn = "";
    global $post;

    $sap = get_option('permalink_structure') ? '?' : '&';
    $burl = $burl . $sap;
    if (isset($_GET['p']) && $_GET['p'] != '') $burl .= 'p=' . $_GET['p'] . '&';
    if (isset($_GET['src']) && $_GET['src'] != '') $burl .= 'src=' . $_GET['src'] . '&';
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'create_date';
    $order = ucfirst($order);
    $order_field = " " . __(ucwords(str_replace("_", " ", $order_field)),"wpdmpro");
    $ttitle = __('Title', 'wpdmpro');
    $tdls = __('Downloads', 'wpdmpro');
    $tcdate = __('Publish Date', 'wpdmpro');
    $tudate = __('Update Date', 'wpdmpro');
    $tasc = __('Asc', 'wpdmpro');
    $tdsc = __('Desc', 'wpdmpro');
    $tsrc = __('Search', 'wpdmpro');
    $order_by_label = __('Order By','wpdmpro');
    if ($toolbar || get_option('__wpdm_cat_tb') == 1)
        $toolbar = <<<TBR

                 <div class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">$cats</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

      <ul class="nav navbar-nav navbar-right">
       <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{$order_by_label} {$order_field} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                         <li><a href="{$burl}orderby=title&order=asc">{$ttitle}</a></li>
                         <!-- li><a href="{$burl}orderby=download_count&order=desc">{$tdls}</a></li -->
                         <li><a href="{$burl}orderby=publish_date&order=desc">{$tcdate}</a></li>
                        </ul>
                     </li>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">$order <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                         <li><a href="{$burl}orderby={$orderby}&order=asc">{$tasc}</a></li>
                         <li><a href="{$burl}orderby={$orderby}&order=desc">{$tdsc}</a></li>
                        </ul>
                     </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</div>
TBR;
    else
        $toolbar = '';
    return "<div class='w3eden'>" . $cimg . $desc . $toolbar . $subcats . $html  . $pgn . "<div style='clear:both'></div></div>";
}

function wpdm_package_file_list($file)
{
    global $current_user;
    $file['files'] = maybe_unserialize($file['files']);
    $fhtml = '';
    $idvdl = isset($file['individual_file_download']) ? $file['individual_file_download'] : 0;
    $pd = isset($file['publish_date'])&&$file['publish_date']!=""?strtotime($file['publish_date']):0;
    $xd = isset($file['expire_date'])&&$file['expire_date']!=""?strtotime($file['expire_date']):0;

    $cur = is_user_logged_in()?$current_user->roles:array('guest');

    if(($xd>0 && $xd<time()) || ($pd>0 && $pd>time()))  $idvdl = 0;

    if (count($file['files']) > 0) {
        $fileinfo = isset($file['fileinfo']) ? $file['fileinfo'] : array();
        $pwdlock = isset($file['password_lock']) ? $file['password_lock'] : 0;

        //Check if any other lock option apllied for this package
        $olock = wpdm_is_locked($file['ID']) ? 1 : 0;
        $swl = 0;
        if(!isset($file['quota'])||$file['quota']<=0) $file['quota'] = 9999999999999;
        if(is_user_logged_in()) $cur[] = 'guest';
        if(!isset($file['access']) || count($file['access'])==0 || !array_intersect($file['access'], $cur) || wpdm_is_download_limit_exceed($file['ID']) || $file['quota'] <= $file['download_count']) $olock = 1;
        $pwdcol = $dlcol = '';
        if ($pwdlock && $idvdl) $pwdcol = "<th>".__("Password","wpdmpro")."</th>";
        if ($idvdl && ($pwdlock || !$olock)) { $dlcol = "<th align=center>".__("Download","wpdmpro")."</th>"; $swl = 1; }
        $allfiles = is_array($file['files'])?$file['files']:array();
        $dir = isset($file['package_dir'])?$file['package_dir']:'';
        $dfiles = array();
        if($dir!=''){
            $dfiles = wpdm_get_files($dir);

        }

        //$allfiles = array_merge($allfiles, $dfiles);
        $fhtml = "<table class='wpdm-filelist table table-hover'><tr><th>".__("File","wpdmpro")."</th>{$pwdcol}{$dlcol}</tr>";
        if (is_array($allfiles)) {

            foreach ($allfiles as $ind => $sfile) {
                if (!@is_array($fileinfo[$sfile])) $fileinfo[$sfile] = array();
                if(!isset($fileinfo[$sfile]['password'])) $fileinfo[$sfile]['password'] = "";
                if ($swl) {
                    if ($fileinfo[$sfile]['password'] == '' && $pwdlock) $fileinfo[$sfile]['password'] = $file['password'];
                    $ttl = isset($fileinfo[$sfile]['title']) && $fileinfo[$sfile]['title']!="" ? $fileinfo[$sfile]['title'] : preg_replace("/([0-9]+)_/", "", basename($sfile));
                    $fhtml .= "<tr><td>{$ttl}</td>";
                    $fileinfo[$sfile]['password'] = $fileinfo[$sfile]['password'] == '' ? $file['password'] : $fileinfo[$sfile]['password'];
                    if ($fileinfo[$sfile]['password'] != '' && $pwdlock)
                        $fhtml .= "<td width='110' align=right><input  onkeypress='jQuery(this).removeClass(\"input-error\");' size=10 type='password' value='' id='pass_{$file['ID']}_{$ind}' placeholder='Password' name='pass' class='form-control input-sm inddlps' /></td>";
                    if ($fileinfo[$sfile]['password'] != '' && $pwdlock)
                        $fhtml .= "<td width=150><button class='inddl btn btn-primary btn-sm' file='{$sfile}' rel='" . wpdm_download_url($file) . "&ind=" . $ind . "' pass='#pass_{$file['ID']}_{$ind}'><i class='fa fa-download'></i>&nbsp;".__("Download","wpdmpro")."</button></td></tr>";
                    else
                        $fhtml .= "<td width=150 align=center><a class='btn btn-primary btn-xs' href='" . wpdm_download_url($file) . "&ind=" . $ind . "'><i style='opacity:0.5;margin-top:0px' class='fa fa-download'></i>&nbsp;".__("Download","wpdmpro")."</a></td></tr>";
                } else {
                    $ttl = !isset($fileinfo[$sfile]['title']) || $fileinfo[$sfile]['title']=='' ?  preg_replace("/([0-9]+)wpdm_/", "", basename($sfile)):$fileinfo[$sfile]['title'];
                    $fhtml .= "<tr><td>{$ttl}</td></tr>";
                }
            }

        }

        if (is_array($dfiles)) {

            foreach ($dfiles as $ind => $sfile) {
                if (!@is_array($fileinfo[$sfile])) $fileinfo[$sfile] = array();
                if(!isset($fileinfo[$sfile]['password'])) $fileinfo[$sfile]['password'] = "";
                if ($idvdl == 1 && ($pwdlock || !$olock)) {
                    if ($fileinfo[$sfile]['password'] == '' && $pwdlock) $fileinfo[$sfile]['password'] = $file['password'];
                    $ttl = isset($fileinfo[$sfile]['title']) && $fileinfo[$sfile]['title']!="" ? $fileinfo[$sfile]['title'] :  str_replace('/', " <i class='fa fa-angle-right text-primary'></i> ",str_replace($dir, "", $sfile));
                    $fhtml .= "<tr><td>{$ttl}</td>";
                    $fileinfo[$sfile]['password'] = $fileinfo[$sfile]['password'] == '' ? $file['password'] : $fileinfo[$sfile]['password'];
                    if ($fileinfo[$sfile]['password'] != '' && $pwdlock)
                        $fhtml .= "<td width='110' align=right><input  onkeypress='jQuery(this).removeClass(\"input-error\");' size=10 type='password' value='' id='pass_{$file['ID']}_{$ind}' placeholder='Password' name='pass' class='form-control input-sm inddlps' /></td>";
                    if ($fileinfo[$sfile]['password'] != '' && $pwdlock)
                        $fhtml .= "<td width=150><button class='inddl btn btn-primary btn-sm' file='{$sfile}' rel='" . wpdm_download_url($file) . "&ind=" . (count($allfiles)+$ind) . "' pass='#pass_{$file['ID']}_{$ind}'><i class='fa fa-download'></i>&nbsp;Download</button></td></tr>";
                    else
                        $fhtml .= "<td width=150 align=center><a class='btn btn-primary btn-xs' href='" . wpdm_download_url($file) . "&ind=" . $ind . "'><i style='opacity:0.5;margin-top:0px' class='fa fa-download-alt'></i>&nbsp;Download</a></td></tr>";
                } else {
                    $ttl = isset($fileinfo[$sfile]['title']) && $fileinfo[$sfile]['title']!="" ? $fileinfo[$sfile]['title'] :  str_replace('/', " <i class='fa fa-angle-right text-primary'></i> ",str_replace($dir, "", $sfile));
                    $fhtml .= "<tr><td>{$ttl}</td></tr>";
                }
            }

        }
            $fhtml .= "</table>";
            $siteurl = home_url('/');
            $fhtml .= "<script type='text/javascript' language='JavaScript'> jQuery('.inddl').click(function(){ var tis = this; jQuery.post('{$siteurl}',{wpdmfileid:'{$file['ID']}',wpdmfile:jQuery(this).attr('file'),actioninddlpvr:jQuery(jQuery(this).attr('pass')).val()},function(res){ res = res.split('|'); var ret = res[1]; if(ret=='error') jQuery(jQuery(tis).attr('pass')).addClass('input-error'); if(ret=='ok') location.href=jQuery(tis).attr('rel')+'&_wpdmkey='+res[2];});}); </script> ";


    }


    return $fhtml;

}

/**
 * @usage Generate thumbnail dynamically
 * @param $path
 * @param $size
 * @return mixed
 */

function wpdm_dynamic_thumb($path, $size)
{

    $abspath = str_replace("\\","/", ABSPATH);
    $cachedir = str_replace("\\","/", WPDM_CACHE_DIR);
    $path = str_replace("\\","/", $path);
    $path = str_replace(site_url('/'), $abspath, $path);

    if (!file_exists($path)) return;
    $name_p = explode(".", $path);
    $ext = "." . end($name_p);
    $filename = basename($path);
    $thumbpath = $cachedir .'/'. str_replace($ext, "-" . implode("x", $size) . $ext, $filename);
    if (file_exists($thumbpath)) {
        $thumbpath = str_replace($abspath, site_url('/'), $thumbpath);
        return $thumbpath;
    }
    $image = wp_get_image_editor($path);
    if (!is_wp_error($image)) {
        $image->resize($size[0], $size[1], true);
        $image->save($thumbpath);
    }
    $thumbpath = str_replace("\\","/", $thumbpath);
    $thumbpath = str_replace($abspath, site_url('/'), $thumbpath);
    return $thumbpath;
}


/**
 * @usage Return Post Thumbail
 * @param string $size
 * @param bool $echo
 * @param null $extra
 * @return mixed|string|void
 */
function wpdm_post_thumb($size='', $echo = true, $extra = null){
    global $post;
    $size = $size?$size:'thumbnail';
    $class = isset($extra['class'])?$extra['class']:'';
    $alt = $post->post_title;
    if(is_array($size))
    {
        $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full');
        $large_image_url = $large_image_url[0];
        if($large_image_url!=''){
            $path = str_replace(site_url('/'), ABSPATH, $large_image_url);
            $thumb = wpdm_dynamic_thumb($path, $size);
            $thumb = str_replace(ABSPATH, site_url('/'), $thumb);
            $alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true);
            $img = "<img src='".$thumb."' alt='{$alt}' class='{$class}' />";
            if($echo) { echo $img; return; }
            else
                return $img;
        }
    }
    if($echo&&has_post_thumbnail($post->ID ))
        echo get_the_post_thumbnail($post->ID, $size, $extra );
    else if(!$echo&&has_post_thumbnail($post->ID ))
        return get_the_post_thumbnail($post->ID, $size, $extra );
    else if($echo)
        echo "";
    else
        return "";
}

/**
 * @usage Generate Thumnail for the given package
 * @param $post
 * @param string $size
 * @param bool $echo
 * @param null $extra
 * @return mixed|string|void
 */
function wpdm_thumb($post, $size='', $echo = true, $extra = null){
    if(is_int($post))
    $post = get_post($post);
    $size = $size?$size:'thumbnail';
    $class = isset($extra['class'])?$extra['class']:'';
    $alt = $post->post_title;
    if(is_array($size))
    {
        $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full');
        $large_image_url = $large_image_url[0];
        if($large_image_url!=''){
            $path = str_replace(site_url('/'), ABSPATH, $large_image_url);
            $thumb = wpdm_dynamic_thumb($path, $size);
            $thumb = str_replace(ABSPATH, site_url('/'), $thumb);
            $alt = get_post_meta(get_post_thumbnail_id($post->ID), '_wp_attachment_image_alt', true);
            if($echo==='url') return $thumb;
            $img = "<img src='".$thumb."' alt='{$alt}' class='{$class}' />";
            if($echo) { echo $img; return; }
            else
                return $img;
        }
    }
    if($echo&&has_post_thumbnail($post->ID ))
        echo get_the_post_thumbnail($post->ID, $size, $extra );
    else if(!$echo&&has_post_thumbnail($post->ID ))
        return get_the_post_thumbnail($post->ID, $size, $extra );
    else if($echo)
        echo "";
    else
        return "";
}

/**
 * @usage Get All Custom Data of a Package
 * @param $pid
 * @return array
 */
function wpdm_custom_data($pid)
{
    $cdata = get_post_custom($pid);
    $data = array();
    if(is_array($cdata)){
    foreach ($cdata as $k => $v) {
        $k = str_replace("__wpdm_", "", $k);
        $data[$k] = maybe_unserialize($v[0]);
    }}
    //$defaults = array('email_lock'=>0,'password_lock'=>0,'tweet_lock'=>0,'facebooklike_lock'=>0,'linkedin_lock'=>0,'gplusone_lock'=>0,'individual_file_download'=>0);
    //$data = array_merge($defaults, $data);
    return apply_filters('wpdm_custom_data',$data);
}

function wpdm_setup_package_data($vars)
{
    if (isset($vars['formatted'])) return $vars;

    global $wp_query, $post;

    if (!isset($vars['ID'])) return $vars;

    $vars['title'] = stripcslashes($vars['post_title']);
    $vars['description'] = stripcslashes($vars['post_content']);
    $vars['description'] = wpautop(stripslashes($vars['description']));
    $vars['description'] = do_shortcode(stripslashes($vars['description']));
    $vars['excerpt'] = stripcslashes(strip_tags($vars['post_excerpt']));
    $src = wp_get_attachment_image_src(get_post_thumbnail_id($vars['ID']), 'full', false, '');
    $vars['preview'] = $src['0'];
    $vars['create_date'] = date_i18n(get_option('date_format'), strtotime($vars['post_date']));
    $vars['categories'] = get_the_term_list( $vars['ID'], 'wpdmcategory', '', ', ', '' );

    //print_r($vars); die();
    $data = wpdm_custom_data($vars['ID']);

    $vars = array_merge($vars, $data);

    $vars['update_date'] = date_i18n(get_option('date_format'), strtotime($vars['post_modified']));



    //$vars['description'] = apply_filters('the_content',stripslashes($wpdm_package['description']));
    $vars['files'] = get_post_meta($vars['ID'], '__wpdm_files', true);
    $vars['file_count'] = count($vars['files']);
    $vars['file_list'] = wpdm_package_file_list($vars);
    $vars['link_label'] = isset($vars['link_label']) ? $vars['link_label'] : __('Download', 'wpdmpro');
    $vars['page_link'] = "<a href='" . get_permalink($vars['ID']) . "'>{$vars['title']}</a>";
    $vars['page_url'] = get_permalink($vars['ID']);
    $tags = get_the_tags($vars['ID']);
    $taghtml = "";
    if(is_array($tags)){
    foreach ($tags as $tag)
    {
        $taghtml .= "<a class='btn btn-default btn-xs' style='margin:0 5px 5px 0' href=\""
        . get_tag_link($tag->term_id)
        . "\"><i class='fa fa-tag'></i> &nbsp; ".$tag->name."</a> &nbsp;";
    }}
    $vars['tags'] = $taghtml;


    if (count($vars['files']) > 1) $vars['file_ext'] = 'zip';
    if (is_array($vars['files']) && count($vars['files']) == 1) { $tmpdata = explode(".", $vars['files'][0]); $vars['file_ext'] = end($tmpdata); }
    $vars['file_size'] = wpdm_package_size($vars['ID']);

    //$vars['create_date'] = $vars['create_date']?@date(get_option('date_format'),$vars['create_date']):@date(get_option('date_format'),get_wpdm_meta($vars['ID'],'create_date'));
    //$vars['update_date'] = $vars['update_date']?@date(get_option('date_format'),$vars['update_date']):@date(get_option('date_format'),get_wpdm_meta($vars['ID'],'update_date'));

    $type = (get_post_type() != 'wpdmpro' || !array_key_exists(get_option('__wpdm_purl_base', 'download'), $wp_query->query_vars)) ? 'link' : 'page';
    $vars['audio_player'] = wpdm_audio_playlist($vars, true);
    $vars['audio_player_single'] = wpdm_audio_player($vars, true);
    //$vars['quick_download'] = wpdm_ddl_button($vars,$type=='link');
    //$vars['email_download'] = wpdm_email_button($vars,$type=='link');

    if (!isset($vars['icon']) || $vars['icon'] == '')
        $vars['icon'] = '<img class="wpdm_icon" src="' . plugins_url('download-manager/file-type-icons/') . (@count($vars['files']) <= 1 ? @end(@explode('.', @end($vars['files']))) : 'zip') . '.png" onError=\'this.src="' . plugins_url('download-manager/file-type-icons/_blank.png') . '";\' />';
    else if (!strpos($vars['icon'], '://'))
        $vars['icon'] = '<img class="wpdm_icon"   src="' . plugins_url($vars['icon']) . '" />';
    else if (!strpos($vars['icon'], ">"))
        $vars['icon'] = '<img class="wpdm_icon"   src="' . $vars['icon'] . '" />';

    if (isset($vars['preview']) && $vars['preview'] != '') {
        $vars['thumb'] = "<img title='' src='" . wpdm_dynamic_thumb($vars['preview'], array(400, 300)) . "'/>";
    } else
        $vars['thumb'] = $vars['thumb_page'] = $vars['thumb_gallery'] = $vars['thumb_widget'] = "";

    $k = 1;
    $vars['additional_previews'] = isset($vars['more_previews']) ? $vars['more_previews'] : array();
    $img = "<img id='more_previews_{$k}' title='' class='more_previews' src='" . wpdm_dynamic_thumb($vars['preview'], array(575, 170)) . "'/>\n";
    $tmb = "<a href='#more_previews_{$k}' class='spt'><img title='' src='" . wpdm_dynamic_thumb($vars['preview'], array(100, 45)) . "'/></a>\n";


    //WPMS fix
    global $blog_id;
    if (defined('MULTISITE')) {
        $vars['thumb'] = str_replace(home_url('/files'), ABSPATH . 'wp-content/blogs.dir/' . $blog_id . '/files', $vars['thumb']);
    }


    if (!isset($vars['download_link_called'])) {
        $tmpvar = DownloadLink($vars, 0, array('btnclass' => '[btnclass]'));
        $tmpvar1 = DownloadLink($vars, 1);
        $vars['download_link'] = $tmpvar;
        $vars['download_link_extended'] = $tmpvar1;
        $vars['download_link_called'] = 1;
    }
    $vars = apply_filters("wdm_before_fetch_template", $vars);
    if (!isset($vars['formatted'])) $vars['formatted'] = 0;
    ++$vars['formatted'];

    return $vars;
}


/**
 * @usage Check if a package is locked or public
 * @param $id
 * @return bool
 */
function wpdm_is_locked($id){
    $package = array();
    $package['ID'] = $id;
    $package = array_merge($package, wpdm_custom_data($package['ID']));
    $lock = '';

        if (isset($package['email_lock']) && $package['email_lock'] == 1) $lock = 'locked';
        if (isset($package['password_lock']) && $package['password_lock'] == 1) $lock = 'locked';
        if (isset($package['gplusone_lock']) && $package['gplusone_lock'] == 1) $lock = 'locked';
        if (isset($package['facebooklike_lock']) && $package['facebooklike_lock'] == 1) $lock = 'locked';

        if ($lock !== 'locked')
            $lock = apply_filters('wpdm_check_lock', $id, $lock);

    return ($lock=='locked');


}

function FetchTemplate($template, $vars, $type = 'link')
{
    if (!isset($vars['ID']) || intval($vars['ID']) <1 ) return '';


    $default['link'] = file_get_contents(dirname(__FILE__) . '/templates/link-template-default.php');
    $default['popup'] = file_get_contents(dirname(__FILE__) . '/templates/page-template-default.php');
    $default['page'] = file_get_contents(dirname(__FILE__) . '/templates/page-template-default.php');

    $vars = wpdm_setup_package_data($vars);

    if ($template == '') {
        $template = $type == 'page' ? $vars['page_template'] : $vars['template'];
    }

    if ($template == '')
        $template = $default[$type];


    if (file_exists(TEMPLATEPATH . '/' . $template)) $template = file_get_contents(TEMPLATEPATH . '/' . $template);
    else if (file_exists(dirname(__FILE__) . '/templates/' . $template)) $template = file_get_contents(dirname(__FILE__) . '/templates/' . $template);
    else if (file_exists(dirname(__FILE__) . '/templates/' . $template . '.php')) $template = file_get_contents(dirname(__FILE__) . '/templates/' . $template . '.php');
    else if (file_exists(dirname(__FILE__) . '/templates/'. $type . "-template-" . $template . '.php')) $template = file_get_contents(dirname(__FILE__) . '/templates/'. $type . "-template-" . $template . '.php');

    $templates = maybe_unserialize(get_option("_fm_link_templates", true));
    if(isset($templates[$template]) && isset($templates[$template]['content'])) $template = $templates[$template]['content'];

    preg_match_all("/\[cf ([^\]]+)\]/", $template, $cfmatches);
    preg_match_all("/\[thumb_([0-9]+)x([0-9]+)\]/", $template, $matches);
    preg_match_all("/\[thumb_url_([0-9]+)x([0-9]+)\]/", $template, $umatches);
    preg_match_all("/\[thumb_gallery_([0-9]+)x([0-9]+)\]/", $template, $gmatches);
    preg_match_all("/\[excerpt_([0-9]+)\]/", $template, $xmatches);
    preg_match_all("/\[pdf_thumb_([0-9]+)x([0-9]+)\]/", $template, $pmatches);
    //preg_match_all("/\[download_link ([^\]]+)\]/", $template, $cmatches);


    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($vars['ID']), 'full');
    $vars['preview'] = $thumb['0'];

    $pdf = isset($vars['files'][0])?$vars['files'][0]:'';
    $ext = explode(".", $pdf);
    $ext = end($ext);

    if($ext=='pdf')
        $vars['pdf_thumb'] = "<img src='".wpdm_pdf_thumbnail($pdf, $vars['ID'])."' />";
    else $vars['pdf_thumb'] = $vars['preview']!=''?"<img src='{$vars['preview']}' />":"";

    foreach ($pmatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $imsrc  = wpdm_dynamic_thumb(wpdm_pdf_thumbnail($pdf, $vars['ID']), array($pmatches[1][$nd], $pmatches[2][$nd]));
        $values[] = $imsrc != '' ? "<img src='" . $imsrc . "' alt='{$vars['title']}' />" : '';
    }


    $vars['wpdm_custom_field_table'] = wpdm_custom_fields_table($vars);

    foreach ($matches[0] as $nd => $scode) {
        $keys[] = $scode;
        $imsrc  = wpdm_dynamic_thumb($vars['preview'], array($matches[1][$nd], $matches[2][$nd]));
        $values[] = $vars['preview'] != '' ? "<img src='" . $imsrc . "' alt='{$vars['title']}' />" : '';
    }

    foreach ($umatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $values[] = $vars['preview'] != '' ? wpdm_dynamic_thumb($vars['preview'], array($umatches[1][$nd], $umatches[2][$nd])) : '';
    }

    foreach ($gmatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $values[] = wpdm_get_additional_preview_images($vars, $gmatches[1][$nd], $gmatches[2][$nd]);
    }
    /**
     * Depracated for premium package add-on
     * foreach ($cmatches[0] as $nd => $scode) {
     * $keys[] = $scode;
     * //die($vars['download_link'])              ;
     * $tmpvar = explode(".", $cmatches[1][$nd]);
     * if (in_array(end($tmpvar), array('png', 'jpg', 'jpeg', 'gif'))) {
     * $cmatches[1][$nd] = trim($cmatches[1][$nd]);
     * $vars['link_label'] = "<img class='wpdm-thumb' src='{$cmatches[1][$nd]}' alt='" . __('Download', 'wpdmpro') . "' />";
     * $values[] = DownloadLink($vars, 0);
     * } //preg_replace("/>.+?<\/a>/","/><img src='".$cmatches[1][$nd].'"/></a>', $vars['download_link']);
     * else
     * $values[] = str_replace('[btnclass]', $cmatches[1][$nd], $vars['download_link']);
     * }
     */
    foreach ($xmatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $ss = substr(strip_tags($vars['description']), 0, intval($xmatches[1][$nd]));
        $tmp = explode(" ", substr(strip_tags($vars['description']), intval($xmatches[1][$nd])));
        $bw = array_shift($tmp);
        $ss .= $bw;
        $values[] = $ss . '...';
    }

    if ($type == 'page' && (strpos($template, '[similar_downloads]') || strpos($vars['description'], '[similar_downloads]')))
        $vars['similar_downloads'] = wpdm_similar_packages($vars, 5);

    $vars['doc_preview'] = wpdm_doc_preview($vars);

    foreach ($vars as $key => $value) {
        $keys[] = "[$key]";
        $values[] = $value;
    }


    if ($vars['download_link'] == 'blocked' && $type == 'link') return "";
    if ($vars['download_link'] == 'blocked' && $type == 'page') return get_option('wpdm_permission_msg');
    if ($vars['download_link'] == 'loginform' && $type == 'link') return "";
    if ($vars['download_link'] == 'loginform' && $type == 'page') return wpdm_loginform();

    return @str_replace($keys, $values, @stripcslashes($template));
}

function wpdm_loginform(){

     if(isset($_SESSION['login_error'])&&$_SESSION['login_error']!='') {
        $err = preg_replace("/<a.*?<\/a>\?/i","",$_SESSION['login_error']);
        $_SESSION['login_error']='';
        $error =<<<ERR
         <div class="error alert alert-danger" >
            <b>Login Failed!</b><br/>
            $err
        </div>

ERR;
     } else $error = "";

    $here = $_SERVER['REQUEST_URI'];
    $rem =  __('Remember Me','wpdmpro');
    return <<<LOGINFORM


    <form name="loginform" id="loginform" action="" method="post" class="login-form w3eden">

<input type="hidden" name="permalink" value="$here" />
    <div class="panel panel-primary">
        <div class="panel-heading"><h3 style="margin: 0">Login</h3></div>
        <div class="panel-body">
            $error
            <p class="login-username">
                <label for="user_login"><?php _e('Username','wpdmpro'); ?></label>
                <input type="text" name="login[log]" id="user_login" class="form-control input required text" value="" size="20" tabindex="38" />
            </p>
            <p class="login-password">
                <label for="user_pass"><?php _e('Password','wpdmpro'); ?></label>
                <input type="password" name="login[pwd]" id="user_pass" class="form-control input required password" value="" size="20" tabindex="39" />
            </p>

            <p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="39" /> $rem</label></p>
            <p class="login-submit">
                <input type="submit" name="wp-submit" id="wp-submit" value="Log In" tabindex="40" class="btn btn-primary" />
                <input type="hidden" name="redirect_to" value="$here" />

            </p>
            </div>
            </div>
</form>
<h1 class="header-1 entry-title"><?php _e('Forgot Password?','wpdmpro'); ?></h1>
<div class="stripe"></div>
<a href="<?php echo site_url('/wp-login.php?action=lostpassword'); ?>"><?php _e('Request New Password.','wpdmpro'); ?></a>


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

LOGINFORM;

}

function wpdm_doc_preview($package){
        $files = $package['files'];

        if(!is_array($files)) return "";
        $ind = -1;
        foreach($files as $i=>$sfile){
            $sfile = explode(".", $sfile);
            if(in_array(end($sfile),array('pdf','doc','docs','xls','xlsx','ppt','pptx'))) { $ind = $i; break; }
        }
        if($ind==-1) return "";
        $url = wpdm_download_url($package, 'ind='.$ind);
        if(strpos($files[$ind], "://")) $url = $files[$ind];
       return '<iframe src="http://docs.google.com/viewer?url='.urlencode($url).'&embedded=true" width="100%" height="600" style="border: none;"></iframe>';
}

function wpdm_top_downloads($offset, $items, $template, $category = ''){
        $params = array(

            'post_type' => 'wpdmpro',
            'posts_per_oage' => $items,
            'orderby' => 'meta_value_num',
            'meta_key' => '__wpdm_download_count',
            'order' => 'desc'

        );


}


function wpdm_show_notice()
{
    global $wpdm_message;
    ?>
    <style type="text/css">
        .wpdm-message {
            -webkit-background-size: 40px 40px;
            -moz-background-size: 40px 40px;
            background-size: 40px 40px;
            background-image: -webkit-gradient(linear, left top, right bottom, color-stop(.25, rgba(255, 255, 255, .05)), color-stop(.25, transparent), color-stop(.5, transparent), color-stop(.5, rgba(255, 255, 255, .05)), color-stop(.75, rgba(255, 255, 255, .05)), color-stop(.75, transparent), to(transparent));
            background-image: -webkit-linear-gradient(135deg, rgba(255, 255, 255, .05) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .05) 50%, rgba(255, 255, 255, .05) 75%, transparent 75%, transparent);
            background-image: -moz-linear-gradient(135deg, rgba(255, 255, 255, .05) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .05) 50%, rgba(255, 255, 255, .05) 75%, transparent 75%, transparent);
            background-image: -ms-linear-gradient(135deg, rgba(255, 255, 255, .05) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .05) 50%, rgba(255, 255, 255, .05) 75%, transparent 75%, transparent);
            background-image: -o-linear-gradient(135deg, rgba(255, 255, 255, .05) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .05) 50%, rgba(255, 255, 255, .05) 75%, transparent 75%, transparent);
            background-image: linear-gradient(135deg, rgba(255, 255, 255, .05) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, .05) 50%, rgba(255, 255, 255, .05) 75%, transparent 75%, transparent);

            -moz-box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .4);
            -webkit-box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .4);
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .4);
            width: 100%;
            border: 1px solid;
            color: #fff;
            padding: 10px 20px;
            top: 0px;
            left: 0px;
            z-index: 999999;
            position: fixed;
            _position: absolute;
            text-shadow: 0 1px 0 rgba(0, 0, 0, .5);
            -webkit-animation: animate-bg 5s linear infinite;
            -moz-animation: animate-bg 5s linear infinite;
            font-family: 'Segoe UI', Verdana;
            font-size: 14pt;
        }

        .wpdm-error {
            background-color: #de4343;
            border-color: #c43d3d;
            text-align: center;

        }

        .wpdm-message b {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 0px 0;
            padding: 0px;
            font-family: 'Segoe UI', Verdana;

            margin-right: 20px;
        }

        .wpdm-message p {
            margin: 0;
        }

        @-webkit-keyframes animate-bg {
            from {
                background-position: 0 0;
            }
            to {
                background-position: -80px 0;
            }
        }

        @-moz-keyframes animate-bg {
            from {
                background-position: 0 0;
            }
            to {
                background-position: -80px 0;
            }
        }

    </style>
    <div class="wpdm-error wpdm-message">

        <p><b>Error!</b> <?php echo $wpdm_message;; ?></p>

    </div>
<?php
}

/***
 * Show notice
 *
 * @param mixed $msg
 */
function wpdm_notice($msg)
{
    global $wpdm_message;
    $wpdm_message = $msg;
    add_action('wp_footer', 'wpdm_show_notice');
}

/**
 * Process Download Request
 *
 */

function wpdm_downloadnow()
{

    global $wpdb, $current_user, $wp_query;
    get_currentuserinfo();
    if (!isset($wp_query->query_vars['wpdmdl']) && !isset($_GET['wpdmdl'])) return;
    $id = isset($_GET['wpdmdl']) ? (int)$_GET['wpdmdl'] : (int)$wp_query->query_vars['wpdmdl'];
    if ($id <= 0) return;
    $key = array_key_exists('_wpdmkey', $_GET) ? $_GET['_wpdmkey'] : '';
    $key = $key == '' && array_key_exists('_wpdmkey', $wp_query->query_vars) ? $wp_query->query_vars['_wpdmkey'] : $key;
    $key = preg_replace("/[^_a-z|A-Z|0-9]/i", "", $key);
    $key = "__wpdmkey_".$key;
    $package = get_post($id, ARRAY_A);
    $package['ID'] = $package['ID'];
    $package = array_merge($package, wpdm_custom_data($package['ID']));
    if (isset($package['files']))
        $package['files'] = maybe_unserialize($package['files']);
    else
        $package['files'] = array();
    //$package = wpdm_setup_package_data($package);

    if (is_array($package)) {
        $role = @array_shift(@array_keys($current_user->caps));
        $cpackage = apply_filters('before_download', $package);
        $lock = '';
        $package = $cpackage ? $cpackage : $package;
        if (isset($package['email_lock']) && $package['email_lock'] == 1) $lock = 'locked';
        if (isset($package['password_lock']) && $package['password_lock'] == 1) $lock = 'locked';
        if (isset($package['gplusone_lock']) && $package['gplusone_lock'] == 1) $lock = 'locked';
        if (isset($package['facebooklike_lock']) && $package['facebooklike_lock'] == 1) $lock = 'locked';
        if (isset($package['tweet_lock']) && $package['tweet_lock'] == 1) $lock = 'locked';

        if ($lock !== 'locked')
            $lock = apply_filters('wpdm_check_lock', $id, $lock);

        if (isset($_GET['masterkey']) && esc_attr($_GET['masterkey']) == $package['masterkey']) {
            $lock = 0;
        }


        $limit = $key ? (int)trim(get_post_meta($package['ID'], $key, true)) : 0;


        if ($limit <= 0 && $key != '') delete_post_meta($package['ID'], $key);
        else if ($key != '')
            update_post_meta($package['ID'], $key, $limit - 1);

        $matched = (is_array(@maybe_unserialize($package['access'])) && is_user_logged_in())?array_intersect($current_user->roles, @maybe_unserialize($package['access'])):array();

        if (($id != '' && is_user_logged_in() && count($matched) < 1 && !@in_array('guest', $package['access'])) || (!is_user_logged_in() && !@in_array('guest', $package['access']) && $id != '')) {
            wpdm_download_data("permission-denied.txt", __("You don't have permission to download this file", 'wpdmpro'));
            die();
        } else {

            if ($lock === 'locked' && $limit <= 0) {
                if ($key != '')
                    wpdm_download_data("link-expired.txt", __("Download link is expired. Please get new download link ( $key ". print_r(get_post_meta($id),1) ." ).", 'wpdmpro'));
                else
                    wpdm_download_data("invalid-link.txt", __("Download link is expired or not valid. Please get new download link.", 'wpdmpro'));
                die();
            } else
                if ($package['ID'] > 0)
                    include("process.php");

        }
    } else
        wpdm_notice(__("Invalid download link.", 'wpdmpro'));
}

/*function wpdm_mail_download_link($id){
        global $wpdb, $current_user;        
        get_currentuserinfo();
        $id = (int)$_GET[file];    
        $package = $wpdb->get_row("select * from {$wpdb->prefix}ahm_files where id='$id'",ARRAY_A);
        $dkey = is_array($package['files'])?md5(serialize($package['files'])):md5($package['files']);
        $download_url = home_url("/?file={$package[id]}&downloadkey=".$dkey);
        file_get_contents();
        
    } */


function wpdm_is_ajax()
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        return true;
    return false;
}


/**
 * @usage Create audio player with playlist
 * @param $package
 * @param bool $return
 * @return mixed|string|void
 */

function wpdm_audio_playlist($package, $return  = false )
{


    if(!is_array($package) && is_numeric($package)) {
        $packageid = $package;
        $package = array();
        $package['ID'] = $packageid;
        $package['files'] = maybe_unserialize(get_post_meta($packageid, '__wpdm_files', true));
        $package['fileinfo'] = maybe_unserialize(get_post_meta($packageid, '__wpdm_fileinfo', true));

    }

    if(wpdm_is_locked($package['ID'])) return wpdm_sample_audio_playlist($package, $return);

    if (!is_array($package['files']) || count($package['files']) == 0) return;
    $audios = array();
    $nonce = wp_create_nonce($_SERVER['REQUEST_URI']);

    foreach($package['files'] as $index => $file){
        $realpath = file_exists($file)?$file:UPLOAD_DIR.$file;
        $filetype = wp_check_filetype( $realpath );
        $tmpvar = explode('/',$filetype['type']);
        if($tmpvar[0]=='audio')
        $audios[$index] =  $file;
    }

    $audiohtml = "";

    if(count($audios)>0){
        $audiohtml = "
        <script src='".WPDM_BASE_URL."/js/audio.js'></script>
        <script>

          jQuery(function($) {

            var a = audiojs.createAll({
              trackEnded: function() {
                var next = $('ol#playlist-{$package['ID']} li.playing').next();
                if (!next.length) next = $('ol li').first();
                next.addClass('playing').siblings().removeClass('playing');
                audio.load($('a', next).attr('data-src'));
                audio.play();
              }
            });


            var audio = a[0];
                first = $('ol#playlist-{$package['ID']} a').attr('data-src');
            $('ol#playlist-{$package['ID']} li').first().addClass('playing');
            audio.load(first);


            $('ol#playlist-{$package['ID']} li').click(function(e) {
              e.preventDefault();
              $(this).addClass('playing').siblings().removeClass('playing');
              audio.load($('a', this).attr('data-src'));
              audio.play();
            });

            $(document).keydown(function(e) {
              var unicode = e.charCode ? e.charCode : e.keyCode;

              if (unicode == 39) {
                var next = $('li.playing').next();
                if (!next.length) next = $('ol li').first();
                next.click();

              } else if (unicode == 37) {
                var prev = $('li.playing').prev();
                if (!prev.length) prev = $('ol li').last();
                prev.click();

              } else if (unicode == 32) {
                audio.playPause();
              }
            })
          });
        </script>

        <audio preload></audio>";
        $audiohtml .= "<ol class='list-group' id='playlist-{$package['ID']}' style='margin: 10px 0'>";
        foreach($audios as $index => $audio){
            $song = home_url("/?wpdmdl={$package['ID']}&_nonce={$nonce}&ind={$index}&play=".basename($audio));

            if(strpos($audio, "ttp://")) $song = $audio;
            $title = isset($package['fileinfo'][$audio]['title']) && $package['fileinfo'][$audio]['title']!=''?$package['fileinfo'][$audio]['title']:basename($audio);
            $audiohtml .= "<li class='list-group-item'><a href='#' data-src='{$song}'>{$title}</a></li>";
        }
        $audiohtml .= "</ol>";
    }

    // the filter could be useful in case you want to change the player and style
    $audiohtml = apply_filters("wpdm_audio_playlist", $audiohtml, $package);

    if($return)
    return $audiohtml;

    echo  $audiohtml;

}

/**
 * @usage Generate play button for link template
 * @param $package
 * @param bool $return
 * @return mixed|string|void
 */
function wpdm_audio_player($package, $return  = true )
{

    $audiohtml = "";

    if(wpdm_is_locked($package['ID'])) return wpdm_sample_audio_single($package, $return);

    if (!is_array($package['files']) || count($package['files']) == 0) return;
    $audios = array();
    $nonce = wp_create_nonce($_SERVER['REQUEST_URI']);

    foreach($package['files'] as $index => $file){
        $realpath = file_exists($file)?$file:UPLOAD_DIR.$file;
        $filetype = wp_check_filetype( $realpath );
        $tmpvar = explode('/',$filetype['type']);
        if($tmpvar[0]=='audio')
        $audios[$index] =  $file;
    }

    if(count($audios)>0){
        $song = home_url("/?wpdmdl={$package['ID']}&ind=0&play=".basename(array_shift($audios)));
        $audiohtml = do_shortcode("[audio {$song}]");
    }

    if($return)
    return $audiohtml;

    echo  $audiohtml;

}


/**
 * @usage Create sample audio player with playlist
 * @param $package
 * @param bool $return
 * @return mixed|string|void
 */

function wpdm_sample_audio_playlist($package, $return  = false )
{

    $audios = get_attached_media( 'audio', $package['ID'] );

    $audiohtml = '';

    if(count($audios)>0){
        $audiohtml = "
        <script src='".WPDM_BASE_URL."/js/audio.js'></script>
        <script>

          jQuery(function($) {

            var a = audiojs.createAll({
              trackEnded: function() {
                var next = $('ol#playlist-{$package['ID']} li.playing').next();
                if (!next.length) next = $('ol li').first();
                next.addClass('playing').siblings().removeClass('playing');
                audio.load($('a', next).attr('data-src'));
                audio.play();
              }
            });


            var audio = a[0];
                first = $('ol a').attr('data-src');
            $('ol li').first().addClass('playing');
            audio.load(first);


            $('ol li').click(function(e) {
              e.preventDefault();
              $(this).addClass('playing').siblings().removeClass('playing');
              audio.load($('a', this).attr('data-src'));
              audio.play();
            });

            $(document).keydown(function(e) {
              var unicode = e.charCode ? e.charCode : e.keyCode;

              if (unicode == 39) {
                var next = $('li.playing').next();
                if (!next.length) next = $('ol li').first();
                next.click();

              } else if (unicode == 37) {
                var prev = $('li.playing').prev();
                if (!prev.length) prev = $('ol li').last();
                prev.click();

              } else if (unicode == 32) {
                audio.playPause();
              }
            })
          });
        </script>

        <audio preload></audio>";
        $audiohtml .= "<ol class='list-group' id='playlist-{$package['ID']}' style='margin: 10px 0'>";
        foreach($audios as $audio){
            $audiohtml .= "<li class='list-group-item'><a href='#' data-src='{$audio->guid}'>{$audio->post_title}</a></li>";
        }
        $audiohtml .= "</ol>";
    }

    // the filter could be useful in case you want to change the player and style
    $audiohtml = apply_filters("wpdm_audio_playlist", $audiohtml, $package);

    if($return)
    return $audiohtml;

    echo  $audiohtml;

}

/**
 * @usage Create sample audio player for link template
 * @param $package
 * @param bool $return
 * @return mixed|string|void
 */
function wpdm_sample_audio_single($package, $return  = false )
{


    $audios = get_attached_media( 'audio', $package['ID'] );
    $audiohtml = "";


    if(count($audios)>0){
        $audio = array_shift($audios);
        $audiohtml = do_shortcode("[audio src='{$audio->guid}']");
    }



    if($return)
    return $audiohtml;

    echo  $audiohtml;

}



/**
 * Get template list options
 *
 * @param mixed $type
 * @param mixed $tpl
 */
function wpdm_get_templates($type, $tpl = '')
{
    ?>
    <option value="">Select</option>
    <?php
    if ($templates = unserialize(get_option("_fm_{$type}_templates", true))) {
        foreach ($templates as $id => $template) {
            ?>
            <option
                value="<?php echo $id; ?>"  <?php echo ($tpl == $id) ? ' selected=selected ' : ''; ?>><?php echo $template['title']; ?></option>
        <?php
        }
    }
}

function wpdm_get_page_templates()
{
    wpdm_get_templates('page', $_REQUEST['tpl']);
    die();
}

function wpdm_get_link_templates()
{
    wpdm_get_templates('link', $_REQUEST['tpl']);
    die();
}


function __msg($key)
{
    include("messages.php");
    return $msgs[$key] ? $msgs[$key] : $key;
}


/**
 * @usage Delete package from front-end
 */
function delete_package_frontend()
{
    global $wpdb, $current_user;
    if (isset($_GET['ID']) && intval($_GET['ID'])>0) {
        $id = (int)$_GET['ID'];
        $uid = $current_user->ID;
        if ($uid == '') die('Error! You are not logged in.');
        $post = get_post($id);
        if($post->post_author==$uid)
        wp_delete_post($id, true);
        echo "deleted";
        die();
    }
}

/**
 * @usage Short-code [wpdm_all_packages] to list all packages in tabular format
 * @param array $params
 * @return string
 */
function wpdm_all_packages($params = array())
{
    global $wpdb, $current_user, $wp_query;
    $items = isset($params['items_per_page']) && $params['items_per_page'] > 0 ? $params['items_per_page'] : 20;
    if(isset($params['jstable']) && $params['jstable']==1) $items = 2000;
    $cp = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] : 1;
    $terms = isset($params['categories']) ? explode(",", $params['categories']) : array();
    if (isset($_GET['wpdmc'])) $terms = array(esc_attr($_GET['wpdmc']));
    $offset = ($cp - 1) * $items;
    $total_files = wp_count_posts('wpdmpro')->publish;
    if (count($terms) > 0) {
        $tax_query = array(array(
            'taxonomy' => 'wpdmcategory',
            'field' => 'slug',
            'terms' => $terms,
            'operator' => 'IN',
            'include_children' => false
        ));
    }

    ob_start();
    include("wpdm-all-downloads.php");
    $data = ob_get_clean();
    return $data;
}


/**
 * @usage Check if logged in user is authorized admin
 * @return bool
 */
function wpdm_is_custom_admin()
{
    global $current_user, $add_new_page;
    $admins = explode(",", get_option('__wpdm_custom_admin', ''));
    return in_array($current_user->user_login, $admins) ? true : false;
}


function wpdm_add_help_tab()
{
    global $add_new_page;
    $screen = get_current_screen();
    $tmpvar = explode('/', $_GET['page']);
    $page = array_shift($tmpvar);
    if ($page != 'file-manager') return;
    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab(array(
        'ID' => 'my_help_tab_0',
        'title' => __('Legends'),
        'content' => '<p>' . "<img align=left src='" . plugins_url('/download-manager/images/add-image.gif') . "' hspace=10 />" . __(" Click on the icon to launch media manager to select or upload preview images") . "<br/><img align=left src='" . plugins_url('/download-manager/images/reload.png') . "' hspace=10 />" . __(" Reload link or page templates.") . '</p>',
    ));

    $screen->add_help_tab(array(
        'ID' => 'my_help_tab_1',
        'title' => __('Package Settings','wpdmpro'),
        'content' => '<p>' . __("<b>Link Label:</b> Label to show with download link, like: download now, get it now<br/>
                                    <b>Password:</b> You can set single or multiple password for a package. In case of multiple password, each password have to be inside `[]`, like: [1234][456][789sf] and user will be able to download package using any one of them<br/>
                                    <b>PW Usage Limit:</b> When you are using multiple password, then you may want set a limit, how many time user will be able to use a password, you can set the numeric value here, suppose `n`. So each password will expire after it used for `n` times.<br/>
                                    <b>Stock Limit:</b> Should be a numeric value, suppose `9`. After package dowloaded for `9` times, the no one will able to download it anymore, will show 'out of stock' message<br/>
                                    <b>Download Limit/user:</b> Set a numeric value here if you want to block user after a certain times of download for this package.<br/>
                                    <b>Access</b>: Check the user roles, you want to enable to download this package, `All Visitors` will enable every one to download this package<br/>
                                    <b>Link Template:</b> Shortcode will be rendered based on select link template.<br/>
                                    <b>Page Template:</b> Package details page will be rendered based on selected page template<br/>
                                ",'wpdmpro') . '</p>',
    ));

}

/**
 * @usage Get Package Data By Package ID
 * @param $id
 * @return bool|mixed|null|void|WP_Post
 */
function wpdm_get_package($id)
{
    global $wpdb, $wpdm_package;
    $id = (int)$id;
    if ($id <= 0) return false;
    if ($id == $wpdm_package['ID']) return $wpdm_package;
    $data = get_post($id, ARRAY_A);
    $data = apply_filters('wpdm_data_init', $data);
    $data = wpdm_setup_package_data($data);
    return $data;
}

/**
 * @usage Get download manager package data
 * @param $ID
 * @param $meta
 * @return mixed
 */
function get_package_data($ID, $key){
    $data = get_post_meta($ID, "__wpdm_{$key}", true);
    return $data;
}


/**
 * @usage Validate individual file password
 */
function wpdm_check_invpass()
{
    if (isset($_POST['actioninddlpvr']) && $_POST['actioninddlpvr'] != '') {
        $fileid = intval($_POST['wpdmfileid']);
        $data = get_post_meta($_POST['wpdmfileid'], '__wpdm_fileinfo', true);
        $data = $data ? $data : array();
        $package = get_post($fileid);
        $packagemeta = wpdm_custom_data($fileid);
        $password = $data[$_POST['wpdmfile']]['password'] != "" ? $data[$_POST['wpdmfile']]['password'] : $packagemeta['password'];
        if ($password == $_POST['actioninddlpvr'] || strpos($password, "[" . $_POST['actioninddlpvr'] . "]") !== FALSE) {
            $id = "__wpu_" . uniqid();
            update_post_meta($fileid, "__wpdmkey_".$id, 3);
            die("|ok|$id|");
        } else
            die('|error|');
    }
}

/**
 * @usage Password generator
 */
function wpdm_generate_password()
{
    include('wpdm-generate-password.php');
    die();

}

/**
 * @usage Special Sort-code: Email to Download
 * @param $params
 * @return mixed|string
 */
function wpdm_email_2download($params)
{
    $package = wpdm_get_package($params['download']);
    if (isset($params['title'])) $package['email_heading'] = $params['title'];
    if (isset($params['msg'])) $package['email_intro'] = $params['msg'];
    $scolor = (isset($params['scolor'])) ? $params['scolor'] : 'default';
    $html = wpdm_email_lock_form($package);
    $class = isset($params['style']) ? $params['style'] : ""; //wpdm-email2dl  drop-shadow lifted
    $html = str_replace("panel-default", $class . " panel-" . $scolor, $html);
    //$html = "<div class='w3eden wpdm-email2dl  drop-shadow lifted'><div class='wcon'><strong>$params[title]</strong><br/>{$params[msg]}<br clear='all' />$html</div></div>";
    return $html;
}

/**
 * @usage Special Sort-code: Google+1 to Download
 * @param $params
 * @return mixed|string
 */
function wpdm_plus1_2download($params)
{
    $package = wpdm_get_package($params['download']);
    if (isset($params['title'])) $package['gplus_heading'] = $params['title'];
    if (isset($params['msg'])) $package['gplus_intro'] = $params['msg'];
    $scolor = (isset($params['scolor'])) ? $params['scolor'] : 'default';
    $html = wpdm_plus1st_google_plus_one($package);
    $class = isset($params['style']) ? $params['style'] : ""; //wpdm-email2dl  drop-shadow lifted
    $html = str_replace("panel-default", $class . " panel-" . $scolor, $html);
    //$html = "<div class='wpdm-email2dl  drop-shadow lifted'><div class='wcon'><strong>$params[title]</strong><br/>{$params[msg]}<br clear='all' /><center>$html</center></div></div>";
    return $html;
}

/**
 * @usage Special Sort-code: Facebook Like to Download
 * @param $params
 * @return mixed|string
 */
function wpdm_like_2download($params)
{
    $package = wpdm_get_package($params['download']);
    if (isset($params['title'])) $package['facebook_heading'] = $params['title'];
    if (isset($params['msg'])) $package['facebook_intro'] = $params['msg'];
    $scolor = (isset($params['scolor'])) ? $params['scolor'] : 'default';
    $html = wpdm_facebook_like_button($package);
    $class = isset($params['style']) ? $params['style'] : ""; //wpdm-email2dl  drop-shadow lifted
    $html = str_replace("panel-default", $class . " panel-" . $scolor, $html);
    //$html = "<div class='wpdm-email2dl  drop-shadow lifted'><div class='wcon'><strong>$params[title]</strong><br/>{$params[msg]}<br clear='all' />$html</div></div>";
    return $html;
}

/**
 * @usage Special Sort-code: Tweet to Download
 * @param $params
 * @return mixed|string
 */
function wpdm_tweet_2download($params)
{
    $package = wpdm_get_package($params['download']);
    if (isset($params['title'])) $package['tweet_heading'] = $params['title'];
    if (isset($params['msg'])) $package['tweet_intro'] = $params['msg'];
    $scolor = (isset($params['scolor'])) ? $params['scolor'] : 'default';
    $html = wpdm_tweet_button($package);
    $class = isset($params['style']) ? $params['style'] : ""; //wpdm-email2dl  drop-shadow lifted
    $html = str_replace("panel-default", $class . " panel-" . $scolor, $html);
    //$html = "<div class='wpdm-email2dl  drop-shadow lifted'><div class='wcon'><strong>$params[title]</strong><br/>{$params[msg]}<br clear='all' /><center>$html</center></div></div>";
    return $html;
}

/**
 * @usage Special Sort-code: Share in Linkedin to Downlaod
 * @param $params
 * @return mixed|string
 */
function wpdm_lishare_2download($params)
{
    $package = wpdm_get_package($params['download']);
    if (isset($params['title'])) $package['linkedin_heading'] = $params['title'];
    if (isset($params['msg'])) $package['linkedin_intro'] = $params['msg'];
    $scolor = (isset($params['scolor'])) ? $params['scolor'] : 'default';
    $html = wpdm_linkedin_share($package);
    $class = isset($params['style']) ? $params['style'] : ""; //wpdm-email2dl  drop-shadow lifted
    $html = str_replace("panel-default", $class . " panel-" . $scolor, $html);
    //$html = "<div class='wpdm-email2dl  drop-shadow lifted'><div class='wcon'><strong>$params[title]</strong><br/>{$params[msg]}<br clear='all' /><center>$html</center></div></div>";
    return $html;
}


//add custom fields with csv file
function wpdm_export_custom_form_fields($custom_fields)
{
    $custom_fields[] = 'name';
    return $custom_fields;
}

//add cuistom fields option html to show in admin
function wpdm_ask_for_custom_data($pid)
{
    $cff = get_post_meta($pid, '__wpdm_custom_form_field', true);
    $idl = get_post_meta($pid, '__wpdm_email_lock_idl', true);
    if (!$cff) $cff = array();
    ?>
    <table>
        <tr>
            <td>
               <label><input type="checkbox" name="file[custom_form_field][name]" value="1" <?php if (isset($cff['name']) && $cff['name'] == 1) echo 'checked=checked'; ?> > <?php _e("Ask for Visitor's Name","wpdmpro");?></label> <br/>

                <hr size="1" noshade="noshade"/>
                <?php echo __('After submit form:','wpdmpro'); ?>
                <label><input type="radio" id="idl" name="file[email_lock_idl]"
                              value="0" <?php if ($idl != 1) echo 'checked=checked'; ?>> <?php echo __('Mail Download Link','wpdmpro'); ?></label>
                <label><input type="radio" id="idl" name="file[email_lock_idl]"
                              value="1" <?php if ($idl == 1) echo 'checked=checked'; ?> > <?php echo __('Downlaod Instantly','wpdmpro'); ?></label>
                <br/>
            </td>
        </tr>
    </table>

<?php
}

//add cuistom fields html to show at front end with email form
function wpdm_render_custom_data($pid)
{
    if (!$pid) return;
    $cff = get_post_meta($pid, '__wpdm_custom_form_field', true);
    $labels['name'] = __('Your Name',"wpdmpro");
    if (!$cff) return;
    $html = "";
    foreach ($cff as $name => $value) {
        $html .= <<<DATA
    <label><nobr>{$labels[$name]}:</nobr></label><input placeholder="Enter {$labels[$name]}" type="text" name="custom_form_field[$name]" class="form-control" />
DATA;
    }
    return $html;
}

// Function that output's the contents of the dashboard widget
function wpdm_dashboard_widget_function()
{
    global $wpdb;
    echo "<img height='30px' src='" . plugins_url('/download-manager/images/wpdm-logo.png') . "' /><br/>";
    ?>
    <link href="<?php echo plugins_url('/download-manager/bootstrap/css/bootstrap.css'); ?>" rel='stylesheet'
          type='text/css'>
    <script language="JavaScript"
            src="<?php echo plugins_url('/download-manager/bootstrap/js/bootstrap.min.js'); ?>"></script>

    <style type="text/css">
        .nav-tabs {
            margin-bottom: 0px !important;
        }

        .tab-content {
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-top: 0px;
            -webkit-border-bottom-right-radius: 5px;
            -webkit-border-bottom-left-radius: 5px;
            -moz-border-radius-bottomright: 5px;
            -moz-border-radius-bottomleft: 5px;
            border-bottom-right-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        .tab-content * {
            font-family: 'Open Sans';
            font-size: 10pt;
            font-weight: 400;
        }

        .nav-tabs a {
            font-size: 9pt;
            font-weight: 700;
        }

        .tab-content * {
            font-size: 10pt;
            font-weight: 400;
        }

    </style>


    <div class="w3eden">
        <ul class="nav nav-tabs" id="myTab">
            <li class="active"><a href="#home"><?php _e('Summary','wpdmpro'); ?></a></li>
            <li><a href="#social"><?php _e('Social','wpdmpro'); ?></a></li>
            <li><a href="#messages"><?php _e('Messages','wpdmpro'); ?></a></li>
            <li><a href="#settings"><?php _e('News Updates','wpdmpro'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="home">
                <table class="table table-bordered table-striped" style="margin-bottom: 0px;width:100%">
                    <tr>
                        <td><?php _e('Total Packages','wpdmpro'); ?></td>
                        <th><?php $packs = wp_count_posts('wpdmpro'); echo $packs->publish; ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Total Downloads','wpdmpro'); ?></td>
                        <th><?php echo $wpdb->get_var("select sum(meta_value) from {$wpdb->prefix}postmeta where meta_key='__wpdm_download_count'"); ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Total Categories','wpdmpro'); ?></td>
                        <th><?php echo wp_count_terms('wpdmcategory'); ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Total Subscribers','wpdmpro'); ?></td>
                        <th><?php echo count($wpdb->get_results("select count(email) from {$wpdb->prefix}ahm_emails group by email")); ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Subscribed Today','wpdmpro'); ?></td>
                        <th><?php $s = strtotime(date("Y-m-d 0:0:0"));
                            $e = time();
                            echo count($wpdb->get_results("select count(email) from {$wpdb->prefix}ahm_emails where date > $s and date < $e group by email")); ?></th>
                    </tr>
                </table>
            </div>
            <div class="tab-pane" id="social">
                <table class="table table-bordered table-striped" style="margin-bottom: 0px;width:100%">
                    <tr>
                        <td><?php _e('Total FB Likes','wpdmpro'); ?></td>
                        <th><?php echo get_option('wpdm_fb_likes', 0); ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Total Tweets','wpdmpro'); ?></td>
                        <th><?php echo get_option('wpdm_tweets', 0); ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Total Google +1','wpdmpro'); ?></td>
                        <th><?php echo get_option('wpdm_gplus1s', 0); ?></th>
                    </tr>
                    <tr>
                        <td><?php _e('Total LinkedIn Shares','wpdmpro'); ?></td>
                        <th><?php echo get_option('wpdm_linkedins', 0); ?></th>
                    </tr>
                </table>
            </div>
            <div class="tab-pane" id="messages">...</div>
            <div class="tab-pane" id="settings">
                <iframe src="http://cdn.wpdownloadmanager.com/notice.php?wpdmvarsion=<?php echo WPDM_Version; ?>"
                        style="height: 300px;width:100%;border:0px"></iframe>
            </div>
        </div>

        <script>
            jQuery(function () {

                jQuery('#myTab a').click(function (e) {
                    e.preventDefault();
                    jQuery(this).tab('show');
                    jQuery(this).css('outline', 'none');
                });
            })
        </script>


    </div>

<?php
}

// Function that beeng used in the action hook
function wpdm_add_dashboard_widgets()
{
    wp_add_dashboard_widget('wpdm_dashboard_widget', 'WordPress Download Manager', 'wpdm_dashboard_widget_function');
    global $wp_meta_boxes;
    $side_dashboard = $wp_meta_boxes['dashboard']['side']['core'];
    $wpdm_widget = array('wpdm_dashboard_widget' => $wp_meta_boxes['dashboard']['normal']['core']['wpdm_dashboard_widget']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['wpdm_dashboard_widget']);
    $sorted_dashboard = array_merge($wpdm_widget, $side_dashboard);
    $wp_meta_boxes['dashboard']['side']['core'] = $sorted_dashboard;
}
 

// Register the new dashboard widget into the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'wpdm_add_dashboard_widgets');


function wpdmpp_get_files($dir)
{
    global $dfiles;
    $tdfiles = scandir($dir);
    array_shift($tdfiles);
    array_shift($tdfiles);
    foreach ($tdfiles as $file_index => $file) {
        if (!is_dir($dir . $file))
            $dfiles[] = $dir . $file;
        else
            wpdmpp_get_files($dir . $file . '/');
    }
}

function wpdm_currency($return = 0)
{
    if ($return)
        return get_option('_wpdm_currency');
    echo get_option('_wpdm_currency');
}

function wpdm_currency_sign($return = 0)
{
    if ($return)
        return get_option('_wpdm_currency_symbol');
    echo get_option('_wpdm_currency_symbol');
}

/**
 * Depracated Function
 */
function wpdm_load_data()
{
    return;
    global $wp_query, $wpdm_category, $wpdm_package;
    if (!isset($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')]) && !isset($wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')])) return;

    if ($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] != '') {
        //die($wp_query->query_vars[get_option('__wpdm_purl_base','download')]);         
        $wpdm_package = wpdm_get_package($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')]);

    }

    if (isset($wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')]) && $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] != '') {
        $cats = maybe_unserialize(get_option('_fm_categories'));
        $wpdm_category = $cats[$wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')]];
        $wpdm_category['ID'] = $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')];
    }

}



//added from 3.3.7

function wpdm_tpled_packages($params = array('category' => '', 'limit' => 10, 'order_by' => 'create_date', 'order' => 'desc', 'linktemplate' => 'link-template-default.php'))
{
    global $wpdb;
    extract($params);
    if (!isset($limit) || $limit == 0) $limit = 10;
    if (!isset($order_by) || $order_by == '') $order_by = 'create_date';
    if (!isset($order) || $order == '') $order = 'desc';
    $tdata = $wpdb->get_results("select * from {$wpdb->prefix}ahm_files where category like '%{$category}%' order by {$order_by} desc limit 0, {$limit}", ARRAY_A);
    foreach ($tdata as $data) {
        $postlink = get_permalink($post->ID);
        $data['page_url'] = "{$postlink}{$sap}wpdm_page={$id}";
        //$data['popup_link'] = "<a href='{$postlink}{$sap}wpdm_page={$id}&mode=popup' class='popup-link' >$link_label</a>";
        $data['page_link'] = "<a href='{$postlink}{$sap}wpdm_page={$id}'>$link_label</a>";
        if ($data['preview'] == '')
            $data['preview'] = "download-manager/preview/noimage.gif";
        $data['thumb'] = "<img src='" . plugins_url() . '/download-manager/timthumb.php?w=' . get_option('_wpdm_wthumb_w', 150) . '&h=' . get_option('_wpdm_wthumb_h', 70) . '&zc=1&src=' . $data[preview] . "'/>";
        if ($data['icon'] != '')
            $data['icon'] = "<img class='wpdm_icon' align='left' src='" . plugins_url() . "/{$data[icon]}' />";
        $data = apply_filters('wdm_pre_render_link', $data);
        $linktemplates = unserialize(get_option("_fm_link_templates", true));
        $linktemplates = maybe_unserialize(get_option("_fm_link_templates"));
        if (isset($linktemplates[$linktemplate]) && $linktemplates[$linktemplate] != '') $linktemplate = $linktemplates[$linktemplate]['content'];
        $html .= "<li>" . FetchTemplate($linktemplate, $data, 'link') . "</li>";

    }
    echo $html;
}

function wpdm_update_client_profile()
{
    global $current_user;
    $task = isset($_REQUEST['wpdmtask']) ? $_REQUEST['wpdmtask'] : '';
    if ($task != 'wpdmupdateprofile' || !is_user_logged_in()) return;
    update_user_meta($current_user->ID, '_wpdm_client', $_POST['_wpdm_client']);
    die('Saved');
}


function wpdm_enqueue_scripts()
{
    global $post;

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-form');

    if(has_shortcode($post->post_content,'wpdm_frontend') || get_post_type()=='wpdmpro')
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');

    if(has_shortcode($post->post_content,'wpdm_frontend')){
        wp_enqueue_script('jquery-ui');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
    }

    wp_enqueue_style('wpdm-front', plugins_url() . '/download-manager/css/front.css');

    if (get_option('__wpdm_twitter_bootstrap') != 'dall' && get_option('__wpdm_twitter_bootstrap') != 'dcss'){
        wp_enqueue_style('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/css/bootstrap.css'));
        wp_enqueue_style('wpdm-bootstrap-theme', plugins_url('/download-manager/bootstrap/css/bootstrap-theme.min.css'));
    }

    if (get_option('__wpdm_twitter_bootstrap') != 'dall' && get_option('__wpdm_twitter_bootstrap') != 'djs')
        wp_enqueue_script('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/js/bootstrap.min.js'), array('jquery'));


    wp_enqueue_script('jquery-cookie', plugins_url('/download-manager/js/jquery.cookie.js'), array('jquery'));
    wp_enqueue_script('frontjs', plugins_url('/download-manager/js/front.js'), array('jquery'));


}

function wpdm_admin_enqueue_scripts()
{
    if(get_post_type()=='wpdmpro'||in_array(wpdm_query_var('page'),array('settings','emails','wpdm-stats','templates','importable-files','wpdm-addons'))){
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-form');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-timepicker', WPDM_BASE_URL.'js/jquery-ui-timepicker-addon.js',array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider') );
    wp_enqueue_style('icons', plugins_url() . '/download-manager/css/icons.css');
    wp_enqueue_script('wp-pointer');
    wp_enqueue_style('wp-pointer');
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
    wp_enqueue_script('media-upload');
    wp_enqueue_media();

    wp_enqueue_script('jquery-choosen', plugins_url('/download-manager/js/chosen.jquery.min.js'), array('jquery'));
    wp_enqueue_style('choosen-css', plugins_url('/download-manager/css/chosen.css'));
    wp_enqueue_style('jqui-css', plugins_url('/download-manager/jqui/theme/jquery-ui.css'));
    //if(isset($_GET['page']) && $_GET['page']== 'settings' && get_post_type()=='wpdmpro')

        wp_enqueue_script('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/js/bootstrap.min.js'), array('jquery'));
        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
    }


}


function wpdm_delete_all_cats()
{
    if (isset($_GET['_nonce']) && wp_verify_nonce($_GET['_nonce'], 'wpdmdcs') && $_GET['task'] == 'deleteallcats') {
        update_option('_fm_categories', array());
        header("location: admin.php?page=file-manager/categories");
        die();
    }
}


//import csv to mysql    

function wpdm_import_category_csv_file()
{
    global $wpdb;
    if (!isset($_GET['task']) || $_GET['task'] != 'wpdm-import-category-csv') return;
    $max_line_length = 10000;
    $source_file = $_FILES['csv']['tmp_name'];
    if (($handle = fopen("$source_file", "r")) !== FALSE) {
        $columns = fgetcsv($handle, $max_line_length, ",");
        foreach ($columns as &$column) {
            $column = str_replace(".", "", $column);
        }

        while (($data = fgetcsv($handle, $max_line_length, ",")) !== FALSE) {
            while (count($data) < count($columns))
                array_push($data, NULL);
            //$query = "$insert_query_prefix (".join(",",quote_all_array($data)).");";            
            $values = quote_all_array($data);
            $drow = array_combine($columns, $values);
            $category_id = $drow['category_id'];
            unset($drow['category_id']);
            $drow['access'] = serialize(explode(",", $drow['access']));
            $cats[$category_id] = $drow;
        }
        fclose($handle);


        $categories = maybe_unserialize(get_option("_fm_categories"));
        if (is_array($cats)) {
            $categories = array_merge($categories, $cats);
            update_option("_fm_categories", $categories);
        }
        @unlink($source_file);
    }
    header("location: admin.php?page=file-manager/categories");
    die();
}


function wpdm_import_csv_file()
{
    global $wpdb;
    if (!isset($_GET['task']) || $_GET['task'] != 'wpdm-import-csv') return;
    $max_line_length = 10000;
    $source_file = $_FILES['csv']['tmp_name'];
    $alldata = file_get_contents($source_file);
    $alldata = str_getcsv(str_replace("\r","",$alldata), "\n");
    if (is_array($alldata)) {

        foreach ($alldata as &$data) {
            $data = str_getcsv($data, ",");
        }

        $columns = array_shift($alldata);

        foreach ($alldata as $idx => $adata) {

            $adata[0] = trim($adata[0]);
            while (count($adata) < count($columns))
                array_push($adata, NULL);
            $values = quote_all_array($adata);
            $drow = array_combine($columns, $values);
            if (isset($drow['url_key']))
                unset($drow['url_key']);
            $drow['files'] = explode(',', $drow['files']);
            $drow['category'] = explode(',', $drow['category']);
            $drow['create_date'] = isset($drow['create_date']) ?  date("Y-m-d H:i:s", strtotime($drow['create_date'])) : date("Y-m-d H:i:s",time());
            $drow['update_date'] = isset($drow['update_date']) ? strtotime($drow['update_date']) : time();
            $access = explode(",", $drow['access']);
            $drow['access'] = isset($drow['access']) && $drow['access'] != '' ? $access : array('guest');

            if (!isset($drow['ID'])) {
                $postdata = array(
                    'post_title' => $drow['title'],
                    'post_content' => $drow['description'],
                    'post_date' => $drow['create_date'],
                    'post_type' => 'wpdmpro',
                    'post_status' => 'publish'
                );

                $post_id = wp_insert_post($postdata);
                $ret = wp_set_post_terms($post_id, $drow['category'], 'wpdmcategory' );
            } else {
                $post_id = $drow['ID'];
            }
            if (isset($drow['title']))
                unset($drow['title']);
            if (isset($drow['description']))
                unset($drow['description']);
            if (isset($drow['create_date']))
                unset($drow['create_date']);

            foreach ($drow as $meta_key => $value) {
                update_post_meta($post_id, "__wpdm_".$meta_key, $value);
            }

            do_action('after_add_package', $post_id, $drow);
        }

    }
    @unlink($source_file);
    header("location: edit.php?post_type=wpdmpro");
    die();
}

function quote_all_array($values)
{
    foreach ($values as $key => $value)
        if (is_array($value))
            $values[$key] = quote_all_array($value);
        else
            $values[$key] = quote_all($value);
    return $values;
}

function quote_all($value)
{
    if (is_null($value))
        return "NULL";

    $value = mysql_real_escape_string($value);
    return $value;
}

//added from v3.3.5.rc3
function wpdm_dashboard()
{
    require_once(dirname(__FILE__) . '/wpdm-dashboard.php');
}

//added from v3.3.5
// upgraded on v4.0.0
function wpdm_similar_packages($package_id = null, $count = 5)
{
    $id = $package_id?$package_id:get_the_ID();
    $tags = wp_get_post_tags($id);
    $posts = array();
    if ($tags) {
        $tag_ids = array();
        foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
        $args=array(
            'post_type'=>'wpdmpro',
            'tag__in' => $tag_ids,
            'post__not_in' => array($id),
            'posts_per_page'=>$count
        );

        $posts = get_posts( $args , ARRAY_A );

        $html = "";

        foreach( $posts as $p ) {

            $package['ID'] = $p->ID;
            $package['post_title'] = $p->post_title;
            $package['post_content'] =  $p->post_content;
            $package['post_excerpt'] = $p->post_excerpt;
            $html .= "<div class='col-md-6'>".FetchTemplate("link-template-panel.php", $package, 'link')."</div>";

        }
    }
    if(count($posts)==0) $html = "<div class='col-md-12'><div class='alert alert-info'>".__('No related download found!','wpdmpro')."</div> </div>";
    $html = "<div class='w3eden'><div class='row'>".$html."</div></div>";
    wp_reset_query();
    return $html;


}

function wpdm_template_preview()
{
    error_reporting(0);

    $wposts = array();

    $template = wpdm_query_var("template","html");


        $args=array(
            'post_type'=>'wpdmpro',
            'posts_per_page'=>1
        );

        $wposts = get_posts( $args  );

        $html = "";

        foreach( $wposts as $p ) {

            $package = (array)$p;

            $html .= FetchTemplate($template, $package, 'link');

        }

    if(count($wposts)==0) $html = "<div class='col-md-12'><div class='alert alert-info'>".__('No package found! Please create at least 1 package to see template preview','wpdmpro')."</div> </div>";
    $html = "<div class='w3eden'>".$html."</div><div style='clear:both'></div>";

    echo $html;
    die();

}

//lagecy code for v3.3.8
function wpdm_custom_fields_table($package){
    $custom_data = maybe_unserialize(get_post_meta($package['ID'], '__wpdm_custom_fields'));
    //$wpdm_custom_data = array();
    $data = "";

    $data = "<table class='table'>";
    if(count($custom_data)>0){
    foreach($custom_data['name'] as $index => $dname){
        $wpdm_custom_data[$dname] = $custom_data['value'][$index];
        $package['wpdm_cf '.$dname] = $custom_data['value'][$index];
        $data .= "<tr><td>{$dname}</td><td>{$custom_data['value'][$index]}</td></tr>";
    }}

    $cdata = get_post_custom($package['ID']);

    foreach ($cdata as $k => $v) {
        if(!preg_match("/_[\w]+/",$k, $found))
        $data .= "<tr><td>{$k}</td><td>{$v[0]}</td></tr>";
    }

    $data .= "</table>";



    return $data;

}

function wpdm_sitemap_xml()
{
    return;
    global $wpdb;
    $page = basename($_SERVER['REQUEST_URI']);
    if ($page == 'wpdmpro-sitemap.xml') {
        header('Content-type: text/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="' . plugins_url('download-manager/css/sitemap-style.xml') . '"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $dls = get_posts("post_type=wpdmpro&posts_per_page=1000");
        foreach ($dls as $wpdm_package) {
            ?>
            <url>
                <loc><?php echo get_permalink($wpdm_package->ID) ?></loc>
                <lastmod><?php the_modified_date($wpdm_package->ID) ?></lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.6</priority>
            </url>
        <?php } ?>
        </urlset>

        <?php
        die();
    }
}


/** download manager new **/
function wpdm_meta_boxes()
{
    $settings = maybe_unserialize(get_option('_wpdmpp_settings'));
    $meta_boxes = array(
        'wpdm-attached-files' => array('title' => __('Attached Files', "wpdmpro"), 'callback' => 'wpdm_meta_box_files', 'position' => 'normal', 'priority' => 'core'),
        'wpdm-attached-dir' => array('title' => __('Attach Dir', "wpdmpro"), 'callback' => 'wpmp_dir_browser_metabox', 'position' => 'side', 'priority' => 'core'),
        'wpdm-settings' => array('title' => __('Package Settings', "wpdmpro"), 'callback' => 'wpdm_meta_box_package_settings', 'position' => 'normal', 'priority' => 'low'),
        'wpdm-upload-file' => array('title' => __('Attach File', "wpdmpro"), 'callback' => 'wpdm_meta_box_upload_file', 'position' => 'side', 'priority' => 'core'),
        //'wpdm-items'=>array('title'=>__('Other Items',"wpdm"),'callback'=>'wpdm_meta_box_other_items','position'=>'side','priority'=>'low'),
    );


    $meta_boxes = apply_filters("wpdm_meta_box", $meta_boxes);
    foreach ($meta_boxes as $id => $meta_box) {
        extract($meta_box);
        if(!isset($position)) $position = 'normal';
        if(!isset($priority)) $priority = 'core';
        add_meta_box($id, $title, $callback, 'wpdmpro', $position, $priority);
    }
}

function wpdm_meta_box_files($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/metaboxes/attached-files.php");
}

function wpdm_meta_box_package_settings($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/metaboxes/package-settings.php");
}

function wpdm_meta_box_upload_file($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/metaboxes/attach-file.php");
}

function wpdm_meta_box_other_items($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/items.php");
}

function wpdm_tag_query($query)
{

    if (is_tag()) {

        $post_type = get_query_var('post_type');
        if (!is_array($post_type))
            $post_type = array('post', 'wpdmpro', 'nav_menu_item');
        else
            $post_type = array_merge($post_type, array('post', 'wpdmpro', 'nav_menu_item'));
        $query->set('post_type', $post_type);
        return $query;
    }
}

function wpdm_view_countplus(){
    if(isset($_REQUEST['_nonce'])&&wp_verify_nonce($_REQUEST['_nonce'],"__wpdm_view_count")){

        $id = intval($_REQUEST['id']);
        $views = get_post_meta($id, '__wpdm_view_count', true);
        update_post_meta($id, '__wpdm_view_count', $views+1);
        echo $views+1;
        die();

    }
}

function wpdm_view_countplusjs(){
    if(is_single()&&get_post_type()=='wpdmpro'){
        ?>
    <script>
        jQuery(function($){
            $.get('<?php echo 'index.php?_nonce='.wp_create_nonce('__wpdm_view_count').'&id='.get_the_ID(); ?>');
        });
    </script>
    <?php
    }
}

function wpdm_array_splice_assoc(&$input, $offset, $length, $replacement) {
    $replacement = (array) $replacement;
    $key_indices = array_flip(array_keys($input));
    if (isset($input[$offset]) && is_string($offset)) {
        $offset = $key_indices[$offset];
    }
    if (isset($input[$length]) && is_string($length)) {
        $length = $key_indices[$length] - $offset;
    }

    $input = array_slice($input, 0, $offset, TRUE)
        + $replacement
        + array_slice($input, $offset + $length, NULL, TRUE);
}


function wpdm_columns_th($defaults) {
    if(get_post_type()!='wpdmpro') return $defaults;
    $img['image'] = "<img src='".WPDM_BASE_URL."images/image.png' style='margin-left:10px' title='Img/Ico' alt='Img/Ico' />";
    wpdm_array_splice_assoc( $defaults, 1, 0, $img );
    $otf['download_count'] = "<img src='".WPDM_BASE_URL."images/download-count.png' title='Download Count' alt='Downloads' />";
    $otf['shortcode'] = 'Short-code';
    wpdm_array_splice_assoc( $defaults, 3, 0, $otf );
    return $defaults;
}

function wpdm_columns_td($column_name, $post_ID) {
    if(get_post_type()!='wpdmpro') return;
    if ($column_name == 'download_count') {

            echo get_post_meta($post_ID, '__wpdm_download_count', true);

    }
    if ($column_name == 'shortcode') {

            echo "<input readonly=readonly class='wpdm-scode' onclick='this.select();' value=\"[wpdm_package id='$post_ID']\" />";

    }
    if ($column_name == 'image') {
        if(has_post_thumbnail($post_ID))
        echo get_the_post_thumbnail( $post_ID, 'thumbnail', array('class'=>'img60px') );
        else {
            $icon = get_post_meta($post_ID,'__wpdm_icon', true);
            if($icon!=''){
            $icon = plugins_url('/').$icon;
            echo "<img src='$icon' class='img60px' alt='Icon' />";
            }
        }
    }
}

function wpdm_dlc_sortable( $columns ) {

    if(get_post_type()!='wpdmpro') return $columns;

    $columns['download_count'] = 'download_count';

    return $columns;
}

function wpdm_dlc_orderby( $vars ) {

    if ( isset( $vars['orderby'] ) && 'download_count' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => '__wpdm_download_count',
            'orderby' => 'meta_value_num'
        ) );
    }

    return $vars;
}

function wpdm_addonslist(){

    if(!isset($_SESSION['wpdm_addon_store_data'])){
        $data = remote_get('http://www.wpdownloadmanager.com/?wpdm_api_req=getPackageList');
        $cats = remote_get('http://www.wpdownloadmanager.com/?wpdm_api_req=getCategoryList');
        $_SESSION['wpdm_addon_store_data'] = $data;
        $_SESSION['wpdm_addon_store_cats'] = $cats;
    }
    else {
        $data = $_SESSION['wpdm_addon_store_data'];
        $cats = $_SESSION['wpdm_addon_store_cats'];
    }

    include(WPDM_BASE_DIR."/tpls/wpdm-addons-list.php");
}

/**
 * Added from v4.1.1
 * WPDM add-on installer
 */
function wpdm_install_addon(){
    if(isset($_REQUEST['addon']) && current_user_can('manage_options')){
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
        if(strpos($_REQUEST['addon'], '.zip'))
            $downloadlink = $_REQUEST['addon'];
        else
            $downloadlink = 'http://www.wpdownloadmanager.com/?wpdmdl='.$_REQUEST['addon'];
        $upgrader->install($downloadlink);
        die();
    } else {
        die("Only site admin is authorized to install add-on");
    }
}


function wpdm_category_page($content){
    global $post;
    if(defined('WPDM_THEME_SUPPORT') || !is_tax('wpdmcategory')) return $content;
    $id = get_the_ID();
    $cpageinfo = get_option('__wpdm_cpage_info');
    $data = wpdm_setup_package_data((array)$post);
    $data['version'] = isset($data['version']) && $data['version']!=''? $data['version']: '1.0.0';
    //$data['download_link'] = str_replace("<a","<a style=\"padding:5px 10px;min-width:auto;font-size:8pt;\"", $data['download_link']);
    if(is_archive() && get_post_type()=='wpdmpro'){

        if(get_option('__wpdm_cpage_style')=='basic'){

        $info = "<div class='w3eden'><div class='well wpdm-archive-meta' style='margin: 10px 0;box-shadow: none;background: #f5f5f5;padding: 10px 20px;color: #444444;border-radius: 2px;font-size: 10pt'>
                        <ul class='nav nav-pills nav-justified' style='list-style: none;padding:0;margin:0;'>";

        if(isset($cpageinfo['version']))
        $info .="<li><i class='fa fa-history'></i> &nbsp;".__('Version','wpdmpro')." {$data['version']}</li>";

        if(isset($cpageinfo['view_count']))
        $info .="<li><i class='fa fa-eye'></i>  &nbsp;{$data['view_count']} ".__('View(s)','wpdmpro')."</li>";

        if(isset($cpageinfo['download_count']))
        $info .="<li><i class='fa fa-download'></i>  &nbsp;{$data['download_count']} ".__('Download(s)',"wpdmpro")."</li>";

        if(isset($cpageinfo['package_size']))
        $info .="<li><i class='fa fa-th'></i>  &nbsp;{$data['package_size']}</li>";

        if(isset($cpageinfo['download_link']))
        $info .="<li>{$data['download_link']}</li>";

        $info .="</ul>
        </div></div>";

        return get_option('__wpdm_cpage_excerpt')=='before'?$info.$content:$content.$info;

        }

        if(get_option('__wpdm_cpage_style')=='ltpl'){
            return "<div class='w3eden'>".FetchTemplate(get_option('__wpdm_cpage_template'), $data)."</div>";
        }

    }

    return $content;
}

/**
 * @param $pid
 * @param $w
 * @param $h
 * @param bool $echo
 * @return string
 * @usage Generates thumbnail html from PDF file attached with a Package. [ From v4.1.3 ]
 */
function wpdm_pdf_preview($pid, $w, $h, $echo = true){

    $post = get_post($pid);
    $files = get_post_meta($pid, '__wpdm_files', true);
    $pdf = $files[0];
    $ext = explode(".", $pdf);
    $ext = end($ext);

    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($pid), 'full');
    $preview = $thumb['0'];

    if($ext=='pdf')
        $pdf_thumb =  wpdm_pdf_thumbnail($pdf, $pid);
    else $pdf_thumb = $preview;

    $imsrc  = wpdm_dynamic_thumb($pdf_thumb, array($w, $h));

    if(!$echo)
        return "<img src='{$imsrc}' alt='{$post->post_title}'/>";

    echo "<img src='{$imsrc}' alt='{$post->post_title}'/>";

}

/**
 * @param $pdf
 * @param $id
 * @return string
 * @usage Generates thumbnail from PDF file. [ From v4.1.3 ]
 */
function wpdm_pdf_thumbnail($pdf, $id){
    $pdfurl = '';
    if(strpos($pdf, "://")) { $pdfurl = $pdf; $pdf = str_replace(home_url('/'), ABSPATH, $pdf); }
    if($pdf == $pdfurl) return;
    if(file_exists($pdf)) $source = $pdf;
    else $source = UPLOAD_DIR.$pdf;
    $dest = WPDM_CACHE_DIR. "/pdfthumbs/{$id}.png";
    $durl = WPDM_BASE_URL."cache/pdfthumbs/{$id}.png";
    $ext = explode(".", $source);
    $ext = end($ext);
    if($ext!='pdf') return '';
    if(file_exists($dest)) return $durl;
    $source = $source.'[0]';
    if(!class_exists('Imagick')) return "Imagick is not installed properly";
    try{
    $image = new imagick($source);
    $image->setResolution( 800, 800 );
    $image->setImageFormat( "png" );
    $image->writeImage($dest);
    } catch(Exception $e){
        return '';
    }
    return $durl;
}

/**
 * @usage Show Login Form
 */
function wpdm_login_form($params = array()){
    extract($params);
    if(!isset($redirect)) $redirect = home_url('/');
    ob_start();
    include(WPDM_BASE_DIR.'wpdm-login-form.php');
    return ob_get_clean();
}

function wpdm_user_logged_in($msg){
    echo $msg;
}

function wpdm_sfb_access(){

    global $wp_roles;

    $roleids = array_keys($wp_roles->roles);
    $roles = get_option('_wpdm_file_browser_access',array('administrator'));
    $naroles = array_diff($roleids, $roles);

    foreach($roles as $role) {
        $role = get_role($role);
        if(is_object($role))
        $role->add_cap('access_server_browser');
    }

    foreach($naroles as $role) {
        $role = get_role($role);
        $role->remove_cap('access_server_browser');
    }

}

/*** developer fns **/
function  dd($data)
{
    echo "<pre>" . print_r($data, 1) . "</pre>";
    die();
}
/*** developer fns **/

