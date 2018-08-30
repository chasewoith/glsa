<?php

/**
 *
 * @package ESIG_CF7_Admin
 * @author  Arafat Rahman <arafatrahmank@gmail.com>
 */
if (!class_exists('ESIG_CF7_Admin')) :

    class ESIG_CF7_Admin extends ESIG_CF7_SETTING {

        /**
         * Instance of this class.
         * @since    1.0.1
         * @var      object
         */
        protected static $instance = null;
        public $name;

        /**
         * Slug of the plugin screen.
         * @since    1.0.1
         * @var      string
         */
        protected $plugin_screen_hook_suffix = null;

        /**
         * Initialize the plugin by loading admin scripts & styles and adding a
         * settings page and menu.
         * @since     0.1
         */
        public function __construct() {
            /*
             * Call $plugin_slug from public plugin class.
             */
            $plugin = ESIG_CF7::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();
            $this->document_view = new esig_cf7_document_view();

            add_action('init', array($this, 'cf7_wpesignature_init_text_domain'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'), 999);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('wpcf7_enqueue_scripts', array($this, 'contact_form_js'));
            add_action('wpcf7_editor_panels', array($this, 'cf7_esignature_panels'));
            add_action('wpcf7_after_save', array($this, 'esig_save_contact_form'));
            add_filter('esig_sif_buttons_filter', array($this, 'add_sif_cf7_buttons'), 13, 1);
            add_filter('esig_text_editor_sif_menu', array($this, 'add_sif_cf7_text_menu'), 13, 1);
            add_filter('esig_admin_more_document_contents', array($this, 'document_add_data'), 10, 1);
            add_action('wp_ajax_esig_cf7_form_fields', array($this, 'esig_cf7_form_fields'));
            add_action('wp_ajax_nopriv_esig_cf7_form_fields', array($this, 'esig_cf7_form_fields'));
            add_action('admin_init', array($this, 'esig_almost_done_cf7_settings'));
            add_filter('show_sad_invite_link', array($this, 'show_sad_invite_link'), 10, 3);
            add_filter('esig_invite_not_sent', array($this, 'show_invite_error'), 10, 2);
            add_shortcode('esigcf7', array($this, "render_shortcode_esigcf7"));
            add_action('wpcf7_mail_sent', array($this, 'esig_sad_page_redirect'), 999);
            add_action('wpcf7_before_send_mail', array($this, 'esig_cf7_processing'));

            add_filter('wpcf7_save_contact_form', array($this, 'confige_validte'), 10, 1);

            add_action('admin_notices', array($this, 'esig_cf7_addon_requirement'));
            add_action('admin_menu', array($this, 'esig_contactform_adminmenu'));
            add_action('esig_signature_loaded', array($this, 'after_sign_check_next_agreement'), 99, 1);
        }

        final function contact_form_js() {
            wp_enqueue_script('digital-signature-js', plugins_url('assets/js/cf7-front.js', __FILE__), array('jquery'), '12', true);
        }

        final function after_sign_check_next_agreement($args) {

            $document_id = $args['document_id'];

            if (!ESIG_CF7_SETTING::is_cf7_requested_agreement($document_id)) {
                return;
            }
            if (!ESIG_CF7_SETTING::is_cf7_esign_required()) {
                return;
            }

            $invite_hash = WP_E_Sig()->invite->getInviteHash_By_documentID($document_id);
            ESIG_CF7_SETTING::save_esig_cf7_meta($invite_hash, "signed", "yes");

            $temp_data = ESIG_CF7_SETTING::get_temp_settings();

            //$t_data = krsort($temp_data);

            foreach ($temp_data as $invite => $data) {
                if ($data['signed'] == "no") {
                    $invite_url = ESIG_CF7_SETTING::get_invite_url($invite);
                    wp_redirect($invite_url);
                    exit;
                }
            }
        }

        public function esig_contactform_adminmenu() {


            add_submenu_page('wpcf7', __('E-signature', 'esig'), __('E-signature', 'esig'), 'read', 'esign-cf7-about', array(&$this, 'cf7_about_page'));
            if (!function_exists('WP_E_Sig')) {


                if (empty($GLOBALS['admin_page_hooks']['esign'])) {
                    add_menu_page('E-Signature', 'E-Signature', 'read', "esign", array(&$this, 'esig_core_page'), plugins_url('assets/images/pen_icon.svg', __FILE__));
                }

                add_submenu_page("esign", "Contactform E-signature", "Contactform E-signature", 'read', "esign-cf7-about", array(&$this, 'cf7_about_page'));


                return;
            }
        }

        public function cf7_about_page() {

            include_once(dirname(__FILE__) . "/views/cf7-esign-about.php");
        }

        public function esig_core_page() {

            include_once(dirname(__FILE__) . "/views/esig-core-about.php");
        }

        final function esig_cf7_addon_requirement() {
            if (is_plugin_active('contact-form-7/wp-contact-form-7.php') && function_exists("WP_E_Sig") && class_exists('ESIG_SAD_Admin') && class_exists('ESIG_SIF_Admin'))
                return;


            include_once "views/alert-modal.php";
        }

        public function confige_validte($contact_form) {

            
            $content = ESIG_POST('wpcf7-form');
            
            list($firstName)= explode(" ",ESIG_POST('settings_signer_name'),2);
            if(!empty($firstName)){
            $signer_name = strpos($content,$firstName);
            }
            else {
                $signer_name=false;
            }
            
            $signer_email = strpos($content, ESIG_POST('settings_signer_email_address'));
            $http_referer = ESIG_POST('_wp_http_referer');
            $enableEsignature = ESIG_POST('settings_enable_esignature');
            $reminder_email = ESIG_POST('reminder_email');
            $first_reminder_send = ESIG_POST('first_reminder_send');
            $expire_reminder = ESIG_POST('expire_reminder');
            $selectSad = ESIG_POST('select_sad');
            $tab = ESIG_POST('active-tab');

            if ($enableEsignature) {

                $cf7_settings = array(
                    'settings_signer_name' => $signer_name,
                    'settings_signer_email_address' => $signer_email,
                    'signing_logic' => ESIG_POST('signing_logic'),
                    'settings_enable_esignature' => ESIG_POST('settings_enable_esignature'),
                    'select_sad' => $selectSad,
                    'underline_data' => ESIG_POST('underline_data'),
                    'signing_reminder_email' => ESIG_POST('signing_reminder_email'),
                    'esig_reminder_for' => absint($reminder_email),
                    'esig_reminder_repeat' => absint($first_reminder_send),
                    'esig_reminder_expire' => absint($expire_reminder),
                );
                update_post_meta(ESIG_POST('post_ID'), 'esig-cf7-settings', $cf7_settings);

                if (!$signer_name) {
                    wp_redirect($http_referer . "&active-tab={$tab}&signer_error=error");
                    exit;
                }
                if (!$signer_email) {
                    wp_redirect($http_referer . "&active-tab={$tab}&email_error=error");
                    exit;
                }
                if (empty($selectSad)) {
                    wp_redirect($http_referer . "&active-tab={$tab}&agreement_error=error");
                    exit;
                }
                if (!empty($reminder_email) && absint($reminder_email) === 0) {
                    wp_redirect($http_referer . "&active-tab={$tab}&reminder_email=error");
                    exit;
                }
                if (!empty($first_reminder_send) && absint($first_reminder_send) === 0) {
                    wp_redirect($http_referer . "&active-tab={$tab}&first_reminder_send=error");
                    exit;
                }
                if (!empty($expire_reminder) && absint($expire_reminder) === 0) {
                    wp_redirect($http_referer . "&active-tab={$tab}&expire_reminder=error");
                    exit;
                }
            }

            return true;
        }

        public function esig_sad_page_redirect($contact_form) {

            if (wpcf7_load_js()) {
                return false;
            }

            $cf7_settings = self::get_cf7_settings($contact_form->id());
            $invite_url = self::get_invite_url();
            if ($cf7_settings['signing_logic'] == "redirect") {
                if ($invite_url) {
                    self::remove_invite_url();
                    wp_redirect($invite_url);
                    exit;
                }
            }
        }

        /**
         * Get full name from 
         * @param type $legalName
         * @return string
         */
        
        private function getFullName($legalName) {
            
            $nameArray = explode(" ", $legalName);
            $signerName = "";
            foreach ($nameArray as $key => $name) {
                if ($key == 0) {
                    $signerName .= ESIG_POST($name);
                } else {
                    $signerName .= " " . ESIG_POST($name);
                }
            }
            return $signerName;
        }

        public function esig_cf7_processing($contact_form) {

            if (!function_exists('WP_E_Sig')) {
                return;
            }


            $sad = new esig_sad_document();
            $form_id = $contact_form->id();

            $cf7_settings = self::get_cf7_settings($form_id);

            $enableEsign = esigget('settings_enable_esignature', $cf7_settings);
            if (!$enableEsign) {
                return;
            }

            $legalName = $cf7_settings['settings_signer_name'];
            $legalEmail = $cf7_settings['settings_signer_email_address'];

            $signer_name = $this->getFullName($legalName);
            $signer_email = ESIG_POST($legalEmail);

            $signing_logic = $cf7_settings['signing_logic'];

            $sad_page_id = $cf7_settings['select_sad'];

            $document_id = $sad->get_sad_id($sad_page_id);

            if (!is_email($signer_email)) {
                return;
            }

            //sending email invitation / redirecting .
            self::esig_invite_document($document_id, $signer_email, $signer_name, $form_id, $signing_logic);
        }

        public static function esig_invite_document($old_doc_id, $signer_email, $signer_name, $form_id, $signing_logic) {


            if (!function_exists('WP_E_Sig'))
                return;


            global $wpdb;

            /* make it a basic document and then send to sign */
            $old_doc = WP_E_Sig()->document->getDocument($old_doc_id);

            // Copy the document
            $doc_id = WP_E_Sig()->document->copy($old_doc_id);

            WP_E_Sig()->meta->add($doc_id, 'esig_cf7_form_id', $form_id);
            //$api->meta->add($doc_id, 'esig_cf7_entry_id', $entry_id);
            WP_E_Sig()->document->saveFormIntegration($doc_id, 'cf7');
            self::save_submission_value($doc_id, $form_id);
            self::save_file_url($doc_id);
            // set document timezone
            $esig_common = new WP_E_Common();
            $esig_common->set_document_timezone($doc_id);
            // Create the user=
            $recipient = array(
                "user_email" => $signer_email,
                "first_name" => $signer_name,
                "document_id" => $doc_id,
                "wp_user_id" => '',
                "user_title" => '',
                "last_name" => ''
            );

            $recipient['id'] = WP_E_Sig()->user->insert($recipient);

            $doc_title = $old_doc->document_title . ' - ' . $signer_name;
            // Update the doc title


            

            WP_E_Sig()->document->updateTitle($doc_id, $doc_title);
            WP_E_Sig()->document->updateType($doc_id, 'normal');
            WP_E_Sig()->document->updateStatus($doc_id, 'awaiting');
            
            $doc = WP_E_Sig()->document->getDocument($doc_id);

            // trigger an action after document save .
            do_action('esig_sad_document_invite_send', array(
                'document' => $doc,
                'old_doc_id' => $old_doc_id,
            ));


            // Get Owner
            $owner = WP_E_Sig()->user->getUserByID($doc->user_id);


            // Create the invitation?
            $invitation = array(
                "recipient_id" => $recipient['id'],
                "recipient_email" => $recipient['user_email'],
                "recipient_name" => $recipient['first_name'],
                "document_id" => $doc_id,
                "document_title" => $doc->document_title,
                "sender_name" => $owner->first_name . ' ' . $owner->last_name,
                "sender_email" => $owner->user_email,
                "sender_id" => 'stand alone',
                "document_checksum" => $doc->document_checksum,
                "sad_doc_id" => $old_doc_id,
            );


            $invite_controller = new WP_E_invitationsController();

            if ($signing_logic == "email") {

                if ($invite_controller->saveThenSend($invitation, $doc)) {
                    return true;
                }
            } elseif ($signing_logic == "redirect") {
                $invitation_id = $invite_controller->save($invitation);
                $invite_hash = WP_E_Sig()->invite->getInviteHash($invitation_id);
                self::save_invite_url($invite_hash, $doc->document_checksum);
            }
        }

        public function render_shortcode_esigcf7($atts) {

            extract(shortcode_atts(array(
                'formid' => '',
                'field_id' => '', //foo is a default value
                            ), $atts, 'esigcf7'));

            if (!function_exists('WP_E_Sig'))
                return;



            $csum = isset($_GET['csum']) ? sanitize_text_field($_GET['csum']) : null;

            if (empty($csum)) {
                $document_id = get_option('esig_global_document_id');
            } else {
                $document_id = WP_E_Sig()->document->document_id_by_csum($csum);
            }

            $form_id = WP_E_Sig()->meta->get($document_id, 'esig_cf7_form_id');
            $cf7_settings = self::get_cf7_settings($form_id);
            $underline_data = $cf7_settings['underline_data'];

            if (empty($form_id)) {
                return;
            }

            $cf7_value = self::get_submission_value($document_id, $form_id, $field_id);


            //html link
            if (strpos($field_id, 'url') !== false) {

                return '<a href="' . $cf7_value . '" target="_blank">' . $cf7_value . '</a>';
            }

            if (strpos($field_id, 'your-email') !== false) {
                return '<a href="mailto:' . $cf7_value . '" target="_blank">' . $cf7_value . '</a>';
            }


            if (strpos($field_id, 'file') !== false) {
                return '<a href="' . self::get_file_url($document_id) . '" target="_blank">' . $cf7_value . '</a>';
            }
            //html link end here


            if (!$cf7_value) {
                return;
            }

            return self::display_value($underline_data, $form_id, $cf7_value);
        }

        final function esig_almost_done_cf7_settings() {


            if (!function_exists('WP_E_Sig'))
                return;

            // getting sad document id 
            $sad_document_id = isset($_GET['doc_preview_id']) ? $_GET['doc_preview_id'] : null;


            if (!$sad_document_id) {
                return;
            }
            // creating esignature api here 


            $documents = WP_E_Sig()->document->getDocument($sad_document_id);


            $document_content = $documents->document_content;

            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);


            if (has_shortcode($document_raw, 'esigcf7')) {


                preg_match_all('/' . get_shortcode_regex() . '/s', $document_raw, $matches, PREG_SET_ORDER);

                $esigcf7_shortcode = '';

                foreach ($matches as $match) {

                    if (in_array('esigcf7', $match)) {
                        $esigcf7_shortcode = $match[0];
                    }
                }

                WP_E_Sig()->document->saveFormIntegration($sad_document_id, 'cf7');

                $atts = shortcode_parse_atts($esigcf7_shortcode);

                extract(shortcode_atts(array(
                    'formid' => '',
                    'field_name' => '',
                                ), $atts, 'esigcf7'));
                $data = array("formid" => $formid);


                $display_notice = dirname(__FILE__) . '/views/alert-almost-done.php';
                WP_E_Sig()->view->renderPartial('', $data, true, '', $display_notice);
            }
        }

        public function esig_cf7_form_fields() {

            if (!function_exists('WP_E_Sig'))
                return;


            $form_id = ESIG_POST('form_id');
            $contact_form = WPCF7_ContactForm::get_instance($form_id);

            $html = '';

            $html .= '<select name="esig_cf7_field_id" class="chosen-select" style="width:250px;">';

            $post = get_post($form_id);

            $obj = WPCF7_ShortcodeManager::get_instance();

            $shortcodes = $obj->scan_shortcode($post->post_content);

            foreach ($shortcodes as $field) {

                if (strpos($field['name'], 'acceptance') !== false) {
                    continue;
                }

                if ($field['name'] == '') {
                    continue;
                }

                $html .= '<option value=' . $field['name'] . '>' . $field['name'] . '</option>';
            }

            $html .= '</select>';


            echo $html;

            die();
        }

        public function document_add_data($more_option_page) {

            $more_option_page .= $this->document_view->esig_cf7_document_view();
            return $more_option_page;
        }

        public function add_sif_cf7_buttons($sif_menu) {

            $esig_type = isset($_GET['esig_type']) ? $_GET['esig_type'] : null;

            $document_id = isset($_GET['document_id']) ? $_GET['document_id'] : null;

            if (empty($esig_type) && !empty($document_id)) {



                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }

            if ($esig_type != 'sad') {
                return $sif_menu;
            }

            $sif_menu .= ' {text: "Contact Form 7 Data",value: "cf7", onclick: function () { tb_show( "+ Contact Form 7 option", "#TB_inline?width=450&height=300&inlineId=esig-contact-option");}},';


            return $sif_menu;
        }

        public function add_sif_cf7_text_menu($sif_menu) {

            $esig_type = esigget('esig_type');
            $document_id = esigget('document_id');

            if (empty($esig_type) && !empty($document_id)) {
                $document_type = WP_E_Sig()->document->getDocumenttype($document_id);
                if ($document_type == "stand_alone") {
                    $esig_type = "sad";
                }
            }

            if ($esig_type != 'sad') {
                return $sif_menu;
            }
            $sif_menu['Contact'] = array('label' => "Contact Form 7 Data");
            return $sif_menu;
        }

        public function esig_save_contact_form($contact_form) {

            $sad = new esig_sad_document();

            //$form_id = ESIG_POST('post_ID');

            $signer_name = ESIG_POST('settings_signer_name');

            $signer_email = ESIG_POST('settings_signer_email_address');

            $sad_page_id = ESIG_POST('select_sad');
            $signing_logic = ESIG_POST('signing_logic');
            $underline_data = ESIG_POST('underline_data');
            $signing_reminder_email = ESIG_POST('signing_reminder_email');

            $document_id = $sad->get_sad_id($sad_page_id);
            $reminder_email = ESIG_POST('reminder_email');
            $first_reminder_send = ESIG_POST('first_reminder_send');
            $expire_reminder = ESIG_POST('expire_reminder');

            $cf7_settings = array(
                'settings_signer_name' => $signer_name,
                'settings_signer_email_address' => $signer_email,
                'signing_logic' => $signing_logic,
                'settings_enable_esignature' => ESIG_POST('settings_enable_esignature'),
                'select_sad' => $sad_page_id,
                'underline_data' => $underline_data,
                'signing_reminder_email' => $signing_reminder_email,
                'esig_reminder_for' => absint($reminder_email),
                'esig_reminder_repeat' => absint($first_reminder_send),
                'esig_reminder_expire' => absint($expire_reminder),
            );

            update_post_meta(ESIG_POST('post_ID'), 'esig-cf7-settings', $cf7_settings);

            if ($signing_reminder_email == '1') {
                $esig_cf7_reminders_settings = array(
                    "esig_reminder_for" => absint($reminder_email),
                    "esig_reminder_repeat" => absint($first_reminder_send),
                    "esig_reminder_expire" => absint($expire_reminder),
                );

                WP_E_Sig()->meta->add($document_id, "esig_reminder_settings_", json_encode($esig_cf7_reminders_settings));
                WP_E_Sig()->meta->add($document_id, "esig_reminder_send_", "1");
            }
        }

        public function cf7_esignature_panels($panels) {
            $panels['esignature-panel'] = array('title' => 'E-signature', 'callback' => array($this, 'cf7_esignature_panel_meta'));
            return $panels;
        }

        public function cf7_esignature_panel_meta($post) {

            $cf7_settings = self::get_cf7_settings($post->id());

            $signer_name = $cf7_settings['settings_signer_name'];
            $signer_email = $cf7_settings['settings_signer_email_address'];
            $settings_enable_esignature = esigget('settings_enable_esignature', $cf7_settings);
            $signing_logic = $cf7_settings['signing_logic'];
            $select_sad = $cf7_settings['select_sad'];
            $underline_data = $cf7_settings['underline_data'];
            $signing_reminder_email = $cf7_settings['signing_reminder_email'];
            $reminder_email = $cf7_settings['esig_reminder_for'];
            $first_reminder_send = $cf7_settings['esig_reminder_repeat'];
            $expire_reminder = $cf7_settings['esig_reminder_expire'];

            include plugin_dir_path(__FILE__) . '/views/esig-cf7-view.php';
        }

        public static function get_cf7_settings($post_id) {

            $settings = get_post_meta($post_id, 'esig-cf7-settings', true);
            if (is_array($settings)) {
                return $settings;
            }
            return false;
        }

        final function show_sad_invite_link($show, $doc, $page_id) {
            if (!isset($doc->document_content)) {
                return $show;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigcf7')) {

                $show = false;
                return $show;
            }
            return $show;
        }

        final function show_invite_error($ret, $docId) {

            $doc = WP_E_Sig()->document->getDocument($docId);
            if (!isset($doc->document_content)) {
                return $show;
            }
            $document_content = $doc->document_content;
            $document_raw = WP_E_Sig()->signature->decrypt(ENCRYPTION_KEY, $document_content);

            if (has_shortcode($document_raw, 'esigcaldera')) {

                $ret = true;
                return $ret;
            }
            return $ret;
        }

        public function enqueue_admin_styles() {
            $screen = get_current_screen();

            $admin_screens = array(
                'admin_page_esign-contact-about',
                'admin_page_esign-cf7-about',
                'forms_page_esign-contact-about',
                'contact_page_esign-cf7-about'
            );



            if (in_array($screen->id, $admin_screens)) {

                wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('assets/css/esig-cf7-about.css', __FILE__), array());
            }
        }

        public function enqueue_admin_scripts() {

            $screen = get_current_screen();

            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document',
            );



            if (in_array($screen->id, $admin_screens)) {
                wp_enqueue_script('jquery');
                wp_enqueue_script('cf7-digital-signature' . '-admin-script', plugins_url('assets/js/esig-add-cf7.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }

            if ($screen->id != "plugins") {
                wp_enqueue_script('cf7-digital-signature' . '-admin-script', plugins_url('assets/js/esig-cf7-control.js', __FILE__), array('jquery', 'jquery-ui-dialog'), '0.1.0', true);
            }
        }

        public function cf7_wpesignature_init_text_domain() {

            load_plugin_textdomain('cf7-wpesignature', FALSE, CF7_WPESIGNATURE_PATH . 'languages');
        }

        /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

    }

    

    

    

    

    

    
    
endif;

