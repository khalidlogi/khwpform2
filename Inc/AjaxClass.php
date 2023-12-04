<?php


defined('ABSPATH') || exit;

class AjaxClass
{

    private $mydb;
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpforms_db2';
        $this->mydb = new KHdb();
        add_action('wp_ajax_update_form_values', array($this, 'update_form_values'));
        add_action('wp_ajax_nopriv_update_form_values', array($this, 'update_form_values'));
        add_action('wp_ajax_get_form_values', array($this, 'get_form_values'));
        add_action('wp_ajax_nopriv_get_form_values', array($this, 'get_form_values'));
        add_action('wp_ajax_delete_form_row', array($this, 'delete_form_row'));
        add_action('wp_ajax_nopriv_delete_form_row', array($this, 'delete_form_row'));

        add_action('wp_ajax_handle_file_upload', array($this, 'handle_file_upload'));
        add_action('wp_ajax_nopriv_handle_file_upload', array($this, 'handle_file_upload'));
    }

    function handle_file_upload()
    {

        error_log('got this from ajax file call', $_POST['test']);
        wp_send_json_success('handle_file_upload is active');


        // Check for the nonce
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            echo "File uploaded successfully!";
        } else {
            echo $movefile['error'];
        }

        wp_die();
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


}

new AjaxClass();