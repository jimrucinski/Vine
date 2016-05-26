<?php
   /*
   Plugin Name: PMA Event Registration Tracker
   Plugin URI: 
   Description: Display current event registration progress for the current year
   Version: 1
   Author: Jim Rucinski
   Author URI: 
   License: GPL2
   */

/********************************
 * global variables
 *********************************/

/********************************
 * includes
 *********************************/

include('includes/scripts.php');//all JS and CSS
include('includes/dataprocessing.php');//all data interaction
include('includes/display-functions.php');//displays content functions
include('includes/adminPage.php');//plugin options page HTML and save functions



/************************
 * 
 */

add_action('admin_menu','PMAEventRegistrationTracker_admin_actions');
function PMAEventRegistrationTracker_admin_actions(){
    add_options_page('PmaEventRegistrationTracker','PmaEventRegistrationTracker','manage_options',__FILE__,'PMAEventRegistrationTracker_admin');
} 
function PMAEventRegistrationTracker_admin()
{
    ?>
    <div class="wrap">
        <h4>PMA Event Registration Tracker</h4>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Post Title</th>
                    <th>Post ID</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Post Title</th>
                    <th>Post ID</th>
                </tr>
            </tfoot>
            <tbody>
        <?php
            global $wpdb;
            $mytestdrafts = $wpdb->get_results(
                    "SELECT ID, post_title FROM $wpdb->posts WHERE post_status='draft'"
            );
            
            foreach ($mytestdrafts as $mytestdraft) {
                ?>
                <tr>
                    <?php
                    echo "<td>" . $mytestdraft->post_title . "</td>";
                    echo "<td>". $mytestdraft->ID . "</td>";
                    ?>
                </tr>
                <?php
                
            }
        ?>
                
            
        </table>            
    </div>
<?php
}
?>