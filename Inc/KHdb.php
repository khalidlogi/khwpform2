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
        $this->formid = $this->retrieve_form_id();
        //$this->count_items();
    }

    /**
     *  Deletes a row from the database table based on the specified ID
     *
     * @return bool
     */
    public function delete_tabledb()
    {
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
        wp_die(); // terminate immediately and return a proper response

    }


    /**
     * Count the number of items in the database table.
     *
     * @param int|null $formid The form ID to filter by. If null, counts all items.
     *
     * @return int The number of items in the database table.
     */
    public function count_items($formid = null)
    {
        global $wpdb;

        // Initialize the count to zero.
        $items_count = 0;

        // Set the form ID to the provided value.
        $this->formid = $formid;

        if ($formid === null) {
            // Select all rows.
            $query = "SELECT COUNT(DISTINCT id) FROM {$this->table_name}";
        } else {
            // If $formid is provided, select rows where form_id matches.
            $query = $wpdb->prepare(
                "SELECT COUNT(DISTINCT id) FROM {$this->table_name} WHERE form_id = %d",
                $formid
            );
        }

        // Retrieve the count from the database.
        $items_count = $wpdb->get_var($query);

        // Return the count of items.
        return $items_count;
    }


    /**
     * Function to retrieve form id from Database.
     *
     * @return bool True if the table is empty, false if it has data.
     */
    function retrieve_form_id()
    {
        $form_id_setting = get_option('form_id_setting');

        if (is_array($form_id_setting)) {
            $form_ids = array();

            foreach ($form_id_setting as $value) {
                if (is_numeric($value)) {
                    $form_ids[] = $value;
                }
            }

            $concatenated_form_id = implode(' , ', $form_ids);
            return $concatenated_form_id;
        } elseif (is_numeric($form_id_setting)) {
            return $form_id_setting;
        }
        return NULL; // If no valid value found, return an empty string
    }

    /**
     * Function to check if there is no data in a database table.
     *
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
     */
    function getDate()
    {
        global $wpdb;
        $first_date_query = $wpdb->get_var("SELECT MIN(form_date) FROM $this->table_name");
        $last_date_query = $wpdb->get_var("SELECT MAX(form_date) FROM $this->table_name");
        $datecsv = "Initial Date: $first_date_query | Final Date: $last_date_query";
        return $datecsv;
    }

    /**
     *  Function to retrieve and unserialize the form values from the database.
     *
     * @since 1.0.0
     */
    public function retrieve_form_values($formid = '')
    {
        global $wpdb;

        // Retrieve the 'form_value' column from the database
        if (!empty($formid)) {
            $formid = sanitize_text_field($formid);
        } else {
            $formid = $this->retrieve_form_id();

        }

        error_log('Enable_data_saving_checkbox : ' . get_option('Enable_data_saving_checkbox'));
        if ($formid === null) {
            $results = $wpdb->get_results("SELECT id, form_id, form_value FROM  $this->table_name ");
        } else {
            $results = $wpdb->get_results("SELECT id, form_id, form_value FROM  $this->table_name  where form_id IN($formid)");

        }
        if ($results === false) {
            error_log("SQL Error: " . $wpdb->last_error);
            return false;
        }

        $form_values = array();

        foreach ($results as $result) {
            $serialized_data = $result->form_value;
            $form_id = $result->form_id;
            $id = $result->id;

            // Unserialize the serialized form value
            $unserialized_data = unserialize($serialized_data);

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
     *  Function to retrieve and unserialize the form values from the database.
     *
     * @since 1.0.0
     */
    public function retrieve_form_values2()
    {
        global $wpdb;
        $form_values = array();


        // Retrieve the 'form_value' column from the database
        $results = $wpdb->get_results("SELECT id,form_id, form_value FROM $this->table_name");


        if (!$results) {
            error_log('get_results working KHdb class : ' . $wpdb->last_error);
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
}