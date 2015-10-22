<?php
 

function wpdm_unInstall()
{
    global $wpdb;
    global $jal_db_version;

    $table_name = "{$wpdb->prefix}ahm_files";
    if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {

        $sql = "DROP TABLE " . $table_name;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        remove_option("fm_db_version");

    }

}

global $stabs, $package, $wpdm_package;
$stabs['basic'] = array('id' => 'basic', 'link' => 'edit.php?post_type=wpdmpro&page=settings', 'title' => 'Basic', 'callback' => 'basic_settings');
$stabs['frontend'] = array('id' => 'frontend', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=frontend', 'title' => 'Frontend', 'callback' => 'wpdm_frontend_settings');
if(function_exists('bp_is_active')){
    $stabs['buddypress'] = array('id' => 'buddypress', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=buddypress', 'title' => 'BuddyPress', 'callback' => 'buddypress_addon_settings');
}
function add_wdm_settings_tab($tablink, $newtab, $func)
{
    global $stabs;
    $stabs["{$tablink}"] = array('id' => $tablink, 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=' . $tablink, 'title' => $newtab, 'callback' => $func);
}

function render_settings_tabs($sel = '')
{
    global $stabs;

    foreach ($stabs as $tab) {
        if ($sel == $tab['id'])
            echo "<li class='active'><a id='{$tab['id']}' href='{$tab['link']}'>{$tab['title']}</a></li>";
        else
            echo "<li class=''><a id='{$tab['id']}' href='{$tab['link']}'>{$tab['title']}</a></li>";
        if (isset($tab['func']) && function_exists($tab['func'])) {
            add_action('wp_ajax_' . $tab['func'], $tab['func']);
        }
    }
}

function buddypress_addon_settings(){
    if(isset($_POST['section']) && $_POST['section']=='buddypress' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings'){
        foreach($_POST as $k => $v){
            if(strpos($k, '_wpdm_')){
                update_option($k, $v);
            }
        }
        die('Settings Saved Successfully!');
    }
    include("settings/buddypress.php");
}

function wpdm_frontend_settings(){
    if(isset($_POST['section']) && $_POST['section']=='frontend' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings'){
        foreach($_POST as $k => $v){
            if(strpos($k, '_wpdm_')){
                update_option($k, $v);
            }
        }
        die('Settings Saved Successfully!');
    }
    include("settings/frontend.php");
}


function wpdm_is_download_limit_exceed($id)
{
    global $wpdb, $current_user;
    get_currentuserinfo();
    $cond[] = "pid='$id'";
    if (is_user_logged_in())
        $cond[] = "uid='{$current_user->ID}'";
    else
        $cond[] = "ip='{$_SERVER['REMOTE_ADDR']}'";
    $td = $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_download_stats where " . implode(" and ", $cond));
    $mx = get_post_meta($id, '__wpdm_download_limit_per_user', true);
    if ($mx > 0 && $td >= $mx) return true;
    return false;
}


function DownloadPageTitle($title)
{
    global $wpdb, $wp_query;
    if ($wp_query->query_vars['wpdm_page'] != '') {
        $id = (int)$wp_query->query_vars['wpdm_page'];
        $data = $wpdb->get_row("select title from {$wpdb->prefix}ahm_files where id='$id'", ARRAY_A);
        return $data['title'];
    }
    return $title;

}

function DownloadPageContent($embedid = 0)
{
    global $wpdb, $wp_query, $wpdm_package, $post;
    if (is_singular('wpdmpro') || $embedid > 0) {
        if ($embedid > 0)
            $linktemplates = maybe_unserialize(get_option("_fm_link_templates"));
        $pagetemplates = maybe_unserialize(get_option("_fm_page_templates"));
        if (!isset($wpdm_package['ID']))
            $wpdm_package = get_post(get_the_ID(), ARRAY_A);
        $wpdm_package['id'] = get_the_ID();

        $wpdm_package = wpdm_setup_package_data($wpdm_package);

        $wpdm_package['template'] = isset($wpdm_package['template']) ? $wpdm_package['template'] : 'link-template-default.php';
        $wpdm_package['page_template'] = isset($wpdm_package['page_template']) ? $wpdm_package['page_template'] : 'page-template-default.php';

        if (file_exists(dirname(__FILE__) . '/templates/' . $wpdm_package['template'])) $wpdm_package['template'] = @file_get_contents(dirname(__FILE__) . '/templates/' . $wpdm_package['template']);
        else
            $wpdm_package['template'] =isset($linktemplates) && !empty($linktemplates[$wpdm_package['template']]['content']) ? $linktemplates[$wpdm_package['template']]['content'] : $wpdm_package['template'];

        if (file_exists(dirname(__FILE__) . '/templates/' . $wpdm_package['page_template'])) $wpdm_package['page_template'] = @file_get_contents(dirname(__FILE__) . '/templates/' . $wpdm_package['page_template']);
        else
            $wpdm_package['page_template'] = $pagetemplates[$wpdm_package['page_template']]['content'] ? $pagetemplates[$wpdm_package['page_template']]['content'] : $wpdm_package['page_template'];

        $wpdm_package = apply_filters('wdm_pre_render_page', $wpdm_package);
        if (isset($_GET['mode']) && $_GET['mode'] == 'popup') {
            echo "<div class='w3eden'>";
            echo FetchTemplate($wpdm_package['page_template'], $wpdm_package, 'popup');
            echo '<br><div style="clear: both;"></div><br></div> ';
        } else {
            $wpdm_package['page_template'] = stripcslashes($wpdm_package['page_template']);
            $data = FetchTemplate($wpdm_package['page_template'], $wpdm_package, 'page');
            $siteurl = site_url('/');
            return "<div class='w3eden'>" . $data . "<div style='clear:both'></div></div>";
        }
    }

}

function wpdm_multisite(){
    if( defined('MULTISITE') && MULTISITE == true ){

    }
}

/**
 * @param (int|array) $package Package ID (INT) or Complete Package Data (Array)
 * @param string $ext
 * @return string|void
 */
function wpdm_download_url($package, $ext = '')
{
    if ($ext) $ext = '&' . $ext;
    $id = is_int($package)?$package:$package['ID'];
    $permalink = get_permalink($id);
    $sap = strpos($permalink, '?')?'&':'?';
    return $permalink.$sap."wpdmdl={$id}{$ext}";
}



function wpdm_upload_file()
{
    if (!isset($_FILES['Filedata'])) return;
    if (is_uploaded_file($_FILES['Filedata']['tmp_name']) && is_admin() && $_GET['task'] == 'wpdm_upload_files') {
        $tempFile = $_FILES['Filedata']['tmp_name'];
        $targetFile = UPLOAD_DIR . time() . 'wpdm_' . $_FILES['Filedata']['name'];
        move_uploaded_file($tempFile, $targetFile);
        echo basename($targetFile);
        die();
    }
}


function CreateDir()
{
    if (!file_exists(UPLOAD_BASE)) {
        @mkdir(UPLOAD_BASE, 0755);
    }
    @chmod(UPLOAD_BASE, 0755);
    @mkdir(UPLOAD_DIR, 0755);
    @chmod(UPLOAD_DIR, 0755);
    setHtaccess();
    if (isset($_GET['re']) && $_GET['re'] == 1) {
        if (file_exists(UPLOAD_DIR)) $s = 1;
        else $s = 0;
        echo "<script>
        location.href='{$_SERVER[HTTP_REFERER]}&success={$s}';
        </script>";
        die();
    }
}

function FMSettings()
{

    if (isset($_POST['access']) && $_POST['access'] != '') {
        update_option('access_level', $_POST[access]);
    }

    $access = get_option('access_level');
    include('wpdm-settings.php');
}

function basic_settings()
{
    if (isset($_POST['task']) && $_POST['task'] == 'wdm_save_settings') {
        if ($_POST['__wpdm_curl_base'] == '') $_POST['__wpdm_curl_base'] = 'wpdm-category';
        if ($_POST['__wpdm_purl_base'] == '') $_POST['__wpdm_purl_base'] = 'wpdm-package';
        if ($_POST['__wpdm_curl_base'] == $_POST['__wpdm_purl_base']) $_POST['__wpdm_curl_base'] .= 's';
        foreach ($_POST as $optn => $optv) {
            update_option($optn, $optv);
        }
        if (!isset($_POST['__wpdm_login_form'])) delete_option('__wpdm_login_form');
        if (!isset($_POST['__wpdm_cat_desc'])) delete_option('__wpdm_cat_desc');
        if (!isset($_POST['__wpdm_cat_img'])) delete_option('__wpdm_cat_img');
        if (!isset($_POST['__wpdm_cat_tb'])) delete_option('__wpdm_cat_tb');
        flush_rewrite_rules();
        global $wp_rewrite;
        wpdm_common_actions();
        $wp_rewrite->flush_rules();
        die('Settings Saved Successfully');
    }
    include('settings/basic.php');
}

function wdm_ajax_settings()
{
    global $stabs;
    if(current_user_can('manage_options'))
    call_user_func($stabs[$_POST['section']]['callback']);
    die();
}



function wpdm_save_new_package()
{
    global  $current_user;

    /* get_currentuserinfo();
       if(!is_array($_POST['files'])) $_POST['files'] = array();
       $_POST['whatido'] = 'copy';
       if( $_POST['whatido']=='copy' and !empty($_POST['imports']) )
       {


           foreach($_POST['imports'] as $v)
           {
           copy($v,UPLOAD_DIR.'/'.basename($v));
           array_push($_POST['files'],basename($v));

           }


       } elseif( $_POST['whatido']=='move' and !empty($_POST['imports']) ) {


       foreach($_POST['imports'] as $v)
           {
           rename(dirname(__FILE__).'/imports/'.$v,UPLOAD_DIR.$v);
           $_POST['files'][] = $v;

           }

       }



       if( $_POST['del'] ) foreach( $_POST['del'] as $val )  @unlink( UPLOAD_DIR.$val);

       $_POST['file']['access'] = serialize($_POST['file']['access']);
       $_POST['file']['category'] = serialize($_POST['file']['category']);
       $_POST['file']['files'] = serialize($_POST['files']);
       $_POST['file']['preview'] = $_POST['file']['preview']?$_POST['file']['preview']:'';
       $_POST['file']['uid'] = $current_user->ID;
       $_POST['file']['create_date'] = $_POST['wpdm_meta']['create_date']?strtotime($_POST['wpdm_meta']['create_date']):time();
       $_POST['file']['update_date'] = $_POST['wpdm_meta']['update_date']?strtotime($_POST['wpdm_meta']['update_date']):time();
       $_POST['file']['url_key'] = $_POST['url_key']?$_POST['url_key']:wpdm_url_key($_POST['file']['title']);
       $flds = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}ahm_files");
       array_shift( $flds );
       $fileinf = array();
       foreach($flds as $fld){
       $def = strpos($fld->Type,"nt(")?0:'';
       $fileinf[$fld->Field] = $_POST['file'][$fld->Field]?$_POST['file'][$fld->Field]:$def;
       }
       $wpdb->insert( "{$wpdb->prefix}ahm_files", $fileinf );
       $id = $wpdb->insert_id;
       update_wpdm_meta($id, 'masterkey',uniqid());
       $post_id = wp_insert_post(array(
             'post_type'       => 'wpdmpro',
             'post_status'     => 'publish',
             'post_author'     => $current_user->ID,
             'ping_status'     => get_option('default_ping_status'),
             'post_title'      => $fileinf['title'],
             'post_content'      => $fileinf['description'],
       ));
       update_post_meta($post_id,'wpdmid',$id);
       update_post_meta($post_id,'access',$fileinf['access']);
       foreach($_POST['wpdm_meta'] as $k => $v){
           update_post_meta($post_id,$k,$v);
       }

       do_action('after_add_package',$id, $_POST['file']);
       //update_wpdm_meta($id, 'create_date', time());
       //update_wpdm_meta($id, 'update_date', time()); */

    if (isset($_REQUEST['act']) && in_array($_REQUEST['act'], array('_ap_wpdm', '_ep_wpdm'))) {

        $pack = $_POST['pack'];
        $pack['post_type'] = 'wpdmpro';

        if ($_POST['act'] == '_ep_wpdm') {
            $hook = "edit_package_frontend";
            $pack['ID'] = $_POST['id'];
            unset($pack['post_status']);
            unset($pack['post_author']);
            $post = get_post($pack['ID']);

            $ostatus = $post->post_status=='publish'?'publish':get_option('__wpdm_ips_frontend','publish');
            $status = isset($_POST['status']) && $_POST['status'] == 'draft'?'draft': $ostatus;
            $pack['post_status'] = $status;

            $id = wp_update_post($pack);
            $ret = wp_set_post_terms($pack['ID'], $_POST['cats'], 'wpdmcategory' );

        }
        if ($_POST['act'] == '_ap_wpdm'){
            $hook = "create_package_frontend";
            $status = isset($_POST['status']) && $_POST['status'] == 'draft'?'draft': get_option('__wpdm_ips_frontend','publish');
            $pack['post_status'] = $status;
            $pack['post_author'] = $current_user->ID;
            $id = wp_insert_post($pack);
            wp_set_post_terms( $id, $_POST['cats'], 'wpdmcategory' );
        }

        wpdm_save_package_data_frontend($id);

        do_action($hook, $id, get_post($id));

        $data = array('result' => $_POST['act'], 'id' => $id);

        header('Content-type: application/json');
        echo json_encode($data);
        die();


    }
}

function ImportFiles()
{


    if (!@is_dir(dirname(__FILE__) . '/imports/'))
        mkdir(dirname(__FILE__) . '/imports/');
    $k = 0;
    ?>

    <?php
    if (isset($_POST['wpdm_importdir'])) update_option('wpdm_importdir', $_POST['wpdm_importdir']);
    $scan = @scandir(get_option('wpdm_importdir', false));
    $k = 0;
    if ($scan) {
        foreach ($scan as $v) {
            if ($v == '.' or $v == '..' or @is_dir(get_option('wpdm_importdir') . $v)) continue;

            $fileinfo[$k]['file'] = get_option('wpdm_importdir') . $v;
            $fileinfo[$k]['name'] = $v;
            $k++;
        }
    }

    include dirname(__FILE__) . '/wpdm-import.php';


    ?>


<?php
}

function wpdm_dimport()
{
    global $wpdb;

    array_shift($flds);
    $fileinf = array();
    $files = array($_POST['fname']);
    $fileinf['access'] = $_POST['access'];
    if (isset($_POST['password']) && $_POST['password'] != '') {
        $fileinf['password_lock'] = 1;
        $fileinf['password'] = $_POST['password'];

    }
    $fileinf['files'] = $files;
    $post_id = wp_insert_post(array(
        'post_title' => esc_attr($_POST['title']),
        'post_content' => esc_attr($_POST['description']),
        'post_type' => 'wpdmpro',
        'post_status' => 'publish'
    ));
    wp_set_post_terms($post_id, $_POST['category'], 'wpdmcategory');
    foreach ($fileinf as $meta_key => $value) {
        update_post_meta($post_id, "__wpdm_" . $meta_key, $value);
    }
    print_r($fileinf);
    copy(get_option('wpdm_importdir') . $_POST['fname'], UPLOAD_DIR . '/' . $_POST['fname']);
    do_action('after_add_package', $wpdb->insert_id, $fileinf);
    //@unlink(dirname(__FILE__).'/imports/'.$_POST['fname']);
    die('Done!');
}

function EditPackage()
{

    global $wpdb, $current_user;
    get_currentuserinfo();
    $cond_uid = wpdm_multi_user() && !wpdm_is_custom_admin() ? "and uid='{$current_user->ID}'" : "";


    $id = $_GET['id'];

    $table_name = "{$wpdb->prefix}ahm_files";
    $file = $wpdb->get_row("SELECT * FROM {$table_name} WHERE `id` = {$id} $cond_uid", ARRAY_A);
    if (!$file[id]) {
        $error = "Sorry, You don't have permission to edit that file!";
        include("error-page.php");
        return;
    }

    include('add-new-file.php');
}

function wpdm_save_package_data($post)
{
    global $wpdb, $current_user;
    get_currentuserinfo();
    if (get_post_type() != 'wpdmpro' || !isset($_POST['file'])) return;

    // Deleted old zipped file
    $zipped = get_post_meta($post, "__wpdm_zipped_file", true);
    if($zipped!='' && file_exists($zipped)) { @unlink($zipped); }

    $cdata = get_post_custom($post);
    foreach ($cdata as $k => $v) {
        $tk = str_replace("__wpdm_", "", $k);
        if (!isset($_POST['file'][$tk]) && $tk != $k)
            delete_post_meta($post, $k);

    }

    foreach ($_POST['file'] as $meta_key => $meta_value) {
        $key_name = "__wpdm_" . $meta_key;
        update_post_meta($post, $key_name, $meta_value);
    }

    update_post_meta($post, '__wpdm_masterkey', uniqid());

    if (isset($_POST['reset_key']) && $_POST['reset_key'] == 1)
        update_post_meta($post, '__wpdm_masterkey', uniqid());

    //do_action('after_update_package',$post, $_POST['file']);


}

function wpdm_save_package_data_frontend($post)
{
    global $wpdb, $current_user;
    get_currentuserinfo();

    if (!is_user_logged_in() || !isset($_POST['file']) || !isset($_POST['act']) || !in_array($_POST['act'],array('_ap_wpdm','_ep_wpdm'))) return;

    $cdata = get_post_custom($post);
    foreach ($cdata as $k => $v) {
        $tk = str_replace("__wpdm_", "", $k);
        if (!isset($_POST['file'][$tk]) && $tk != $k)
            delete_post_meta($post, $k);

    }

    if(isset($_POST['file']['preview'])){
        $preview = $_POST['file']['preview'];
        $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $preview ));
        set_post_thumbnail($post, $attachment_id);
        unset($_POST['file']['preview']);
    } else {
        delete_post_thumbnail($post);
    }

    foreach ($_POST['file'] as $meta_key => $meta_value) {
        $key_name = "__wpdm_" . $meta_key;
        update_post_meta($post, $key_name, $meta_value);
    }

    update_post_meta($post, '__wpdm_masterkey', uniqid());

    if (isset($_POST['reset_key']) && $_POST['reset_key'] == 1)
        update_post_meta($post, '__wpdm_masterkey', uniqid());

    //do_action('after_update_package',$post, $_POST['file']);


}


function wpdm_Categories()
{
    if ($_GET['task'] == 'DeleteCategory') {
        $tpldata = maybe_unserialize(get_option('_fm_categories'));
        unset($tpldata[$_GET['cid']]);
        foreach ($tpldata as $id => $data) {
            if ($data['parent'] == $_GET['cid'])
                unset($tpldata[$id]);
        }
        update_option('_fm_categories', @serialize($tpldata));
        echo "<script>
        location.href='{$_SERVER[HTTP_REFERER]}';
        </script>";
        die();
    }

    if ($_POST['cat']) {
        $tpldata = maybe_unserialize(get_option('_fm_categories'));
        if (!is_array($tpldata)) $tpldata = array();
        $tcid = $_POST['cid'] ? $_POST['cid'] : sanitize_title($_POST['cat']['title']);
        $cid = $tcid;
        while (array_key_exists($cid, $tpldata) && $_POST['cid'] == '') {
            $cid = $tcid . "-" . (++$postfx);
        }

        $tpldata[$cid] = $_POST['cat'];
        update_option('_fm_categories', @serialize($tpldata));
        echo "<script>
        location.href='{$_SERVER[HTTP_REFERER]}';
        </script>";
        die();
    }
    if (isset($_GET['view']))
        update_option('wpdm_cat_view', $_GET['view']);
    include("categories.php");
}

function wpdm_create_category()
{
    $categories = maybe_unserialize(get_option('_fm_categories', array()));
    $tcid = $_POST['cid'] ? $_POST['cid'] : sanitize_title($_POST['cat']['title']);
    $cid = $tcid;
    while (array_key_exists($cid, $categories) && $_POST['cid'] == '') {
        $cid = $tcid . "-" . (++$postfx);
    }

    $categories[$cid] = $_POST['cat'];
    update_option('_fm_categories', @serialize($categories));
    $info = $_POST['cat']['parent'] ? ' &mdash; Child of ' . $categories[$_POST['cat']['parent']]['title'] : '';
    echo "|||$cid|||{$_POST['cat']['title']}|||$info|||";
    die();
}

/**
 * @usage Render Download Manager Category List with ul/li hirarchy
 * @param int $parent
 * @param int $level
 * @param bool $recur
 */
function wpdm_list_categories($parent = 0, $level = 0, $recur = true)
{
    $parent = isset($parent)?$parent:0;
    $args = array(
        'show_option_all'    => '',
        'orderby'            => 'name',
        'order'              => 'ASC',
        'style'              => 'list',
        'show_count'         => 0,
        'hide_empty'         => 1,
        'use_desc_for_title' => 1,
        'child_of'           => $parent,
        'feed'               => '',
        'feed_type'          => '',
        'feed_image'         => '',
        'exclude'            => '',
        'exclude_tree'       => '',
        'include'            => '',
        'hierarchical'       => 1,
        'title_li'           => '',
        'show_option_none'   => __('No categories'),
        'number'             => null,
        'echo'               => 1,
        'depth'              => 0,
        'current_category'   => 0,
        'pad_counts'         => 0,
        'taxonomy'           => 'wpdmcategory',
        'walker'             => null
    );

            echo "<ul>";
        wp_list_categories( $args );

        echo "</ul>\n";


}

/**
 * @usage Check if a download manager category has child
 * @param $parent
 * @return bool
 */

function wpdm_cat_has_child($parent)
{
    $termchildren = get_term_children( $parent, 'wpdmcategory' );
    if(count($termchildren)>0) return true;
    return false;
}

function wpdm_cblist_categories($parent = 0, $level = 0, $sel = array())
{
    $cats = get_terms('wpdmcategory', array('hide_empty' => false, 'parent' => $parent));
    if (!$cats) $cats = array();
    if ($parent != '') echo "<ul>";
    foreach ($cats as $cat) {
        $id = $cat->slug;
        $pres = $level * 5;

            if (in_array($id, $sel))
                $checked = 'checked=checked';
            else
                $checked = '';
            echo "<li style='margin-left:{$pres}px;padding-left:0'><label><input id='c$id' type='checkbox' name='file[category][]' value='$id' $checked /> ".$cat->name."</label></li>\n";
            wpdm_cblist_categories($cat->term_id, $level + 1, $sel);

    }
    if ($parent != '') echo "</ul>";
}

function wpdm_dropdown_categories($name = '', $selected = '', $id = '')
{
    wp_dropdown_categories('show_option_none=Select category&show_count=0&orderby=name&echo=1&taxonomy=wpdmcategory&hide_empty=0&name=' . $name . '&id=' . $id . '&selected=' . $selected);

}

function LinkTemplates()
{

    $ttype = isset($_GET['_type']) ? $_GET['_type'] : 'link';

    if (isset($_GET['task']) && ($_GET['task'] == 'EditTemplate' || $_GET['task'] == 'NewTemplate')) {
        include("wpdm-template-editor.php");
    } else
        include("wpdm-link-templates.php");
}

function wpdm_save_template()
{
    if (!isset($_GET['page']) || $_GET['page'] != 'templates') return;
    $ttype = isset($_GET['_type']) ? $_GET['_type'] : 'link';
    if (isset($_GET['task']) && $_GET['task'] == 'DeleteTemplate') {
        $tpldata = maybe_unserialize(get_option("_fm_{$ttype}_templates"));
        if (!is_array($tpldata)) $tpldata = array();
        unset($tpldata[$_GET['tplid']]);
        update_option("_fm_{$ttype}_templates", @serialize($tpldata));

        header("location: edit.php?post_type=wpdmpro&page=templates&_type=$ttype");
        die();
    }

    if (isset($_POST['tpl'])) {
        if (is_array(get_option("_fm_{$ttype}_templates")))
            $tpldata = (get_option("_fm_{$ttype}_templates"));
        else
            $tpldata = maybe_unserialize(get_option("_fm_{$ttype}_templates"));
        if (!is_array($tpldata)) $tpldata = array();
        $tpldata[$_POST['tplid']] = $_POST['tpl'];
        update_option("_fm_{$ttype}_templates", @serialize($tpldata));

        header("location: edit.php?post_type=wpdmpro&&page=templates&_type=$ttype");
        die();
    }
}

function Stats()
{
    include("wpdm-stats.php");
}


function setHtaccess()
{
    $cont = 'RewriteEngine On
    <Files *>
    Deny from all
    </Files> 
       ';
    @file_put_contents(UPLOAD_DIR . '.htaccess', $cont);
}

function remote_post($url, $data)
{
    $fields_string = "";
    foreach ($data as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    //open connection
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $result;
}

function remote_get($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_USERAGENT => "spider", // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);
    return $content;
}


/**
 * @usage Generate direct link to download
 * @param $params
 * @param string $content
 * @return string
 */
function wpdm_direct_link($params, $content = "")
{
    extract($params);
    global $wpdb;
    $package = $wpdb->get_row("select * from {$wpdb->prefix}ahm_files where id='$id'", ARRAY_A);
    $url = wpdm_download_url($package);
    $data_icon = isset($data_icon) ? $data_icon : plugins_url('/download-manager/images/download-now.png');
    return "<div class='w3eden aligncenter'><br/><a style='text-align:left;padding:8px 15px;' class='btn $class' rel='nofollow' href='$url'><img src='{$data_icon}' style='border:0px;box-shadow:none;max-height:40px;width:auto;margin-right:10px;float:left;' /> <span id='mlbl' style='font-size:13pt;font-weight:bold;'>{$link_label}</span><br/><small id='slbl'>{$link_slabel}</small></a><br/><div style='clear:both;'></div></div>";
}


function is_valid_license_key()
{
    $key = $_POST['_wpdm_license_key'] ? $_POST['_wpdm_license_key'] : get_option('_wpdm_license_key');
    $domain = strtolower(str_replace("www.", "", $_SERVER['HTTP_HOST']));
    if (file_exists(dirname(__FILE__) . "/cache/wpdm_{$domain}")) {
        $data = unserialize(base64_decode(file_get_contents(dirname(__FILE__) . "/cache/wpdm_{$domain}")));
        if ($data[0] == md5($domain . $key) && $data[1] > time())
            return true;
        else
            @unlink(dirname(__FILE__) . "/cache/wpdm_{$domain}");
    }
    $res = remote_post('http://www.wpdownloadmanager.com/', array('action' => 'wpdm_pp_ajax_call', 'execute' => 'verifylicense', 'domain' => $domain, 'key' => $key, 'product' => 'wpdmpro'));
    //print_r($res);die();
    if ($res === 'valid') {
        file_put_contents(dirname(__FILE__) . "/cache/wpdm_{$domain}", base64_encode(serialize(array(md5($domain . $key), strtotime("+30 days")))));
        return true;
    }
    if (get_option('settings_ok') == '')
        update_option('settings_ok', strtotime('+30 days'));
    else {
        $page =  isset($_GET['page'])?$_GET['page']:"";
        $time = (int)get_option('settings_ok');
        if ($time < time() && $page == 'settings' && $_GET['tab'] != 'license') {
            die("<script>location.href='edit.php?post_type=wpdmpro&page=settings&tab=license';</script>");
        }
    }
    return false;
}

function wpdm_licnese()
{
    if (isset($_POST['task']) && $_POST['task'] == 'wdm_save_settings') {

        /*if($_POST['registerme']==1){
        $res = unserialize(remote_post('http://www.wpdownloadmanager.com/',array('action'=>'wpdm_pp_ajax_call','execute'=>'wpdm_register','domain'=>$domain,'email'=>$_POST['email'],'username'=>$_POST['username'])));                
        if($res['res']==='registered'){
            
            echo "       
                 
                    Your registration completed.<br/>
                    Please login to get your license key.<br/>
                    Login url: <a href='http://www.wpdownloadmanager.com/'>http://www.wpdownloadmanager.com/</a> <br/>
                    Username: {$_POST[username]}<br/>
                    Password: {$res[password]}
                
                ";
        }    
        }*/
        if (is_valid_license_key()) {
            update_option('_wpdm_license_key', $_POST['_wpdm_license_key']);
            die('Congratulation! Your <b>Download Manager</b> copy registered successfully!');
        } else {
            delete_option('_wpdm_license_key');
            die('Invalid License Key!');
        }
    }
    ?>
    <div class="panel panel-default">

            <div class="panel-heading"><b>License Key&nbsp;</b></div>
            <div class="panel-body"><input type="text" placeholder="Enter License Key" class="form-control" value="<?php echo get_option('_wpdm_license_key'); ?>"
                       name="_wpdm_license_key"/></div>

        <?php if (!get_option('_wpdm_license_key')) { ?>
            <!--
            <tr><td colspan="2">
            <br/><br/><a target="_blank" href='http://www.wpdownloadmanager.com/?task=buynow&package=3'>Buy Now / only 19.5 usd</a>
            <br>
            <br>
            <b>Register Your `Download Manager` Copy:</b> <br>
            <br>
            <div id="regc">
            <table cellpadding="5">
            <tr><td>Username: </td><td><input style="width: 300px;" type="text" name="username" id="uname"></td></tr>
            <tr><td>Email: </td><td><input type="text" style="width: 300px;" name="email" id="email"></td></tr>
            <tr><td>Password: </td><td><input type="password" style="width: 300px;" name="password" id="password"></td></tr>
            <tr><td>Confirm Password: </td><td><input type="password" style="width: 300px;" name="cpassword" id="cpassword"></td></tr>
            <tr><td></td><td><input class="button-secondary" style="padding: 7px 10px;" type="button" name="register" value="Register" id="cpassword"></td></tr>
            </table>
            </div>

            </td></tr>
            -->
        <?php } ?>
    </div>
<?php
}


function isAjax()
{
    return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;
}

function check_license()
{
    if ($_SERVER['HTTP_HOST'] == 'localhost') return true;
    if (!isAjax()) {
        if (!is_valid_license_key()) {
            $time = (int)get_option('settings_ok');
            if ($time > time())
                echo "
        <div id=\"warning\" class=\"error fade\"><p>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b> 
        </div>
        ";
            else
                echo "
        <div id=\"warning\" class=\"error fade\"><p>
        Trial period for <b>Download Manager</b> is expired.<br/>
        Please enter a valid <a href='edit.php?post_type=wpdmpro&page=settings&tab=license'>license key</a> for <b>Download Manager</b> to reactivate it.<br/>
        <a href='http://www.wpdownloadmanager.com/'>Buy your copy now only at 45.00 usd</a>
        </div>
        ";
        }
    }
}

function addusercolumn()
{
    ?>
    <script type="text/javascript">
        jQuery(function () {

            /*jQuery('#role').after('<th>WPDM Stats</th>');
             jQuery('tfoot .column-role').after('<th>WPDM Stats</th>');*/

            jQuery('table.users tbody tr').each(function (index) {
                var uid = this.id.split('-')[1];
                var cell = jQuery(this).find('td.sports_data');
                jQuery('#' + this.id + ' .row-actions').append(' | <a href="edit.php?post_type=wpdmpro&page=wpdm-stats&type=pvdpu&uid=' + uid + '">Download Stats</a>');
            });

        });
    </script>
<?php
}

function wpdm_remove_tinymce()
{
    if ($_GET['page'] != 'file-manager/add-new-package') return false;
    ?>
    <script language="JavaScript">
        <!--
        tinyMCE.execCommand('mceRemoveControl', false, 'file[description]');
        //-->
    </script>
<?php
}

function wpdm_adminjs()
{
    remove_submenu_page( 'index.php', 'wpdm-welcome' );
    ?>
    <script language="JavaScript">
        <!--
        jQuery(function () {
            jQuery('#TB_closeWindowButton').click(function () {
                tb_remove();
            });

            var title = '';
            var edge = 'left';
            jQuery('.infoicon').css('cursor', 'pointer').mouseover(function () {
                title = this.title;
                this.title = '';
                if (jQuery(this).attr('edge')) edge = jQuery(this).attr('edge');
                else edge = 'left';
                var options = {"content": "<h3>Quick Help!<\/h3><p style=\"font-family:'Segoe UI','Lucida Sans'\">" + title + "<\/p>", "position": {"edge": edge, "align": "center"}};

                if (!options)
                    return;

                options = jQuery.extend(options, {
                    close: function () {
                        /*$.post( ajaxurl, {
                         pointer: 'global_wpdm_dd_option',
                         action: 'dismiss-wpdm-dd-pointer'
                         }); */
                    }
                });

                jQuery(this).pointer(options).pointer('open');

            });
            jQuery('.infoicon').mouseout(function () {
                this.title = title;
                jQuery(this).pointer('close');

            });

        });


        //-->
    </script>

<?php
}

function wdm_ajax_help()
{
    if (isset($_GET['action']) && $_GET['action'] == 'wdm_help') {
        echo remote_post('http://www.wpdownloadmanager.com/' . $_REQUEST['helpfile'] . '/', array("mode" => "help"));
        die();
    }
}

function wpdm_ajax_call_exec()
{
    if (isset($_POST['action']) && $_POST['action'] == 'wpdm_ajax_call') {
        if ($_POST['execute']=='wpdm_getlink')
            wpdm_getlink();
        die();
    }
}

class wpdm_auto_update
{

    public $current_version;

    public $update_path;


    public $plugin_slug;

    public $slug;


    function __construct($current_version, $update_path, $plugin_slug)
    {
        // Set the class public variables
        $this->current_version = $current_version;
        $this->update_path = $update_path;
        $this->plugin_slug = $plugin_slug;
        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);
        // define the alternative API for updating checking

        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
    }

    public function check_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get the remote version
        $remote_version = $this->getRemote_version();

        // If a newer version is available, add the update
        if (version_compare($this->current_version, $remote_version, '<')) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->update_path;
            $obj->package = $this->update_path;
            $transient->response[$this->plugin_slug] = $obj;
        }

        return $transient;
    }

    public function check_info($false, $action, $arg)
    {
        if ($arg->slug === $this->slug) {
            $information = $this->getRemote_information();
            return $information;
        }
        return false;
    }


    public function getRemote_version()
    {
        $key = get_option('_wpdm_license_key');
        $domain = strtolower(str_replace("www.", "", $_SERVER['HTTP_HOST']));
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'version', 'domain' => $domain, 'key' => $key)));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }


    public function getRemote_information()
    {
        $domain = strtolower(str_replace("www.", "", $_SERVER['HTTP_HOST']));
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'info', 'domain' => $domain, 'key' => get_option('_wpdm_license_key', '74242-25662-86362-62837'))));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return unserialize($request['body']);
        }
        return false;
    }

    public function getRemote_license()
    {
        $request = wp_remote_post($this->update_path, array('body' => array('action' => 'license')));
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }
}

