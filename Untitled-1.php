##

##

## Tools 

Download [boilerplate](https://www.nexcess.net/blog/wordpress-plugins-getting-started-with-wppb-boilerplate/) files
plugin template

Install [WordPress admin plugin](https://github.com/bueltge/WordPress-Admin-Style) 

Create the plugin 

**Enabable debug mode:**

Create debug.log file 

And add the folowing to wp.config:

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );
// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

<!---->

You can find debug file in 

***

First, you need to create a directory and give it a unique name, then add a PHP file with the same name as the
directory. Add the following code the php file that will contain all the info needed for WordPress to display the
plugin 

<?PHP

|                                                                                                                                                                                                                                                                           |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|/* Plugin Name: kh-stt Plugin URI: https://kh-test.com/ Description: Plugin to accompany tutsplus guide to creating plugins, registers a post type. Version: 1.0 Author: Rachel McCollin Author URI: https://kh.com/ License: GPLv2 or later Text Domain: tutsplus */ |


##

## Security 

## // restrict direct access here

## defined( 'ABSPATH' ) or die('Cant Access on this on');

## ***

## include( plugin_dir_path( __FILE__ ) . 'ipn/paypal-ipn.php')

##

## Enqueue script 

- ../file.php (file is in the folder that is one level higher than the current directory)

Construct function 

add_action( 'wp_enqueue_scripts', array( $this,'wpse_load_plugin_css' ));

 function wpse_load_plugin_css() {

                $plugin_url = plugin_dir_url( __FILE__ );

           

                wp_enqueue_style( 'style1', $plugin_url . 'css/style1.css' );

                wp_enqueue_style( 'style2', $plugin_url . 'css/style2.css' );

            }

Add wp_head so that enque style will work 

|                                                                                                                                                                                                                                                                    |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| add_action('wp_enqueue_scripts','register_my_scripts'); function register_my_scripts(){ wp_enqueue_style( 'style1', plugins_url( 'css/style1.css' , __FILE__ ) ); wp_enqueue_style( 'style2', plugins_url( 'css/style2.css' , __FILE__ ) ); } |


### Hooks

add_action('admin_head','enqueue_settings_style'); //Fired at the head section


# Wpdb

Get database name

|                                                                                                                                                                                                            |
| ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| function my_update_notice() {    global $wpdb;    ?>    <div class="updated notice">        <p>
        <?php _e( "name of Database $wpdb->dbname", 'my_plugin_textdomain' ); ?></p>    </div>    <?php} |


### Show last query

$wpdb->last_query;

**Show errors**

 echo $wpdb->last_query ." | " .$wpdb->last_error;

Insert, replace, and update. 

$wpdb→insert($table, $data, $format) can be used to insert data into the database. Rather than building your own INSERT query, you simply pass the table name and an associative array containing the row data and WordPress will build the query and **escape** it for you. 

The keys of your $data array must map to column names in the table. The values in the array are the values to insert into the table row:

$wpdb->insert( $wpdb->schoolpress_assignment_submissions, array( "assignment_id"=>$assignment_id, "submission_id"=>$submission_id ), array( '%d', '%d' )

);

$wpdb→update($table, $data, $where, $format = null, $where_format = null ) can be used to update rows in a database table. Rather than building your own UPDATE query, you simply pass the table and an associative array containing the updated columns and new data along with an associative array $where containing the fields to check against in the WHERE clause and WordPress will build the query and escape the UPDATE query for you.

|                                                                                                                                                                                                           |
| --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| $wpdb->update( 'ecommerce_orders', //table name array( 'status' => 'pending', //data fields 'subtotal' => '100.00', 'tax' => '6.00', 'total' => '106.00'), array( 'id' => $order_id ) //where fields ); |

|                                                                                                                                                                                                                                                                                                                                                                                                                                |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| $data = array('titre'=> $subject,'name'=> $name , 'ville' =>  $city, 'email'=> $email,'tel'=> $tel,'type1'=> $type1,'type2'=> $type2,'message'=> $message,); $format = array('%s','%s','%s','%s','%s','%s','%s','%s'); //$wpdb->print_error(); //$wpdb->insert($table,$data,$format); $wpdb->print_error(); if(isset($id)){     $where = array('id' => $id); $wpdb->update($table,$data, $where,$format); //$wpdb->print; }  |


###

### Delete 

|                                                                           |
| ------------------------------------------------------------------------- |
|  $wpdb->query("DELETE FROM eLearning_progress WHERE ID = '$user_id' AND |


### Displaying/hiding SQL errors:

<?php $wpdb->show_errors(); ?> 

<?php $wpdb->hide_errors(); ?> 

<?php $wpdb->print_error(); ?>

if you want to know if query failed

$wpdb -> show_errors (); $wpdb -> get_results ($wpdb -> prepare($sql)); $wpdb -> print_error ();


### Notification notice

$wpdb->insert($table,$data,$format);

echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">

    "'.$wpdb->last_query.'"</div>';

Enqueue style

|
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
|
/*----------------------------------------------------------------callstyle*/ function enqueue_settings_style(){
wp_enqueue_style( 'enqueue_settings_style',plugins_url( '/css/style.css', __FILE__ )); }
add_action('admin_print_styles','enqueue_settings_style');
//---------------------------------------------------------------- |


##

## Notice 

[link](https://wpmudev.com/blog/adding-admin-notices/)


## Rederct 

<?php wp_redirect( home_url() ); exit; ?>

***

**Path directory file**

Abslute path: $file_path = ABSPATH . 'wp-content/';

Require class from another plugin: require_once(ABSPATH . '/wp-content/plugins/wpforms-lite/wpforms.php');


# Short code 

   

    ob_start();

    require_once(plugin_dir_path( __FILE__).'html/form.php');

    $content = ob_get_contents();

    ob_end_clean();

    return $content;

//This code starts by using the `ob_start()` function, which begins output buffering. This means that anything printed
or echoed after this point will not be immediately sent to the browser, but instead held in a buffer.

After the file is included and any content it outputs is buffered, the code then gets the current buffer contents using
the `ob_get_contents()` function and stores it in a variable called `$content`.

Finally, the buffer is cleared using the `ob_end_clean()` function and the variable `$content` is returned as the result
of executing this PHP function.

Overall, this code seems to include the contents of an HTML form from another file and return the resulting HTML as a
string. The use of output buffering ensures that only the contents of the included file are captured and not any other
output that might occur during the execution of the calling code.

Devide this saperately in HTML and PHP LOGIC, Take a look on this example:

function my_shortcode() { // Set HTML saperate and return it return include('template/shortcode_template.php'); }

 Save

**shortcode_template.php**

| |
|
------------------------------------------------------------------------------------------------------------------------------------------------------
|
| <?php ob_start(); ?> <**strong**>Hi there!</**strong**>
<**p**> This is an include test for shortcodes... </**p**> <?php return ob_get_clean(); |

**Ps : you have to use RETURN**

**Or use** 

**echo ''?> <div>[Whatever HTML you want]</div> <?php;**

|                                                                                                                                                                                                                                                                                                                                            |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| function create_shortcode(){  ob_start();     return "<h2>Hello world !</h2>";  ob_get_clean(); } add_shortcode('my_shortcode', 'create_shortcode'); // Use [my_shortcode] function create_shortcode(){     return "<h2>Hello world !</h2>"; } add_shortcode('my_shortcode', 'create_shortcode'); // Use [my_shortcode] |
|                                                                                                                                                                                                                                                                                                                                            |

Ps :  ob_get_clean(); 


# Add option

**get_option** function is used to retrieve a value from from options table based on option name.

Get_option second argument, which is by default set to FALSE, is optional.

**_<?php get_option( 'my_text', "I don't have anything written. Yet." ); ?>_**

// Create new option within WordPress

**add_option**( $option, $value = , $deprecated = , $autoload = 'yes' );


## Create a custom admin settings menu in your Wordpress Dashboard 

| |
|
-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
|
| add_action('admin_menu','khssettingspage'); function khssettingspage(){ add_menu_page( 'kh-settings-page',
'kh-settings', 'manage_options', 'kh-settings', 'kh-settings-api' ); }; |

| |
|
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
|
| <?php function dbi_render_plugin_settings_page() {     ?>     <**h2**>Example Plugin Settings</**h2**>     <**form**
    action="options.php" method="post">        
    <?php          settings_fields( '[dbi_example_plugin_options](https://docs.google.com/document/d/1lgNskDZtb0CmYsjpMOXKKTIeSl_4HB9EzTcdO3olP_E/edit#bookmark=id.zgeyjs2jd2j7)' );         do_settings_sections( 'dbi_example_plugin' ); ?>
           
    <**input** name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />    
</**form**>     <?php } |

Settings_fields(‘name-of-settings-group’);

Renders code to tell the form what to as well as well as hidden input (nonce).

You have to register the settings group.

 Do_settings_sections

Outputs sections and fields

<?php

| function dbi_register_settings() {// [register_setting](https://developer.wordpress.org/reference/functions/register_setting/) to create a new record in the [wp_options](https://deliciousbrains.com/tour-wordpress-database/#wp_options) table for our settings, with ‘dbi_example_plugin_options’ as the option_name. register_setting( 'dbi_example_plugin_options', 'dbi_example_plugin_options', '[dbi_example_plugin_options_validate](https://docs.google.com/document/d/1lgNskDZtb0CmYsjpMOXKKTIeSl_4HB9EzTcdO3olP_E/edit#bookmark=id.n840eiapabyv)' );//the name of the function which handles validating data entered when saving the option.    add_settings_section( 'api_settings', 'API Settings', 'dbi_plugin_section_text', 'dbi_example_plugin' );     add_settings_field( 'dbi_plugin_setting_api_key', 'API Key', 'dbi_plugin_setting_api_key', 'dbi_example_plugin', 'api_settings' );     add_settings_field( 'dbi_plugin_setting_results_limit', 'Results Limit', 'dbi_plugin_setting_results_limit', 'dbi_example_plugin', 'api_settings' );     add_settings_field( 'dbi_plugin_setting_start_date', 'Start Date', 'dbi_plugin_setting_start_date', 'dbi_example_plugin', 'api_settings' ); } add_action( 'admin_init', 'dbi_register_settings' ); |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |

|                                                                                                                                                                                                                                                                             |
| --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| <?php function dbi_example_plugin_options_validate( $input ) {     $newinput['api_key'] = trim( $input['api_key'] );     if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {         $newinput['api_key'] = '';     }     return $newinput; } |

|/** Settings Initialization **/    settings_error(); to show errors              function myplugin_settings_init() { |                                                    |                                                                         |
| :-------------------------------------------------------------------------------------------------------------------------: | -------------------------------------------------- | ----------------------------------------------------------------------- |
|                                  /** Setting section 1. **/    add_settings_section(                                 | // add Field to section      add_settings_field( |  // Register this field with our settings group.    register_setting(  |
|                                add_action( 'admin_init', '**myplugin_settings_init**' );                                |                                                    |                                                                         |


### Sanitize text field by removing HTML tags

Add a function to register settings 

register_setting( 'myplugin_settings_group', 'myplugin_field_2',**'saintizfield'**);

function saintizfield($input){

    $output = sanitize_text_field( $input );

    return $output;

}

***


### Get plugins 

Include a plugin :

// Check if get_plugins() function exists. This is required on the front end of the // site, since it is in a file that is normally only loaded in the admin. 

if ( ! function_exists( 'get_plugins' ) ) {&#x20;

require_once ABSPATH . 'wp-admin/includes/plugin.php'; } 

$all_plugins = get_plugins(); 

// Save the data to the error log so you can see what the array format is like. error_log( print_r( $all_plugins, true ) );

/** * Detect plugin. For use on Front End and Back End. */ 

// check for plugin using plugin name 

if(in_array('plugin-directory/plugin-file.php', 

apply_filters('active_plugins', get_option('active_plugins')))){&#x20;

//plugin is activated 

}

if (in_array('wpforms-lite/wpforms.php', apply_filters('active_plugins', get_option('active_plugins')))) {

                //plugin is activated

                error_log('wpform is active');

            }