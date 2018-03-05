<?php
global $wpdb;

$table_prefix = $wpdb->prefix . "esign_";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // UPgrade Documents Table
  // db upgrade version : 6.0 

// existing user table is being updated. 
/*$sql_update_user_table = "ALTER TABLE ". $table_prefix ."users
ADD COLUMN `is_admin` SMALLINT(6) NOT NULL AFTER `last_name`,
ADD COLUMN `is_signer` SMALLINT(6) NOT NULL AFTER `is_admin`,
ADD COLUMN `is_sa` SMALLINT(6) NOT NULL AFTER `is_signer`,
ADD COLUMN `is_inactive` SMALLINT(6) NOT NULL AFTER `is_sa`;";

$wpdb->query($sql_update_user_table);*/

// document events table upgrade scripts 

/*$sql_update_event_table = "ALTER TABLE ". $table_prefix ."documents_events
ADD COLUMN `ip_address` varchar(100) NOT NULL AFTER `date`;";
$wpdb->query($sql_update_event_table );*/

$sql_documents_table = "ALTER TABLE ". $table_prefix ."documents
CHANGE `ip_address` `ip_address` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
$wpdb->query($sql_documents_table);

$sql_documents_signature_table = "ALTER TABLE ". $table_prefix ."documents_signatures
CHANGE `ip_address` `ip_address` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
$wpdb->query($sql_documents_signature_table);


$sql_documents_event_table = "ALTER TABLE ". $table_prefix ."documents_events
CHANGE `ip_address` `ip_address` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
$wpdb->query($sql_documents_event_table);


$sql_documents_invitations_table = "ALTER TABLE ". $table_prefix ."invitations
CHANGE `sender_ip` `sender_ip` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
$wpdb->query($sql_documents_invitations_table);




// document fields  data tables 
$sql = "CREATE TABLE IF NOT EXISTS `" . $table_prefix . "documents_fields_data`(
			    `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                            `field_id` varchar(100) NOT NULL,
                            `recipient_id` bigint(20) NOT NULL,
                            `document_id` bigint(20) NOT NULL,
                            `value` longtext NOT NULL,
			    `created_at` datetime NOT NULL
			    ) ENGINE = INNODB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci";


dbDelta($sql);