function wpdm_activate_au()
{
    $wpdm_plugin_current_version = WPDM_Version;
    $domain = strtolower(str_replace("www.", "", $_SERVER['HTTP_HOST']));
    $key = get_option('_wpdm_license_key');
    $hash = rtrim(base64_encode($domain . '|' . $key), '=');
    $wpdm_plugin_remote_path = 'http://www.wpdownloadmanager.com/?task=wpdm-update&hash=' . $hash;
    $wpdm_plugin_slug = plugin_basename(dirname(__FILE__) . '/download-manager.php');
    new wpdm_auto_update ($wpdm_plugin_current_version, $wpdm_plugin_remote_path, $wpdm_plugin_slug);
}


function wpdm_check_update()
{
    if (get_option('wpdm_update_notice') == 'disabled') return;
    $tmpvar = explode("?", basename($_SERVER['REQUEST_URI']));
    $page = array_shift($tmpvar);
    $latest = get_option('wpdm_latest');
    $latest_check = get_option('wpdm_latest_check');
    $time = time() - intval($latest_check);
    if ($latest == '' || $time > 86400) {

        $latest = remote_get('http://www.wpdownloadmanager.com/wp-content/plugins/download-manager/version.txt');
        update_option('wpdm_latest', $latest);
        update_option('wpdm_latest_check', time());
    }
    $tmpdata = isset($_REQUEST['page'])?explode('/', $_REQUEST['page']):array();
    if (version_compare(WPDM_Version, $latest, '<') == true && ($page == 'plugins.php' || array_shift($tmpdata) == 'file-manager')) {
        if ($page == 'plugins.php')
            echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){       
        jQuery('tr#download-manager').addClass('update').after('<tr class="plugin-update-tr"><td colspan=3 class="plugin-update colspanchange"><div style="border-radius:3px;background:#BB4F06;margin:7px;border-top:0px;padding:5px 15px;color:#fff"><b>There is a new version of Download Manager available. <a href="http://www.wpdownloadmanager.com/wordpress-download-manager-change-log/#{$latest}" style="color:#fff" target=_blank>View Version {$latest} Details &#187;</a></b></div></td></tr>');
      });
      </script>
