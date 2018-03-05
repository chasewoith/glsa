<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!class_exists('esigCf7Filters')):

    class esigCf7Filters {

        protected static $instance = null;

        private function __construct() {
            add_filter("esig_document_title_filter", array($this, "cf7_document_title_filter"), 10, 2);
            add_filter("esig_strip_shortcodes_tagnames", array($this, "tag_list_filter"), 10, 1);
            
        }
        public function tag_list_filter($listArray){
              $listArray[]="contact-form-7";
              return $listArray;
        }

        public function cf7_document_title_filter($docTitle, $docId) {
            $formIntegration = WP_E_Sig()->document->getFormIntegration($docId);
            if ($formIntegration != "cf7") {
                return $docTitle;
            }

            $formId = WP_E_Sig()->meta->get($docId, 'esig_cf7_form_id');
            preg_match_all('/{{(.*?)}}/', $docTitle, $matches);
            if (empty($matches[1])) {
                return $docTitle;
            }
            $fieldId = is_array($matches) ? str_replace('cf7-field-id-', "", $matches[1][0]) : false;
            
            if ($fieldId) {
                $cf7Value = ESIG_CF7_SETTING::get_submission_value($docId, $formId, $fieldId);
                $title = str_replace("{{cf7-field-id-" . $fieldId . "}}", $cf7Value, $docTitle);
                return $title;
            }
            return $docTitle;
        }

        /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function instance() {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

    }

    

    
endif;
