<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>
<div class="esig-error-message-wrap">
<a href='https://www.approveme.com/wp-digital-e-signature' target='_blank' style='text-decoration:none;'>
				<img src='<?php echo ESIGN_ASSETS_DIR_URI ; ?>/images/logo.png' alt='WP E-Signature'>
</a>
<h1><?php _e('Access Denied', 'esig' );?></h1>
<p><?php echo sprintf( __( 'Woah tiger! %s for WP E-Signature is a pro feature. Because security is a big deal, only the WordPress admin user who first saves the settings for WP E-Signature can have access to the E-Signature documents & settings page.', 'esig'), $data['feature'])?></p> 

 <?php 
    if(Esign_licenses::is_business_license()){
 ?>
<p><?php echo sprintf(__(' If additional users need to upload, send and manage documents we recommend that you enable our <a href="https://www.approveme.com/wp-digital-signature-plugin-docs/article/install-and-activate-plugins-extensions/" target="_blank">E-Signature Unlimited Sender Roles feature</a> OR <a href="https://www.approveme.com/wp-digital-signature-plugin-docs/article/wordpress-electronic-signature-unlimited-sender-roles/" target="_blank">Check your settings if this add-on is already enabled</a>. %s would need to login and enable this feature.', 'esig' ),  WP_E_Sig()->user->superAdminUserName());?></p>


    <?php }
    else {
    ?>
<p><?php _e('If additional users need to upload, send and manage document we recommend you upgrade and install our <a href="https://www.approveme.com/downloads/unlimited-sender-roles/">E-Signature Unlimited Sender Roles</a> feature.', 'esig' );?></p>
<?php
    }
?>

<p><?php _e('Please checkout our awesome list of extensions <a href="https://www.approveme.com/wordpress-electronic-digital-signature-add-ons/">here</a>', 'esig' ); ?></p>
</div>

<?php echo $data['esig_user_role']; ?>