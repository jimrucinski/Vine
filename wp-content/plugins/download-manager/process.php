<?php

if (!defined('ABSPATH')) die('Error!');

do_action("wpdm_onstart_download", $package);
global $current_user, $dfiles;

$speed = 1024; //in KB - default 1 MB
$speed = apply_filters('wpdm_download_speed', $speed);

get_currentuserinfo();
if (wpdm_is_download_limit_exceed($package['ID'])) wp_die(__msg('DOWNLOAD_LIMIT_EXCEED'));
$files = $package['files'];

$dir = isset($package['package_dir']) ? $package['package_dir'] : '';
if ($dir != '') {
    $dfiles = array();
    $dfiles = wpdm_get_files($dir);

}
$log = new Stats();

$oid = isset($_GET['oid']) ? addslashes($_GET['oid']) : '';
$indsc = 1;
$indsc = isset($_GET['ind']) && get_option('__wpdm_ind_stats') == 0 ? 0 : 1;
if ($indsc && !isset($_GET['nostat']))
    $log->NewStat($package['ID'], $current_user->ID, $oid);

if (count($files) == 0 && count($dfiles) == 0) {
    if (isset($package['sourceurl']) && $package['sourceurl'] != '') {

        if (!isset($package['url_protect']) || $package['url_protect'] == 0 && strpos($package['sourceurl'], '://')) {
            header('location: ' . $package['sourceurl']);
            die();
        }

        $r_filename = basename($package['sourceurl']);
        $r_filename = explode("?", $r_filename);
        $r_filename = $r_filename[0];
        wpdm_download_file($package['sourceurl'], $r_filename, $speed, 1, $package);
        die();
    }

    wpdm_download_data('download-not-available.txt', __('Sorry! Download is not available yet.', "wpdmpro"));
    die();

}

$idvdl = isset($package['individual_file_download']) && isset($_GET['ind']) ? 1 : 0;

if ((count($files) > 1 || count($dfiles) > 1) && !$idvdl) {
    $zipped = get_post_meta($package['ID'], "__wpdm_zipped_file", true);
    if ($zipped == '' || !file_exists($zipped)) {
        $zip = new ZipArchive();
        $zipped = UPLOAD_DIR . sanitize_file_name($package['post_title']) . '-' . $package['ID'] . '.zip';
        if ($zip->open($zipped, ZIPARCHIVE::CREATE) !== TRUE) {
            wpdm_download_data('error.txt', 'Failed to create file. Please make "' . UPLOAD_DIR . '" dir writable..');
            die();
        }

        foreach ($files as $file) {
            $file = trim($file);
            if (file_exists(UPLOAD_DIR . $file)) {
                $fnm = preg_replace("/^[0-9]+?wpdm_/", "", $file);
                $zip->addFile(UPLOAD_DIR . $file, $fnm);
            } else if (file_exists($file))
                $zip->addFile($file, basename($file));
            else if (file_exists(WP_CONTENT_DIR . end($tmp = explode("wp-content", $file)))) //path fix on site move
                $zip->addFile(WP_CONTENT_DIR . end($tmp = explode("wp-content", $file)), basename(WP_CONTENT_DIR . end($tmp = explode("wp-content", $file))));
        }
        if ($dfiles) {
            foreach ($dfiles as $file) {
                $zip->addFile($file, str_replace($dir, '', $file));
            }
        }

        $zip->close();
        update_post_meta($package['ID'], "__wpdm_zipped_file", $zipped);
    }
    wpdm_download_file($zipped, sanitize_file_name($package['post_title']) . '.zip', $speed, 1, $package);
    //@unlink($zipped);
} else {

    //Individual file or single file download section

    $ind = isset($_GET['ind']) ? intval($_GET['ind']) : 0;

    if (strpos($files[$ind], '://')) {

        if (!isset($package['url_protect']) || $package['url_protect'] == 0) {
            header('location: ' . $files[$ind]);

        } else {
            $r_filename = basename($files[$ind]);
            $r_filename = explode("?", $r_filename);
            $r_filename = $r_filename[0];
            wpdm_download_file($files[$ind], $r_filename, $speed, 1, $package);

        }

        die();
    }


    if (is_array($dfiles)) $files = array_merge($files, $dfiles);
    $files[$ind] = trim($files[$ind]);
    if (file_exists(UPLOAD_DIR . $files[$ind]))
        $filepath = UPLOAD_DIR . $files[$ind];
    else if (file_exists($files[$ind]))
        $filepath = $files[$ind];
    else if (file_exists(WP_CONTENT_DIR . end($tmp = explode("wp-content", $files[$ind])))) //path fix on site move
        $filepath = WP_CONTENT_DIR . end($tmp = explode("wp-content", $files[$ind]));
    else {
        wpdm_download_data('file-not-found.txt', 'File not found or deleted from server');
        die();
    }

    //$plock = get_wpdm_meta($file['id'],'password_lock',true);
    //$fileinfo = get_wpdm_meta($package['id'],'fileinfo');

    $filename = basename($filepath);
    $filename = preg_replace("/([0-9]+)[wpdm]*_/", "", $filename);

    wpdm_download_file($filepath, $filename, $speed, 1, $package);
    //@unlink($filepath);

}
do_action("after_downlaod", $package);
die();
?>
