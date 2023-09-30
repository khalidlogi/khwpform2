<?php


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class KHdb
{

    protected $table_name;
    private $formid;
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpforms_db2';
        //$this->count_items();
    }

    // Description: Deletes a row from the database table based on the specified ID.
// Parameters:
// - $id (int): The ID of the row to delete.

public function delete_tabledb() {
    global $wpdb;

    $table_name = $wpdb->prefix . $this->table_name;

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        // The table exists, let's drop it
        $sql = "DROP TABLE $table_name;";
        
        if ($wpdb->query($sql) !== false) {
            // Table dropped successfully
            return true;
        } else {
            // Error occurred while dropping the table
            return false;
            error_log('cant delete table');
        }
    } else {
        // Table doesn't exist
        return false;
    }
}

 /**
         * Create the table kh_wpfomdb2
         *
         * @return Array
         */
        public function create_tabledb()
        {
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                form_id INT(11) NOT NULL,
                form_date DATETIME NOT NULL,
                form_value LONGTEXT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

    function delete_data($id)
    {
        global $wpdb;

        // Delete the row with the specified form_id
        $wpdb->delete($this->table_name, array('id' => $id));

        wp_die(); // This is required to terminate immediately and return a proper response
    }

    public function count_items($formid = null)
    {

        global $wpdb;
        $items_count = 0;
        $this->formid = $formid;
        if ($formid === '1') {
            // If $formid is null, select all rows
            $query = "SELECT COUNT(DISTINCT id) FROM {$this->table_name}";
        } else {
            // If $formid is provided, select rows where form_id matches
            $query = $wpdb->prepare(
                "SELECT COUNT(DISTINCT id) FROM {$this->table_name} WHERE form_id = %d",
                $formid
            );
        }
        $items_count = $wpdb->get_var($query);
        return $items_count;



    }

    /**
     * This function checks if there is no data in a database table.
     *
     * @param string $table_name The name of the table to check.
     * @return bool True if the table is empty, false if it has data.
     */
    function is_table_empty()
    {
        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $this->table_name");

        if ($count === '0') {
            return true; // Table is empty
        } else {
            return false; // Table has data
        }
    }




    /**
     * Get the first and last date from the database.
     *
     * Retrieves the minimum and maximum dates from the 'form_date' column
     * of a specified database table and returns them as a string.
     *
     * @return string A string containing the first and last dates.
     */

    function getDate()
    {
        global $wpdb;
        $first_date_query = $wpdb->get_var("SELECT MIN(form_date) FROM $this->table_name");
        $last_date_query = $wpdb->get_var("SELECT MAX(form_date) FROM $this->table_name");
        $datecsv = "Initial Date: $first_date_query | Final Date:: $last_date_query";
        return $datecsv;

    }

    public function retrieve_form_values2()
    {
        global $wpdb;
        $form_values = array();


        // Retrieve the 'form_value' column from the database
        $results = $wpdb->get_results("SELECT id,form_id, form_value FROM $this->table_name");


        if ($results) {
            error_log('get_results working');
            error_log(print_r($results, true)); // Log the contents of $results
        } else {
            error_log('get_results working KHdb class : ' . $wpdb->last_error);
            // error_log(print_r($results, true)); // Log the contents of $results
        }


        foreach ($results as $result) {
            $serialized_data = $result->form_value;
            $form_id = $result->form_id;
            $id = $result->id;

            // Unserialize the serialized form value
            $unserialized_data = unserialize($serialized_data);

            $form_values[] = array(
                'form_id' => $form_id,
                'data' => $unserialized_data,
                'id' => $id,


            );
        }

        return $form_values;
    }


    /*
    
    public function view_item( $id, $format = OBJECT ) {

        global $wpdb;
        $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE id = ' . $id;
        $result = $wpdb->get_row($sql, $format);

        return $result;
    }

    public function save( $data, $id = 0 ) {

        global $wpdb;
        
        $success = false;
        $msg = '';
        
        $nonce = $data['nonce'];
        
        if (!wp_verify_nonce($nonce, 'form-nonce')) {
            wp_die('Security check!');
        }
        
        unset($data['nonce']);
        
        if ( $id > 0 ) {
            
           $affected_rows = $wpdb->update( $this->table_name, $data, array( 'id' => $id ) );   
           $success = true;
           $msg = 'Item has been successfully updated!';
        } else {
            $affected_rows = $wpdb->insert( $this->table_name, $data );
            $success = true;
            $msg = 'Saved item!';
        }

        if (!$affected_rows) {

            $success = false;
            $msg = 'Query error!';//$wpdb->last_error;            
        } 
        
        return (object) array( 'success' => $success, 'msg' => __( $msg, 'fme-request-for-quote' ) );
    }

    public function trash( $id ) {
        
        global $wpdb;
        $status = 3; // trash
        
        $msg = '';
        
        if (is_array($id)) {
            $id = implode( ',', $id );
        }
        
        $result = $wpdb->query(
            'UPDATE  '. $this->table_name . ' SET item_status = '. $status .' WHERE id IN ('. $id .')'  
        );
        
        if ( false === $result ) {

            $error = new WP_Error( 'broke', __( 'Trash operation failed', 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else if ( 0 === $result ) {

            $error = new WP_Error( 'broke', __( 'No record found with the ID(s): '. $id, 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else {
            
            $msg = sprintf( '(%d) Item(s) moved to trash', $result );
        }
        
        if ($msg == '') {
            $msg = __( 'Unknown Error', 'fem-request-for-quote' );
        }
        
        return (object) array('success' => $status, 'msg' => $msg);
    }
    
    public function untrash( $id ) {
        
        global $wpdb;
        $status = 1; // all|untrash
        
        $msg = '';
        
        if (is_array($id)) {
            $id = implode( ',', $id );
        }
        
        $result = $wpdb->query(
            'UPDATE  '. $this->table_name . ' SET item_status = '. $status .' WHERE id IN ('. $id .')'  
        );
        
        if ( false === $result ) {

            $error = new WP_Error( 'broke', __( 'Restore item(s) operation failed', 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else if ( 0 === $result ) {

            $error = new WP_Error( 'broke', __( 'No record found with the ID(s): '. $id, 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        } else {
            
            $msg = sprintf( '(%d) Item(s) restored', $result );
        }
        
        if ($msg == '') {
            $msg = __( 'Unknown Error', 'fem-request-for-quote' );
        }
        
        return (object) array('success' => $status, 'msg' => $msg);
    }
    
    public function delete( $id ) {
        
        global $wpdb;
        
        $status = true;
        $msg = __( 'Item deleted successfully!', 'fme-request-for-quote' );
        
        if (is_array($id)) {
            $id = implode( ',', $id );
        }
        
        $result = $wpdb->query(
            'DELETE FROM '. $this->table_name . ' WHERE id IN ('. $id .')'  
        );
        
        if ( false === $result ) {

            $error = new WP_Error( 'broke', __( 'Delete operation failed', 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        }

        if ( 0 === $result ) {

            $error = new WP_Error( 'broke', __( 'No record found with the ID(s): '. $id, 'fme-request-for-quote' ) );
            $msg = $error->get_error_message();
            $status = false;
        }
        
        return (object) array('success' => $status, 'msg' => $msg);
    }
    
    public function send_mail($to, $subject, $content, $headers = '') {
        
        send_email($to, $subject, $content);
        return true;
    }

    public function set_html_content_type() {
        return 'text/html';
    }

    public function get_plugin_options( $section, $key, $value = false ) {
        
        return get_plugin_options($section, $key, $value);
    }
    */
}