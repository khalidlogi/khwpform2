<?php


defined('ABSPATH') || exit;

class KHwidget
{

    private $mydb;
    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'register_first_custom_dashboard_widget'));
    }

    function register_first_custom_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'my_first_custom_dashboard_widget',
            'My Custom Dashboard Widget',
            array($this, 'my_first_custom_dashboard_widget_display')
        );

    }
    function my_first_custom_dashboard_widget_display()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_db2';


        // Get the form IDs
        $sql = "SELECT DISTINCT form_id FROM {$table_name}";
        $results = $wpdb->get_results($sql);

        // Create an array to store the number of forms for each form ID
        $form_counts = array();

        // Loop through the results and count the number of forms for each form ID
        foreach ($results as $row) {
            $form_id = $row->form_id;

            $sql = "SELECT COUNT(*) AS count FROM {$table_name} WHERE form_id = $form_id";
            $result2 = $wpdb->get_results($sql);
            $row2 = $result2[0];

            $form_counts[$form_id] = $row2->count;
        }

        // Print the number of forms for each form ID
        foreach ($form_counts as $form_id => $count) {
            echo "Form ID: $form_id, Number of forms: $count<br>";
        }

        echo '<br><strong>Recently Published</strong> <br>';
        $results = KHdb::get_last_three_dates();
        foreach ($results as $result) {
            echo $result . '<br>';
        }


    }
}
new KHwidget();