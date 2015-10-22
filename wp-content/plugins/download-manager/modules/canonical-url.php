<?php

/**
* custom url
*/         
/*
// flush_rules() if our rules are not yet included
function wpdm_flush_rules(){
    $rules = get_option( 'rewrite_rules' );            
    if ( ! isset( $rules['('.get_option('__wpdm_purl_base','download').')/([^\/]*)$'] )||! isset( $rules['('.get_option('__wpdm_curl_base','downloads').')/([^\/]*)$'] ) ) {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
        flush_rewrite_rules();
    }
    
} 

// Adding a new rule
function wpdm_insert_rewrite_rules( $rules )
{
    $newrules = array();    
    $newrules['('.get_option('__wpdm_purl_base','download').')/([^\/]*)$'] = 'index.php?'.get_option('__wpdm_purl_base','download').'=$matches[2]';
    $newrules['('.get_option('__wpdm_curl_base','downloads').')/([^\/]*)$'] = 'index.php?'.get_option('__wpdm_curl_base','downloads').'=$matches[2]';        
    return $newrules + $rules;
}

// Adding the id var so that WP recognizes it
function wpdm_insert_query_vars( $vars )
{   
    array_push($vars, get_option('__wpdm_purl_base','download'));        
    array_push($vars, get_option('__wpdm_curl_base','downloads'));        
        
    return $vars;
}

function wpdm_select_base_page(){        
    global $wp_query;
    $uri = "wpdmbase".$_SERVER['REQUEST_URI'];     
    if(!strpos($uri,"/".get_option('__wpdm_purl_base','download')."/")&&!strpos($uri,"/".get_option('__wpdm_curl_base','downloads')."/")) return;     
    $_GLOBAL['wpdmpro'] = $_REQUEST['wpdmpro'] = $_GET['wpdmpro'] = 'wpdmpro';    
    //$_GLOBAL['pagename'] = $_REQUEST['pagename'] = $_GET['pagename'] = $pages[0]->post_name;     
}

function wpdm_custom_page_template() {
    global $wpdb, $wp_query, $package, $wpdm_package;    
    
    extract($wpdm_package,EXTR_PREFIX_ALL,"wpdm_package");    
    
    if(get_option('_wpdm_custom_template')==1){
    if(file_exists(TEMPLATEPATH . '/wpdm-package.php'))                                    
    include(TEMPLATEPATH . '/wpdm-package.php');
    else    
    include(WPDM_BASE_DIR . 'templates/wpdm-package.php');
    } else if(file_exists(TEMPLATEPATH .'/'. get_option('_wpdm_custom_template'))) 
    include(TEMPLATEPATH .'/'. get_option('_wpdm_custom_template'));
    
    exit;
}


function wpdm_set_custom_page_template($template) {
  global $wp_query;   
  if ($wp_query->query_vars[get_option('__wpdm_purl_base','download')]!='') {
    if(file_exists(TEMPLATEPATH .'/'. get_option('_wpdm_custom_template','page.php'))) 
    $template = TEMPLATEPATH .'/'. get_option('_wpdm_custom_template');         
  }
  return $template;
}
add_filter('single_template','wpdm_set_custom_page_template',0);

function wpdm_custom_category_template() {
    global $wpdb, $wp_query, $package, $wpdm_package;    
    $wpdm_package['title'] = stripcslashes($wpdm_package['title']);
    $wpdm_package['download_link'] = DownloadLink($wpdm_package);
    $wpdm_package['link_url'] = site_url('/?download=1&');        
    $dkey = is_array($wpdm_package['files'])?md5(serialize($wpdm_package['files'])):md5($wpdm_package['files']);
    $wpdm_package['download_url'] = wpdm_download_url($wpdm_package,'');
    if(wpdm_is_download_limit_exceed($wpdm_package['id'])){
      $wpdm_package['download_url'] = '#';
      $wpdm_package['link_label'] = __msg('DOWNLOAD_LIMIT_EXCEED');
    }
    $wpdm_package['more_previews'] = get_wpdm_meta($wpdm_package['id'],'more_previews');
    $wpdm_package['individual_download'] = get_wpdm_meta($wpdm_package['id'],'individual_download');
    $wpdm_package = apply_filters('custom_page_template_vars', $wpdm_package);
    $wpdm_package['files'] = unserialize($wpdm_package['files']);    
    extract($wpdm_package,EXTR_PREFIX_ALL,"wpdm_package");    
    
    if(file_exists(TEMPLATEPATH . '/wpdm-category.php'))                                    
    include(TEMPLATEPATH . '/wpdm-category.php');
    else    
    include(WPDM_BASE_DIR . 'templates/wpdm-category.php');
    exit;
}



function wpdm_custom_popup_template(){
    global $wpdb, $wp_query, $package, $wpdm_package;
    $pid = (int)$_GET[get_option('__wpdm_purl_base','download')];
    $file = $wpdb->get_row("select * from {$wpdb->prefix}ahm_files where id='$pid'",ARRAY_A);
    $tpldata = maybe_unserialize(get_option("_fm_page_templates")); 
    $file['page_template'] = $tpldata[$file['page_template']]['content'];
    $file = apply_filters('wdm_pre_render_page', $file);    
    include(WPDM_BASE_DIR . 'download-popup.php');
    exit;
}

function wpdm_create_url_key($pid, $package){
    $key = wpdm_url_key($package['title']);    
    $key = $_POST['url_key']!=''?$_POST['url_key']:$key;
    $key = sanitize_title($key);
    add_wpdm_meta($pid,'url_key',strtolower($key), true);
}
function wpdm_update_url_key($pid, $package){
    $key = wpdm_url_key($package['title']);    
    $key = $_POST['url_key']!=''?$_POST['url_key']:$key;
    $key = sanitize_title($key);    
    update_wpdm_meta($pid,'url_key',strtolower($key), true);
}
 

function wpdm_url_key($str)
{
    if(function_exists('mb_convert_encoding')&&$str !== mb_convert_encoding( mb_convert_encoding($str, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32') )
        $str = mb_convert_encoding($str, 'UTF-8');
    $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
    $str = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $str);
    $str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
    $str = preg_replace(array('`[^a-z0-9]`i','`[-]+`'), '-', $str);
    $str = strtolower( trim($str, '-') );
    $str = sanitize_title($str);
    return $str;
}



function wpdm_flat_url($data){
    if(!$data['id']) return;
    $link_label = $data['title']?$data['title']:__('View Details &#187;','wpdmpro');          
    $key = get_wpdm_meta($data[id],'url_key'); //?$data['url_key']:get_wpdm_meta($data[id],'url_key');
    $key = $key?$key:$data['id'];
    if(get_option('permalink_structure'))
    $data['page_link'] = "<a href='".site_url("/".get_option('__wpdm_purl_base','download')."/{$key}/")."'>$link_label</a>";
    else
    $data['page_link'] = "<a href='".site_url("/?wpdmpro=wpdmpro&".get_option('__wpdm_purl_base','download')."={$key}")."'>$link_label</a>";
    
    if(get_option('permalink_structure'))
    $data['page_url'] = site_url("/".get_option('__wpdm_purl_base','download')."/{$key}/"); 
    else
    $data['page_url'] = site_url("/?wpdmpro=wpdmpro&".get_option('__wpdm_purl_base','download')."={$key}");
    
    $data['popup_link'] = "<a href='".site_url("/?wpdmpro=wpdmpro&".get_option('__wpdm_purl_base','download')."={$data['id']}&mode=popup")."'  class='popup-link' >$link_label</a>";
    return $data;
}

function convert_url_key_to_pid(){
    global $wpdb, $wp_query,$wpdm_package;      
    if(!isset($wp_query->query_vars[get_option('__wpdm_purl_base','download')])||$wp_query->query_vars[get_option('__wpdm_purl_base','download')]==''||is_numeric($wp_query->query_vars[get_option('__wpdm_purl_base','download')])) return;
    $val = urldecode($wp_query->query_vars[get_option('__wpdm_purl_base','download')]);        
    $key = $wpdb->get_var("select pid from {$wpdb->prefix}ahm_filemeta where value='$val' or value='".$wp_query->query_vars[get_option('__wpdm_purl_base','download')]."'");  
    if($key) $wp_query->query_vars[get_option('__wpdm_purl_base','download')] = $key;     
    $wp_query->query_vars[get_option('__wpdm_purl_base','download')] = $wp_query->query_vars[get_option('__wpdm_purl_base','download')]?$wp_query->query_vars[get_option('__wpdm_purl_base','download')]:$val;    
    $wpdm_package = wpdm_get_package($wp_query->query_vars[get_option('__wpdm_purl_base','download')]);
     
}

function update_metas(){
    return true;
    global $wpdb, $wp_query, $wpdm_package;    
    $pid = $wp_query->query_vars[get_option('__wpdm_purl_base','download')];        
    $desc = get_wpdm_meta($pid,'meta_description');
    if(!$desc)
    $desc = array_shift(explode(".",strip_tags($wpdm_package['description'])));
    $btitle = get_bloginfo('name');
    $content = ob_get_contents();
    ob_clean();
   
    $content = preg_replace("/<title>([^<]*)<\/title>/i","<title>{$wpdm_package['title']} | {$btitle}</title>",$content);
    $content = preg_replace("/<meta*?name=\"description\"*?>/i",'<meta name="description" value="'.$desc.'" />',$content);
    if(!strpos($content, '<meta name="description"'))
    $content = preg_replace("/<\/title>/i",'</title>'."\r\n".'<meta name="description" value="'.$desc.'" />',$content);    
    echo ($content);
}  

function wpdm_ptitle($title){
    global $wpdm_package, $wp_query, $post;         
    $wpdm_package['title'] = stripcslashes($wpdm_package['title']);    
    if($wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]){
    $categories = maybe_unserialize(get_option("_fm_categories",true));
    $category = $categories[$wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]];
    $post->post_title = $category['title'];
    return $category['title'];}  
    
    if($wp_query->query_vars[get_option('__wpdm_purl_base','download')]!=''){    
    $post->post_title = $wpdm_package['title'];
    return $wpdm_package['title']?$wpdm_package['title']:$title;
    }
    return $title;
}

function wpdm_page_title($title){
    //return "abc";    
    global $wpdm_package, $wp_query, $post;        
    $wpdm_package['title'] = stripcslashes($wpdm_package['title']);
    if($wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]){
    $categories = maybe_unserialize(get_option("_fm_categories",true));
    $category = $categories[$wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]];
    $post->post_title = $category['title'];
    return $category['title'];}  
    if($wp_query->query_vars[get_option('__wpdm_purl_base','download')]!=''){    
    $post->post_title = $wpdm_package['title'];
    return $wpdm_package['title']?$wpdm_package['title']:$title;
    }
    return $title;
}

function wpdm_meta_desc($desc){    
    global $wpdm_package, $wp_query;    
    if($wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]==''&&$wp_query->query_vars[get_option('__wpdm_purl_base','download')]=='')
    return $desc;
    if($wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]){
    $categories = maybe_unserialize(get_option("_fm_categories",true));
    $category = $categories[$wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]];
    return array_shift(explode(".", $category['description']));} 
    if(!$wpdm_package)
    return $desc;
    return array_shift(explode(".",$wpdm_package['description']));
}

function wpdm_canonical_url($canonical_url, $post = null, $leaveme = null){
   global $wpdm_package, $wp_query;  
    
   if(!isset($wp_query->query_vars[get_option('__wpdm_curl_base','downloads')])&&!isset($wp_query->query_vars[get_option('__wpdm_purl_base','download')]))
    return $canonical_url;
   if($wp_query->query_vars[get_option('__wpdm_purl_base','download')]!=''){
    $key = get_wpdm_meta($wpdm_package['id'],'url_key');
    $key = $key?$key:$wpdm_package['id'];
    if(get_option('permalink_structure'))
    return site_url("/".get_option('__wpdm_purl_base','download')."/{$key}/"); 
    else
    return site_url("/?wpdmpro=wpdmpro&".get_option('__wpdm_purl_base','download')."={$key}");
   }
   if(isset($wp_query->query_vars[get_option('__wpdm_curl_base','downloads')])&&$wp_query->query_vars[get_option('__wpdm_curl_base','downloads')]!=''){
    $key = $wp_query->query_vars[get_option('__wpdm_curl_base','downloads')];    
    if(get_option('permalink_structure'))
    return site_url("/".get_option('__wpdm_curl_base','downloads')."/{$key}/"); 
    else
    return site_url("/?wpdmpro=wpdmpro&".get_option('__wpdm_curl_base','downloads')."={$key}");
   }
   return $canonical_url;
}

function wpdm_breadcrumb($links){
    global $wpdm_package, $wpdm_category; 
    if(isset($wpdm_package['id'])||isset($wpdm_category['id'])) {
        if(isset($wpdm_package['id'])){
        $new_links[0] = $links[0];
        $new_links[1] = array('text'=>$wpdm_package['title']);
         } else {
    $new_links[0] = $links[0];
        $new_links[1] = array('text'=>$wpdm_category['title']);
       }
        return $new_links;
    }
    
    return $links;
}

 

//if(preg_match("/".get_option('__wpdm_purl_base','download')."\/([^\/]+)/",$_SERVER['REQUEST_URI'],$matches)&&get_option('_wpdm_custom_template')==1)
//if(preg_match("/".get_option('__wpdm_purl_base','download')."\/([^\/]+)/",$_SERVER['REQUEST_URI'],$matches))
//add_action('template_redirect', 'wpdm_custom_page_template');

//if(preg_match("/".get_option('__wpdm_curl_base','downloads')."\/([^\/]+)/",$_SERVER['REQUEST_URI'],$matches)&&get_option('_wpdm_custom_template')==1)
//add_action('template_redirect', 'wpdm_custom_category_template');

if(isset($_GET['mode'])&&$_GET['mode']=='popup')
add_action('template_redirect', 'wpdm_custom_popup_template');

add_filter( 'post_link','wpdm_canonical_url',10, 3 );
add_filter( 'rewrite_rules_array','wpdm_insert_rewrite_rules' );
add_filter( 'query_vars','wpdm_insert_query_vars' );
add_action( 'wp','wpdm_flush_rules');
register_activation_hook(dirname( __FILE__).'/download-manager.php', 'wpdm_flush_rules' );
//register_deactivation_hook(dirname( __FILE__).'/download-manager.php', 'wpdm_flush_rules' );
add_action('parse_query','convert_url_key_to_pid');
add_action('after_add_package','wpdm_create_url_key',10,2);
add_action('after_update_package','wpdm_update_url_key',10,2);
add_filter("single_post_title","wpdm_ptitle");   
add_filter("aioseop_title_single","wpdm_page_title",9999);    
add_filter("aioseop_description_override","wpdm_meta_desc");
add_filter("aioseop_canonical_url","wpdm_canonical_url");
add_filter('wdm_before_fetch_template','wpdm_flat_url'); 
add_action('init','wpdm_select_base_page'); 

add_filter("wpseo_title","wpdm_page_title",9999);
add_filter("wpseo_metadesc","wpdm_meta_desc");
add_filter( 'wpseo_canonical','wpdm_canonical_url' );
add_filter('wpseo_breadcrumb_links','wpdm_breadcrumb');

*/
