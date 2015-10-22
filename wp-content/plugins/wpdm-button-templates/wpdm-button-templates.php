<?php
/*
Plugin Name: WPDM - Button Templates
Description: Button Styles Link Templates For WPDM
Plugin URI: http://www.wpdownloadmanager.com/
Author: Shaon
Version: 1.0.0
Author URI: http://www.wpdownloadmanager.com/
*/


class WPDM_Button_Templates {

    function __construct(){
        add_action('wp_enqueue_scripts', array($this, 'enqueue_style'));
        add_shortcode('wpdm_button_template', array($this, 'template'));

    }

    function template($params){
        $style = isset($params['style'])?$params['style']:'';
        $id = isset($params['id'])?$params['id']:false;
        $align = "left";
        if(strpos($style,"entered"))  $align = 'center';
        if(strpos($style,"ight"))  $align = 'right';

        if(!$id) return "";
        $package = get_post($id, ARRAY_A);
        $package = wpdm_setup_package_data($package);
        $link_label = get_post_meta($id, '__wpdm_link_label', true);
        $html = <<<HTML
        <div class="w3eden clear" align="$align">
<div class="link-btn {$style}">
    <div class="media">
        <div class="pull-left">[icon]</div>
        <div class="pull-left text-left"><strong class="ptitle">
                [page_link]
            </strong>

            <div style="font-size: 8pt">[download_link] <i style="margin: 4px 0 0 5px;opacity:0.5"
                                                           class="fa fa-th-large"></i> [file_size]
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script>
jQuery(function(){
jQuery('.link-btn a.wpdm-download-link img').after('{$link_label}');
jQuery('.link-btn a.wpdm-download-link img').remove();
});
</script>
HTML;

        return FetchTemplate($html, $package, 'link');

    }

    function enqueue_style(){
        wp_enqueue_style("wpdm-button-templates",plugins_url("wpdm-button-templates/buttons.css"));
    }



}

new WPDM_Button_Templates();
 


