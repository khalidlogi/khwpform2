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


        public function export_form_data()
        {
            global $wpdb;
            // Create an instance of KHdb
            $khdb = new KHdb();

            // Retrieve the form values from the database
            $form_values = $khdb->retrieve_form_values2();



            // Call the getDate() method
            $datecsv = $khdb->getDate();

            // Start building the HTML table
            $html_table = " $datecsv \n";
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
            header('Content-Disposition: attachment; filename="WPForms-Data-Entries" ' . date('Y-m-d') . '.csv');

            // Output the HTML table
            echo $html_table;

            wp_die(); // This is required to terminate immediately and return a proper response
        }
    }
}