<?php

/* 
 any included css, javascripts, xml, etc is included here.
 */

function pertLoadScripts(){
    //load the css into the head with wp_enqueue_style.
    //plugin_dir_url(__FILE__) retrieves the current path information.
    wp_enqueue_style('pertStyles.css',  plugin_dir_url(__FILE__). 'css/pertStyles.css');//load the css into the head with wp_enqueue_style.
}
add_action('wp_enqueue_scripts','pertLoadScripts');//add_action  = action hook. wp_enqueue_scripts is the action hook that loads the scripts.a