NOTICE;
        else {
            echo <<<NOTICE
     
        <div class="updated" style="border:0px;border-radius:3px;background:#BB4F06;margin:5px 0px;padding:5px 15px;color:#fff"><b>There is a new version of Download Manager available. <a href="http://www.wpdownloadmanager.com/wordpress-download-manager-change-log/#{$latest}" style="color:#fff" target=_blank>View Version {$latest} Details &#187;</a></b></div>
      
NOTICE;
        }
    }
}

if (isset($_GET['page'])) {
    if ($_GET['page'] == 'file-manager' || $_GET['page'] == 'file-manager/add-new-package') {
        add_action("admin_footer", "check_license");
        //add_action('admin_notices', 'wpdm_check_update');
    }
}

/**/


/**
 * Fontend style at tinymce
 */
if (!function_exists('wpdm_frontend_css')) {
    function wpdm_frontend_css($wp)
    {
        $wp .= ',' . get_bloginfo('stylesheet_url');
        return $wp;
    }
}


if (!isset($_REQUEST['P3_NOCACHE'])) {

include(dirname(__FILE__) . "/hooks.php");

$files = scandir(dirname(__FILE__) . '/modules/');
    foreach ($files as $file) {
        $tmpdata = explode(".", $file);
        if ($file != '.' && $file != '..' && !@is_dir($file) && end($tmpdata) == 'php')
            include(dirname(__FILE__) . '/modules/' . $file);
    }
}
 


  
