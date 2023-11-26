<?php

/*
Plugin Name: Adas_Wpforms_Database_Add-On 
Description: Enhance WPForms with a powerful database feature for effortless storage and organization of form submissions.
Version: 1.0
Author: Khalidlogi
License: GPLv2 or later
Text Domain: adas
*/

// to do 
// if fiels admin exist, skip admin field 
// remove ini_set('display_errors', 1);

if (!defined('ABSPATH')) {
    exit;
}
//error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!class_exists('KHMYCLASS')) {
    class KHMYCLASS
    {
        /**
         * Plugin version for enqueueing, etc.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '1.0';
        private $mydb;
        private $mysetts;
        private $table_name;
        private $myselectedformid;
        private $mylink;
        private $text_color;
        private $label_color;
        private $bgcolor;

        private $exportbgcolor;
        private $isdataenabled;
        private $isnotif;

        public function __construct()
        {
            // Setup and initialization
            $this->setup_constants();
            $this->includes();
            $this->mydb = new KHdb();
            $this->mysetts = new KHSettings();

            // Hooks and Actions
            $this->regsiter_hooks();
            //add_shortcode('display_form_values', array($this, 'display_form_values_shortcode'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_form_values_css'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_font_awesome'));

            add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_script'));
            add_action('admin_enqueue_scripts', array($this, 'admin_styles'));

            // Activate if enabled
            if ($this->isdataenabled === '1') {
                add_action('wpforms_process_entry_save', array($this, 'process_entry'), 10, 4);
            }


            // Other actions
            // add_filter('login_redirect', array($this, 'custom_login_redirect'), 10, 3);
            add_action('wp_login', array($this, 'redirect_to_saved_url'));




        }






        // Redirect users to the saved URL upon login
        function redirect_to_saved_url()
        {
            $saved_url = get_option('saved_url');
            if (!empty($saved_url)) {
                $redirect_param = isset($_GET['redirect']) ? $_GET['redirect'] : '';
                $paged_param = isset($_GET['paged']) ? $_GET['paged'] : ''; // Check for 'paged' parameter
                if ($redirect_param === 'specific_value' && empty($paged_param)) {
                    delete_option('saved_url');
                    wp_redirect($saved_url);
                    exit;
                }
            }
        }

        /* Translation */
        public function kh_wpfdb_load_textdomain()
        {
            load_plugin_textdomain(TABLESOME_DOMAIN, false, basename(dirname(__FILE__)) . '/languages');
        }

        function enqueue_font_awesome()
        {
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), '5.15.3');
        }

        /**
         * Enqueue CSS styles for the form values.
         */
        function enqueue_form_values_css()
        {



            // Enqueue your custom CSS.
            wp_enqueue_style('form-values-style', plugin_dir_url(__FILE__) . 'assets/css/form-values.css');

            // Enqueue Font Awesome from a CDN.
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), '5.15.3');

            // Enqueue jQuery UI stylesheet (optional).
            wp_enqueue_style('jquery-ui-style', plugin_dir_url(__FILE__) . 'assets/css/jquery-ui.css');
        }

        /**
         * Enqueue custom JavaScript script.
         */
        function enqueue_custom_script()
        {
            // Enqueue your custom JavaScript.
            wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery'), '1.0', true);

            // Localize the script with custom variables for AJAX.
            wp_localize_script('custom-script', 'custom_vars', array('ajax_url' => admin_url('admin-ajax.php')));

            // Enqueue jQuery UI scripts (core and droppable) (optional).
            wp_enqueue_script('jquery-ui-core', plugin_dir_url(__FILE__) . 'assets/js/jquery-ui-core', array('jquery'), '1.0', true);
            wp_enqueue_script('jquery-ui-droppable');
        }
        public function kh_wpfdb_activation()
        {
            if (!version_compare(PHP_VERSION, '5.4', '>=')) {
                add_action('admin_notices', array($this, 'kh_wpfdb_fail_php_version'));
            } elseif (!version_compare(get_bloginfo('version'), '4.5', '>=')) {
                add_action('admin_notices', array($this, 'kh_wpfdb_fail_wp_version'));
            }
        }

        public function kh_wpfdb_fail_php_version()
        {
            /* translators: %s: PHP version */
            $message = sprintf(esc_html__('kh_wpforms_db plugin requires PHP version %s+, plugin may not work properly.', 'khwpformsdb'), '5.4');
            $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
            echo wp_kses_post($html_message);
        }

        /**
         * Show in WP Dashboard notice about the plugin is not activated (WP version).
         * @since 1.5.0
         * @return void
         */
        public function kh_wpfdb_fail_wp_version()
        {
            /* translators: %s: WP version */
            $message = sprintf(esc_html__('kh_wpforms_db plugin requires WordPress version %s+. Because you are using an earlier version, the plugin may not work properly.', 'khwpformsdb'), '4.5');
            $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
            echo wp_kses_post($html_message);
        }


        /**
         * Include all the necessary files
         */
        private function includes()
        {
            include_once KHFORM_PATH . 'Inc/KHTelegram.php';
            include_once KHFORM_PATH . 'Inc/KHCSV.php';
            include_once KHFORM_PATH . 'Inc/KHSettings.php';
            include_once KHFORM_PATH . 'Inc/KHPDF.php';
            include_once KHFORM_PATH . 'Inc/KHdb.php';
            include_once KHFORM_PATH . 'Inc/AjaxClass.php';
            include_once KHFORM_PATH . 'Inc/KHwidget.php';
            include_once KHFORM_PATH . 'Inc/display_form_values_shortcode.php';

        }

        /**
         * Styles for Dashboard
         *
         * @return void
         */
        function admin_styles()
        {
            wp_enqueue_style('admin_style', plugin_dir_url(__FILE__) . 'assets/css/admin.css');
            wp_enqueue_style('admin_style', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css');


        }


        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function setup_constants()
        {

            global $wpdb;

            // Plugin version.
            if (!defined('KHFORM_DOMAIN')) {
                define('KHFORM_DOMAIN', 'khwpformsdb');
            }
            // Plugin version.
            if (!defined('KHFORM_VERSION')) {
                define('KHFORM_VERSION', $this->version);
            }

            // Plugin Folder Path.
            if (!defined('KHFORM_PATH')) {
                define('KHFORM_PATH', plugin_dir_path(__FILE__));
            }

            /* Plugin Folder URL.
            if (!defined('WPFORMS_PLUGIN_URL')) {
                define('KHFORM_URL', plugin_dir_url(__FILE__));
            }*/

            //table name wpforms_db2
            $this->table_name = $wpdb->prefix . 'wpforms_db2';

        }

        /**
         * function to create table
         *
         */
        public function create_table()
        {
            global $wpdb;
            $this->mydb->create_tabledb();
        }

        /**
         * Delete wpforms_db2 table on plugin deactivation
         *
         */
        public function deactivate()
        {
            global $wpdb;
            // $this->mydb->delete_tabledb();
        }

        /**
         * Plugin activation hook callback function.
         */
        public function activate()
        {
            global $wp_version;
            $this->kh_wpfdb_activation();
            // Create the table on plugin activation
            $this->create_table();
        }

        /**
         *  Update form values
         *
         * @return void
         */
        function update_form_values()
        {

            global $wpdb;

            // Retrieve the serialized form data from the AJAX request
            $form_data = sanitize_text_field($_POST['formData']);
            $form_id = intval($_POST['form_id']);
            $id = intval($_POST['id']);

            // Parse the serialized form data
            parse_str($form_data, $fields);


            if (!$id) {
                wp_send_json_error('Invalid ID');
                exit;
            }

            // Check permissions
            if (!current_user_can('delete_posts')) {
                wp_send_json_error('Insufficient permissions');
                exit;
            }

            // Check for nonce security      
            if (!wp_verify_nonce($_POST['nonceupdate'], 'nonceupdate')) {
                die('Busted!');
            }

            $status = $wpdb->update(
                $this->table_name,
                array('form_value' => serialize($fields)),
                array('id' => $id)
            );

            if ($status === false) {
                // An error occurred, send an error response
                $error_message = $wpdb->last_error;
                wp_send_json_error(array('message' => $error_message));
            } else {
                // Update was successful, send a success response
                wp_send_json_success(array('message' => 'Update successful!', 'fieldsfromupdate' => $fields));
            }

        }


        /**
         * Retrieve and return form values
         *
         * @return  array $fields
         *
         */
        function get_form_values()
        {
            global $wpdb;

            $form_id = intval($_POST['form_id']);
            $id = intval($_POST['id']);

            // Fetch form_value from the wpform_db2 table based on the form_id
            $query = $wpdb->prepare("SELECT id, form_value FROM $this->table_name WHERE id = %d", $id);
            $serialized_data = $wpdb->get_results($query);

            if ($wpdb->last_error) {
                wp_send_json_error('Error: ' . $wpdb->last_error);
            }

            if ($serialized_data) {
                // Unserialize the serialized form value
                $unserialized_data = unserialize($serialized_data[0]->form_value);
                $fields = array();

                foreach ($unserialized_data as $key => $value) {
                    $fields[] = array(
                        'name' => $key,
                        'value' => $value
                    );
                }

                wp_send_json_success(array('fields' => $fields));
            } else {
                wp_send_json_error('Form values not found for the given form_id.');
            }
        }


        /**
         * delete form row by its id
         */
        function delete_form_row()
        {
            global $wpdb;

            $id = intval($_POST['form_id']);

            if (!$id) {
                wp_send_json_error('Invalid ID');
                exit;
            }

            // Check permissions
            if (!current_user_can('delete_posts')) {
                wp_send_json_error('Insufficient permissions');
                exit;
            }

            // Check for nonce security      
            if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
                die('Busted!');
            }

            $this->mydb->delete_data($id);
            if (!$wpdb->delete()) {
                wp_send_json_error('Error deleting');
                exit;

            }
            wp_send_json_success('deleted successfully');
            exit;

        }






        /**
         * Function to insert data into database
         * @return void
         */
        function process_entry($fields, $entry, $form_data, $entry_id)
        {


            global $wpdb;

            $current_url = get_permalink();

            update_option('saved_url', $current_url);

            error_log('process_entry activated');
            // Obviously we need to have form fields to proceed.
            if (empty($fields)) {
                return;
            }

            $now = new DateTime($entry->date);
            error_log(print_r($entry), true);
            error_log(print_r($now, true));

            $form_date = current_time('Y-m-d H:i:s');

            if ($fields) {

                foreach ($fields as $field) {
                    $name = sanitize_text_field($field['name']); // Sanitize field name
                    $value = is_array($field['value']) ? serialize($field['value']) : $field['value'];

                    // Check if the value contains newlines and replace them with '&'.
                    $value = str_replace("\n", " & ", $value);
                    $serialized_data[$name] = $value;
                }
            }

            // insert data into table
            $wpdb->insert(
                $wpdb->prefix . 'wpforms_db2',
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

            //send telegram notifications
            if ($this->isnotif === '1') {
                $telegram = new KHTelegram();
                error_log(print_r($serialized_data, true));
                // Create the message text
                $telegram->send_khwpforms_message($serialized_data, $entry_id);
                $telegram->sendNotification();
            }
        }


        /**
         * Register plugin activation deactivation hooks
         *
         * @return void
         */
        function regsiter_hooks()
        {
            register_activation_hook(__FILE__, array($this, 'activate'));
            register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        }
    }
}

if (class_exists('KHMYCLASS')) {
    new KHMYCLASS();
    new KHCSV();
    new KHPDF();

}