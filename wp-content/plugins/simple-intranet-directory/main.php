<?php
/*
Plugin Name: Simple Intranet Employee Directory
Description: Provides a simple employee directory for your intranet.
Plugin URI: http://www.simpleintranet.org
Description: Provides a simple intranet which includes extended user employee profile data, employee photos, custom fields and out of office alerts.
Version: 3.5
Author: Simple Intranet
Author URI: http://www.simpleintranet.org
License: GPL2

Credit goes Jake Goldman, Avatars Plugin, (http://www.10up.com/) for contributing to this code that allows for user photo uploads.
*/

add_action( 'init', 'si_create_post_type' );
function si_create_post_type() {

load_plugin_textdomain( 'simpleintranet', false, dirname( plugin_basename( __FILE__ ) ). '/languages' ); 	
	
	$capabilities = array(
    'read_post' => 'si_read_profile',
    'publish_posts' => 'si_publish_profile',	
	'edit_post' => 'si_edit_profile',
	'edit_posts' => 'si_edit_profiles',
	'delete_post' => 'si_delete_profile',
	'delete_posts' => 'si_delete_profiles',
	'si_edit_outofoffice' => 'si_edit_outofoffice',
);
	register_post_type( 'si_profile',
		array(
			'labels' => array(
			'name' => __( 'Biographies' ),
			'singular_name' => __( 'Biography' )
			),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),		
		'public' => true,
		'show_ui'=> true,
		'capability_type'=> 'post',
		'exclude_from_search'=>true,
		'capabilities' => $capabilities,
		'menu_position' => 5,
		'has_archive' => true,
	    'rewrite' => array('slug' => 'bios','with_front' => true),
		)
	);

	$role = get_role( 'administrator' );
	$role2 = get_role( 'editor' );
				if ( is_object($role)) {
				$role->add_cap( 'si_read_profile' );
				$role->add_cap( 'si_publish_profile' );
				$role->add_cap( 'si_edit_profile' );
				$role->add_cap( 'si_edit_profiles' );
				$role->add_cap( 'si_delete_profile' );
				$role->add_cap( 'si_delete_profiles' );
				$role->add_cap( 'si_edit_outofoffice' );
			}
			if ( is_object($role2)) {
				$role2->add_cap( 'si_read_profile' );
				$role2->add_cap( 'si_publish_profile' );
				$role2->add_cap( 'si_edit_profile' );
				$role2->add_cap( 'si_edit_profiles' );
				$role2->add_cap( 'si_delete_profile' );
				$role2->add_cap( 'si_delete_profiles' );
				$role2->add_cap( 'si_edit_outofoffice' );
}
}

load_plugin_textdomain( 'simpleintranet', false, dirname( plugin_basename( __FILE__ ) ). '/languages' ); 	

if (get_option( 'legacy_photos')=="No" || get_option( 'legacy_photos')==""){
include dirname(__FILE__) . '/avatars.php';
}
if (get_option( 'legacy_photos')=="Yes"){
include dirname(__FILE__) . '/profiles.php';
}

add_action( 'wp_enqueue_scripts', 'employee_style' );

function employee_style() {
        wp_register_style( 'employee-directory', plugins_url('/css/si_employees.css', __FILE__) );
        wp_enqueue_style( 'employee-directory' );
	if(get_option('si_round_avatars')=='Yes'){
		wp_register_style( 'employee-directory-avatars', plugins_url('/css/si_round_avatars.css', __FILE__) );
		wp_enqueue_style( 'employee-directory-avatars' );
		}
    }

if(is_admin()){
remove_action("admin_color_scheme_picker", "admin_color_scheme_picker");
 }

function si_add_contactmethod( $contactmethods ) {
  unset($contactmethods['aim']);
  unset($contactmethods['jabber']);
  unset($contactmethods['yim']);
  $contactmethods['twitter'] = 'Twitter';  
  $contactmethods['facebook'] = 'Facebook';
  $contactmethods['linkedin'] = 'LinkedIn';
  $contactmethods['googleplus'] = 'Google+';
  return $contactmethods;
}
add_filter('user_contactmethods','si_add_contactmethod',10,1);

add_shortcode("employees", "si_contributors");
   
// OUT OF OFFICE DEFAULTS and CUSTOM FIELD LABELS
add_action('admin_init', 'office_text_default');

function office_text_default($defaults) {
	global $current_user,$letin ;	
     $user_id = $current_user->ID;
	 $letin=0;
	$expirytext= get_the_author_meta('expirytext', $user_id); 
	$officetext=get_the_author_meta('officenotification', $user_id); 
	if ($officetext==''){
	$out = __('Out of the office.','simpleintranet');
	update_user_meta( $user_id, 'officenotification', $out ); 	
	}
	if ($expirytext==''){
	$exptext = __('Back in ','simpleintranet');
	update_user_meta( $user_id, 'expirytext', $exptext ); 	
	}
	return $defaults;	
	// ADD CUSTOM LABEL OPTIONS
add_option('phonelabel', 'Phone: ');
add_option('phoneextlabel', 'Extension: ');
add_option('mobilelabel', 'Mobile: ');
add_option('faxlabel', 'Fax: ');
add_option('custom1label', '');
add_option('custom2label', '');
add_option('custom3label', '');
add_option('profiledetail', '');
add_option('sroles', '');

}

// ALLOW HTML IN BIOGRAPHY

// his is to remove HTML stripping from Author Profile
remove_filter('pre_user_description', 'wp_filter_kses');
// This is to sanitize content for allowed HTML tags for post content
add_filter( 'pre_user_description', 'wp_filter_post_kses' );


