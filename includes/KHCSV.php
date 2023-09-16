<?php

if (!class_exists('KHCSV')) {

    class KHCSV
    {

        private $db;
        public function __construct()
        {

            add_action('wp_ajax_export_form_data', array($this, 'export_form_data'));
            add_action('wp_ajax_nopriv_export_form_data', array($this, 'export_form_data')); // If you want to allow non-logged-in users


            //$this->export_form_data();
        }

        public function retrieve_form_values2()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'wpforms_db2';

            // Retrieve the 'form_value' column from the database
            $results = $wpdb->get_results("SELECT form_id, form_value FROM $table_name");


            if ($results) {
                error_log('get_results working');
            } else {
                error_log($wpdb->last_error);
            }

            $form_values = array();

            foreach ($results as $result) {
                $serialized_data = $result->form_value;
                $form_id = $result->form_id;

                // Unserialize the serialized form value
                $unserialized_data = unserialize($serialized_data);

                $form_values[] = array(
                    'form_id' => $form_id,
                    'data' => $unserialized_data,


                );
            }

            return $form_values;
        }

        public function export_form_data()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'wpforms_db2';

            // Retrieve the form values from the database
            $form_values = $this->retrieve_form_values2();

            // Create an instance of KHdb
            $khdb = new KHdb();

            // Call the getDate() method
            $datecsv = $khdb->getDate();

            // Start building the HTML table
            $html_table .= " $datecsv \n";
            $html_table .= " $ Form ID, Field ,Value \n";
            $html_table .= "Form ID, Field ,Value \n";




            foreach ($form_values as $form_value) {
                $form_id = $form_value['form_id'];
                $data = $form_value['data'];


                foreach ($data as $key => $value) {
                    $html_table .= "$form_id ,  $key , $value";
                    $html_table .= "\n";
                }
            }

            // Close the HTML table


            // Set the response headers for downloading
            header('Content-Type:text/csv');
            header('Content-Disposition: attachment; filename="form_data_table.csv"');

            // Output the HTML table
            echo $html_table;

            wp_die(); // This is required to terminate immediately and return a proper response
        }
    }
}