<?php

/**
 * @package   	      WP E-Signature
 * @author	      Kevin Michael Gray (Approve Me), Abu Shoaib(Approve Me)
 * @wordpress-plugin
 * Plugin Name:       WP E-Signature - RTL
 * Plugin URI:        http://approveme.me/wp-digital-e-signature
 * Description:       Adds Right-to-left (RTL) support to Agreement page & Admin Area of E-signature.
 * Version:           1.4.0
 * Author:            Approve Me
 * Author URI:        http://approveme.me/
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
define('ESIGN_RTL_URL',  plugins_url("",__FILE__));

if (!class_exists("Esig_RTL")):

    class Esig_RTL {

        public function __construct() {

            add_action("init", array($this, "esig_rtl_init"));

            add_action("admin_notices", array($this, "esig_requirement_fallback"));
            add_filter("esig-pdf-export-stylesheet",array($this,"esig_rtl_pdf_styles"), 10, 1);
        }
        
        public function esig_rtl_pdf_styles($stylesheet) {
            
            $style_data = file_get_contents( ESIGN_RTL_URL . '/assets/css/rtl-pdf.css'); // external rtl pdf css
            $stylesheet .= $style_data ;
            return $stylesheet;
        }

       
        
        public function esig_requirement_fallback() {
            if (!function_exists("WP_E_Sig")) {
                echo ' <div class="error"> <h4>WP E-Signature is not installed. &nbsp; It is required to run the E-Signature RTL add-on. &nbsp;Get your business license now  - <a href="http://aprv.me">http://aprv.me</a></h4></div>';
            }
        }

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
                $document_type = $api->document->getDocumenttype($doc_id);
                
                if ($document_type == "normal") {
                add_action("esig_head", array($this, "esig_rtl_basic_pdf_styles"));
                }
                else{
                  add_action("esig_head", array($this, "esig_rtl_frontend_styles"));  
                }
                
                $current_screen = isset($_GET['page']) ? $_GET['page'] : '';
                
                $signature_screens = array(
                'esign-add-document',
                'esign-edit-document',
                'esign-addons',
                );
                
               // if (in_array($current_screen, $signature_screens)) {
                add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
               // }
                
            }
        }
        
        public function enqueue_admin_styles() {
            echo "<link rel='stylesheet' id='esig-rtl-css'  href='" . plugins_url('assets/css/rtl-admin.css', __FILE__) . "' type='text/css' media='all' />";
        }

        public function esig_rtl_frontend_styles() {
            echo "<link rel='stylesheet' id='esig-rtl-css'  href='" . plugins_url('assets/css/rtl.css', __FILE__) . "' type='text/css' media='all' />";
        }

        public function esig_rtl_basic_pdf_styles() {
            echo "<link rel='stylesheet' id='esig-rtl-css-basic'  href='" . plugins_url('assets/css/rtl-basic.css', __FILE__) . "' type='text/css' media='all' />";
        }
        
        public function tinymce_bi_directional_buttons(array $buttons) {
            array_push($buttons, "separator", "ltr", "rtl");
            return $buttons;
        }

        public function tinymce_bi_directional_plugin(array $plugins) {

            //checking wp version 
            if (version_compare(3.9, self::get_wp_version(), "<")) {
                $plugins['directionality'] = includes_url('js/tinymce/plugins/directionality/editor_plugin.js');
            } else {
                $plugins['directionality'] = includes_url('js/tinymce/plugins/directionality/plugin.min.js');
            }

            return $plugins;
        }

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

        private static function get_wp_version() {
            return bloginfo('version');
        }

        private static function is_rtl_enabled() {
            if (is_rtl()) {
                return true;
            }
            return false;
        }

    }

    endif;

// initialize wp esignature rtl 
new Esig_RTL();

