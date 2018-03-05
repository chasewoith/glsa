<?php

if (!class_exists('ESIG_CF7_SETTING')):

    class ESIG_CF7_SETTING {

        const ESIG_CF7_COOKIE = 'esig-cf7-redirect';
        const CF7_COOKIE = 'esig-cf7-temp-data';
        const CF7_FORM_ID_META = 'esig_cf7_form_id';
        const CF7_ENTRY_ID_META = 'esig_cf7_entry_id';

        public static function is_cf7_requested_agreement($document_id) {
            $cf7_form_id = WP_E_Sig()->meta->get($document_id, self::CF7_FORM_ID_META);
            $cf7_entry_id = WP_E_Sig()->meta->get($document_id, self::CF7_ENTRY_ID_META);
            if ($cf7_form_id && $cf7_entry_id) {
                return true;
            }
            return false;
        }

        public static function is_cf7_esign_required() {
            if (self::get_temp_settings()) {
                return true;
            } else {
                return false;
            }
        }

        public static function get_temp_settings() {
            if (ESIG_COOKIE(self::CF7_COOKIE)) {
                return json_decode(stripslashes(ESIG_COOKIE(self::CF7_COOKIE)), true);
            }
            return false;
        }

        public static function save_esig_cf7_meta($meta_key, $meta_index, $meta_value) {

            $temp_settings = self::get_temp_settings();
            if (!$temp_settings) {
                $temp_settings = array();
                $temp_settings[$meta_key] = array($meta_index => $meta_value);
                // finally save slv settings . 
                self::save_temp_settings($temp_settings);
            } else {

                if (array_key_exists($meta_key, $temp_settings)) {
                    $temp_settings[$meta_key][$meta_index] = $meta_value;
                    self::save_temp_settings($temp_settings);
                } else {
                    $temp_settings[$meta_key] = array($meta_index => $meta_value);
                    self::save_temp_settings($temp_settings);
                }
            }
        }

        public static function save_temp_settings($value) {
            $json = $value;
            //esig_setcookie(self::CF7_COOKIE, $json, 600);
            if (!headers_sent()) {
                setrawcookie(self::CF7_COOKIE, $value, time() + 600, COOKIEPATH, COOKIE_DOMAIN, false);
            } elseif (defined('WP_DEBUG') && WP_DEBUG) {
                headers_sent($file, $line);
                trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE);
            }
            // for instant cookie load. 
            $_COOKIE[self::CF7_COOKIE] = $json;
        }

        public static function save_invite_url($invite_hash, $document_checksum) {
            $invite_url = WP_E_Invite::get_invite_url($invite_hash, $document_checksum);
            esig_setcookie(self::ESIG_CF7_COOKIE, $invite_url, 600);
            $_COOKIE[self::ESIG_CF7_COOKIE] = $invite_url;
        }

        public static function get_invite_url() {
            return esigget(self::ESIG_CF7_COOKIE, $_COOKIE);
        }

        public static function remove_invite_url() {
            setcookie(self::ESIG_CF7_COOKIE, null, time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        }

        public static function save_submission_value($document_id, $form_id) {


            $submission = WPCF7_Submission::get_instance();
            $cf7_data = $submission->get_posted_data();

            WP_E_Sig()->meta->add($document_id, "esig_cf7_submission_value", json_encode($cf7_data));
        }

        public static function get_submission_value($document_id, $form_id, $field_id) {
            $cf7_value = json_decode(WP_E_Sig()->meta->get($document_id, "esig_cf7_submission_value"), true);
            if (is_array($cf7_value)) {
                return esigget($field_id, $cf7_value);
            }
        }

        public static function save_file_url($document_id) {

            global $wpdb;
            $form_to_DB = WPCF7_Submission::get_instance();

            if (!$form_to_DB) {
                return;
            }

            $uploaded_files = $form_to_DB->uploaded_files(); // this allows you access to the upload file in the temp location

            foreach ($uploaded_files as $key => $upload) {

                $file_name = basename($upload);

                $image_location = $uploaded_files[$key];

                $image_content = file_get_contents($image_location);
                $upload = wp_upload_bits($file_name, null, $image_content);
                $fileurl = $upload['url'];

                WP_E_Sig()->meta->add($document_id, "esig_cf7_file_url", $fileurl);
            }
        }

        public static function get_file_url($document_id) {
            $cf7_url = WP_E_Sig()->meta->get($document_id, "esig_cf7_file_url");

            if ($cf7_url) {
                return $cf7_url;
            }
        }

        public static function display_value($underline_data, $form_id, $cf7_value) {



            $result = '';
            if ($underline_data == "underline") {

                if (is_array($cf7_value)) {
                    foreach ($cf7_value as $val) {
                        $result .= '<u>' . $val . '</u></br>';
                    }
                } else {
                    $result = '<u>' . $cf7_value . '</u>';
                }
                // $result .= '<u>' . implode(", ", $cf7_value) . '</u><br>';
            } else {
                if (is_array($cf7_value)) {
                    foreach ($cf7_value as $val) {
                        $result .= $val . '</br>';
                    }
                } else {
                    $result = $cf7_value;
                }
            }
            return $result;
        }

    }

    

    

endif;