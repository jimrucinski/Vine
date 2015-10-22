<?php


/*
Plugin Name: TurnOffSmartQuotes
Plugin URI:
Description: Stops WordPress from converting your quote symbols into smartquotes. The three lines below stop the smartquote conversion in 1) your post content, 2) your comments, and 3) your post excerpts.
Version: 0.1
Author: Katja Stokley
Author URI:
*/
remove_filter('the_content', 'wptexturize');
remove_filter('comment_text', 'wptexturize');
remove_filter('the_excerpt', 'wptexturize');