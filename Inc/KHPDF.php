<?php

if (!class_exists('KHPDF')) {


    class KHPDF
    {



        public function __construct()
        {
            add_action('wp_ajax_export_form_data_pdf', array($this, 'export_form_data_pdf'));
            add_action('wp_ajax_nopriv_export_form_data_pdf', array($this, 'export_form_data_pdf')); // If you want to allow non-logged-in users

            require_once dirname(__DIR__) . '/vendor/autoload.php';


            //$this->export_form_data();
        }


        public function export_form_data_pdf()
        {
            global $wpdb;

            // Create an instance of KHdb
            $khdb = new KHdb();

            // Call the getDate() method
            $datecsv = $khdb->getDate();

            // Retrieve the form values from the database
            $form_values = $khdb->retrieve_form_values2();
            $prev_id = null; // Track previous ID
            // Start building the HTML table

            $html_table = '<table style="margin-bottom:10px; width:100%; border-collapse:collapse; border:1px solid #ccc; font-family: Arial, sans-serif; font-size: 14px;">';
            $html_table .= '<thead>
    
            <tr style="background-color:#f2f2f2;">
                    <th style="padding:10px; border-bottom:1px solid #ccc; color:#FF0000;">ID</th>
                    <th style="padding:10px; border-bottom:1px solid #ccc; color:#FF0000;">Form ID</th>
                    <th style="padding:10px; border-bottom:1px solid #ccc; color:#FF0000;">Field</th>
                    <th style="padding:10px; border-bottom:1px solid #ccc; color:#FF0000;">Value</th>
                </tr>
            </thead>';
            $html_table .= '<tbody>';

            foreach ($form_values as $form_value) {

                $form_id = $form_value['form_id'];
                $data = $form_value['data'];
                $id = $form_value['id'];

                foreach ($data as $key => $value) {
                    //$row_class = ($id === $prev_id) ? 'same-id-row' : ''; // Add a CSS class for rows with the same ID

                    if ($id === $prev_id) {
                        $html_table .= '<tr  style="border-bottom: 5px solid #ccc;">';

                    } else {
                        $html_table .= '<tr>';
                    }

                    $html_table .= '<td style="background:#F2F2F2; padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $id . '</td>';
                    $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $form_id . '</td>';
                    $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $key . '</td>';
                    $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $value . '</td>';
                    $html_table .= '</tr>';

                    $prev_id = $id; // Update previous ID
                }
            }

            // Close the HTML table
            $html_table .= '</tbody>
            </table>';
            $html_table .= "$datecsv";


            // Set the response headers for downloading
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="form_data_table.html"');

            // Output the PDF table
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html_table);


            // Set HTTP headers to force download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="your_file_name.pdf"');
            $mpdf->Output();
            //echo $html_table;

            wp_die(); // This is required to terminate immediately and return a proper response
        }
    }
}