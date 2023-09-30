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
            // Dummy data
            $data = [
                ['Name', 'Age', 'Country'],
                ['John Doe', 30, 'USA'],
                ['Jane Smith', 25, 'Canada'],
                ['Mark Johnson', 35, 'Australia'],
            ];

            // Generate HTML table
            $html = '<table>';

            foreach ($data as $row) {
                $html .= '<tr>';

                foreach ($row as $cell) {
                    $html .= '<td>' . $cell . '</td>';
                }

                $html .= '</tr>';
            }
            $html .= '</table>';

            // Output the PDF table
            $mpdf = new \Mpdf\Mpdf();

            try {
                $mpdf->WriteHTML($html);
                // Other code
                $mpdf->Output();
            } catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
                // Process the exception, log, print etc.
                echo $e->getMessage();
            }

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