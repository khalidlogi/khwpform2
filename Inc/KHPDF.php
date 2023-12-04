<?php

use Dompdf\Dompdf;

if (!class_exists('KHPDF')) {
    class KHPDF
    {

        protected $myselectedformid;
        protected $mydb;

        /**
         * Construct method
         */
        public function __construct()
        {
            $mydb = new KHdb();
            add_action('wp_ajax_export_form_data_pdf', array($this, 'export_form_data_pdf'));
            add_action('wp_ajax_nopriv_export_form_data_pdf', array($this, 'export_form_data_pdf'));

            require_once dirname(__DIR__) . '/vendor/autoload.php';

        }


        // call back function for data export as PDF using /Mpdf
        public function export_form_data_pdf()
        {
            global $wpdb;
            $this->myselectedformid = (get_option('form_id_setting')) ? get_option('form_id_setting') : '';

            // Create an instance of KHdb
            $khdb = new KHdb();

            try {
                $this->myselectedformid = (get_option('form_id_setting')) ? get_option('form_id_setting') : '';

                $datecsv = $khdb->getDate();
                $formbyid = $this->myselectedformid;
                $form_values = $khdb->retrieve_form_values($formbyid);

                // Start building the HTML table

                $html_table = ' ' . $datecsv;
                $html_table .= '<table style="margin-bottom:1px; width:100%; border-collapse:collapse; border:1px solid #ccc; font-family: Arial, sans-serif; font-size: 14px;">';
                $html_table .= '<thead style=" background-color: #007acc;color: #fff;font-weight: bold;">
    
            <tr>
                    <th >ID</th>
                    <th >Form ID</th>
                    <th >Field</th>
                    <th >Value</th>
                </tr>
            </thead>';
                $html_table .= '<tbody>';

                $isOddRow = false; // Initialize as false

                foreach ($form_values as $form_value) {

                    $form_id = $form_value['form_id'];
                    $data = $form_value['data'];
                    // Toggle the $isOddRow flag to alternate background colors
                    $isOddRow = !$isOddRow;
                    // Define the background color based on $isOddRow
                    $background_color = $isOddRow ? ' #f2f2f2' : 'white';


                    foreach ($data as $key => $value) {
                        //error_log(print_r($data, true));
                        $id = $form_value['id'];
                        $value = empty($value) ? "----" : $value;
                        $html_table .= '<tr style="background: ' . $background_color . '; border-bottom: 1px solid #ccc;">';
                        $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:Charcoal;">' . $id . '</td>';
                        $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $form_id . '</td>';
                        $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $key . '</td>';

                        // Check if value is an email
                        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            echo '';
                            $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;"> 
                            <a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a> </td>';

                        } else {
                            $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $value . '</td>';
                        }

                        $html_table .= '</tr>';
                    }
                }

                $html_table .= '</tbody></table>';
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html_table);
                // (Optional) Setup the paper size and orientation
                $dompdf->setPaper('A4', 'landscape');

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                $dompdf->stream();

                wp_die(); //Terminate
            } catch (Exception $e) {
                // Handle exceptions.
                wp_die('Error: ' . $e->getMessage(), 'Error', ['response' => 500]);
            }
        }
    }
}