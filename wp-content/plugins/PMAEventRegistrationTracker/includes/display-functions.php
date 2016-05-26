<?php

/* 
 * display functions for outputting to the screen
 */

function pertAddContent($content){
    if(is_single()){ // will only run on single post pages. Without this it will load on every page. Other options include is_page to load on only "pages"...is_search ... is_archive ... is_singular
        $extra_content = '<p class="twitter-message">Follow me on <a href="http://twitter.com/pma">Twitter</a></p>';
        $content .= $extra_content;
    }
    return $content;
}
add_filter('the_content','pertAddContent');