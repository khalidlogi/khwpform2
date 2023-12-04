<?php

/**
 * Handle plugin installation upon activation.
 *
 * @since 1.0.0
 */


class KHInstall {

    private $mydb;
    /**
     * Primary class constructor.
     *

     */
    public function __construct() {

        // Instantiate Datamanagmnet class and settings class
        $this->mydb = new KHdb();

        //create custom database when the plugins are loaded
        //hook added on 'plugins_loaded' as dependency on WPForms is checked before creating custom table for entries
        add_action('plugins_loaded', array($this, 'create_entry_db'));
        //Admin notices to show WPForms dependency messages
        $this->wpforms_notice();

        register_activation_hook(__FILE__, array($this, 'install'));
        //Not dropping table on deactivation of plugin
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Perform certain actions on plugin activation.
     *

     *
     */
    public function install() {
        //TODO: Add installation methods
    }

    /**
     * Create database table to store entries coming from WPForms
     * Database table is created if WPForms lite plugin is activated
     *

     *
     */
    public function create_entry_db() {
        if(class_exists('WPForms\WPForms')) {

            global $wpdb;
            $this->mydb->create_tabledb();

        }
    }


    /**
     * Checks if WPForms plugin is active and adds custom notices accordingly.
     */
    public function wpforms_notice() {
        // Check if get_plugins() function exists. This is required on the front end
        if(!function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        if(
            is_plugin_active('wpforms-lite/wpforms.php') || is_plugin_active('wpforms/wpforms.php')
        ) {
            add_action('admin_notices', array($this, 'custom_success_notice'));

            //error_log('wpform is_plugin_active  is active');
        } else {
            //error_log('wpform is_plugin_active  is  not active');
            add_action('admin_notices', array($this, 'wpforms_admin_notice'));

        }
    }

    function custom_success_notice() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php _e('WPForms is currently active. To get started with this plugin, 
                you can begin by creating a form using the WPForms plugin.', 'Adas'); ?>
            </p>
            <p>
                <?php _e('Add shortcode: [display_form_values], into any page/post to display the entries', 'Adas'); ?>
            </p>
        </div>
        <?php
    }
    function wpforms_admin_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php _e('WPForms plugin is not active. Please install and activate WPForms to use this plugin.', 'your-text-domain'); ?>
            </p>
        </div>
        <?php
        deactivate_plugins(__FILE__); //Deactivate current plugin

    }






    /**
     * Perform certain actions on plugin deactivation.
     *

     */
    public function deactivate() {
        //TODO: Add settings options to drop table on deactivation
        //$sql = $this->entry_db_instance->drop_table();
    }
}

new KHInstall();