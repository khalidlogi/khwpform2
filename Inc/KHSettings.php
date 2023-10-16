<?php


class KHSettings
{

    protected $table_name;
    public function __construct()
    {

        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpforms_db2';
        $plugin_basename = 'kh-wpform';
        //delte this 
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        //add Settings link 
        add_filter('plugin_action_links_kh-wpform/main.php', array($this, 'khwpforms_settings_link'), 10, 2);

        // add menu link
        add_action('admin_menu', array($this, 'admin_list_table_page'));
        add_action('admin_init', array($this, 'custom_settings_init'));

        //Display notice 
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

        // Check if wpforms is enabled 
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
        <?php _e('WPForms plugin is not active. Please install and activate WPForms to use this plugin.', 'kh-wpforms'); ?>
    </p>
</div>
<?php
    }

    /**
     * Success Notice message
     */
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


    /**
     * Add menu page
     */
    public function admin_list_table_page()
    {

        // Display a custom admin page
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

    /**
     * Include all the necessary files
     */
    public function render_list_table_page()
    {

        global $wpdb;
        $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $this->table_name");
        //$this->wpforms_notice();

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


        // Register the settings
        register_setting('custom_settings_group', 'form_id_setting');
        // register_setting('custom_settings_group', 'my_plugin_capability');
    }

    // Call back function for the settings section
    function custom_settings_section_callback()
    {
        echo "After the initial submission of a WPForms form, 
        the form ID will be included or associated with that specific form.<br><br>";

        echo "<strong>Select the wpforms' form ID:</strong>";
    }

    // Callback function for the form ID setting field
    function form_id_setting_callback()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_db2';
        $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $table_name");

        $form_id = get_option('form_id_setting');

        if (empty($results_formids)) {
            printf(__('It appears that there are no form entries detected. Please add a form using the WPForms plugin and submit at least one form.'));

            if (is_plugin_inactive('wpforms-lite/wpforms.php')) {
                // The plugin is not activated
                               printf(' <br>Activate wpforms-lite plugin and try again');
            } else {
                printf(' <br><a href="%s">Visit your WPForms forms</a>.', admin_url('admin.php?page=wpforms-overview'));

            }
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


    /**
     * call back function for action link
     */
    function wk_plugin_settings_link($links)
    {

        $forms_link = '<a href="admin.php?page=khwplist.php">WPForms Data DB</a>';
        array_unshift($links, $forms_link);
        return $links;
    }


}