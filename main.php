<?php

/*
Plugin Name: Adas Wpforms Database Add-On 
Description: Enhance WPForms with a powerful database feature for effortless storage and organization of form submissions.
Version: 1.0
Author: Khalidlogi
License: GPLv2 or later
Text Domain: adas
*/

if(!defined('ABSPATH')) {
    exit;
}
//ini_set('display_errors', 1);

if(!class_exists('KHMYCLASS')) {
    class KHMYCLASS {


        public $version = '1.0';
        //private $mydb;
        private $mysetts;
        private $myselectedformid;
        private $mylink;
        private $text_color;
        private $label_color;
        private $bgcolor;
        private $exportbgcolor;
        private $isdataenabled;
        private $isnotif;

        public function __construct() {
            // Setup and initialization
            $this->setup_constants();
            $this->includes();

            // Instantiate Datamanagmnet class and settings class
            //$this->mydb = new KHdb();
            $this->mysetts = new KHSettings();

            // Hooks and Actions
            //$this->regsiter_hooks();

            // Activate if enabled
            $this->isdataenabled = get_option('Enable_data_saving_checkbox') ?: '1';

            if($this->isdataenabled === '1') {
                add_action('wpforms_process_entry_save', array($this, 'process_entry'), 10, 4);
            }

            // Redirect back after logged in
            add_action('wp_login', array($this, 'redirect_to_saved_url'));

        }


        // Redirect users to the saved URL upon login
        function redirect_to_saved_url() {
            $saved_url = get_option('saved_url');
            if(!empty($saved_url)) {
                $redirect_param = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                $paged_param = isset($_GET['paged']) ? $_GET['paged'] : ''; // Check for 'paged' parameter
                if($redirect_param === 'specific_value' && empty($paged_param)) {
                    delete_option('saved_url');
                    wp_redirect($saved_url);
                    exit;
                }
            }
        }


        /**
         * Include all the necessary files
         */
        private function includes() {
            include_once KHFORM_PATH.'Inc/KHTelegram.php';
            include_once KHFORM_PATH.'Inc/KHCSV.php';
            include_once KHFORM_PATH.'Inc/KHSettings.php';
            include_once KHFORM_PATH.'Inc/KHPDF.php';
            include_once KHFORM_PATH.'Inc/KHdb.php';
            include_once KHFORM_PATH.'Inc/AjaxClass.php';
            include_once KHFORM_PATH.'Inc/KHwidget.php';
            include_once KHFORM_PATH.'Inc/display_form_values_shortcode.php';
            include_once KHFORM_PATH.'Inc/EnqueueClass.php';
            include_once KHFORM_PATH.'Inc/KHinstall.php';
        }


        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function setup_constants() {

            // Plugin version.
            if(!defined('KHFORM_DOMAIN')) {
                define('KHFORM_DOMAIN', 'khwpformsdb');
            }
            // Plugin version.
            if(!defined('KHFORM_VERSION')) {
                define('KHFORM_VERSION', $this->version);
            }

            // Plugin Folder Path.
            if(!defined('KHFORM_PATH')) {
                define('KHFORM_PATH', plugin_dir_path(__FILE__));
            }

            /* Plugin Folder URL.
            if (!defined('WPFORMS_PLUGIN_URL')) {
                define('KHFORM_URL', plugin_dir_url(__FILE__));
            }*/
        }

        /**
         * Function to insert data into database
         * @return void
         */
        function process_entry($fields, $entry, $form_data, $entry_id) {


            global $wpdb;

            error_log(' function process_entry');

            //save permalink for redirect purposes
            $current_url = get_permalink();
            update_option('saved_url', $current_url);

            //error_log('process_entry activated');
            // Obviously we need to have form fields to proceed.
            if(empty($fields)) {
                return;
            }

            //$now = new DateTime($entry->date);
            //error_log(print_r($entry), true);
            //error_log(print_r($now, true));

            $form_date = current_time('Y-m-d H:i:s');

            if($fields) {

                foreach($fields as $field) {
                    $name = sanitize_text_field($field['name']); // Sanitize field name
                    $value = is_array($field['value']) ? serialize($field['value']) : $field['value'];

                    // Check if the value contains newlines and replace them with '&'.
                    $value = str_replace("\n", " & ", $value);
                    $serialized_data[$name] = $value;
                }
            }

            // insert data into table
            $wpdb->insert(
                $wpdb->prefix.'wpforms_db2',
                // table name
                array(
                    //'email' => $email,
                    'form_id' => $entry_id['id'],
                    'form_value' => serialize($serialized_data),
                    'form_date' => $form_date
                ),
                array(

                    '%s',
                    // form_fields
                    '%s',
                    // form_data
                )
            );

            $this->send_confirmation_on_telegram($serialized_data, $entry_id);
        }

        /**
         *Send Telegram notifications
         *
         * @return void
         */
        public function send_confirmation_on_telegram($serialized_data = '', $entry_id = '') {

            //send telegram notifications
            if($this->isnotif === '1') {
                $telegram = new KHTelegram();
                $telegram->send_khwpforms_message($serialized_data, $entry_id);
                $telegram->sendNotification();
            }
        }


    }
}

if(class_exists('KHMYCLASS')) {
    new KHMYCLASS();
    new KHCSV();
    new KHPDF();
}