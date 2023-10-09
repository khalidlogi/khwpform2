<?php


class KHSettings
{

    protected $table_name;


    public function __construct()
    {

        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpforms_db2';
        $plugin_basename = 'kh-wpform';
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        add_filter('plugin_action_links_kh-wpform/main.php', array($this, 'khwpforms_settings_link'), 10, 2);


        //add_filter('plugin_action_links', array($this, 'wk_plugin_settings_link'), 10, 2);
        add_action('admin_menu', array($this, 'admin_list_table_page'));
        add_action('admin_init', array($this, 'custom_settings_init'));

        //$this->TestWpform();
        $this->wpforms_notice();

    }



    function khwpforms_settings_link($links_array)
    {
        $Settings = '<a href="admin.php?page=khwplist.php">Settings</a>';
        array_unshift($links_array, $Settings);
        return $links_array;
    }

    /**
     * Checks if WPForms plugin is active and adds custom notices accordingly.
     */
    function wpforms_notice()
    {
        // Check if get_plugins() function exists. This is required on the front end of the
        // site, since it is in a file that is normally only loaded in the admin.
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (
            is_plugin_active('wpforms-lite/wpforms.php') || is_plugin_active('wpforms/wpforms.php')
        ) {
            add_action('admin_notices', array($this, 'custom_success_notice'));

            error_log('wpform is_plugin_active  is active');
        } else {
            error_log('wpform is_plugin_active  is  not active');
            add_action('admin_notices', array($this, 'wpforms_admin_notice'));

        }
    }



    /**
     * @param string $plugin
     * @return boolean
     */
    public function is_plugin_active($plugin)
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active($plugin);
    }

    function wpforms_admin_notice()
    {
        ?>
        <div class="notice notice-error">
            <p>
                <?php _e('WPForms plugin is not active. Please install and activate WPForms to use this plugin.', 'your-text-domain'); ?>
            </p>
        </div>
        <?php
    }

    function custom_success_notice()
    {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php _e('WPForms is currently active. To get started with this plugin, 
                you can begin by creating a form using the WPForms plugin.', 'your-text-domain'); ?>
            </p>
            <p>
                <?php _e('Add shortcode: [display_form_values], into any page/post to display the entries', 'your-text-domain'); ?>
            </p>
        </div>
        <?php
    }


    public function admin_list_table_page()
    {
        // Add your custom functionality here
        // This function will be executed when the admin menu is rendered

        // Example: Display a custom admin page
        add_menu_page(
            'khwplist.php',
            // Page Title
            'KHWpformsDb',
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

        global $wpdb;
        $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $this->table_name");
        $this->wpforms_notice();
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
            <h1>Settings</h1>
            <form method="post" action="options.php">
                <?php
                // Output the settings fields
                settings_fields('custom_settings_group');
                do_settings_sections('custom-settings');
                if (!empty($results_formids)) {

                    submit_button();
                }

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
        // add select option field 
        // Add the capability field to the same section
        /* add_settings_field(
             'my_plugin_capability',
             'Edit access',
             array($this, 'my_plugin_capability_callback'),
             'custom-settings',
             'custom_settings_section' // Specify the section name
         );*/

        // Register the settings
        register_setting('custom_settings_group', 'form_id_setting');
        // register_setting('custom_settings_group', 'my_plugin_capability');
    }

    // Callback function for the settings section
    function custom_settings_section_callback()
    {
        echo "Select the wpforms' form ID:";
    }

    /* function my_plugin_capability_callback()
     {
         $capability_name = esc_attr(get_option('my_plugin_capability'));


         ?> <select name="my_plugin_capability">
     <option value="Edit entries" <?php selected($capability_name, 'Edit entries'); ?>>Edit entries</option>
     <option value="View entries" <?php selected($capability_name, 'View entries'); ?>>View entries</option>
     <option value="Deny access" <?php selected($capability_name, 'Deny access'); ?>>Deny access</option>

     </option>
 </select>
 <?php

     }*/





    // Callback function for the form ID setting field
    function form_id_setting_callback()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_db2';
        $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $table_name");

        $form_id = get_option('form_id_setting');

        if (empty($results_formids)) {
            printf(__('"Oops, it appears that no forms entries  have been detected. Please consider adding a form using WPForms plugin."




            <a href="%s">your WPForms forms</a>.'), admin_url('admin.php?page=wpforms-overview'));

        } else {
            echo '<div class="form-field">';
            echo '<label for="form_id_setting">Form ID</label>';
            echo '<select name="form_id_setting" id="form_id_setting">';
            // Initialize an empty array to store form_id values
            foreach ($results_formids as $row) {
                $selected = ($row->form_id == $form_id) ? 'selected' : '';
                echo '<option value="' . esc_attr($row->form_id) . '" ' . $selected . '>' . esc_html($row->form_id) . '</option>';

            }
            $selected_all = ($form_id == '1') ? 'selected' : '';
            echo '<option value="1" ' . $selected_all . '>All forms</option>';
            echo '</select>';
            echo '</div>';
        }

    }


    function wk_plugin_settings_link($links)
    {

        $forms_link = '<a href="admin.php?page=khwplist.php">WPForms Data DB</a>';
        array_unshift($links, $forms_link);
        return $links;
    }

    function user_roles()
    {
        $my_plugin_capability = esc_attr(get_option('my_plugin_capability'));
        // Get current user
        if (wp_get_current_user()) {
            $current_user = wp_get_current_user();
            $user_role = $current_user->roles[0];

        }

        // Get user role


        if (!isset($current_user->user_nicename)) {
            return 'guest';
        } else {
            return $user_role;
        }




    }

}