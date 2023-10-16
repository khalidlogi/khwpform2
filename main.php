<?php
/*
Plugin Name: kh-wpform

Plugin URI: https://kh-test.com/

Description: Plugin to accompany tutsplus guide to creating plugins, registers a post type.

Version: 1.0

Author: Khalidlogi

Author URI: https://kh.com/

License: GPLv2 or later

Text Domain: khwpforms

*/

/*
to do:

remove logs comments , var_dump ,  console.log , error_reporting//  
//add nonce to export_form_data
// optional add validating to js 
fix refresh button apearing second time

Installation
Install this plugin, along with WPForms (or WPForms Lite).
In the WordPress Dashboard, go to WPForms > Add New and create a form. You can add whatever fields you like, but at a minimum you must include an Email and Name field. 
Click “WpformsDb” from the Dashboard, then select “Form id”. From the dropdowns.


*/


if (!defined('ABSPATH')) {
    exit;
}
error_reporting(E_ALL);
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

        public $mydb, $mysetts;

        protected $table_name;

        /**
         * form id 
         * @var 
         */
        public $myselectedformid;

        /**
         * Primary Class Constructor
         *
         */
        public function __construct()
        {


            $this->regsiter_hooks();
            add_action('wpforms_process_entry_save', array($this, 'process_entry'), 10, 4);
            add_shortcode('display_form_values', array($this, 'display_form_values_shortcode'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_form_values_css'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_script'));


            // On options select
            //add_action('wp_ajax_callback_options', array($this, 'callback_options')); // For logged-in users
//add_action('wp_ajax_nopriv_callback_options', array($this, 'callback_options'));

            add_action('wp_ajax_update_form_values', array($this, 'update_form_values'));
            add_action('wp_ajax_nopriv_update_form_values', array($this, 'update_form_values'));

            add_action('wp_ajax_get_form_values', array($this, 'get_form_values'));
            add_action('wp_ajax_nopriv_get_form_values', array($this, 'get_form_values')); // If needed

            //csv export

            add_action('wp_ajax_delete_form_row', array($this, 'delete_form_row'));
            add_action('wp_ajax_nopriv_delete_form_row', array($this, 'delete_form_row')); // If you want to allow non-logged-in users

            $this->setup_constants();
            $this->includes();

            //instantiate the Khdb class
            $this->mydb = new KHdb();
            $this->mysetts = new KHSettings();

            //require_once(ABSPATH . '/wp-content/plugins/wpforms-lite/wpforms.php');

        }

        /**
         * Include all the necessary files
         */
        private function includes()
        {
            // include_once KHFORM_PATH . 'includes/KHWPformdb.php';
            include_once KHFORM_PATH . 'Inc/KHCSV.php';
            include_once KHFORM_PATH . 'Inc/KHSettings.php';
            include_once KHFORM_PATH . 'Inc/KHPDF.php';
            include_once KHFORM_PATH . 'Inc/KHdb.php';

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

        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function setup_constants()
        {

            global $wpdb;

            //get the default form_id
            $this->myselectedformid = (get_option('form_id_setting')) ? get_option('form_id_setting') : '';
            error_log('myselectedformid ' . $this->myselectedformid);

            // Plugin version.
            if (!defined('KHFORM_VERSION')) {
                define('KHFORM_VERSION', $this->version);
            }

            // Plugin Folder Path.
            if (!defined('KHFORM_PATH')) {
                define('KHFORM_PATH', plugin_dir_path(__FILE__));
            }

            // Plugin Folder URL.
            if (!defined('WPFORMS_PLUGIN_URL')) {
                define('KHFORM_URL', plugin_dir_url(__FILE__));
            }

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
            $this->mydb->delete_tabledb();
        }

        /**
         * Plugin activation hook callback function.
         */
        public function activate()
        {
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

            error_log(print_r($form_data, true));
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
         * @param null
         * @return  Array
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
                //$value2 = print_r($serialized_data, true);
                //error_log("get_form_values ~ unserialized_data : $value2");
                // Retrieve fields array from the unserialized data
                $fields = array();

                foreach ($unserialized_data as $key => $value) {
                    // if ($key !== 'Comment or Message') {
                    $fields[] = array(
                        'name' => $key,
                        'value' => $value
                    );
                    // }
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
         * display form values shortcode
         *
         * @since 1.0.0
         */
        //
        function display_form_values_shortcode($atts)
        {
            global $wpdb;

            // see if user do not have authorization 
            if (!current_user_can('manage_options')) {

                ob_start();

                echo '<div style="text-align: center; color: red;">You are not authorized to access this page.<a href="' . wp_login_url() . '">  Login</div>';
                echo 'login: ' . wp_login_url();

                return ob_get_clean();

            } else {

                //get the form id
                $formbyid = $this->myselectedformid;
                // retrieve form values
                $form_values = $this->mydb->retrieve_form_values($formbyid);

                //error_log('user_role_setting: ' . get_option('user_role_setting'));

                //Check if there is at least one entry
                if ($this->mydb->is_table_empty() === true) {
                    ob_start();

                    echo '<div style="text-align: center; color: red;">No data available! Please add etries to your form and try again.';
                    echo ' <a style="text-align: center; color: black;" href="' . admin_url('admin.php?page=khwplist.php') . '">Settings
                    DB</a></div>';

                    return ob_get_clean();

                } else {
                    ob_start();


                    foreach ($form_values as $form_value) {
                        $form_id = intval($form_value['form_id']);
                        $id = intval($form_value['id']);
                    }

                    //include edit-form file
                    include_once KHFORM_PATH . 'Inc/html/edit_popup.php';

                    echo '<br><div class="form-wraper">';

                    // see if there is no form if saved 

                    echo '
                        Visit the <a href="' . admin_url('admin.php?page=khwplist.php') . '"> settings page </a> to update the form ID value..';



                    if ($form_values) {

                        echo '<div class="container">';
                        echo 'Number of forms submitted: ' . $this->mydb->count_items($this->myselectedformid);

                        if (!empty($this->myselectedformid)) {

                            echo '<br> Default form id: ' . (($this->myselectedformid === '1') ? 'Show all forms' : $this->myselectedformid);

                        }


                        //$role = (get_option('user_role_setting')) ? get_option('user_role_setting') : 'Admin';
                        //echo 'Who can access: ' . $role;

                        foreach ($form_values as $form_value) {
                            $form_id = $form_value['form_id'];
                            $data = $form_value['data'];
                            $id = $form_value['id'];

                            //Delete button 
                            echo '<div class="form-set-container" data-id="' . esc_attr($id) . '">';
                            echo '<button class="delete-btn" data-form-id="' . esc_attr($id) . '"
                             data-nonce="' . wp_create_nonce('ajax-nonce') . '">
                             <i class="fas fa-trash"></i></button>';

                            //Edit button 
                            echo '<button class="edit-btn delete-btn2" 
                             data-form-id="' . esc_attr($form_id) . '" data-id="' . esc_attr($id) . '"><i
                             class="fas fa-edit"></i></button>';

                            echo '<div class="form-id-container">';
                            echo '<div style="color:black;" class="form-id-label id">ID: ' . esc_html($id) . '</div>';
                            echo '<span class="form-id-label">Form ID:</span>';
                            echo '<span class="form-id-value">' . esc_html($form_id) . '</span>';
                            echo '</div>';

                            foreach ($data as $key => $value) {
                                if (empty($value)) {
                                    continue;
                                }

                                echo '<div class="form-data-container">';
                                echo '<span class="field-label">' . esc_html($key) . ': </span>';
                                echo '<span class="value">' . esc_html($value) . '</span>';
                                echo '</div>';
                            }

                            echo '</div>';
                        }

                        echo '<button class="export-btn"><i class="fas fa-download"></i> Export as CSV</button>';
                        echo '<button class="export-btn-pdf"><i class="fas fa-download"></i> Export as PDF</button>';

                        echo '</div>';
                        echo '</div>';
                        echo '</div>';

                        return ob_get_clean();
                    }
                }
            }
        }


        /**
         * Function to insert data into database
         * @return void
         */
        function process_entry($fields, $entry, $form_data, $entry_id)
        {

            global $wpdb;
            $form_date = current_time('Y-m-d H:i:s');

            if ($fields) {

                foreach ($fields as $field) {
                    $name = sanitize_text_field($field['name']); // Sanitize field name
                    $value = is_array($field['value']) ? serialize($field['value']) : $field['value'];
                    //$value = sanitize_text_field($field['value']);

                    // Check if the value contains newlines and replace them with '&'.
                    $value = str_replace("\n", " & ", $value);

                    error_log(print_r('field[name]' . $field['name'], true));

                    error_log(print_r('field[value]' . $value, true));


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
        }


        /**
         * Register plugin activation deactivation hooks
         *
         * @param null
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