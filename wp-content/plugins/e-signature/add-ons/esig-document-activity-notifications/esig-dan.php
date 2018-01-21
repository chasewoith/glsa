<?php

/**
 * @package   	      WP E-Signature - Document Activity Notifications
 * @contributors      Kevin Michael Gray (Approve Me), Abu Shoaib (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       WP E-Signature - Document Activity Notifications
 * URI:        https://approveme.com/wp-digital-e-signature
 * Description:       This add-on sends document activity email notifications every time your signer has viewed a document sent for signature (even if they haven't signed it).
 * mini-description  receive email notifications with document activity
<<<<<<< HEAD
 * Version:           1.5.1
=======
 * Version:           1.5.0.2
>>>>>>> 13d8cc50221f2cb8e86cd37efe6bc5066804ec09
 * Author:            Approve Me
 * Author URI:        https://approveme.com/
 * Documentation:     http://aprv.me/1ULVJvR
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}




//if (is_admin()) {

require_once( dirname(__FILE__) . '/admin/esig-dan-admin.php' );
add_action('wp_esignature_loaded', array('ESIG_DAN_Admin', 'get_instance'));

//}