//EXTRA PROFILE FIELDS 
function fb_add_custom_user_profile_fields( $user ) {
global $in_out;
?>
	<h3><?php _e('Company Information', 'simpleintranet'); ?></h3>
	<table class="form-table">
    <?php if(current_user_can('administrator') ) { ?>
    <tr>
			<th>
				<label for="profiledetail"><?php _e('Employee Profile Page?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
           
				<input type="checkbox" name="profiledetail" id="profiledetail" value="Yes"  <?php if (get_option( 'profiledetail' )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="profiledetail" ><?php _e('Include a profile page accessible by clicking on the photo or name in the Employee Directory.<br>Note, each person will have a post generated with their name as the title, and saved in the Employees category.<br>', 'simpleintranet'); ?></label>
    <blockquote>Check roles to allow access to detailed profile page; <br /><?php    

    // Get WP Roles
    global $wp_roles;
    $roles = $wp_roles->get_names();
	$savedroles = get_option('sroles');	
    // Generate HTML code
    if(is_array($roles)){
	foreach ($roles as $key=>$value) {  
	?>
    <input type="checkbox" name="savedroles[<?php echo $key;?>]" value="Yes" <?php if(isset($savedroles[$key]) && $savedroles[$key]=="Yes"){ 
	echo 'checked=/"checked/"';	
	} ?> /> <?php echo $value; ?><br />
<?php  
	}
}
?>  
</blockquote>
   <input type="checkbox" name="publicbio" id="publicbio" value="Yes"  <?php if (get_option( 'publicbio' )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> Allow non-logged in users to view detail/biography page (overrides role settings above). <br />
    <input type="checkbox" name="custombio" id="custombio" value="Yes"  <?php if (get_option( 'custombio' )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> Allow each user to create and edit a custom HTML detail/biography page. <br />
     <input type="checkbox" name="hideemail" id="hideemail" value="Yes"  <?php if (get_option( 'hideemail' )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> Hide all e-mails from the Employee Directory. <br />
 
    </td>
		</tr><?php } ?>
		
        <tr>
			<th>
				<label for="hidemyemail"><?php _e('Hide My E-mail?', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="checkbox" name="hidemyemail" id="hidemyemail" value="Yes"  <?php if (get_the_author_meta( 'hidemyemail', $user->ID )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> Check to hide your e-mail from the Employee Directory. <br />
				<span class="description"><?php _e('Choose if you want to hide your e-mail address from the Employee Directory.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        
        <tr>
			<th>
				<label for="company"><?php _e('Company or Division', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="text" name="company" id="company" value="<?php echo esc_attr( get_the_author_meta( 'company', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your division or company.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				<label for="department"><?php _e('Department', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="text" name="department" id="department" value="<?php echo esc_attr( get_the_author_meta( 'department', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your department.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				<label for="title"><?php _e('Title', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="text" name="title" id="title" value="<?php echo esc_attr( get_the_author_meta( 'title', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your title.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        	<tr>
			<th>
				<label for="address"><?php _e('Address', 'simpleintranet'); ?>
			</label></th>
			<td><textarea name="address" rows="4" class="regular-text" id="address"><?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?></textarea>
	      <br />
				<span class="description"><?php _e('Please enter your address.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				<label for="postal"><?php _e('Zip or Postal Code', 'simpleintranet'); ?>
			</label></th>
			<td><input type="text" name="postal" id="postal" value="<?php echo esc_attr( get_the_author_meta( 'postal', $user->ID ) ); ?>" class="regular-text" />
	      <br />
				<span class="description"><?php _e('Please enter your zip or postal code.', 'simpleintranet'); ?></span>
			</td>
		</tr>
         <tr>
			<th>
				 <?php if(current_user_can('administrator') ) { 
				_e('Phone (customize label below):', 'simpleintranet'); ?>
                <input name="phonelabel" type="text" class="regular-text" id="phonelabel" value="<?php echo get_option( 'phonelabel') ; ?>" size="20" /><?php } else { 				
				$phonelabel= get_option( 'phonelabel');	
				echo $phonelabel;		
				if ($phonelabel==''){
				echo 'Phone: ';
				}
               } 			   
			   ?>
			</th>
			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your direct business phone #.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
					 <?php if(current_user_can('administrator') ) { 
				_e('Phone extension (customize label below):', 'simpleintranet'); ?>
                <input name="phoneextlabel" type="text" class="regular-text" id="phoneextlabel" value="<?php echo get_option( 'phoneextlabel') ; ?>" size="20" /><?php } else { 				
				$phoneextlabel= get_option( 'phoneextlabel');	
				echo $phoneextlabel;		
				if ($phoneextlabel==''){
				echo 'Extension: ';
				}
               } 			   
			   ?></th>
			<td>
				<input type="text" name="phoneext" id="phoneext" value="<?php echo esc_attr( get_the_author_meta( 'phoneext', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your direct phone extension.', 'simpleintranet'); ?></span>
			</td>
		</tr>
         <tr>
			<th>
				 <?php if(current_user_can('administrator') ) { 
				_e('Mobile (customize label below):', 'simpleintranet'); ?>
                <input name="mobilelabel" type="text" class="regular-text" id="mobilelabel" value="<?php echo get_option( 'mobilelabel') ; ?>" size="20" /><?php } else { 				
				$mobilelabel= get_option( 'mobilelabel');	
				echo $mobilelabel;		
				if ($mobilelabel==''){
				echo 'Mobile: ';
				}
               } 			   
			   ?></th>
			<td>
				<input type="text" name="mobilephone" id="mobilephone" value="<?php echo esc_attr( get_the_author_meta( 'mobilephone', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your mobile phone number.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				 <?php if(current_user_can('administrator') ) { 
				_e('Fax (customize label below):', 'simpleintranet'); ?>
                <input name="faxlabel" type="text" class="regular-text" id="faxlabel" value="<?php echo get_option( 'faxlabel') ; ?>" size="20" /><?php } else { 				
				$faxlabel= get_option( 'faxlabel');	
				echo $faxlabel;		
				if ($faxlabel==''){
				echo 'Fax: ';
				}
               } 			   
			   ?></th>
			<td>
				<input type="text" name="fax" id="fax" value="<?php echo esc_attr( get_the_author_meta( 'fax', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your fax number.', 'simpleintranet'); ?></span>
			</td>
		</tr>
         <tr>
			<th>
				<label for="city"><?php _e('City', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your city.', 'simpleintranet'); ?></span>
			</td>
		</tr>
         <tr>
			<th>
				<label for="region"><?php _e('Region, state or province', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="text" name="region" id="region" value="<?php echo esc_attr( get_the_author_meta( 'region', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Please enter your region.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        
         <tr>
			<th>
				<label for="country"><?php _e('Country', 'simpleintranet'); ?>
			</label></th>
			<td>
				
                <select name="country" id="country"  class="regular-text" VALUE="<?php echo esc_attr( get_the_author_meta( 'country', $user->ID ) ); ?>">
 <OPTION VALUE="<?php echo esc_attr( get_the_author_meta( 'country', $user->ID ) ); ?>"><?php echo esc_attr( get_the_author_meta( 'country', $user->ID ) ); ?>
 <OPTION VALUE="<?php _e('Select A Country','simpleintranet');?>"><?php _e('Select A Country','simpleintranet');?>
  <OPTION VALUE="<?php _e('Afghanistan','simpleintranet');?>">Afghanistan
  <OPTION VALUE="<?php _e('Albania','simpleintranet');?>">Albania
  <OPTION VALUE="<?php _e('Algeria','simpleintranet');?>">Algeria
  <OPTION VALUE="<?php _e('American Samoa','simpleintranet');?>">American Samoa
  <OPTION VALUE="<?php _e('Andorra','simpleintranet');?>">Andorra
  <OPTION VALUE="<?php _e('Angola','simpleintranet');?>">Angola
  <OPTION VALUE="<?php _e('Anguilla','simpleintranet');?>">Anguilla
  <OPTION VALUE="<?php _e('Antarctica','simpleintranet');?>">Antarctica
  <OPTION VALUE="<?php _e('Antigua and Barbuda','simpleintranet');?>">Antigua and Barbuda
  <OPTION VALUE="<?php _e('Argentina','simpleintranet');?>">Argentina
  <OPTION VALUE="<?php _e('Armenia','simpleintranet');?>">Armenia
  <OPTION VALUE="<?php _e('Aruba','simpleintranet');?>">Aruba
  <OPTION VALUE="<?php _e('Australia','simpleintranet');?>">Australia
  <OPTION VALUE="<?php _e('Austria','simpleintranet');?>">Austria
  <OPTION VALUE="<?php _e('Azerbaijan','simpleintranet');?>">Azerbaijan
  <OPTION VALUE="<?php _e('Bahamas','simpleintranet');?>">Bahamas
  <OPTION VALUE="<?php _e('Bahrain','simpleintranet');?>">Bahrain
  <OPTION VALUE="<?php _e('Bangladesh','simpleintranet');?>">Bangladesh
  <OPTION VALUE="<?php _e('Barbados','simpleintranet');?>">Barbados
  <OPTION VALUE="<?php _e('Belarus','simpleintranet');?>">Belarus
  <OPTION VALUE="<?php _e('Belgium','simpleintranet');?>">Belgium
  <OPTION VALUE="<?php _e('Belize','simpleintranet');?>">Belize
  <OPTION VALUE="<?php _e('Benin','simpleintranet');?>">Benin
  <OPTION VALUE="<?php _e('Bermuda','simpleintranet');?>">Bermuda
  <OPTION VALUE="<?php _e('Bhutan','simpleintranet');?>">Bhutan
  <OPTION VALUE="<?php _e('Bolivia','simpleintranet');?>">Bolivia
  <OPTION VALUE="<?php _e('Bosnia and Herzegovina','simpleintranet');?>">Bosnia and 
              Herzegovina
  <OPTION VALUE="<?php _e('Botswana','simpleintranet');?>">Botswana
  <OPTION VALUE="<?php _e('Bouvet Island','simpleintranet');?>">Bouvet Island
  <OPTION VALUE="<?php _e('Brazil','simpleintranet');?>">Brazil
  <OPTION VALUE="<?php _e('British Indian Ocean Territory','simpleintranet');?>">
              British Indian Ocean Territory
  <OPTION VALUE="<?php _e('Brunei Darussalam','simpleintranet');?>">Brunei Darussalam
  <OPTION VALUE="<?php _e('Bulgaria','simpleintranet');?>">Bulgaria
  <OPTION VALUE="<?php _e('Burkina Faso','simpleintranet');?>">Burkina Faso
  <OPTION VALUE="<?php _e('Burundi','simpleintranet');?>">Burundi
  <OPTION VALUE="<?php _e('Cambodia','simpleintranet');?>">Cambodia
  <OPTION VALUE="<?php _e('Cameroon','simpleintranet');?>">Cameroon
  <OPTION VALUE="<?php _e('Canada','simpleintranet');?>">Canada
  <OPTION VALUE="<?php _e('Cape Verde','simpleintranet');?>">Cape Verde
  <OPTION VALUE="<?php _e('Cayman Islands','simpleintranet');?>">Cayman Islands
  <OPTION VALUE="<?php _e('Central African Republic','simpleintranet');?>">
             Central African Republic
  <OPTION VALUE="<?php _e('Chad','simpleintranet');?>">Chad
  <OPTION VALUE="<?php _e('Chile','simpleintranet');?>">Chile
  <OPTION VALUE="<?php _e('China','simpleintranet');?>">China
  <OPTION VALUE="<?php _e('Christmas Island','simpleintranet');?>">Christmas Island
  <OPTION VALUE="<?php _e('Cocos (Keeling Islands)','simpleintranet');?>">
             Cocos (Keeling Islands)
  <OPTION VALUE="<?php _e('Colombia','simpleintranet');?>">Colombia
  <OPTION VALUE="<?php _e('Comoros','simpleintranet');?>">Comoros
  <OPTION VALUE="<?php _e('Congo','simpleintranet');?>">Congo
  <OPTION VALUE="<?php _e('Cook Islands','simpleintranet');?>">Cook Islands
  <OPTION VALUE="<?php _e('Costa Rica','simpleintranet');?>">Costa Rica
  <OPTION VALUE="<?php _e('Cote D Ivoire (Ivory Coast)','simpleintranet');?>">
               Cote D Ivoire (Ivory Coast)
  <OPTION VALUE="<?php _e('Croatia (Hrvatska','simpleintranet');?>">Croatia (Hrvatska
  <OPTION VALUE="<?php _e('Cuba','simpleintranet');?>">Cuba
  <OPTION VALUE="<?php _e('Cyprus','simpleintranet');?>">Cyprus
  <OPTION VALUE="<?php _e('Czech Republic','simpleintranet');?>">Czech Republic
  <OPTION VALUE="<?php _e('Denmark','simpleintranet');?>">Denmark
  <OPTION VALUE="<?php _e('Djibouti','simpleintranet');?>">Djibouti
  <OPTION VALUE="<?php _e('Dominican Republic','simpleintranet');?>">Dominican Republic
  <OPTION VALUE="<?php _e('Dominica','simpleintranet');?>">Dominica
  <OPTION VALUE="<?php _e('East Timor','simpleintranet');?>">East Timor
  <OPTION VALUE="<?php _e('Ecuador','simpleintranet');?>">Ecuador
  <OPTION VALUE="<?php _e('Egypt','simpleintranet');?>">Egypt
  <OPTION VALUE="<?php _e('El Salvador','simpleintranet');?>">El Salvador
  <OPTION VALUE="<?php _e('Equatorial Guinea','simpleintranet');?>">Equatorial Guinea
  <OPTION VALUE="<?php _e('Eritrea','simpleintranet');?>">Eritrea
  <OPTION VALUE="<?php _e('Estonia','simpleintranet');?>">Estonia
  <OPTION VALUE="<?php _e('Ethiopia','simpleintranet');?>">Ethiopia
  <OPTION VALUE="<?php _e('Falkland Islands (Malvinas)','simpleintranet');?>">
                  Falkland Islands (Malvinas)
  <OPTION VALUE="<?php _e('Faroe Islands','simpleintranet');?>">Faroe Islands
  <OPTION VALUE="<?php _e('Fiji','simpleintranet');?>">Fiji
  <OPTION VALUE="<?php _e('Finland','simpleintranet');?>">Finland
  <OPTION VALUE="<?php _e('France, Metropolitan','simpleintranet');?>">France, Metropolitan
  <OPTION VALUE="<?php _e('France','simpleintranet');?>">France
  <OPTION VALUE="<?php _e('French Guiana','simpleintranet');?>">French Guiana
  <OPTION VALUE="<?php _e('French Polynesia','simpleintranet');?>">French Polynesia
  <OPTION VALUE="<?php _e('French Southern Territories','simpleintranet');?>">
              French Southern Territories
  <OPTION VALUE="<?php _e('Gabon','simpleintranet');?>">Gabon
  <OPTION VALUE="<?php _e('Gambia','simpleintranet');?>">Gambia
  <OPTION VALUE="<?php _e('Georgia','simpleintranet');?>">Georgia
  <OPTION VALUE="<?php _e('Germany','simpleintranet');?>">Germany
  <OPTION VALUE="<?php _e('Ghana','simpleintranet');?>">Ghana
  <OPTION VALUE="<?php _e('Gibraltar','simpleintranet');?>">Gibraltar
  <OPTION VALUE="<?php _e('Greece','simpleintranet');?>">Greece
  <OPTION VALUE="<?php _e('Greenland','simpleintranet');?>">Greenland
  <OPTION VALUE="<?php _e('Grenada','simpleintranet');?>">Grenada
  <OPTION VALUE="<?php _e('Guadeloupe','simpleintranet');?>">Guadeloupe
  <OPTION VALUE="<?php _e('Guam','simpleintranet');?>">Guam
  <OPTION VALUE="<?php _e('Guatemala','simpleintranet');?>">Guatemala
  <OPTION VALUE="<?php _e('Guinea-Bissau','simpleintranet');?>">Guinea-Bissau
  <OPTION VALUE="<?php _e('Guinea','simpleintranet');?>">Guinea
  <OPTION VALUE="<?php _e('Guyana','simpleintranet');?>">Guyana
  <OPTION VALUE="<?php _e('Haiti','simpleintranet');?>">Haiti
  <OPTION VALUE="<?php _e('Heard and McDonald Islands','simpleintranet');?>">
            Heard and McDonald Islands
  <OPTION VALUE="<?php _e('Honduras','simpleintranet');?>">Honduras
  <OPTION VALUE="<?php _e('Hong Kong','simpleintranet');?>">Hong Kong
  <OPTION VALUE="<?php _e('Hungary','simpleintranet');?>">Hungary
  <OPTION VALUE="<?php _e('Iceland','simpleintranet');?>">Iceland
  <OPTION VALUE="<?php _e('India','simpleintranet');?>">India
  <OPTION VALUE="<?php _e('Indonesia','simpleintranet');?>">Indonesia
  <OPTION VALUE="<?php _e('Iran','simpleintranet');?>">Iran
  <OPTION VALUE="<?php _e('Iraq','simpleintranet');?>">Iraq
  <OPTION VALUE="<?php _e('Ireland','simpleintranet');?>">Ireland
  <OPTION VALUE="<?php _e('Israel','simpleintranet');?>">Israel
  <OPTION VALUE="<?php _e('Italy','simpleintranet');?>">Italy
  <OPTION VALUE="<?php _e('Jamaica','simpleintranet');?>">Jamaica
  <OPTION VALUE="<?php _e('Japan','simpleintranet');?>">Japan
  <OPTION VALUE="<?php _e('Jordan','simpleintranet');?>">Jordan
  <OPTION VALUE="<?php _e('Kazakhstan','simpleintranet');?>">Kazakhstan
  <OPTION VALUE="<?php _e('Kenya','simpleintranet');?>">Kenya
  <OPTION VALUE="<?php _e('Kiribati','simpleintranet');?>">Kiribati
  <OPTION VALUE="<?php _e('Korea (North)','simpleintranet');?>">Korea (North)
  <OPTION VALUE="<?php _e('Korea (South)','simpleintranet');?>">Korea (South)
  <OPTION VALUE="<?php _e('Kuwait','simpleintranet');?>">Kuwait
  <OPTION VALUE="<?php _e('Kyrgyzstan','simpleintranet');?>">Kyrgyzstan
  <OPTION VALUE="<?php _e('Laos','simpleintranet');?>">Laos
  <OPTION VALUE="<?php _e('Latvia','simpleintranet');?>">Latvia
  <OPTION VALUE="<?php _e('Lebanon','simpleintranet');?>">Lebanon
  <OPTION VALUE="<?php _e('Lesotho','simpleintranet');?>">Lesotho
  <OPTION VALUE="<?php _e('Liberia','simpleintranet');?>">Liberia
  <OPTION VALUE="<?php _e('Libya','simpleintranet');?>">Libya
  <OPTION VALUE="<?php _e('Liechtenstein','simpleintranet');?>">Liechtenstein
  <OPTION VALUE="<?php _e('Lithuania','simpleintranet');?>">Lithuania
  <OPTION VALUE="<?php _e('Luxembourg','simpleintranet');?>">Luxembourg
  <OPTION VALUE="<?php _e('Macau','simpleintranet');?>">Macau
  <OPTION VALUE="<?php _e('Macedonia','simpleintranet');?>">Macedonia
  <OPTION VALUE="<?php _e('Madagascar','simpleintranet');?>">Madagascar
  <OPTION VALUE="<?php _e('Malawi','simpleintranet');?>">Malawi
  <OPTION VALUE="<?php _e('Malaysia','simpleintranet');?>">Malaysia
  <OPTION VALUE="<?php _e('Maldives','simpleintranet');?>">Maldives
  <OPTION VALUE="<?php _e('Mali','simpleintranet');?>">Mali
  <OPTION VALUE="<?php _e('Malta','simpleintranet');?>">Malta
  <OPTION VALUE="<?php _e('Marshall Islands','simpleintranet');?>">Marshall Islands
  <OPTION VALUE="<?php _e('Martinique','simpleintranet');?>">Martinique
  <OPTION VALUE="<?php _e('Mauritania','simpleintranet');?>">Mauritania
  <OPTION VALUE="<?php _e('Mauritius','simpleintranet');?>">Mauritius
  <OPTION VALUE="<?php _e('Mayotte','simpleintranet');?>">Mayotte
  <OPTION VALUE="<?php _e('Mexico','simpleintranet');?>">Mexico
  <OPTION VALUE="<?php _e('Micronesia','simpleintranet');?>">Micronesia
  <OPTION VALUE="<?php _e('Moldova','simpleintranet');?>">Moldova
  <OPTION VALUE="<?php _e('Monaco','simpleintranet');?>">Monaco
  <OPTION VALUE="<?php _e('Mongolia','simpleintranet');?>">Mongolia
  <OPTION VALUE="<?php _e('Montserrat','simpleintranet');?>">Montserrat
  <OPTION VALUE="<?php _e('Morocco','simpleintranet');?>">Morocco
  <OPTION VALUE="<?php _e('Mozambique','simpleintranet');?>">Mozambique
  <OPTION VALUE="<?php _e('Myanmar','simpleintranet');?>">Myanmar
  <OPTION VALUE="<?php _e('Namibia','simpleintranet');?>">Namibia
  <OPTION VALUE="<?php _e('Nauru','simpleintranet');?>">Nauru
  <OPTION VALUE="<?php _e('Nepal','simpleintranet');?>">Nepal
  <OPTION VALUE="<?php _e('Netherlands Antilles','simpleintranet');?>">Netherlands Antilles
  <OPTION VALUE="<?php _e('Netherlands','simpleintranet');?>">Netherlands
  <OPTION VALUE="<?php _e('New Caledonia','simpleintranet');?>">New Caledonia
  <OPTION VALUE="<?php _e('New Zealand','simpleintranet');?>">New Zealand
  <OPTION VALUE="<?php _e('Nicaragua','simpleintranet');?>">Nicaragua
  <OPTION VALUE="<?php _e('Nigeria','simpleintranet');?>">Nigeria
  <OPTION VALUE="<?php _e('Niger','simpleintranet');?>">Niger
  <OPTION VALUE="<?php _e('Niue','simpleintranet');?>">Niue
  <OPTION VALUE="<?php _e('Norfolk Island','simpleintranet');?>">Norfolk Island
  <OPTION VALUE="<?php _e('Northern Mariana Islands','simpleintranet');?>">
             Northern Mariana Islands
  <OPTION VALUE="<?php _e('Norway','simpleintranet');?>">Norway
  <OPTION VALUE="<?php _e('Oman','simpleintranet');?>">Oman
  <OPTION VALUE="<?php _e('Pakistan','simpleintranet');?>">Pakistan
  <OPTION VALUE="<?php _e('Palau','simpleintranet');?>">Palau
  <OPTION VALUE="<?php _e('Panama','simpleintranet');?>">Panama
  <OPTION VALUE="<?php _e('Papua New Guinea','simpleintranet');?>">Papua New Guinea
  <OPTION VALUE="<?php _e('Paraguay','simpleintranet');?>">Paraguay
  <OPTION VALUE="<?php _e('Peru','simpleintranet');?>">Peru
  <OPTION VALUE="<?php _e('Philippines','simpleintranet');?>">Philippines
  <OPTION VALUE="<?php _e('Pitcairn','simpleintranet');?>">Pitcairn
  <OPTION VALUE="<?php _e('Poland','simpleintranet');?>">Poland
  <OPTION VALUE="<?php _e('Portugal','simpleintranet');?>">Portugal
  <OPTION VALUE="<?php _e('Puerto Rico','simpleintranet');?>">Puerto Rico
  <OPTION VALUE="<?php _e('Qatar','simpleintranet');?>">Qatar
  <OPTION VALUE="<?php _e('Reunion','simpleintranet');?>">Reunion
  <OPTION VALUE="<?php _e('Romania','simpleintranet');?>">Romania
  <OPTION VALUE="<?php _e('Russian Federation','simpleintranet');?>">Russian Federation
  <OPTION VALUE="<?php _e('Rwanda','simpleintranet');?>">Rwanda
  <OPTION VALUE="<?php _e('S. Georgia and S. Sandwich Isls.','simpleintranet');?>">
         S. Georgia and S. Sandwich Isls.
  <OPTION VALUE="<?php _e('Saint Kitts and Nevis','simpleintranet');?>">Saint Kitts and Nevis
  <OPTION VALUE="<?php _e('Saint Lucia','simpleintranet');?>">Saint Lucia
  <OPTION VALUE="<?php _e('Saint Vincent and The Grenadines','simpleintranet');?>">
         Saint Vincent and The Grenadines
  <OPTION VALUE="<?php _e('Samoa','simpleintranet');?>">Samoa
  <OPTION VALUE="<?php _e('San Marino','simpleintranet');?>">San Marino
  <OPTION VALUE="<?php _e('Sao Tome and Principe','simpleintranet');?>">Sao Tome and Principe
  <OPTION VALUE="<?php _e('Saudi Arabia','simpleintranet');?>">Saudi Arabia
  <OPTION VALUE="<?php _e('Senegal','simpleintranet');?>">Senegal
  <OPTION VALUE="<?php _e('Seychelles','simpleintranet');?>">Seychelles
  <OPTION VALUE="<?php _e('Sierra Leone','simpleintranet');?>">Sierra Leone
  <OPTION VALUE="<?php _e('Singapore','simpleintranet');?>">Singapore
  <OPTION VALUE="<?php _e('Slovak Republic','simpleintranet');?>">Slovak Republic
  <OPTION VALUE="<?php _e('Slovenia','simpleintranet');?>">Slovenia
  <OPTION VALUE="<?php _e('Solomon Islands','simpleintranet');?>">Solomon Islands
  <OPTION VALUE="<?php _e('Somalia','simpleintranet');?>">Somalia
  <OPTION VALUE="<?php _e('South Africa','simpleintranet');?>">South Africa
  <OPTION VALUE="<?php _e('Spain','simpleintranet');?>">Spain
  <OPTION VALUE="<?php _e('Sri Lanka','simpleintranet');?>">Sri Lanka
  <OPTION VALUE="<?php _e('St. Helena','simpleintranet');?>">St. Helena
  <OPTION VALUE="<?php _e('St. Pierre and Miquelon','simpleintranet');?>">
              St. Pierre and Miquelon
  <OPTION VALUE="<?php _e('Sudan','simpleintranet');?>">Sudan
  <OPTION VALUE="<?php _e('Suriname','simpleintranet');?>">Suriname
  <OPTION VALUE="<?php _e('Svalbard and Jan Mayen Islands','simpleintranet');?>">
              Svalbard and Jan Mayen Islands
  <OPTION VALUE="<?php _e('Swaziland','simpleintranet');?>">Swaziland
  <OPTION VALUE="<?php _e('Sweden','simpleintranet');?>">Sweden
  <OPTION VALUE="<?php _e('Switzerland','simpleintranet');?>">Switzerland
  <OPTION VALUE="<?php _e('Syria','simpleintranet');?>">Syria
  <OPTION VALUE="<?php _e('Taiwan','simpleintranet');?>">Taiwan
  <OPTION VALUE="<?php _e('Tajikistan','simpleintranet');?>">Tajikistan
  <OPTION VALUE="<?php _e('Tanzania','simpleintranet');?>">Tanzania
  <OPTION VALUE="<?php _e('Thailand','simpleintranet');?>">Thailand
  <OPTION VALUE="<?php _e('Togo','simpleintranet');?>">Togo
  <OPTION VALUE="<?php _e('Tokelau','simpleintranet');?>">Tokelau
  <OPTION VALUE="<?php _e('Tonga','simpleintranet');?>">Tonga
  <OPTION VALUE="<?php _e('Trinidad and Tobago','simpleintranet');?>">Trinidad and Tobago
  <OPTION VALUE="<?php _e('Tunisia','simpleintranet');?>">Tunisia
  <OPTION VALUE="<?php _e('Turkey','simpleintranet');?>">Turkey
  <OPTION VALUE="<?php _e('Turkmenistan','simpleintranet');?>">Turkmenistan
  <OPTION VALUE="<?php _e('Turks and Caicos Islands','simpleintranet');?>">
       Turks and Caicos Islands
  <OPTION VALUE="<?php _e('Tuvalu','simpleintranet');?>">Tuvalu
  <OPTION VALUE="<?php _e('US Minor Outlying Islands','simpleintranet');?>">
     US Minor Outlying Islands
  <OPTION VALUE="<?php _e('Uganda','simpleintranet');?>">Uganda
  <OPTION VALUE="<?php _e('Ukraine','simpleintranet');?>">Ukraine
  <OPTION VALUE="<?php _e('United Arab Emirates','simpleintranet');?>">
     United Arab Emirates
  <OPTION VALUE="<?php _e('United Kingdom','simpleintranet');?>">United Kingdom
  <OPTION VALUE="<?php _e('United States','simpleintranet');?>">United States
  <OPTION VALUE="<?php _e('Uruguay','simpleintranet');?>">Uruguay
  <OPTION VALUE="<?php _e('Uzbekistan','simpleintranet');?>">Uzbekistan
  <OPTION VALUE="<?php _e('Vanuatu','simpleintranet');?>">Vanuatu
  <OPTION VALUE="<?php _e('Vatican City State','simpleintranet');?>">Vatican City State
  <OPTION VALUE="<?php _e('Venezuela','simpleintranet');?>">Venezuela
  <OPTION VALUE="<?php _e('Viet Nam','simpleintranet');?>">Viet Nam
  <OPTION VALUE="<?php _e('Virgin Islands (British)','simpleintranet');?>">
     Virgin Islands (British)
  <OPTION VALUE="<?php _e('Virgin Islands (US)','simpleintranet');?>">
     Virgin Islands (US)
  <OPTION VALUE="<?php _e('Wallis and Futuna Islands','simpleintranet');?>">
     Wallis and Futuna Islands
  <OPTION VALUE="<?php _e('Western Sahara','simpleintranet');?>">Western Sahara
  <OPTION VALUE="<?php _e('Yemen','simpleintranet');?>">Yemen
  <OPTION VALUE="<?php _e('Yugoslavia','simpleintranet');?>">Yugoslavia
  <OPTION VALUE="<?php _e('Zaire','simpleintranet');?>">Zaire
  <OPTION VALUE="<?php _e('Zambia','simpleintranet');?>">Zambia
  <OPTION VALUE="<?php _e('Zimbabwe','simpleintranet');?>">Zimbabwe
</SELECT>
                <br />
				<span class="description"><?php _e('Please enter your country.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        
        <tr>
			<th>				
                <?php if(current_user_can('administrator') ) { 
				_e('Custom Field #1 Label: ', 'simpleintranet'); ?>
                <input name="custom1label" type="text" class="regular-text" id="custom1label" value="<?php echo get_option( 'custom1label'); ?>" size="20" /><?php } else { 				
				$custom1label= get_option( 'custom1label');	
				echo $custom1label;		
				if ($custom1label==''){
				echo 'Custom Field #1: ';
				}
               } 			   
			   ?>
		  </th>
			<td>
				<input type="text" name="custom1" id="custom1" value="<?php echo get_the_author_meta( 'custom1', $user->ID ) ; ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter a custom field that will show up in the Employee Directory if populated. HTML code is OK, but use carefully.', 'simpleintranet'); ?></span>
			</td>
		</tr>
          <tr>
			<th>
				 <?php if(current_user_can('administrator') ) { 
				_e('Custom Field #2 Label: ', 'simpleintranet'); ?>
                <input name="custom2label" type="text" class="regular-text" id="custom2label" value="<?php echo get_option( 'custom2label') ; ?>" size="20" /><?php } else { 				
				$custom2label= get_option( 'custom2label');	
				echo $custom2label;		
				if ($custom2label==''){
				echo 'Custom Field #2: ';
				}
               } 			   
			   ?>
			</th>
			<td>
				<input type="text" name="custom2" id="custom2" value="<?php echo get_the_author_meta( 'custom2', $user->ID ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter a custom field that will show up in the Employee Directory if populated. HTML code is OK, but use carefully.', 'simpleintranet'); ?></span>
			</td>
		</tr>
          <tr>
			<th>
				 <?php if(current_user_can('administrator') ) { 
				_e('Custom Field #3 Label: ', 'simpleintranet'); ?>
                <input name="custom3label" type="text" class="regular-text" id="custom3label" value="<?php echo get_option( 'custom3label'); ?>" size="20" /><?php } else { 				
				$custom3label= get_option( 'custom3label');	
				echo $custom3label;		
				if ($custom3label==''){
				echo 'Custom Field #3: ';
				}
               } 			   
			   ?>
			</th>
			<td>
				<input type="text" name="custom3" id="custom3" value="<?php echo get_the_author_meta( 'custom3', $user->ID ) ; ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter a custom field that will show up in the Employee Directory if populated. HTML code is OK, but use carefully.', 'simpleintranet'); ?></span>
			</td>
		</tr>
          
         <tr>
			<th>
				<label for="si_office_status"><?php _e('Out of the office? ', 'simpleintranet'); ?></label> </th><td>            
	<?php 				
	global $in_out, $officeexpire,  $current_user;	
	if (!current_user_can( 'administrator' )) {
    $user_id = $current_user->ID;
	}
	if (current_user_can( 'administrator' ) && $_GET['user_id']!='' ) {
    $user_id = $_GET['user_id'];
	}
	if (current_user_can( 'administrator' ) && $_GET['user_id']=='' ) {
    $user_id = $current_user->ID;
	}
//	$gofs = get_option( 'gmt_offset' ); // get WordPress offset in hours
//	$tz = date_default_timezone_get(); // get current PHP timezone
//	date_default_timezone_set('Etc/GMT'.(($gofs < 0)?'+':'').-$gofs); // set the PHP timezone to match WordPress
	
	$right_now=current_time( 'timestamp' );
	
	$in_out= esc_attr( get_the_author_meta( 'si_office_status', $user_id, true) ); 
	$ignore2= esc_attr( get_the_author_meta( 'si_office_ignore', $user_id, true) ); 
	if($_GET['si_ignore']){
	$dismiss=$_GET['si_ignore'];
	update_user_meta($user_id, 'si_office_status', $dismiss);   
	}
	$officeexpire= get_the_author_meta( 'officeexpire', $user_id ) ;
	if($officeexpire!=''){
	$expire_string = implode("-", $officeexpire);
	}
	$officeexpire_unix1=strtotime($expire_string);
	$officeexpire_unix=$officeexpire_unix1+$gmt;	
		
	update_user_meta($user_id, 'officeexpire_unix',$officeexpire_unix ); 
	$set_expiry= esc_attr( get_the_author_meta( 'expiry', $user_id ) ); 	
				
	if($set_expiry=="Yes"){
	if($officeexpire_unix<=$right_now ){
	$in_out='false';
	update_user_meta($user_id, 'si_office_status','false'); 
	}
	if($officeexpire_unix>=$right_now ){
	$in_out='true';
	update_user_meta($user_id, 'si_office_status','true'); 
	}
	}
	
	?> 
                 <select name="si_office_status" id="si_office_status">
                    <option value="false" <?php if ($in_out == "" || $in_out=="false" ) { 
					echo "selected=\"selected\""; 
					}?>>No</option>
                    <option value="true" <?php if ($in_out=="true" ) { echo "selected=\"selected\"";
					}?>>Yes</option>   
                     </select>             
                <br />
                Hide Dashboard out of the office reminder notice? <select name="si_office_ignore" id="si_office_ignore">
                    <option value="false" <?php if ($ignore2=="false" || $ignore2=="") { 
					echo "selected=\"selected\""; 
					}?>>No</option>
                    <option value="true" <?php if ($ignore2=="true" ) { echo "selected=\"selected\"";
					}?>>Yes</option>   
                     </select>   <br />
				<span class="description"><?php _e('Update out of the office status.', 'simpleintranet'); ?> </span>
			</td>
		</tr>
          <tr>
			<th>
				<label for="expiry"><?php _e('Out of the office expiry?', 'simpleintranet'); ?>
			</label></th>
            
			<td><input type="checkbox" name="expiry" id="expiry" value="Yes"  <?php if (get_the_author_meta( 'expiry', $user_id )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="expiry" ><?php _e('Check to set an expiry date for the out of the office alert: ', 'simpleintranet'); ?></label><?php
echo '<input type="date" id="datepicker" name="officeexpire[datepicker]" value="'.$expire_string.'" class="example-datepicker" />';

/**
 * Enqueue the date picker
 */
function enqueue_date_picker2(){
                wp_enqueue_script(
			'field-date-js',
			'js/Field_Date.js',
			array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
			time(),
			true
		);	
		wp_enqueue_style( 'jquery-ui-datepicker' );
}
		  ?><br />
				<span class="description"><?php _e('Enter the day you are back in when the alert will be turned off.', 'simpleintranet'); ?></span>
                <br /><input type="text" name="expirytext" id="expirytext" value="<?php echo esc_attr( get_the_author_meta( 'expirytext', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter custom back in text above, assuming activated.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        
         <tr>
			<th>
				<label for="officenotification"><?php _e('Out of the office text', 'simpleintranet'); ?>
			</label></th>
			<td>
				<input type="text" name="officenotification" id="officenotification" value="<?php echo esc_attr( get_the_author_meta( 'officenotification', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e('Enter custom out of the office notification text here, assuming activated.', 'simpleintranet'); ?></span>
			</td>
		</tr>
        <tr>
			<th>
				<label for="exclude"><?php _e('Exclude from Employee Directory?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
				<input type="checkbox" name="exclude" id="exclude" value="Yes"  <?php if (get_the_author_meta( 'exclude', $user->ID )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="exclude" ><?php _e('Check if you want to exclude from the Employee Directory and Employee Widget.', 'simpleintranet'); ?></label></td>
		</tr>
        <tr>
			<th>
				<label for="includebio"><?php _e('Include Biography in Directory?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
				<input type="checkbox" name="includebio" id="includebio" value="Yes"  <?php if (get_the_author_meta( 'includebio', $user->ID )=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="includebio" ><?php _e('Check if you want to include a biography in the Employee Directory.', 'simpleintranet'); ?></label></td>
		</tr>
       <?php if(current_user_can('administrator') ) { ?> 
         <tr>
			 <th>
				<label for="includebio"><?php _e('Round User Photos?', 'simpleintranet'); ?>
			</label></th>
			 <td align="left">  
            <input type="checkbox" name="si_round_avatars" id="si_round_avatars" value="Yes"  <?php if (get_option( 'si_round_avatars')=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="si_round_avatars" ><?php _e('Make all user photos/avatars round vs square (Note: your theme may not support this).', 'simpleintranet'); ?></label><br />
			 </td></tr>
	  <tr>
			<th>
				<label for="includebio"><?php _e('Use Legacy User Photos?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
				<input type="checkbox" name="legacy_photos" id="legacy_photos" value="Yes"  <?php if (get_option( 'legacy_photos')=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="legacy_photos" ><?php _e('Use legacy photo system in the Employee Directory vs current upgraded method.', 'simpleintranet'); ?></label></td>
		</tr>         
       <tr>
			<th>
				<label for="phoneaustralia"><?php _e('Australian Phone Format?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
				<input type="checkbox" name="phoneaustralia" id="phoneaustralia" value="Yes"  <?php if (get_option( 'phoneaustralia')=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="phoneaustralia" ><?php _e('Use Australian phone format for Employee Directory.', 'simpleintranet'); ?></label></td>
		</tr>
         <tr>
			<th>
				<label for="phonesouthafrica"><?php _e('South African Phone Format?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
				<input type="checkbox" name="phonesouthafrica" id="phonesouthafrica" value="Yes"  <?php if (get_option( 'phonesouthafrica')=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="phonesouthafrica" ><?php _e('Use South African phone format for Employee Directory.', 'simpleintranet'); ?></label></td>
		</tr>
       <tr>
			<th>
				<label for="phoneeurope"><?php _e('European Phone Format?', 'simpleintranet'); ?>
			</label></th>
			<td align="left">
				<input type="checkbox" name="phoneeurope" id="phoneeurope" value="Yes"  <?php if (get_option( 'phoneeurope')=="Yes"){
		echo "checked=\"checked\"";
	} ?>> <label for="phoneeurope" ><?php _e('Use European phone format for Employee Directory.', 'simpleintranet'); ?></label></td>
		</tr>
       
     
       <?php } ?>
        
        
	</table>
<?php }
function fb_save_custom_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return FALSE;
	update_user_meta( $user_id, 'company', $_POST['company'] );
	update_user_meta( $user_id, 'department', $_POST['department'] );
	update_user_meta( $user_id, 'title', $_POST['title'] );
	update_user_meta( $user_id, 'address', $_POST['address'] );
	update_user_meta( $user_id, 'postal', $_POST['postal'] );
	update_user_meta( $user_id, 'phone', $_POST['phone'] );
	update_user_meta( $user_id, 'phoneext', $_POST['phoneext'] );
	update_user_meta( $user_id, 'mobilephone', $_POST['mobilephone'] );
	update_user_meta( $user_id, 'fax', $_POST['fax'] );
	update_user_meta( $user_id, 'city', $_POST['city'] );
	update_user_meta( $user_id, 'region', $_POST['region'] );
	update_user_meta( $user_id, 'country', $_POST['country'] );
	update_user_meta( $user_id, 'hidemyemail', $_POST['hidemyemail'] );
	if(current_user_can('administrator') ) { 
	update_option('phonelabel', $_POST['phonelabel'] );	
	update_option('phoneextlabel', $_POST['phoneextlabel'] );
	update_option('mobilelabel', $_POST['mobilelabel'] );
	update_option('faxlabel', $_POST['faxlabel'] );
	update_option('custom1label', $_POST['custom1label'] );
	update_option('custom2label', $_POST['custom2label'] );
	update_option('custom3label', $_POST['custom3label'] );
	update_option('profiledetail', $_POST['profiledetail'] );
	update_option('hideemail', $_POST['hideemail'] );
	update_option('publicbio', $_POST['publicbio'] );
	update_option('custombio', $_POST['custombio'] );
	update_option('legacy_photos', $_POST['legacy_photos'] );
	update_option('phoneaustralia', $_POST['phoneaustralia'] );
	update_option('phonesouthafrica', $_POST['phonesouthafrica'] );
	update_option('phoneeurope', $_POST['phoneeurope'] );
	update_option('si_round_avatars', $_POST['si_round_avatars'] );
	update_option('sroles', $_POST['savedroles']);
	}
	update_user_meta( $user_id, 'custom1', $_POST['custom1'] );	
	update_user_meta( $user_id, 'custom2', $_POST['custom2'] );	
	update_user_meta( $user_id, 'custom3', $_POST['custom3'] );
	
	update_user_meta( $user_id, 'si_office_status', $_POST['si_office_status'] );
	update_user_meta( $user_id, 'si_office_ignore', $_POST['si_office_ignore'] );
	update_user_meta( $user_id, 'expiry', $_POST['expiry'] );
	update_user_meta( $user_id, 'expirytext', $_POST['expirytext'] );
	update_user_meta( $user_id, 'officeexpire', $_POST['officeexpire'] );
	update_user_meta( $user_id, 'officenotification', $_POST['officenotification'] );
	update_user_meta( $user_id, 'exclude', $_POST['exclude'] );
	update_user_meta( $user_id, 'includebio', $_POST['includebio'] );
	update_user_meta( $user_id, 'employeeofmonth', $_POST['employeeofmonth'] );	
	update_user_meta( $user_id, 'employeeofmonthtext', $_POST['employeeofmonthtext'] );	
	update_user_meta( $user_id, 'parent', $_POST['parent'] );
}
add_action( 'show_user_profile', 'fb_add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'fb_add_custom_user_profile_fields' );
add_action( 'personal_options_update', 'fb_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'fb_save_custom_user_profile_fields' );
//EXTRA PROFILE FIELDS END

add_action('admin_menu', 'add_the_intranet_menu');

function add_the_intranet_menu() {
add_menu_page('Simple Intranet','Simple Intranet', 'publish_pages', 'simple_intranet', 'si_render', '', 4); 
}
 
function si_render() {
	$homeurl=get_option('home'); 
	
	global $title;

echo '<h1><a href="../index.php" target="_blank">'.$title.'</a></h1><br>';
_e( 'Want online forms, a calendar, activity feed and file management for your intranet? <strong><a href="http://www.simpleintranet.org">Visit Simple Intranet</a>.</strong><br><br>');
	_e('<h3><strong>Setup An Employee Directory</strong></h3>');
	_e('a) To add an Employee Directory with photos, insert the <strong>[employees]</strong> shortcode into any page or post.<br>');	
	_e('b) <a href="user-new.php">Add new employees</a> and edit their profiles and upload photo avatars.<br>');
	_e('c) Enable options (admins only) under the <em>Employee Profile Page?</em> heading in <a href="profile.php">Your Profile</a>.<br>');
	_e('d) An archive of all employee biographies can be found at <a href="'.$homeurl.'/bios">'.$homeurl.'/bios</a>.<br>');
	_e('e) You must <a href="options-permalink.php">change your Permalinks</a> from "Default" to "Post name".<br>');
	_e('USER PHOTOS NOT WORKING? Try checking the option for Use Legacy User Photos? at the bottom of <a href="profile.php">Your Profile</a>.<br><br>');
	
	_e('<h4><em>Shortcodes</em></h4>');
		_e('- To add a searchable employee directory to a page or post, insert the <strong>[employees]</strong> shortcode. Limit to 25 employees per page using the limit parameter, display the search bar above the listing with title and department search options, exclude "board" and "executive" custom groups (use slugs) from search pull-down, set avatar pixel width to 100 and display only Subscriber roles as follows: <strong>[employees limit="25" search="yes" title="yes" department="yes" search_exclude="board,executive" avatar="100" group="subscriber"]</strong>. To include only specific users in a commas separated list use the username parameter such as: <strong>[employees username="dsmith,rcharles"]</strong>.<br>');
		
	_e('<h4><em>Widgets</em></h4>');
	_e('- Provide an employee directory search function using the <strong>Search Employees</strong> widget.<br>');
	_e('- Display a list of employees using the <strong>Employees</strong> widget.<br>');
		_e('- Display out of office notifications in the employee directory using the <strong>Employee Out of Office</strong> widget.<br>');
				
}

function si_admin_scripts() {
wp_enqueue_script('media-upload');
wp_enqueue_script('thickbox');
wp_register_script('my-upload', WP_PLUGIN_URL.'/custom_header.js', array('jquery','media-upload','thickbox'));
wp_enqueue_script('my-upload');
}
 
function si_admin_styles() {
wp_enqueue_style('thickbox');
}
 
if (isset($_GET['page']) && $_GET['page'] == 'simple_intranet') {
add_action('admin_print_scripts', 'si_admin_scripts');
add_action('admin_print_styles', 'si_admin_styles');
}

 function count_roles() {
	if ( !empty( $wp_roles->role_names ) )
		return count( $wp_roles->role_names );
	return false;
}
 function si_contributors($params=array()) {	
// Get the global $wp_roles variable. //
global $wp_roles;
$role_count=count_roles();

extract(shortcode_atts(array(
		'limit' => 25,
		'search_exclude'=>'',
		'username'=>'', 
		'avatar'=>100,
		'search'=>'yes',
		'group'=>'',
		'department'=>'yes',
		'title'=>'yes',
		'sort'=>'last_name',
		'order'=>'ASC',
	), $params));	

if(isset($params['search_exclude'])){
$group_exclude_array = explode( ',', $params['search_exclude'] );
}
else {
$group_exclude_array ="";
}
if(isset($params['username'])){
$username_array = explode( ',', $params['username'] );
}
else {
$username_array ="";
}
// employee search form  // 
add_option('employeespagesearch', get_permalink());
if($search=='yes'){
	
echo '<form method="POST" id="employeesearchform" action="'.get_permalink().'" >
	<div><input type="text" name="si_search" id="si_search" />
	<select name="type" id="type">';
$t=ucfirst(	$_POST['type']);
if ($t!='') { ?><option value="<?php echo $t;?>" selected="selected"><?php echo $t;?></option><?php } 
$name1= __('First Name','simpleintranet');
$name2= __('Last Name','simpleintranet');
$title1= __('Title','simpleintranet');
$dept1= __('Department','simpleintranet');
echo ' <option value="First Name">'.$name1.'</option>
<option value="Last Name">'.$name2.'</option>';
if($title=='yes'){
echo '<option value="Title">'.$title1.'</option>';
}
if($department=='yes'){
echo  '<option value="Department">'.$dept1.'</option>';
foreach ($wp_roles->role_names as $roledex => $rolename) {
        $role = $wp_roles->get_role($roledex);	
if ($roledex!="administrator" && $roledex!="editor" && $roledex!="subscriber" && $roledex!="author" && $roledex!="contributor"){		
if(!in_array($roledex,$group_exclude_array)){ 
echo '<option value="'.$roledex.'">'.$rolename.'</option>';
}
}
}
}
echo '</select><input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" /></div></form><br>';
}
//employee directory or search resulrts
global $wpdb,$type,$page;
$name = ( isset($_POST["si_search"]) ) ? sanitize_text_field($_POST["si_search"]) : false ;
if(isset($_POST['type'])){
$type=$_POST['type'];
}

// Get Query Var for pagination. This already exists in WordPress
$number = $limit;
if ( get_query_var('paged') ) { 
$page = get_query_var('paged'); 
}
elseif (get_query_var('page')){
$page = get_query_var('page');	
}
else {	
$page=1;
}
  
// Calculate the offset (i.e. how many users we should skip)
$offset = ($page - 1) * $number;

// prepare arguments

if ($type=="" && $username!=""){	
$newusers=array();
foreach ($username_array as $username){	
$user = get_user_by( 'login', $username );
array_push($newusers, $user->ID);
}
$args  = array(
'number' => $number,
'offset' => $offset,
'include' => $newusers,
);
$authors = get_users($args);
}

elseif ($type==""){
$args  = array(
'number' => $number,
'offset' => $offset,
'role' => $group,
'orderby' => 'meta_value',
'order' => $order,
'meta_key' => $sort,
);
$authors = get_users($args);
}

elseif ($type=="First Name"){
$args  = array(
'number' => $number,
'offset' => $offset,
'role' => $group,
'meta_query' => array(
					  'relation' => 'OR',
					  
array(      
		'key' => 'first_name',
        'value' => $name,	
		'compare' => 'LIKE',
        ),	 
));
$authors = get_users($args);
}

elseif ($type=="Last Name"){
$args  = array(
'number' => $number,
'offset' => $offset,
'role' => $group,
'meta_query' => array(
					  'relation' => 'OR',
					  
array(      
		'key' => 'last_name',
        'value' => $name,	
		'compare' => 'LIKE',
        ),	 
));
$authors = get_users($args);
}

elseif ($type=="Title"){
$args  = array(
'number' => $number,
'offset' => $offset,
'role' => $group,
'meta_query' => array(
					  'relation' => 'OR',				  

array(      
		'key' => 'title',
        'value' => $name,
		'compare' => 'LIKE',
        ),
));
$authors = get_users($args);

function si_cmp($a, $b){ 
    return strcasecmp($a->title, $b->title);
}
usort($authors, "si_cmp");

}

elseif ($type=="Department"){
$args  = array(
'number' => $number,
'offset' => $offset,
'role' => $group,
'meta_query' => array(
					  'relation' => 'OR',				  

array(      
		'key' => 'department',
        'value' => $name,
		'compare' => 'LIKE',
        ),
));
$authors = get_users($args);

function si_cmp($a, $b){ 
    return strcasecmp($a->department, $b->department);
}
usort($authors, "si_cmp");
}

else {
$args  = array(
'role' => $type,
'number' => $number,
'offset' => $offset,
'role' => $group,
// check for two meta_values
'meta_query' => array(
					  'relation' => 'OR',	  
array(      
		'key' => $type,
        'value' => $name,
		'compare' => 'IN',
        ),
));
$authors = get_users($args);
}

// Create the WP_User_Query object
$wp_user_query = new WP_User_Query($args);
// pagination
$total_authors = $wp_user_query->total_users;
$total_pages = intval($total_authors / $number) + 1;
// Get the results

// Create employees category
$addprofile = get_option( 'profiledetail' ); // check for clickable profile post option
$hideemail = get_option( 'hideemail' );
$publicbio = get_option( 'publicbio' );
$custombio = get_option( 'custombio' );

// Format phone and fax #s
function sid_formatPhone($num)
{
$num = preg_replace('/[^0-9]/', '', $num);
$len = strlen($num);
if (get_option( 'phoneaustralia')=="Yes" && $len == 10)
$num = preg_replace('/([0-9]{2})([0-9]{4})([0-9]{4})/', '$1 $2 $3', $num);
if (get_option( 'phonesouthafrica')=="Yes" && $len == 11)
$num = preg_replace('/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{4})/', '+$1 $2 $3 $4', $num);
if (get_option( 'phoneeurope')=="Yes" && $len == 12)
$num = preg_replace('/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{2})([0-9]{2})([0-9]{1})/', '+$1 $2 $3 $4 $5 $6', $num);
elseif($len == 7)
$num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
elseif($len == 10)
$num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);

return $num;
}

// Check for results
if (empty($authors))
{
echo 'No results for the '.$type.' "'.$name.'".<br><br>';
} 

foreach ($authors as $author ) {
	
$c=0;	
$hidemyemail = get_the_author_meta('hidemyemail',$author->ID);
$website=get_the_author_meta('user_url',$author->ID);
if($website!=''){
$website='Website: <a href="'.$website.'">'.$website.'</a><br>';
}
else{
$website='';
}
$twitter=get_the_author_meta('twitter',$author->ID);
if($twitter!=''){
$tw= plugins_url('/images/si_twitter.gif', __FILE__);
$twitter='<a href="'.$twitter.'"><img src="'.$tw.'"></a>  ';
}
else{
$twitter='';
}
$facebook=get_the_author_meta('facebook',$author->ID);
if($facebook!=''){
$fb= plugins_url('/images/si_facebook.gif', __FILE__);
$facebook='<a href="'.$facebook.'"><img src="'.$fb.'"></a>  ';
}
else{
$facebook='';
}
$linkedin=get_the_author_meta('linkedin',$author->ID);
if($linkedin!=''){
$li= plugins_url('/images/si_linkedin.gif', __FILE__);
$linkedin='<a href="'.$linkedin.'"><img src="'.$li.'"></a>  ';
}
else{
$linkedin='';
}
$googleplus=get_the_author_meta('googleplus',$author->ID);
if($googleplus!=''){
$go= plugins_url('/images/si_google.gif', __FILE__);
$googleplus='<a href="'.$googleplus.'"><img src="'.$go.'"></a>  ';
}
else{
$googleplus='';
}
$exclude=get_the_author_meta('exclude', $author->ID);
$bio=get_the_author_meta('includebio', $author->ID);
$biography=get_the_author_meta('description', $author->ID);
$inoffice=get_the_author_meta('si_office_status', $author->ID);
$officetext=get_the_author_meta('officenotification', $author->ID);
if($inoffice=='true') {
$officetext='<div class="outofoffice">'.$officetext.'</div>';
}
else {
$officetext='';
}
$first = get_the_author_meta('first_name', $author->ID);
$last = get_the_author_meta('last_name', $author->ID);
str_replace(' ', '-', $first);
str_replace(' ', '-', $last);
$title = get_the_author_meta('title', $author->ID);
if($title=='yes'){
$title='';
}
$company = get_the_author_meta('company', $author->ID);
if($company!=''){
$company=$company.'<br>';
}
$address = get_the_author_meta('address', $author->ID);
if($address!=''){
$address=$address.'<br>';
}
$postal = get_the_author_meta('postal', $author->ID);
if($postal!=''){
$postal=$postal.'<br>';
}
$city = get_the_author_meta('city', $author->ID);
if($city!=''){
$city=$city.'<br>';
}
$state = get_the_author_meta('region', $author->ID);
if($state!=''){
$state=$state.'<br>';
}
$country = get_the_author_meta('country', $author->ID);
if($country!=''){
$country=$country.'<br>';
}
if($title!=''){
$title=$title.', ';
}
$dept = get_the_author_meta('department', $author->ID);
if ($dept!=''){
$dept=$dept.'<br>';	
}
$phone = get_the_author_meta('phone', $author->ID);
$phonelabel = get_option('phonelabel');
$phoneextlabel = get_option('phoneextlabel');
$mobilelabel = get_option('mobilelabel');
$faxlabel = get_option('faxlabel');
$phone2 =  sid_formatPhone($phone);
if($phone!=''){
$phone2=$phonelabel.'<a href="tel:'.$phone2.'">'.$phone2.'</a> ';
}
$mobilephone = get_the_author_meta('mobilephone', $author->ID);
$mobile2= sid_formatPhone($mobilephone);
if($mobilephone!=''){
$mobile2=$mobilelabel.'<a href="tel:'.$mobile2.'">'.$mobile2.'</a>';
}
$fax = get_the_author_meta('fax', $author->ID);
$fax2= sid_formatPhone($fax);
if($fax!=''){
$fax2=' | '.$faxlabel.'<a href="tel:'.$fax2.'">'.$fax2.'</a><br>';
}
$ext = get_the_author_meta('phoneext', $author->ID);
if($ext!=''){
$ext=' '.$phoneextlabel.$ext.'<br>';
}

$email = get_the_author_meta('email', $author->ID);
if($email!='' && ($hideemail!='Yes' || $hidemyemail!='Yes') ) {
$email= '<a href="mailto:'.$email.'">'.$email.'</a><br>';
}
if($hideemail=='Yes' || $hidemyemail=='Yes'){
$email='';
}
$custom1label = get_option('custom1label');
$custom1 = get_the_author_meta('custom1', $author->ID);
if($custom1!=''){
$cu1='<br>';
}
else {
$cu1='';
$custom1label='';
}
$custom2label = get_option('custom2label');
$custom2 = get_the_author_meta('custom2', $author->ID);
if($custom2!=''){
$cu2='<br>';
}
else {
$cu2='';
$custom2label='';
}
$custom3label = get_option('custom3label');
$custom3 = get_the_author_meta('custom3', $author->ID);
if($custom3!=''){
$cu3='<br>';
}
else {
$cu3='';
$custom3label='';
}
if($exclude!='Yes') {
if($bio!='Yes') {
$biography='';
}

// Create page for employee 
$fullname=$first.' '.$last;
$fullname=sanitize_text_field($fullname);
global $current_user;
$user_roles = $current_user->roles;
$user_role = array_shift($user_roles);
$allowed = get_option('sroles');	

if($allowed!='' ){
$letin="";	
foreach ($allowed as $key=>$value){
if($key==$user_role || $publicbio=="Yes"){
$letin=$letin+1;
}
}
}

if ($addprofile=="Yes" && $letin>0){

$post = array(
  'post_title'    => $fullname, 
  'post_name'	  => $fullname,  
   'post_content'  => '<div class="si-employees-wrap"><div class="employeephotoprofile">'.get_avatar($author->ID,$avatar).'</div><div class="employeebioprofile"><div class="outofoffice">'.$officetext.'</div>'.$title.$dept.$company.$address.$postal.$city.$state.$country.$email.$phone2.$ext.$mobile2.$fax2.$city.$state.$country.'</div><br>'.$custom1label.$custom1.$cu1.$custom2label.$custom2.$cu2.$custom3label.$custom3.$cu3.$website.'<div class="socialicons">'.$twitter.$facebook.$linkedin.$googleplus.'</div><br><div class="employeebiographyprofile">'.$biography.'</div></div>',
   'post_author' => $author->ID,
  'post_type' => 'si_profile',
  'post_status'   => 'publish'
 
);
$page_exists = get_page_by_title( $post['post_name'],$output = OBJECT, $post_type = 'si_profile' );

$c=$c+1;
if ($page_exists==NULL && $c==1 ){

wp_insert_post( $post, $wp_error ); 

}
else {
$post_id=$page_exists->ID;
$updated_post = array(
'ID' => $post_id,
 'post_content'  => '<div class="si-employees-wrap"><div class="employeephotoprofile">'.get_avatar($author->ID,$avatar).'</div><div class="outofoffice">'.$officetext.'</div>
<div class="employeebioprofile"><span class="sid_title">'.$title.'</span><span class="sid_dept">'.$dept.'</span><span class="sid_company">'.$company.'</span><span class="sid_address">'.$address.'</span><span class="sid_postal">'.$postal.'</span><span class="sid_city">'.$city.'</span><span class="sid_state">'.$state.'</span><span class="sid_country">'.$country.'</span><span class="sid_email">'.$email.'</span><span class="sid_phone">'.$phone2.'</span><span class="sid_phone_extension">'.$ext.'</span><span class="sid_mobile_phone">'.$mobile2.'</span><span class="sid_fax">'.$fax2.'</span><br>
<span class="sid_custom1_label">'.$custom1label.'</span><span class="sid_custom1">'.$custom1.$cu1.'</span><span class="sid_custom2_label">'.$custom2label.'</span><span class="sid_custom2">'.$custom2.$cu2.'</span><span class="sid_custom3_label">'.$custom3label.'</span><span class="sid_custom3">'.$custom3.$cu3.'</span><span class="sid_website">'.$website.'</span></div><div class="socialicons">'.$twitter.$facebook.$linkedin.$googleplus.'</div><br><div class="employeebiographyprofile">'.$biography.'</div></div>',
'post_author' => $author->ID,
 'post_type' => 'si_profile',
 'post_status'   => 'publish' 
);

if ($custombio!="Yes" ){	
if ($updated_post['ID']!=''){
wp_update_post( $updated_post);
}
}
}
} // end of extended profile check
echo '<div class="si-employees-wrap"><div class="employeephoto">';
if ($addprofile=="Yes" || $publicbio=="Yes"){ ?><a href="<?php echo get_permalink($post_id);?>"><?php } ;
echo get_avatar( $author->ID,$avatar);
if ($addprofile=="Yes" || $publicbio=="Yes"){ ?></a><?php } 
echo '</div><div class="employeebio">';
if($inoffice=='true') {
echo '<div class="outofoffice">'.$officetext.'</div>';
}
?><?php if ($addprofile=="Yes" || $publicbio=="Yes"){ ?>
<div class="sid_fullname"><a href="<?php echo get_permalink($post_id);?>"><?php echo $first.' '.$last; ?></a></div>
<?php  } 
else {
echo '<span class="sid_fullname">'.$first.' '.$last.'</span> ';
}

echo '<span class="sid_title">'.$title.'</span><span class="sid_dept">'.$dept.'</span><span class="sid_company">'.$company.$address.$postal.'</span><span class="sid_email">'.$email.'</span><span class="sid_phone">'.$phone2.'</span><span class="sid_phone_extension">'.$ext.'</span><span class="sid_mobile_phone">'.$mobile2.'</span><span class="sid_fax">'.$fax2.'</span><br><span class="sid_custom1_label">'.$custom1label.'</span><span class="sid_custom1">'.$custom1.$cu1.'</span><span class="sid_custom2_label">'.$custom2label.'</span><span class="sid_custom2">'.$custom2.$cu2.'</span><span class="sid_custom3_label">'.$custom3label.'</span><span class="sid_custom3">'.$custom3.$cu3.'</span>';
echo '</div></div><br>';
}
}

//pagination stuff
$pr='Previous Page';
$ne='Next Page';
$plink = get_permalink();
if ($page != 1) { 
echo '<a rel="prev" href="'.$plink.'/'.($page - 1).'">'.$pr.'</a>'.'  ';
 } 
if ($page < $total_pages ) { 
echo '<a rel="next" href="'.$plink.'/'.($page + 1).'">'.$ne.'</a>';
 } 

}


// CHANGE LEAVE A REPLY
add_filter('comment_form_defaults', 'simple_comments');

function simple_comments($defaults) {
    global $current_user ;
    $user_id = $current_user->ID;	
	$wall_reply2 = get_user_meta( $user_id, 'wall_text', true);
	$defaults['title_reply'] = $wall_reply2; // CHANGED TODAY
	if ($wall_reply2==''){		
	$defaults['title_reply'] = __('What are you working on?','simpleintranet'); 	
	}
	$defaults['title_reply_to'] = 'Post a reply %s';
	return $defaults;
}

function wp_remove_events_from_admin_bar() {
global $wp_admin_bar;
$wp_admin_bar->remove_menu('tribe-events');
}
add_action( 'wp_before_admin_bar_render', 'wp_remove_events_from_admin_bar' ); 

// REMOVE COMMENT TAGS INFO
function mytheme_init() {
	add_filter('comment_form_defaults','simple_comments_form_defaults');
}
add_action('after_setup_theme','mytheme_init');

function simple_comments_form_defaults($default) {
	unset($default['comment_notes_after']);
	return $default;
}

function sid_redirect_to_front_page() {
	global $redirect_to;
	if (!isset($_GET['redirect_to'])) {
		$redirect_to = get_option('siteurl');
	}
}

add_action('login_form', 'sid_redirect_to_front_page');


// out of the office ALERT

/* Display an out of the office notice that can be dismissed */
add_action('admin_notices', 'si_admin_notice');
function si_admin_notice() {
    global $current_user, $pagenow, $status,$ignore3;
        $user_id = $current_user->ID;		
		 $status= esc_attr( get_the_author_meta( 'si_office_status', $user_id ,true) ); 
		 $ignore= esc_attr( get_the_author_meta( 'si_office_ignore', $user_id ,true) ); 

 if ( !$ignore) {
 add_user_meta($user_id, 'si_office_ignore', 'false', true);
    }
 if(!empty($_GET['si_ignore'])){
$ignore3='';
$ignore3=	$_GET['si_ignore']; 
update_user_meta($user_id, 'si_office_ignore', $ignore3);
 }

 if ( $pagenow == 'profile.php' || $status=='true' ) {
if ($status=='true' && $ignore!='true' && $ignore3!='true') {  
 update_user_meta($user_id, 'si_office_ignore', 'false');
	  echo '<div class="updated"><p>';
        printf(__('Out of office notification is ON. | <a href="%1$s">Turn OFF.</a>'), 'profile.php?si_office=false');
		 printf(__('<br><a href="%1$s">Dismiss this notice.</a>'), 'profile.php?si_ignore=true');
        echo "</p></div>"; 			
             update_user_meta($user_id, 'si_office_status', 'true');   		
			 if($_GET['si_ignore']){
			 $ignore2=$_GET['si_ignore'];
			 update_user_meta($user_id, 'si_office_ignore', $ignore2); 
			 }
			   	
	}
if ($status=='false' && $ignore!='true' && $ignore3!='true') {    
      echo '<div class="updated"><p>';
        printf(__('Out of office notification is OFF. | <a href="%1$s">Turn ON.</a>'), 'profile.php?si_office=true');
		printf(__('<br><a href="%1$s">Dismiss this notice.</a>'), 'profile.php?si_ignore=true');
        echo "</p></div>";	 
		 update_user_meta($user_id, 'si_office_status', 'false');   
		  if(!empty($_GET['si_ignore'])){
			 $ignore2=$_GET['si_ignore'];
			 update_user_meta($user_id, 'si_office_ignore', $ignore2);  
			 }
			 
}
   }
}

add_action('admin_init', 'si_in_office');
function si_in_office() {
    global $current_user;
        $user_id = $current_user->ID;		
        /* If user clicks to be back in office, add that to their user meta */  
		if(isset($_GET['si_office'])){
		if($_GET['si_office']!=''){
             update_user_meta($user_id, 'si_office_status', $_GET['si_office']);   
		}
		}
}

// out of the office WIDGET 

class OutOfOfficeWidget extends WP_Widget
{
  function OutOfOfficeWidget()
  {
    $widget_ops_out = array('classname' => 'OutOfOfficeWidget', 'description' => __('Displays a list of employees who are away.') );
    $this->WP_Widget('OutOfOfficeWidget', 'Employee Out of Office', $widget_ops_out);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 $gofs = get_option( 'gmt_offset' ); // get WordPress offset in hours
$tz = date_default_timezone_get(); // get current PHP timezone
date_default_timezone_set('Etc/GMT'.(($gofs < 0)?'+':'').-$gofs); // set the PHP timezone to match WordPress
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;
 
    // WIDGET CODE GOES HERE
	// Create the WP_User_Query object
$wp_user_query2 = new WP_User_Query($args);
// pagination
// Get the results
$authors2 = $wp_user_query2->get_results();
// Check for results

if (empty($authors2))
{
echo 'None.<br><br>';
} 
global $c;
$c=0;
    foreach ($authors2 as $author2 ) {
$inoffice=get_the_author_meta('si_office_status', $author2->ID, true);
$outtext =get_the_author_meta( 'officenotification', $author2->ID); 
$officeexpireuk= get_the_author_meta( 'officeexpire_unix', $author2->ID) ;
if($officeexpireuk!=''){
$nicedate= gmdate("F j, Y", $officeexpireuk);
}
$expiry= get_the_author_meta( 'expiry', $author2->ID ); 
$expirytext= get_the_author_meta( 'expirytext', $author2->ID ); 

if($inoffice=='true' || $in_out=='true') {
$c=$c+1;
}
$first = get_the_author_meta('first_name', $author2->ID);
$last = get_the_author_meta('last_name', $author2->ID);

if (get_option( 'profiledetail' )=="Yes"){
$fn2=$first.'-'.$last;
$fullname_link=sanitize_text_field($fn2);
$fullname_link2=strtolower($fullname_link);
$fnl3= str_replace(' ', '-', $fullname_link2);
}
else {
$fn13='';
}
$title = get_the_author_meta('title', $author2->ID);
$email = get_the_author_meta('email', $author2->ID);
if ($inoffice=='true' || $in_out=='true'){
echo '<div class="si-employees-wrap"><div class="employeephotowidget">';
if(get_avatar($author2->ID,40))
echo '<a href="'.home_url().'/bios/'.$fnl3.'">'.get_avatar($author2->ID,40).'</a>';
echo '</div>';
echo '<div class="employeebiowidget">';
echo '<strong><a href="'.home_url().'/bios/'.$fnl3.'">'.$first.' '.$last.'</a></strong>';
if($outtext!=''){
echo '<br>'.$outtext.' ';
}
if($expiry=='Yes' && $nicedate!=''){
echo $expirytext.$nicedate.'';
}
echo '</div></div>';
} 
}
 if ($c==0)
{
echo 'None.<br><br>';
}
    echo $after_widget;
	date_default_timezone_set($tz); // set the PHP timezone back the way it was
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("OutOfOfficeWidget");') );


// employees WIDGET 

class EmployeesWidget extends WP_Widget
{
  function EmployeesWidget()
  {
    $widget_ops = array('classname' => 'EmployeesWidget', 'description' => __('Displays a list of employees.') );
    $this->WP_Widget('EmployeesWidget', __('Employees'), $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => __('Employees') ) );
    $etitle = $instance['title'];

?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($etitle); ?>" /></label></p>
   
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];

    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $etitle = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
	
    if (!empty($etitle))
      echo $before_title . $etitle . $after_title;;
 
    // WIDGET CODE GOES HERE
	// Create the WP_User_Query object
$wp_user_query6 = new WP_User_Query($args);
$authors6 = $wp_user_query6->get_results();
// Check for results


global $c, $current_user;
$c=0;
    foreach ($authors6 as $key =>$author6 ) {
$c=$c+1;

$first6 = get_the_author_meta('first_name', $author6->ID);
$last6 = get_the_author_meta('last_name', $author6->ID);	
if (get_option( 'profiledetail' )=="Yes"){
$fn6=$first6.'-'.$last6;
$fullname_link6=sanitize_text_field($fn6);
$fullname_link6=strtolower($fullname_link6);
$fnl6= str_replace(' ', '-', $fullname_link6);
}
else {
$fn16='';
}

if (get_the_author_meta( 'exclude', $author6->ID )!="Yes"){
echo '<div class="si-employees-wrap"><div class="employeephotowidget">';
if(get_avatar($author6->ID,40))
echo '<a href="'.home_url().'/bios/'.$fnl6.'">'.get_avatar($author6->ID,40).'</a>';
echo '</div>';
echo '<div class="employeebiowidget">';
echo '<strong><a href="'.home_url().'/bios/'.$fnl6.'">'.$first6.' '.$last6.'</a></strong>';
echo '</div></div>';
$fn16='';
}
}
 if ($c==0)
{
echo 'None.<br><br>';
}
    echo $after_widget;
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("EmployeesWidget");') );

// Schedule to check out of office alerts are updated regularly
add_action('sid_outofoffice_check', 'sid_outofoffice_hourly_check');

function sid_outofoffice_chron_activation() {
	if ( !wp_next_scheduled( 'sid_outofoffice_check' ) ) {
wp_schedule_event(current_time( 'timestamp' ), 'hourly', 'sid_outofoffice_check');
	}
}

add_action( 'wp', 'sid_outofoffice_chron_activation' );

function sid_outofoffice_hourly_check() {
global $in_out, $officeexpire, $current_user;
$wp_user_query9 = new WP_User_Query($args);
$authors9 = $wp_user_query9->get_results();

	$right_now=current_time( 'timestamp' );
	$gmt = get_option( 'gmt_offset' ) * 3600;
	
	if (is_array($authors9)){
	  foreach ($authors9 as $key =>$author9 ) {
	 
	 $in_out=  get_the_author_meta( 'si_office_status', $author9, true) ; 
	 $officeexpire= get_the_author_meta( 'officeexpire_unix', $author9 ) ;
	 $officeexpire = $officeexpire + $gmt;
	 $set_expiry= esc_attr( get_the_author_meta( 'expiry', $author9 ) ); 	
	 if($set_expiry=="Yes"){
	 if($officeexpire<=$right_now ){
	 $in_out='false';
	 update_user_meta($author9, 'si_office_status','false'); 
	 }
	 if($officeexpire>=$right_now ){
	 $in_out='true';
	 update_user_meta($author9, 'si_office_status','true'); 
	  }
	  }
	  }
}
}

// Employee Search Widget

class EmployeeSearchWidget extends WP_Widget
{
  function EmployeeSearchWidget()
  {
    $widget_ops_search = array('classname' => 'EmployeeSearchWidget', 'description' => __('An employee search option.') );
    $this->WP_Widget('EmployeeSearchWidget', 'Employee Search', $widget_ops_search);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);	
    if (!empty($title))
      echo $before_title . $title . $after_title;
 
    // WIDGET CODE GOES HERE
$wp_user_query12 = new WP_User_Query($args);
$authors12 = $wp_user_query12->get_results();
// employee search form  
$eurl=get_option('employeespagesearch');
echo '<form method="POST" id="employeesearchformwidget" action="'.$eurl.'" ><input type="text" name="si_search" id="si_search" /><select name="type" id="type">';
$t=ucfirst(	$_POST['type']);
if ($t!='') { ?><option value="<?php echo $t;?>" selected="selected"><?php echo $t;?></option><?php } 
$name1= __('First Name','simpleintranet');
$name2= __('Last Name','simpleintranet');
$email2= __('E-mail','simpleintranet');
$title1= __('Title','simpleintranet');
$dept1= __('Department','simpleintranet');
echo ' <option value="First Name">'.$name1.'</option>
<option value="Last Name">'.$name2.'</option>
<option value="E-mail">'.$email2.'</option>
	  <option value="Title">'.$title1.'</option>
	  <option value="Department">'.$dept1.'</option>';
foreach ($wp_roles->role_names as $roledex => $rolename) {
        $role = $wp_roles->get_role($roledex);	
if ($roledex!="administrator" && $roledex!="editor" && $roledex!="subscriber" && $roledex!="author" && $roledex!="contributor"){		
echo '<option value="'.$roledex.'">'.$rolename.'</option>';
}

}
echo '</select><input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" /></form><br>';
// End of search
echo $after_widget;	
  }
 
}
add_action( 'widgets_init', create_function('', 'return register_widget("EmployeeSearchWidget");') );

?>