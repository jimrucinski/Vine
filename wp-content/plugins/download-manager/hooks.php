<?php


if (is_admin()) {
    add_action('admin_enqueue_scripts', 'wpdm_admin_enqueue_scripts');
    add_action("init", 'wpdm_save_email_template');
    add_action("init", 'wpdm_export_emails');
    add_action("init", 'wpdm_delete_emails');
    add_action("admin_menu", "fmmenu");
    add_action('wp_ajax_wdm_settings', 'wdm_ajax_settings');
    add_action('init', 'wdm_ajax_help');
    add_action('init', 'wpdm_save_template');
    add_action("admin_head", "wpdm_adminjs");
    add_action('admin_head', "addusercolumn");
    add_action('post_submitbox_misc_actions', 'wpdm_download_periods');

    //add_action('init','wpdm_update_package');


    add_action("wp_ajax_quick_add_package","wpdm_save_new_package");
    add_action('wp_ajax_wpdm_category_dropdown', 'wpdm_print_cat_dropdown');

    add_action('wp_ajax_wpdm-install-addon', 'wpdm_install_addon');
    add_action('wp_ajax_delete_package_frontend', 'delete_package_frontend');
    add_action('wp_ajax_get_link_templates', 'wpdm_get_link_templates');
    add_action('wp_ajax_get_page_templates', 'wpdm_get_page_templates');
    add_action('wp_ajax_wpdm_generate_password', 'wpdm_generate_password');
    add_action('wp_ajax_photo_gallery_upload', 'wpdm_check_upload');
    add_action('wp_ajax_wpdm_frontend_file_upload', 'wpdm_frontend_file_upload');
    add_action('wp_ajax_icon_upload', 'wpdm_upload_icon');
    add_wdm_settings_tab("license", "License", 'wpdm_licnese');
    add_action('init', 'wpdm_delete_all_cats');
    add_action("admin_init", "wpdm_import_csv_file");
    add_action("admin_init", "wpdm_import_category_csv_file");
    add_filter("wpdm_export_custom_form_fields", 'wpdm_export_custom_form_fields');
    add_action("wpdm_custom_form_field", 'wpdm_ask_for_custom_data');
    add_action('wp_ajax_template_preview', 'wpdm_template_preview');
    //Activate auto update
    //add_action('init', 'wpdm_activate_au');
    add_action('admin_footer', 'wpdm_check_update');
    add_action('admin_init', 'wpdm_meta_boxes', 0);
    add_action("wp_ajax_wpdm_dimport", "wpdm_dimport");

    add_filter('manage_posts_columns', 'wpdm_columns_th');
    add_action('manage_posts_custom_column', 'wpdm_columns_td', 10, 2);
    add_filter( 'request', 'wpdm_dlc_orderby' );
    add_filter( 'manage_edit-wpdmpro_sortable_columns', 'wpdm_dlc_sortable' );
    add_action('activated_plugin','wpdm_welcome_redirect');



} else {

    /** Short-Codes */
    add_shortcode('wpdm_direct_link', 'wpdm_direct_link');
    add_shortcode("wpdm_package", "wpdm_package_link");
    add_shortcode("wpdm_packages", "wpdm_packages");
    add_shortcode("wpdm_file", "wpdm_package_link_legacy");
    add_shortcode("wpdm_category", "wpdm_category");
    add_shortcode("wpdm_tag", "wpdm_tag");
    add_shortcode("wpdm_frontend", "wpdm_new_file_form_sc");
    add_shortcode("wpdm_login_form", "wpdm_login_form");

    add_shortcode('wpdm-email-2download', 'wpdm_email_2download');
    add_shortcode('wpdm-plus1-2download', 'wpdm_plus1_2download');
    add_shortcode('wpdm-like-2download', 'wpdm_like_2download');
    add_shortcode('wpdm-tweet-2download', 'wpdm_tweet_2download');
    add_shortcode('wpdm-lishare-2download', 'wpdm_lishare_2download');

    add_shortcode('wpdm-all-packages', 'wpdm_all_packages');
    add_shortcode('wpdm_all_packages', 'wpdm_all_packages');
    add_action('wp', 'wpdm_save_new_package');

    /** Actions */
    add_action('wp_enqueue_scripts', 'wpdm_enqueue_scripts');
    add_action("init", 'wpdm_view_countplus');
    add_action("wp_footer", 'wpdm_view_countplusjs',999999);
    add_action("init", 'wpdm_update_client_profile');
    add_action("init", "wpdm_DownloadNow");
    add_action("wp", "wpdm_ajax_call_exec");
    add_action('wp', 'wpdm_do_logout');
    add_action('wp', 'wpdm_update_profile');
    add_action('wp_loaded', 'wpdm_do_login');
    add_action('wp_loaded', 'wpdm_do_register');
    add_action('wpdm_user_logged_in', 'wpdm_user_logged_in');

    //add_action('wp_loaded', 'wpdm_sitemap_xml');



    /** Filters */


    //if (get_option('_wpdm_custom_template') == 0)
    //    add_filter('the_content', 'wpdm_downloadable', 99999);
    //else
    add_filter('the_content', 'wpdm_downloadable');
    add_filter('the_excerpt', 'wpdm_category_page');
    add_filter('the_content', 'wpdm_category_page');

    add_filter('widget_text', 'do_shortcode');
    add_filter("wpdm_render_custom_form_fields", 'wpdm_render_custom_data');



    add_action('init', 'wpdm_check_invpass');
    add_filter('wp_footer', 'wpdm_facebook_like_footer');


    add_filter('pre_get_posts', 'wpdm_tag_query');


}

add_filter('run_ngg_resource_manager', 'wpdm_skip_ngg_resource_manager');
add_action( 'admin_init', 'wpdm_sfb_access');
add_action("init", "wpdm_common_actions");
add_action("init", "wpdm_upload_file");
add_action('save_post', 'wpdm_save_package_data', 999999);
