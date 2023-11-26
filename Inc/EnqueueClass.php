<?php


defined('ABSPATH') || exit;

class EnqueueClass
{

    private $mydb;
    public function __construct()
    {
        //add_shortcode('display_form_values', array($this, 'display_form_values_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_form_values_css'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_font_awesome'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_script'));
        //add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
    }


    function enqueue_font_awesome()
    {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), '5.15.3');
    }

    /**
     * Enqueue CSS styles for the form values.
     */
    function enqueue_form_values_css()
    {



        // Enqueue your custom CSS.
        wp_enqueue_style('form-values-style', plugin_dir_url(__FILE__) . 'assets/css/form-values.css');

        // Enqueue Font Awesome from a CDN.
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), '5.15.3');

        // Enqueue jQuery UI stylesheet (optional).
        wp_enqueue_style('jquery-ui-style', plugin_dir_url(__FILE__) . 'assets/css/jquery-ui.css');
    }

    /**
     * Enqueue custom JavaScript script.
     */
    function enqueue_custom_script()
    {
        // Enqueue your custom JavaScript.
        wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . 'assets/js/custom-script.js', array('jquery'), '1.0', true);

        // Localize the script with custom variables for AJAX.
        wp_localize_script('custom-script', 'custom_vars', array('ajax_url' => admin_url('admin-ajax.php')));

        // Enqueue jQuery UI scripts (core and droppable) (optional).
        wp_enqueue_script('jquery-ui-core', plugin_dir_url(__FILE__) . 'assets/js/jquery-ui-core', array('jquery'), '1.0', true);
        wp_enqueue_script('jquery-ui-droppable');
    }


}

new EnqueueClass();