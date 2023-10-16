<?php

if (!class_exists('KHPDF')) {


    class KHPDF
    {


        protected $myselectedformid;
        public function __construct()
        {
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
                    $prev_id = null; // Track previous ID

                    error_log(print_r($data, true));

                    foreach ($data as $key => $value) {
                        if ($key === 'Name') {
                            $html_table .= "<tr>" . $key . ": " . $value . "</tr>";
                        }
                    }



                    foreach ($data as $key => $value) {
                        //$row_class = ($id === $prev_id) ? 'same-id-row' : ''; // Add a CSS class for rows with the same ID

                        $id = $form_value['id'];
                        if ($id !== $prev_id) {
                            $html_table .= '<tr  style="margin: bottom 10px;background:black; border-bottom: 15px solid #ccc;">';

                            $html_table .= '<td></td>';
                            $html_table .= '</tr>';

                        } else {
                            $html_table .= '<tr>';
                            $html_table .= '<td style=" background:gray; padding:10px; border-bottom:1px solid #ccc; color:white;">' . $id . '</td>';
                            $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $form_id . '</td>';
                            $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $key . '</td>';
                            $html_table .= '<td style="padding:10px; border-bottom:1px solid #ccc; color:blue;">' . $value . '</td>';
                            $html_table .= '</tr>';
                        }



                        $prev_id = $id; // Update previous ID
                    }
                }

                // Close the HTML table
                $html_table .= '</tbody>
                </table>';
                $html_table .= "$datecsv";


                // Output the PDF table
                $mpdf = new \Mpdf\Mpdf([
                    'default_font_size' => 10,
                    'default_font' => 'DejaVu'
                ]);
                $mpdf->WriteHTML($html_table);


                // Set HTTP headers to force download
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="data.pdf"');
                $mpdf->Output();
                //echo $html_table;

                wp_die(); // Terminate 
            } catch (Exception $e) {
                // Handle exceptions.
                wp_die('Error: ' . $e->getMessage(), 'Error', ['response' => 500]);
            }
        }
    }
}