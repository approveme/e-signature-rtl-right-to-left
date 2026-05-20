<?php
/**
 * @package   	      WP E-Signature
 * @author	      Kevin Michael Gray (Approve Me), Abu Shoaib(Approve Me)
 * @wordpress-plugin
 * Plugin Name:       WP E-Signature - RTL
 * Plugin URI:        http://approveme.me/wp-digital-e-signature
 * Description:       Adds Right-to-left (RTL) support to Agreement page & Admin Area of E-signature.
 * Version:           2.0
 * Author:            ApproveMe.com
 * Author URI:        https://www.approveme.com/
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
define('ESIGN_RTL_URL', plugins_url("", __FILE__));
define('ESIGN_RTL_VERSION', '1.8.0');
define('ESIGN_RTL_PATH', plugin_dir_path(__FILE__));

if (!class_exists("Esig_RTL")):

    class Esig_RTL {


/**
 * Constructor
 * @since 1.8.0
 * @return void
 */
        public function __construct() {

            // Initialize RTL functionality
            add_action("init", array($this, "esig_rtl_init"));

            add_action("admin_notices", array($this, "esig_requirement_fallback"));
            add_filter("esig-pdf-export-stylesheet", array($this, "esig_rtl_pdf_styles"), 10, 1);
            add_filter("esign-rtl-signature-margin", array($this, "rtl_signature_margin"), 10, 1);

            // Enqueue frontend styles properly
            add_action("wp_enqueue_scripts", array($this, "esig_rtl_frontend_styles"), 20);
            
            // Load rtl admin styles properly
            add_action("admin_enqueue_scripts", array($this, "enqueue_admin_styles"), 20);
            
            // Add RTL body class to frontend
            add_filter("body_class", array($this, "add_rtl_body_class"), 10, 1);
            
            // Add dir attribute to HTML tag
            add_action("wp_head", array($this, "add_rtl_html_direction"), 1);
            
            // Add RTL support to e-signature templates
            add_filter("esig_document_template", array($this, "add_rtl_template_support"), 10, 3);
        }

        /**
         * Add RTL signature margin
         * 
         * @param int $signatureLeanth The length of the signature
         * @return string The margin style
         * @since 1.8.0
         */ 
        public function rtl_signature_margin($signatureLeanth) {           
            
            if ($signatureLeanth <= 40){
                $margin = "margin-top:14%;";
            }else{
                $margin = "margin-top:2%;";
            }                  
            return $margin;
            
        }

        /**
         * Add RTL styles to PDF export
         * 
         * @param string $stylesheet The existing PDF stylesheet
         * @return string The modified PDF stylesheet with RTL styles
         * @since 1.8.0
         */
        public function esig_rtl_pdf_styles($stylesheet = '') {
            if (!self::is_rtl_enabled()) {
                return $stylesheet;
            }
            
            $rtl_css_path = ESIGN_RTL_PATH . 'assets/css/rtl-pdf.css';
            
            // Check if RTL PDF CSS file exists
            if (file_exists($rtl_css_path)) {
                $rtl_style_data = file_get_contents($rtl_css_path);
                
                // Append RTL styles to existing stylesheet
                $stylesheet .= "\n\n/* RTL Styles */\n" . $rtl_style_data;
            }
            
            return $stylesheet;
        }

        /**
         * Display error message if WP E-Signature is not installed
         * @since 1.8.0
         */
        public function esig_requirement_fallback() {
            if (!function_exists("WP_E_Sig")) {
                echo ' <div class="error"> <h4>WP E-Signature is not installed. &nbsp; It is required to run the E-Signature RTL add-on. &nbsp;Get your business license now  - <a href="http://aprv.me">http://aprv.me</a></h4></div>';
            }
        }

        /**
         * Initialize RTL functionality
         * @since 1.8.0
         */
        public function esig_rtl_init() {
            // check esignature core is installed if not return 
            if (!function_exists("WP_E_Sig")) {
                return;
            }

            //check this user has esignature privilege 
            if (self::has_esig_privilege()) {
                add_filter("mce_external_plugins", array($this, "tinymce_bi_directional_plugin"));
                add_filter("mce_buttons", array($this, "tinymce_bi_directional_buttons"));
            }
            // check this site is rtl enabled 
            if (self::is_rtl_enabled()) {

                $api = new WP_E_Api();
                $document_api = new WP_E_Document();
                $doc_id = isset($_GET['csum']) ? $document_api->document_id_by_csum($_GET['csum']) : null;
                if (!$doc_id) {
                    $doc_id = esigget("document_id");
                }
                $document_type = $api->document->getDocumenttype($doc_id);

                add_action("esig_head", array($this, "esig_rtl_frontend_styles"));

                $current_screen = isset($_GET['page']) ? $_GET['page'] : '';

                $signature_screens = array(
                    'esign-add-document',
                    'esign-edit-document',
                    'esign-addons',
                );

                // if (in_array($current_screen, $signature_screens)) {
                //add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
                // }
            }
        }

        /**
         * Enqueue admin styles
         * @since 1.8.0
         */
        public function enqueue_admin_styles() {
            // check rtl enabled
            if (!self::is_rtl_enabled()) {
                return;
            }
            
            // Check if we're on an e-signature admin page
            if (!$this->is_esignature_admin_page()) {
                return;
            }
            
            // Enqueue rtl admin styles properly using WordPress function
            wp_enqueue_style(
                'esig-rtl-admin-css', 
                ESIGN_RTL_URL . '/assets/css/rtl-admin.css', 
                array(), 
                ESIGN_RTL_VERSION, 
                'all'
            );
        }

        /**
         * Enqueue frontend styles
         * @since 1.8.0
         */
        public function esig_rtl_frontend_styles() {

            // check rtl enabled
            if (!self::is_rtl_enabled()) {
                return;
            }

            // Only load on e-signature pages
            if (!$this->is_esignature_page()) {
                return;
            }

            // Enqueue styles properly using WordPress functions
            if (wp_is_mobile()) {
                wp_enqueue_style(
                    'esig-rtl-mobile-css', 
                    ESIGN_RTL_URL . '/assets/css/rtl-basic-mobile.css', 
                    array(), 
                    ESIGN_RTL_VERSION, 
                    'all'
                );
            } else {
                wp_enqueue_style(
                    'esig-rtl-css', 
                    ESIGN_RTL_URL . '/assets/css/rtl.css', 
                    array('esig-frontend'), 
                    ESIGN_RTL_VERSION, 
                    'all'
                );
                
                wp_enqueue_style(
                    'esig-rtl-print-css', 
                    ESIGN_RTL_URL . '/assets/css/rtl-print.css', 
                    array(), 
                    ESIGN_RTL_VERSION, 
                    'print'
                );
            }           
        }

        /**
         * Enqueue basic PDF styles
         * @since 1.8.0
         */
        public function esig_rtl_basic_pdf_styles() {
            
            if (wp_is_mobile()) {
                echo "<link rel='stylesheet' id='esig-rtl-css-basic'  href='" . plugins_url('assets/css/rtl-basic-mobile.css', __FILE__) . "' type='text/css' media='all' />";
            }
            else {
               echo "<link rel='stylesheet' id='esig-rtl-css-basic'  href='" . plugins_url('assets/css/rtl-basic.css', __FILE__) . "' type='text/css' media='all' />"; 
            }
        }

        /**
         * Add RTL buttons to TinyMCE
         * @since 1.8.0
         */
        public function tinymce_bi_directional_buttons(array $buttons) {
            array_push($buttons, "separator", "ltr", "rtl");
            return $buttons;
        }

        /**
         * Add RTL plugin to TinyMCE
         * @since 1.8.0
         */
        public function tinymce_bi_directional_plugin(array $plugins) {

            //checking wp version 
            $wp_version = self::get_wp_version();
            if (version_compare('3.9', $wp_version, "<")) {
                $plugins['directionality'] = includes_url('js/tinymce/plugins/directionality/editor_plugin.js');
            } else {
                $plugins['directionality'] = includes_url('js/tinymce/plugins/directionality/plugin.min.js');
            }

            return $plugins;
        }

        /**
         * Check if user has esignature privilege
         * @since 1.8.0
         */
        private static function has_esig_privilege() {

            // check for admin pages . 
            if (!is_admin()) {
                return false;
            }
            $role = new WP_E_Esigrole;
            // check user has privilege to create documents 
            if (!$role->esig_current_user_can('edit_document')) {

                return false;
            }

            if (self::get_current_post_type() != "esign") {
                return false;
            }


            return true;
        }

        /**
         * Get current post type
         * @since 1.8.0
         */
        private static function get_current_post_type() {

            global $post, $typenow, $current_screen;

            //we have a post so we can just get the post type from that
            if ($post && $post->post_type)
                return $post->post_type;

            //check the global $typenow - set in admin.php
            elseif ($typenow)
                return $typenow;

            //check the global $current_screen object - set in sceen.php
            elseif ($current_screen && $current_screen->post_type)
                return $current_screen->post_type;

            //lastly check the post_type querystring
            elseif (isset($_REQUEST['post_type']))
                return sanitize_key($_REQUEST['post_type']);

            //we do not know the post type!
            return null;
        }

        /**
         * Get WordPress version
         * @since 1.8.0
         */
        private static function get_wp_version() {
            global $wp_version;
            return isset($wp_version) ? (string) $wp_version : '5.0';
        }

        /**
         * Check if RTL is enabled
         * @since 1.8.0
         */
        private static function is_rtl_enabled() {
            if (is_rtl()) {
                return true;
            }
            return false;
        }

        /**
         * Check if current page is an e-signature page
         * @since 1.8.0
         */
        private function is_esignature_page() {
            // Check if we're on a page
            if (!is_page()) {
                return false;
            }

            // Check if e-signature is loaded
            if (!function_exists('WP_E_Sig')) {
                return false;
            }

            // Check if page has e-signature shortcode or is default e-signature page
            if (function_exists('has_esig_shortcode') && function_exists('get_queried_object_id')) {
                $current_page = get_queried_object_id();
                if (has_esig_shortcode($current_page)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Check if current admin page is an e-signature admin page
         * @since 1.8.0
         */
        private function is_esignature_admin_page() {
            if (!is_admin()) {
                return false;
            }

            $screen = get_current_screen();
            if (!$screen) {
                return false;
            }

            // Check if we're on an e-signature admin page
            $esig_screens = array(
                'esign-docs',
                'esign-add-document',
                'esign-edit-document',
                'esign-settings',
                'esign-addons',
                'esign-view-document',
            );

            // Check if current page matches e-signature screens
            foreach ($esig_screens as $esig_screen) {
                if (strpos($screen->id, $esig_screen) !== false) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Add RTL body class to frontend pages
         * @since 1.8.0
         */
        public function add_rtl_body_class($classes) {
            if (!self::is_rtl_enabled()) {
                return $classes;
            }

            if ($this->is_esignature_page()) {
                $classes[] = 'esig-rtl';
                $classes[] = 'rtl';
            }

            return $classes;
        }

        /**
         * Add dir="rtl" to HTML tag via wp_head
         * @since 1.8.0
         */
        public function add_rtl_html_direction() {
            if (!self::is_rtl_enabled()) {
                return;
            }

            if ($this->is_esignature_page()) {
                echo '<script>document.documentElement.setAttribute("dir", "rtl");</script>';
            }
        }

        /**
         * Add RTL template support to e-signature documents
         * @since 1.8.0
         */
        public function add_rtl_template_support($template, $default_page_id, $current_page_id) {
            if (!self::is_rtl_enabled()) {
                return $template;
            }

            // Add filter to modify template output if needed
            add_filter('wp_footer', array($this, 'add_rtl_script_footer'), 999);

            return $template;
        }

        /**
         * Add RTL JavaScript fixes in footer
         * @since 1.8.0
         */
        public function add_rtl_script_footer() {
            if (!self::is_rtl_enabled()) {
                return;
            }

            if (!$this->is_esignature_page()) {
                return;
            }

            ?>
            <script type="text/javascript">
                (function($) {
                    'use strict';
                    
                    // Ensure RTL direction is applied
                    $(document).ready(function() {
                        // Add dir attribute to main containers
                        $('.SX-signing-page, .document-sign-page, .doc_page').attr('dir', 'rtl');
                        
                        // Apply RTL to signature containers
                        $('.wpesign__signature-container, #wpesign__signature-container').css('direction', 'rtl');
                        
                        // Apply RTL to form elements
                        $('form#wpesignature, form.sign-form').attr('dir', 'rtl');
                    });
                })(jQuery);
            </script>
            <?php
        }

    }

    endif;

// initialize wp esignature rtl 
new Esig_RTL();

