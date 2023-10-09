<?php
/*
Plugin Name: kh-wpform2

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

remove logs comments , var_dump//  

Installation
Install this plugin, along with WPForms (or WPForms Lite).
In the WordPress Dashboard, go to WPForms > Add New and create a form. You can add whatever fields you like, but at a minimum you must include an Email and Name field. 
Click “WpformsDb” from the Dashboard, then select “Form id”. From the dropdowns.


*/




if (!defined('ABSPATH')) {
    exit;
}


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
        public $version = '0.1';

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


            // Hook the AJAX action
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

            //table name
            $this->table_name = $wpdb->prefix . 'wpforms_db2';
        }

        public function create_table()
        {
            global $wpdb;
            $this->mydb->create_tabledb();
        }

        public function deactivate()
        {
            global $wpdb;
            $this->mydb->delete_tabledb();
        }

        /**
         * Plugin activation hook callback function.
         */
        public function activate_plugin_name()
        {
            // Create the table on plugin activation
            $this->create_table();
        }

        /**
         * Plugin deactivation hook callback function.
         */


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
         *  update form values
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

            ////////////

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
                wp_send_json_success(array('message' => 'Update successful!', 'fields from update' => $fields));
            }

        }


        /**
         * Enqueue styles and scripts
         *
         * @return void
         */
        function enqueue_form_values_css()
        {
            wp_enqueue_style('form-values-style', plugin_dir_url(__FILE__) . 'assets/css/form-values.css');
            //wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . 'css/fontawesome.min.css');

            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), '5.15.3');
            // Enqueue jQuery UI stylesheet (optional)
            wp_enqueue_style('jquery-ui-style', plugin_dir_url(__FILE__) . 'assets/css/jquery-ui.css');
        }
        function enqueue_custom_script()
        {
            wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery'), '1.0', true);
            wp_localize_script('custom-script', 'custom_vars', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_enqueue_script('jquery-ui-core', plugin_dir_url(__FILE__) . 'assets/js/jquery-ui-core', array('jquery'), '1.0', true);
            wp_enqueue_script('jquery-ui-droppable');


        }



        /**
         * Register plugin hooks
         *
         * @param null
         * @return void
         */
        function get_form_values()
        {
            global $wpdb;

            $form_id = intval($_POST['form_id']);
            $id = intval($_POST['id']);


            // Fetch form_value from the wpform_db2 table based on the form_id
            $query = "SELECT id, form_value FROM  $this->table_name WHERE id = '{$id}'";
            $serialized_data = $wpdb->get_results($query);

            /*$value = print_r($serialized_data, true);
            error_log("get_form_values ~ serialized_data : $value");*/

            if ($wpdb->last_error) {
                error_log($wpdb->last_error);
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
         *
         * @since 1.0.0
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

            if (!current_user_can('manage_options')) {

                ob_start();

                echo '<div style="text-align: center; color: red;">You are not authorized to access this page..</div>';

                return ob_get_clean();

            } else {


                //get the form id
                $formbyid = $this->myselectedformid;
                // retrieve form values
                $form_values = $this->mydb->retrieve_form_values($formbyid);

                error_log('user_role_setting: ' . get_option('user_role_setting'));

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
                        $form_id = $form_value['form_id'];
                        $id = $form_value['id'];
                    }

                    //include edit-form file
                    include_once KHFORM_PATH . 'Inc/html/edit_popup.php';

                    echo '<div class="form-wraper">';

                    if (empty($formbyid)) {
                        echo 'To proceed, please create a form and ensure that its ID is added<a
        href="' . admin_url('admin.php?page=khwplist.php') . '">Go to the settings page</a>| to change the form ID
    value.';
                    }


                    if ($form_values) {
                        echo '<div class="container">';
                        echo 'Number of forms submitted: ' . $this->mydb->count_items($this->myselectedformid);
                        echo '<br>';
                        echo 'Default form id: ' . (($this->myselectedformid === '1') ? 'All' : $this->myselectedformid);
                        echo ' <a style="text-align: center; color: black;"
                               href="' . admin_url('admin.php?page=khwplist.php') . '">Change it here</a></div>';

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
                                echo '<span class="field-label">' . esc_html($key) . ':</span>';
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
         * Insert data into database
         * @return void
         */
        function process_entry($fields, $entry, $form_data, $entry_id)
        {

            global $wpdb;
            // error_log(" the entry id is: $entry_id");
            //$error = print_r($fields, true);
            //error_log('entry_id :' . $error);
            //$form_id = $form_data['id'];
            /*$entry_data = array(
                'form_id' => $entry_id,
                'status' => 'publish',
                'referer' => $_SERVER['HTTP_REFERER'],
                'date_created' => current_time('mysql')
            );*/

            if ($fields) {

                $mydata = $fields;
                $form_data = array();
                error_log('fields : ' . print_r($mydata, true));
                foreach ($mydata as $key => $v) {

                    //https://github.com/wp-telegram-pro/wp-telegram-pro/blob/25edad549c8e1ad5afdda26d513dad5f427317b0/plugins/WPFormsWPTP.php#L68

                    $v['value'] = is_array($v['value']) ? implode('|', $v['value']) : $v['value'];
                    // error_log(print_r($v, true));
                    $bl = array('\"', "\'", '/', '\\', '"', "'");
                    $wl = array('&quot;', '&#039;', '&#047;', '&#092;', '&quot;', '&#039;');
                    $d['value'] = str_replace($bl, $wl, $v['value']);

                    $form_data[$v['name']] = $v['value'];
                    // error_log((" form_data[v['name']" . $form_data[$v['name']]));

                    $form_data = apply_filters('WPFormsDB_before_save_data', $form_data);

                    /* email and name fields may be needed soon
                    if (isset($field['value']) && '' !== $form_data['value']) {
                        $field_value = is_array($form_data['value']) ? serialize($field['value']) : $field['value'];


                        if ($form_data['name'] === 'Email') {
                            $email = $form_data['value'];
                        } elseif ($form_data['name'] === 'Name') {
                            $name = $form_data['value'];
                        }*/


                }
                $form_post_id = $entry_id;
                $form_value = serialize($form_data);
                $form_date = current_time('Y-m-d H:i:s');

            }
            $wpdb->insert(
                $wpdb->prefix . 'wpforms_db2',
                // table name
                array(
                    //'email' => $email,
                    'form_id' => $entry_id['id'],
                    'form_value' => $form_value,
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
         * Register plugin hooks
         *
         * @param null
         * @return void
         */
        function regsiter_hooks()
        {
            register_activation_hook(__FILE__, array($this, 'activate_plugin_name'));
            register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        }
    }



}

if (class_exists('KHMYCLASS')) {
    new KHMYCLASS();
    new KHCSV();
    new KHPDF();

}