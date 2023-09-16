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

        public function retrieve_form_values2()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'wpforms_db2';

            // Retrieve the 'form_value' column from the database
            $results = $wpdb->get_results("SELECT id,form_id, form_value FROM $table_name");
            if ($results) {
                error_log('get_results working');
            } else {
                error_log($wpdb->last_error);
            }

            $form_values = array();

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

        public function export_form_data_pdf()
        {
            global $wpdb;
            $date = date('Y-m-d H:i:s');
            // Create an instance of KHdb
            $khdb = new KHdb();

            // Call the getDate() method
            $datecsv = $khdb->getDate();

            // Retrieve the form values from the database
            $form_values = $this->retrieve_form_values2();
            $prev_id = null; // Track previous ID
            // Start building the HTML table
            ?>
<style>
.same-id-row {
    border-bottom: 5px solid #ccc;
}
</style>
<?php
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

            // Output the PDF table
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html_table);


            // Set HTTP headers to force download
            header('Content-Type: application/pdf');
            header("Content-Disposition: attachment; filename='{$date}.pdf");
            $mpdf->SetTitle(" $datecsv");
            $mpdf->Output('filename.pdf');
            //echo $html_table;

            wp_die(); // This is required to terminate immediately and return a proper response
        }
    }
}