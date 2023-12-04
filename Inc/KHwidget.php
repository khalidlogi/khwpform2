<?php


defined('ABSPATH') || exit;

class KHwidget {

    private $mydb;
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'register_first_custom_dashboard_widget'));
        // AJAX handler to update the option value
        add_action('wp_ajax_update_data_saving_option', array($this, 'update_data_saving_option'));

        add_action('wp_ajax_update_notification_checkbox', array($this, 'update_notification_checkbox'));
    }

    function register_first_custom_dashboard_widget() {
        wp_add_dashboard_widget(
            'my_first_custom_dashboard_widget',
            'Adas wpforms Add-on',
            array($this, 'my_first_custom_dashboard_widget_display')
        );

    }
    function my_first_custom_dashboard_widget_display() {
        global $wpdb;
        $table_name = $wpdb->prefix.'wpforms_db2';

        ?>
<br>
<label class="switch">
    <input <input <?php if(get_option('Enable_notification_checkbox') === '1') {
                echo 'checked';
            }
            ?> type="checkbox" id="switch_button_notifications" type="checkbox" id="switch_button_notifications">
    <span class="slider round"></span>
</label><strong> Activate/Deactivate Notifcations </strong>

<br><br>
<label class="switch">
    <input <?php if(get_option('Enable_data_saving_checkbox') === '1') {
                echo 'checked';
            }
            ?> type="checkbox" id="switch_button_data_saving">
    <span class="slider round"></span>
</label><strong> Activate/Deactivate Data saving </strong>

<br>
<br>
<script>
jQuery(document).ready(function($) {


    // Event listener for the switch button change
    $('#switch_button_data_saving').change(function() {
        UpdateDataOptionValue();
    });

    // Event listener for the notification switch button change
    $('#switch_button_notifications').change(function() {
        updateNotificationOption();
    });

    function UpdateDataOptionValue() {
        var checkboxValue = $('#switch_button_data_saving').prop('checked') ? '1' : '0';
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_data_saving_option',
                value_data_ischecked: checkboxValue
            },
            success: function(response) {
                console.log('Notification option value updated successfully.');
            },
            error: function(error) {
                console.error('Error updating data option value.');
            }
        });
    }
    // AJAX function to update the data saving option value
    function updateNotificationOption() {
        var checkboxValue2 = $('#switch_button_notifications').prop('checked') ? '1' : '0';
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'update_notification_checkbox',
                value: checkboxValue2
            },
            success: function(response) {
                console.log('Data saving option value updated successfully.');
            },
            error: function(error) {
                console.error('Error updating data saving option value.');
            }
        });
    }



});
</script>

<?php
        // Get the form IDs
        $sql = "SELECT DISTINCT form_id FROM {$table_name}";
        $results = $wpdb->get_results($sql);

        if(!empty($results)) {
            // Create an array to store the number of forms for each form ID
            $form_counts = array();

            // Loop through the results and count the number of forms for each form ID
            foreach($results as $row) {
                $form_id = $row->form_id;

                $sql = "SELECT COUNT(*) AS count FROM {$table_name} WHERE form_id = $form_id";
                $result2 = $wpdb->get_results($sql);
                $row2 = $result2[0];

                $form_counts[$form_id] = $row2->count;
            }

            // Print the number of forms for each form ID
            foreach($form_counts as $form_id => $count) {
                echo "<strong>Form ID:</strong> $form_id,<strong> Number of forms:</strong> $count<br>";
            }

            echo '<br><strong>Recently Published</strong> <br>';

        }


        $result = KHdb::get_last_three_dates();
        foreach($result as $result) {
            echo $result.'<br>';
        }

        //Get the data of the last submission
        $lastresult = KHdb::getInstance()->retrieve_form_values('', '', '', 1


        );
        if(!empty($lastresult)) {

            echo '<br><strong>Last submission : </strong>';

            foreach($lastresult[0]['data'] as $data) {
                if(empty($data)) {
                    continue;
                }
                echo "<br>$data";
                //echo "\n"; // Output each element followed by a new line

            }
        }


    }
    // AJAX handler to update the data saving option value
    function update_data_saving_option() {
        if(current_user_can('manage_options')) {
            $new_value = $_POST['value_data_ischecked'];
            update_option('Enable_data_saving_checkbox', $new_value);
        }
        wp_die();
    }
    function update_notification_checkbox() {
        if(current_user_can('manage_options')) {
            $new_value = $_POST['value'];
            update_option('Enable_notification_checkbox', $new_value);
        }
        wp_die();
    }
}
new KHwidget();