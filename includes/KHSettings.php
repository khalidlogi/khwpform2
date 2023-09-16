<?php

class KHSettings
{




    public function __construct()
    {
        add_filter('plugin_action_links', array($this, 'wk_plugin_settings_link'), 10, 2);
        add_action('admin_menu', array($this, 'admin_list_table_page'));
        add_action('admin_init', array($this, 'custom_settings_init'));

        //$this->TestWpform();

    }


    public function admin_list_table_page()
    {
        // Add your custom functionality here
        // This function will be executed when the admin menu is rendered

        // Example: Display a custom admin page
        add_menu_page(
            'khwplist.php',
            // Page Title
            'WpformsDb',
            // Menu Title
            'manage_options',
            // Capability required to access the page
            'khwplist.php',
            // Menu Slug
            array($this, 'render_list_table_page') // Callback function to render the page content
        );
    }

    public function render_list_table_page()
    {
        /*
          if (!function_exists('wpforms')) {

              wp_die('Please activate <a href="https://wordpress.org/plugins/wpforms-lite/" target="_blank">WPForms</a> plugin.');
          }
          // Render the content of your list table page
          echo '<div class="wrap">';
          echo '<h1>List Table Page</h1>';
          // Add more HTML or PHP code as needed
          echo '</div>';*/

        ?>
        <div class="wrap">
            <h1>Custom Settings</h1>
            <form method="post" action="options.php">
                <?php
                // Output the settings fields
                settings_fields('custom_settings_group');
                do_settings_sections('custom-settings');
                submit_button();
                ?>
            </form>
        </div>
        <style>
            .wrap {
                max-width: 600px;
                margin-top: 30px;
            }

            .wrap h1 {
                margin-bottom: 20px;
            }

            .form-field {
                margin-bottom: 20px;
            }

            .form-field label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }

            .form-field select {
                width: 100%;
                padding: 5px;
                font-size: 14px;
            }
        </style>
        <?php
    }

    // Register and initialize the settings
    function custom_settings_init()
    {
        // Define the settings section
        add_settings_section(
            'custom_settings_section',
            'Form ID Setting',
            array($this, 'custom_settings_section_callback'),
            'custom-settings'
        );

        // Add the select option field
        add_settings_field(
            'form_id_setting',
            'Form ID',
            array($this, 'form_id_setting_callback'),
            'custom-settings',
            'custom_settings_section'
        );
        // Register the settings
        register_setting('custom_settings_group', 'form_id_setting');
    }

    // Callback function for the settings section
    function custom_settings_section_callback()
    {
        echo 'Select the Form ID:';
    }

    // Callback function for the form ID setting field
    // Callback function for the form ID setting field
    function form_id_setting_callback()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_db2';
        $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $table_name");

        $form_id = get_option('form_id_setting');

        if (empty($results_formids)) {
            echo '<div class="form-message">No forms detected, please add a form to WPForms.</div>';
        } else {
            echo '<div class="form-field">';
            echo '<label for="form_id_setting">Form ID</label>';
            echo '<select name="form_id_setting" id="form_id_setting">';
        }
        // Initialize an empty array to store form_id values
        $formIdsArray = [];
        foreach ($results_formids as $row) {
            $formIdsArray[] = $row->form_id;
            $selected = ($row->form_id == $form_id) ? 'selected' : '';
            echo '<option value="' . esc_attr($row->form_id) . '" ' . $selected . '>' . esc_html($row->form_id) . '</option>';

        }
        $formIdsString = '';
        $selected2 = ($formIdsString == $form_id) ? 'selected' : '';
        echo '<option value="' . $formIdsString . '" ' . $selected2 . '> ALL </option>';
        echo '</select>';
        echo '</div>';
    }


    function wk_plugin_settings_link($links)
    {
        $forms_link = '<a href="admin.php?page=khwplist.php">WPForms Data DB</a>';
        array_unshift($links, $forms_link);
        return $links;
    }


}