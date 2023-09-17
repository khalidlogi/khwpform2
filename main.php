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

        public $mydb;

        public $myselectedformid;

        /**
         * Primary Class Constructor
         *
         */
        public function __construct()
        {



            add_action('wpforms_process_entry_save', array($this, 'process_entry'), 10, 4);
            add_shortcode('display_form_values', array($this, 'display_form_values_shortcode'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_form_values_css'));
            add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_script'));


            // On options select
            add_action('wp_ajax_callback_options', array($this, 'callback_options')); // For logged-in users
            add_action('wp_ajax_nopriv_callback_options', array($this, 'callback_options'));


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
            $this->mydb = new KHdb();

            require_once(ABSPATH . '/wp-content/plugins/wpforms-lite/wpforms.php');

            /*  if (class_exists('WP_Forms')) {
                  $wpforms = new WP_Forms();
                  error_log('clASS exists');
              } else {
                  // Handle the case when the WP_Forms class does not exist
                  error_log('wpform not found at all');
              }  if (has_action('wpforms_process_entry_save')) {
                // The action hook has registered callbacks


                error_log("The action hook wpforms_process_entry_save exists. {}");

            } else {
                error_log('The action hook wpforms_process_entry_save do not exist exists.');
                //add_action('admin_notices', array($this, 'wc_not_loaded'));
            }*/

            //test if wpform installed

        }

        private function includes()
        {
            // include_once KHFORM_PATH . 'includes/KHWPformdb.php';
            include_once KHFORM_PATH . 'includes/KHCSV.php';
            include_once KHFORM_PATH . 'includes/KHSettings.php';
            include_once KHFORM_PATH . 'includes/KHPDF.php';
            include_once KHFORM_PATH . 'includes/KHdb.php';

        }

        // AJAX handler function
        function callback_options()
        {
            if (isset($_POST['selected_option'])) {
                $selected_option = sanitize_text_field($_POST['selected_option']);
                error_log("selected_option : $selected_option");

            }
            /*
            // Handle the selected option value here, e.g., perform a database query or other operations
            $response = 'You selected: ' . $selected_option;
            echo $response;
        }
        die(); // Always include die() at the end to terminate the AJAX callback.

        */
        }


        public function create_table()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpforms_db2';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                form_id INT(11) NOT NULL,
                form_date DATETIME NOT NULL,
                form_value LONGTEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        function update_form_values()
        {

            global $wpdb;
            $table_name = $wpdb->prefix . 'wpforms_db2';

            // Retrieve the serialized form data from the AJAX request
            $form_data = $_POST['formData'];
            $form_id = $_POST['form_id'];
            $id = $_POST['id'];

            // Parse the serialized form data
            parse_str($form_data, $fields);
            /* foreach ($fields as $field_name => $field_value) {
                 $d = '';
                 $d .= " $field_name  $field_value  ";
             }

             foreach ($data as $key => $value) {
                 echo $key . ': ' . $value . "\n";
               }

            $keys = array_keys($fields);

            for ($i = 0; $i < count($keys); $i++) {
                $key = $keys[$i];
                $value = $fields[$key];

                echo $key . ': ' . $value . "\n";
            }

            wp_send_json_success($keys); */

            $status = $wpdb->update(
                $table_name,
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

            $last_query = $wpdb->last_query;
            error_log($last_query);

            var_dump($status);
            error_log(print_r($fields, true));



        }







        function enqueue_form_values_css()
        {
            wp_enqueue_style('form-values-style', plugin_dir_url(__FILE__) . 'assets/css/form-values.css');
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), '5.15.3');

        }
        function enqueue_custom_script()
        {
            wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery'), '1.0', true);
            wp_localize_script('custom-script', 'custom_vars', array('ajax_url' => admin_url('admin-ajax.php')));
        }


        function get_form_values()
        {
            global $wpdb;

            $form_id = $_POST['form_id'];
            $id = $_POST['id'];


            // Fetch form_value from the wpform_db2 table based on the form_id
            $table_name = $wpdb->prefix . 'wpforms_db2';
            $query = "SELECT id, form_value FROM $table_name WHERE id = '{$id}'";
            $serialized_data = $wpdb->get_results($query);

            $value = print_r($serialized_data, true);
            error_log("get_form_values ~ serialized_data : $value");
            if ($wpdb->last_error) {
                error_log($wpdb->last_error);
            }
            if ($serialized_data) {
                // Unserialize the serialized form value
                $unserialized_data = unserialize($serialized_data[0]->form_value);
                $value2 = print_r($serialized_data, true);
                error_log("get_form_values ~ unserialized_data : $value2");

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








        function delete_form_row()
        {
            global $wpdb;

            $id = $_POST['form_id'];

            // Define your table name
            $table_name = $wpdb->prefix . 'wpforms_db2';

            // Delete the row with the specified form_id
            $wpdb->delete($table_name, array('id' => $id));

            wp_die(); // This is required to terminate immediately and return a proper response
        }




        // Shortcode callback function
        function display_form_values_shortcode($atts)
        {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpforms_db2';

            $form_values = $this->retrieve_form_values();

            foreach ($form_values as $form_value) {
                $form_id = $form_value['form_id'];
                $id = $form_value['id'];
            }

            ob_start(); // Start output buffering

            ?>
<div id="edit-popup" class="edit-popup" style="display: none;">
    <div class="popup-content">
        <button class="dismiss-btn"><i class="fas fa-times"></i></button>
        <h1>Edit values </h1>
        <form id="edit-form" class="edit-form input-row"">

            <button id=" submit-button">Submit</button>

            <div id="result"></div>
            <!-- Form fields go here -->

            <!-- Add this button to your HTML where you want the update button to appear -->

        </form> <button type=" submit" data-form-id="<?php echo esc_attr($form_id); ?>" data-id="0"
            class="update-btn">Save</button>
    </div>
</div>

<?php
            // Retrieve the form values from the database
            $formbyid = get_option('form_id_setting');

            global $wpdb;
            $table_name = $wpdb->prefix . 'wpforms_db2';
            echo '<div class="form-wraper">';

            if (empty($formbyid)) {
                echo 'To proceed, please create a form and ensure that its ID is added<a href="' . admin_url('admin.php?page=khwplist.php') . '">
                Go to the settings page</a> to change the form ID value.';
                $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $table_name");

                $formbyid = '';
                // Stop further execution if the option is empty
            }


            $form_values = $this->retrieve_form_values($formbyid);



            if (!empty($form_values)) {


                echo '<div class="container">';
                echo 'Number of forms submitted: ' . $this->mydb->count_items($this->myselectedformid);

                //  error_log('count items' . $mydb->count_items(11) . 'id from ajax' . $id . '');
                foreach ($form_values as $form_value) {
                    $form_id = $form_value['form_id'];
                    $data = $form_value['data'];
                    $id = $form_value['id'];


                    echo '<div class="form-set-container" data-id="' . esc_attr($id) . '">';
                    echo '<button class="delete-btn" data-form-id="' . esc_attr($id) . '"><i class="fas fa-trash"></i></button>';
                    //echo '<button class="edit-btn" data-form-id="' . esc_attr($form_id) . '"><i class="fas fa-edit"></i></button>';
                    echo '<button class="edit-btn"  data-form-id="' . esc_attr($form_id) . '" data-id="' . esc_attr($id) . '"><i class="fas fa-edit"></i></button>';

                    // Display the form_id as a single line
                    echo '<div class="form-id-container">';
                    echo '<span class="form-id-label">Form ID:</span>';
                    echo '<span class="form-id-value">' . esc_html($form_id) . '</span>';
                    echo '</div>';

                    // Display each form_value key-value pair
                    foreach ($data as $key => $value) {
                        // Skip if the value is empty
                        if (empty($value)) {
                            continue;
                        }

                        echo '<div class="form-data-container">';
                        echo '<span class="field-label">' . esc_html($key) . ':</span>';
                        echo '<span class="value">' . esc_html($value) . '</span>';
                        echo '</div>';
                    }

                    echo '</div>'; // Close form-set-container
                }

                echo '<button class="export-btn"><i class="fas fa-download"></i> Export as CSV</button>';
                echo '<button class="export-btn-pdf"><i class="fas fa-download"></i> Export as PDF</button>';

                echo '</div>'; // Close form-values-container
                echo '</div>'; // Close form-wraper
                echo '</div>'; // Close container

                return ob_get_clean(); // Return the shortcode output
            }
        }





        // Function to retrieve and unserialize the form values from the database
        public function retrieve_form_values($formid = '')
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'wpforms_db2';

            // Retrieve the 'form_value' column from the database
            if ($formid === '' || $formid === 'all') {
                $results = $wpdb->get_results("SELECT id, form_id, form_value FROM $table_name");
            } else {
                $results = $wpdb->get_results("SELECT id, form_id, form_value FROM $table_name  where form_id = '{$formid}'");

            }

            if ($results === false) {
                echo "Database Error: " . $wpdb->last_error;
            }

            $form_values = array();

            foreach ($results as $result) {
                $serialized_data = $result->form_value;
                $form_id = $result->form_id;
                $id = $result->id;

                // Unserialize the serialized form value
                $unserialized_data = unserialize($serialized_data);


                // Retrieve the 'Comment or Message' value from the unserialized data
                //$comment_or_message = $unserialized_data['Comment or Message'];

                // Add the 'Comment or Message' value to the form_values array
                $form_values[] = array(
                    'form_id' => $form_id,
                    'id' => $id,
                    'data' => $unserialized_data,
                    'fields' => $unserialized_data,
                );

            }

            return $form_values;
        }

        /**
         * Setup plugin constants.
         *
         * @since 1.0.0
         */
        private function setup_constants()
        {

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
        }










        /**
         * Show in WP Dashboard notice about the plugin is not activated (PHP version).
         * @since 1.0.0
         * @return void
         */
        public function khform_fail_php_version()
        {

        }

        /**
         * Show in WP Dashboard notice about the plugin is not activated (WP version).
         * @since 1.5.0
         * @return void
         */
        public function khform_fail_wp_version()
        {
            /* translators: %s: WP version */
            $message = sprintf(esc_html__('Tablesome requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT ACTIVE.', 'khform'), '4.5');
            $html_message = sprintf('<div class="error">%s</div>', wpautop($message));
            echo wp_kses_post($html_message);
        }





        // Function to insert data into custom database table
        function process_entry($fields, $entry, $form_data, $entry_id)
        {

            global $wpdb;
            // error_log(" the entry id is: $entry_id");
            $error = print_r($entry_id['id'], true);
            error_log('entry_id :' . $error);
            //$form_id = $form_data['id'];
            $entry_data = array(
                'form_id' => $entry_id,
                'status' => 'publish',
                'referer' => $_SERVER['HTTP_REFERER'],
                'date_created' => current_time('mysql')
            );

            /*  if ($entry_id) {
                  foreach ($fields as $field) {
                      $field = apply_filters('wpforms_process_entry_field', $field, $form_data, $entry_id);
                      if (isset($field['value']) && '' !== $field['value']) {
                          $field_value = is_array($field['value']) ? serialize($field['value']) : $field['value'];
                          $entry_metadata = array(
                              'entry_id' => $entry_id,
                              'meta_key' => $field['name'],
                              'meta_value' => $field_value,
                          );
                          // Insert entry meta.

                      }
                  }
              }*/

            // $email = '';
            //$name = '';

            if ($fields) {

                $mydata = $fields;
                $form_data = array();

                foreach ($mydata as $key => $v) {

                    $v['value'] = is_array($v['value']) ? implode(',', $v['value']) : $v['value'];
                    $bl = array('\"', "\'", '/', '\\', '"', "'");
                    $wl = array('&quot;', '&#039;', '&#047;', '&#092;', '&quot;', '&#039;');
                    $d['value'] = str_replace($bl, $wl, $v['value']);

                    $form_data[$v['name']] = $v['value'];
                    error_log((" form_data[v['name']" . $form_data[$v['name']]));

                    $form_data = apply_filters('WPFormsDB_before_save_data', $form_data);

                    if (isset($field['value']) && '' !== $form_data['value']) {
                        $field_value = is_array($form_data['value']) ? serialize($field['value']) : $field['value'];


                        if ($form_data['name'] === 'Email') {
                            $email = $form_data['value'];
                        } elseif ($form_data['name'] === 'Name') {
                            $name = $form_data['value'];
                        }


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




        }



        /**
         * Plugin activation hook callback function.
         */
        public function activate_plugin_name()
        {
            // Create the table on plugin activation
            $this->create_table();
        }



    }


}

if (class_exists('KHMYCLASS')) {
    new KHMYCLASS();
    new KHdb();
    new KHCSV();
    new KHSettings();
    new KHPDF();
    // Register the activation hook with the class method
    register_activation_hook(__FILE__, array(new KHMYCLASS(), 'activate_plugin_name'));
}