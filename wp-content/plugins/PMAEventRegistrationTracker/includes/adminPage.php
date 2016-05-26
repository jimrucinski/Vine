<?php

function pertOptionsPage(){
    ob_start(); //start the output buffer. OB is used when you will be creating large amounts of client side coding
    ?>
<div class="wrap">
    <h2>PERT Plugin Options</h2>
    <p>this is our settings pages content.</p>
</div>
<?php
echo ob_get_clean();
}

function pertAddOptionsLink(){
    add_options_page('PMA Event Registration Tracker', 'P.E.R.T.', 'manage_options', 'pert-option','pertOptionsPage');
    //add_options_page adds the item to the "settings" menu in the WP Dashboard. 
    //the third parameter 'manage_options' sets up who can see this ... 'manage_options' = administrator.
}
add_action('admin_menu','pertAddOptionsLink');//admin_menu is the hook to call when working with the admin menu.