<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ClioWP Settings Page plugin main class
 *
 * Before use this code in your own plugin:
 * - Change the text domain (khwpformsdb) to your own text domain
 * - Make the appropriate changes in parameters in the constructor
 */
class KHSettings
{

    /**
     * Settings Page title.
     *
     * @var string
     */
    private $page_title;

    /**
     * Menu title.
     *
     * @var string
     */
    private $menu_title;

    /**
     * Capability to access Settings page.
     *
     * @var string
     */
    private $capability;

    /**
     * Menu slug.
     *
     * @var string
     */
    private $menu_slug;

    /**
     * Settings form action.
     *
     * @var string
     */
    private $form_action;

    /**
     * Option group.
     *
     * @var string
     */
    private $option_group;

    /**
     * Constructor
     */
    public function __construct()
    {
        // parameters.
        $this->page_title = esc_html__('Adas Wpforms Database Add-on', 'khwpformsdb');
        $this->menu_title = esc_html__('Adas-Wpforms-Db-Addon', 'khwpformsdb');
        $this->capability = 'manage_options';
        $this->menu_slug = 'khwplist.php';

        //Add settings link
        add_filter('plugin_action_links_Adas_Wpforms_Database_Add-On/main.php', array($this, 'khwpforms_settings_link'), 10, 2);

        $this->form_action = 'options.php';

        $this->option_group = 'khwpformsdb_sp_plugin';

        // actions.
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'add_settings'));
        add_action('init', array($this, 'load_languages'));

        $this->wpforms_notice();

    }

    /**
     * Checks if WPForms plugin is active and adds custom notices accordingly.
     */
    public function wpforms_notice()
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

            //error_log('wpform is_plugin_active  is active');
        } else {
            error_log('wpform is_plugin_active  is  not active');
            add_action('admin_notices', array($this, 'wpforms_admin_notice'));

        }
    }

    function custom_success_notice()
    {
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

    function khwpforms_settings_link($links_array)
    {
        $Settings = '<a href="admin.php?page=khwplist.php">Settings</a>';
        array_unshift($links_array, $Settings);
        return $links_array;
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

    /**
     * Adds a submenu page to the Settings main menu.
     */
    public function add_settings_page()
    {
        /**
         * Params for add_options_page
         *
         * @param  string       $page_title The text to be displayed in the title tags of the page when the menu is selected.
         * @param  string       $menu_title The text to be used for the menu.
         * @param  string       $capability The capability required for this menu to be displayed to the user.
         * @param  string       $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
         * @param  callable     $callback   Optional. The function to be called to output the content for this page.
         * @param  int          $position   Optional. The position in the menu order this item should appear.
         * @return string|false The resulting page's hook_suffix, or false if the user does not have the capability required.
         */
        add_options_page(
            $this->page_title,
            $this->menu_title,
            $this->capability,
            $this->menu_slug,
            array($this, 'settings_page_html')
        );
    }

    /**
     * Compose settings
     */
    public function add_settings()
    {

        // Define Sections ----------------------------------------------------.

        /**
         * Adds a new section to a settings page.
         *
         * Part of the Settings API. Use this to define new settings sections for an admin page.
         * Show settings sections in your admin page callback function with do_settings_sections().
         * Add settings fields to your section with add_settings_field().
         *
         * The $callback argument should be the name of a function that echoes out any
         * content you want to show at the top of the settings section before the actual
         * fields. It can output nothing if you want.
         *
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
         * @param string   $page     The slug-name of the settings page on which to show the section. Built-in pages include
         *                           'general', 'reading', 'writing', 'discussion', 'media', etc. Create your own using
         *                           add_options_page();
         */
        add_settings_section(
            'cliowp_settings_page_section1',
            __('<span class="label_setting label-primary">Database settings', 'khwpformsdb'),
            null,
            $this->menu_slug
        );

        add_settings_section(
            'cliowp_settings_page_section2',
            __('<span class="label_setting label-primary">Telegrem notifications', 'khwpformsdb'),
            null,
            $this->menu_slug
        );

        // Input text field ---------------------------------------------------.

        /**
         * Adds a new field to a section of a settings page.
         *
         * Part of the Settings API. Use this to define a settings field that will show
         * as part of a settings section inside a settings page. The fields are shown using
         * do_settings_fields() in do_settings_sections().
         *
         * The $callback argument should be the name of a function that echoes out the
         * HTML input tags for this setting field. Use get_option() to retrieve existing
         * values to show.
         *
         * @param string   $id       Slug-name to identify the field. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the field. Shown as the label for the field
         *                           during output.
         * @param callable $callback Function that fills the field with the desired form inputs. The
         *                           function should echo its output.
         * @param string   $page     The slug-name of the settings page on which to show the section
         *                           (general, reading, writing, ...).
         * @param string   $section  Optional. The slug-name of the section of the settings page
         *                           in which to show the box. Default 'default'.
         * @param array    $args     {
         *                           Optional. Extra arguments used when outputting the field.
         *
         *     @type string $label_for When supplied, the setting title will be wrapped
         *                             in a `<label>` element, its `for` attribute populated
         *                             with this value.
         *     @type string $class     CSS Class to be added to the `<tr>` element when the
         *                             field is output.
         * }
         */

        /*add_settings_field(
           'cliowp_sp_input1',
           esc_html__('Input1 Label', 'khwpformsdb'),
           array($this, 'input1_html'),
           $this->menu_slug,
           'cliowp_settings_page_section1'
       );*/

        /**
         * Registers a setting and its data.
         *
         * @param string $option_group A settings group name. Should correspond to an allowed option key name.
         *                             Default allowed option key names include 'general', 'discussion', 'media',
         *                             'reading', 'writing', and 'options'.
         * @param string $option_name The name of an option to sanitize and save.
         * @param array  $args {
         *     Data used to describe the setting when registered.
         *
         *     @type string     $type              The type of data associated with this setting.
         *                                         Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
         *     @type string     $description       A description of the data attached to this setting.
         *     @type callable   $sanitize_callback A callback function that sanitizes the option's value.
         *     @type bool|array $show_in_rest      Whether data associated with this setting should be included in the REST API.
         *                                         When registering complex settings, this argument may optionally be an
         *                                         array with a 'schema' key.
         *     @type mixed      $default           Default value when calling `get_option()`.
         * }
         */
        /*
        register_setting(
            $this->option_group,
            'cliowp_sp_input1',
            array(
                'sanitize_callback' => array($this, 'sanitize_input1'),
                'default' => 'input1 test',
            )
        );*/

        // Telegram notification Enable/Disable Checkbox field -----------------------------------------------------.
        add_settings_field(
            'Enable_notification_checkbox',
            __('<span class="label_setting">Enable/Disable Telegram notifications', 'khwpformsdb'),
            array($this, 'checkbox2_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );

        register_setting(
            $this->option_group,
            'Enable_notification_checkbox',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1',
            )
        );

        // MultiSelect field --------------------------------------------------.
        add_settings_field(
            'form_id_setting',
            __('<span class="label_setting">Wpforms\' Form id', 'khwpformsdb'),
            array($this, 'multiselect1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'form_id_setting',
        );

        // Number of entries in page --------------------------------------------------.
        add_settings_field(
            'number_id_setting',
            __('<span class="label_setting">Number of entries per Page', 'khwpformsdb'),
            array($this, 'number_page_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'number_id_setting',
        );

        //Telegram token field
        add_settings_field(
            'telegram_token_setting',
            __('<span class="label_setting">Token  <i class="fas fa-info-circle" data-toggle="tooltip" 
            title="To obtain a bot token, you can reach out to the @BotFather bot on Telegram. 
            Simply send the command /newbot and follow the instructions provided."></i></span>', 'khwpformsdb'),
            array($this, 'input_token_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );
        register_setting(
            $this->option_group,
            'telegram_token_setting',
            array(
                'sanitize_callback' => array($this, 'sanitize_input1'),
                'default' => 'enter token',
            )
        );

        //Telegram Chat_id field
        add_settings_field(
            'telegram_chat_id_setting',
            __('<span class="label_setting">Chat ID <i class="fas fa-info-circle" data-toggle="tooltip" title="
            To obtain the chat ID for sending messages, you can contact out to the @myidbot bot on Telegram. 
            Simply send the /getid command to get your personal chat ID. If you want to retrieve the group chat ID, 
            invite the bot to the group and use the /getgroupid command. Group IDs typically begin with a hyphen, 
            while supergroup IDs start with -100."
            ></i>', 'khwpformsdb'),
            array($this, 'input_chatid_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );
        register_setting(
            $this->option_group,
            'telegram_chat_id_setting',
            array(
                'sanitize_callback' => array($this, 'sanitize_input1'),
                'default' => 'enter Chat_id',
            )
        );



        /* Date field ---------------------------------------------------------.
        add_settings_field(
            'cliowp_sp_date1',
            esc_html__('Date1 Label', 'khwpformsdb'),
            array($this, 'date1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'cliowp_sp_date1',
            array(
                'sanitize_callback' => 'sanitize_text_field',
            )
        );*/

        /* DateTime field -----------------------------------------------------.
        add_settings_field(
            'cliowp_sp_datetime1',
            esc_html__('Datetime1 Label', 'khwpformsdb'),
            array($this, 'datetime1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'cliowp_sp_datetime1',
            array(
                'sanitize_callback' => 'sanitize_text_field',
            )
        );*/

        /* Password field -----------------------------------------------------.
        add_settings_field(
            'cliowp_sp_password1',
            esc_html__('Password1 Label', 'khwpformsdb'),
            array($this, 'password1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'cliowp_sp_password1',
            array(
                'sanitize_callback' => array($this, 'encrypt_password1'),
            )
        );

        // Number field -------------------------------------------------------.
        add_settings_field(
            'form_id_setting',
            esc_html__('Form ID', 'khwpformsdb'),
            array($this, 'number1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1',

        );

        register_setting(
            $this->option_group,
            'form_id_setting',
        );

        // Select field -------------------------------------------------------.
        add_settings_field(
            'form_id_setting',
            esc_html__('Form ID', 'khwpformsdb'),
            array($this, 'select1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'form_id_setting',
            array(
                'sanitize_callback' => array($this, 'sanitize_select1'),
                'default' => '1',
            )
        );*/

        // Checkbox field -----------------------------------------------------.
        add_settings_field(
            'Enable_data_saving_checkbox',
            __('<span class="label_setting">Enable/Disable Data saving', 'khwpformsdb'),
            array($this, 'checkbox1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );

        register_setting(
            $this->option_group,
            'Enable_data_saving_checkbox',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1',
            )
        );

        // view 
        add_settings_field(
            'view_option',
            __('<span class="label_setting">View Option', 'khwpformsdb'),
            array($this, 'view_option_html'),
            $this->menu_slug,
            'cliowp_settings_page_section1'
        );
        register_setting(
            $this->option_group,
            'view_option',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'normal',
            )
        );





        /*Textarea field -----------------------------------------------------.
        add_settings_field(
            'cliowp_sp_textarea1',
            __('<span class="label_setting">Textarea1 Label', 'khwpformsdb'),
            array($this, 'textarea1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2',
            array(
                'rows' => 4,
                'cols' => 30,
            )
        );

        register_setting(
            $this->option_group,
            'cliowp_sp_textarea1',
            array(
                'sanitize_callback' => 'sanitize_textarea_field',
            )
        );*/

        // Color field for wraper--------------------------------------------------------.
        add_settings_field(
            'khwpforms_bg_color',
            __('<span class="label_setting">Background Color', 'khwpformsdb'),
            array($this, 'color1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );

        register_setting(
            $this->option_group,
            'khwpforms_bg_color',
        );

        // Color field for text--------------------------------------------------------.
        add_settings_field(
            'khwpforms_text_color',
            __('<span class="label_setting">Text Color', 'khwpformsdb'),
            array($this, 'color_html2'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );

        register_setting(
            $this->option_group,
            'khwpforms_text_color',
        );

        // Color field for label--------------------------------------------------------.
        add_settings_field(
            'khwpforms_label_color',
            __('<span class="label_setting">Label Text Color', 'khwpformsdb'),
            array($this, 'color_html3'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );

        register_setting(
            $this->option_group,
            'khwpforms_label_color',
        );

        // bg Color for export button --------------------------------------------------------.
        add_settings_field(
            'khwpforms_exportbg_color',
            __('<span class="label_setting">Export button bg Color', 'khwpformsdb'),
            array($this, 'color_exportbg_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2'
        );

        register_setting(
            $this->option_group,
            'khwpforms_exportbg_color',
        );

        /* WYSIWYG editor field -----------------------------------------------.
        add_settings_field(
            'cliowp_sp_editor1',
            __('<span class="label_setting">Editor1 Label', 'khwpformsdb'),
            array($this, 'editor1_html'),
            $this->menu_slug,
            'cliowp_settings_page_section2',
        );

        register_setting(
            $this->option_group,
            'cliowp_sp_editor1',
            array(
                'sanitize_callback' => 'wp_kses_post',
            )
        );*/

    }

    /**
     * Create HTML for input1 field
     */
    public function input_chatid_html()
    {
        $chatId = get_option('telegram_chat_id_setting');

        echo '<input class="form-control " type="text" name="telegram_chat_id_setting" value="' . esc_attr($chatId) . '" />';
    }

    /**
     * Create HTML for input1 field
     */
    public function input_token_html()
    {
        $token = get_option('telegram_token_setting');
        echo '<input class="form-control" type="text" name="telegram_token_setting" value="' . esc_attr($token) . '" />';
    }

    /**
     * Create HTML for input1 field
     */
    /*public function input1_html()
    { ?>
<input type="text" name="cliowp_sp_input1" value="<?php echo esc_attr(get_option('cliowp_sp_input1')); ?>">
<?php
    }*/

    /**
     * Sanitize input1
     *
     * @param string $input The input value.
     */
    public function sanitize_input1($input)
    {
        if (true === empty(trim($input))) {
            add_settings_error(
                'cliowp_sp_input1',
                'cliowp_sp_input1_error',
                esc_html__('Input1 cannot be empty', 'khwpformsdb'),
            );
            return get_option('cliowp_sp_input1');
        }

        return sanitize_text_field($input);
    }

    /**
     * Create HTML for date1 field
     */
    public function date1_html()
    {
        ?>
        <input type="date" name="cliowp_sp_date1" value="<?php echo esc_attr(get_option('cliowp_sp_date1')); ?>">
        <?php
    }

    /**
     * Create HTML for datetime1 field
     */
    public function datetime1_html()
    {
        ?>
        <input type="datetime-local" name="cliowp_sp_datetime1"
            value="<?php echo esc_attr(get_option('cliowp_sp_datetime1')); ?>">
        <?php
    }

    /**
     * Create HTML for password1 field
     *
     * This is the only field that does not retrieve the value from the database
     * (because a hash is stored and not that original value).
     * Check the wp_options table to view what is saved as a hash.
     */
    public function password1_html()
    {
        ?>
        <input class="form-control " type="password" name="cliowp_sp_password1" value="">
        <?php
    }

    /**
     * Encrypt password1
     *
     * @param string $input The plain password.
     */
    public function encrypt_password1($input)
    {

        return wp_hash_password($input);
    }

    /**
     * Create HTML for number1 field
     *
     * @param array $args Arguments passed.
     */
    function number1_html()
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
            echo '<select  name="form_id_setting" id="form_id_setting">';
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
     * Create HTML for select1 field
     */
    public function select1_html()
    {
        ?>
        <select name="cliowp_sp_select1">
            <option value="1" <?php selected(get_option('cliowp_sp_select1'), '1'); ?>>
                <?php esc_attr_e('Option1', 'khwpformsdb'); ?>
            </option>
            <option value="2" <?php selected(get_option('cliowp_sp_select1'), '2'); ?>>
                <?php esc_attr_e('Option2', 'khwpformsdb'); ?>
            </option>
            <option value="3" <?php selected(get_option('cliowp_sp_select1'), '3'); ?>>
                <?php esc_attr_e('Option3', 'khwpformsdb'); ?>
            </option>
        </select>
        <?php
    }

    /**
     * Sanitize select1
     *
     * @param string $input The selected value.
     */
    public function sanitize_select1($input)
    {
        $valid_input = array('1', '2', '3');
        if (false === in_array($input, $valid_input, true)) {
            add_settings_error(
                'cliowp_sp_select1',
                'cliowp_sp_select1_error',
                esc_html__('Invalid option for Select1', 'khwpformsdb'),
            );
            return get_option('cliowp_sp_select1');
        }
        return $input;
    }

    /**
     * Create HTML for checkbox1 field
     */
    public function checkbox1_html()
    {
        ?>
        <input class="form-control " type="checkbox" name="Enable_data_saving_checkbox" value="1" <?php checked(get_option('Enable_data_saving_checkbox'), '1'); ?>>
        <?php
    }

    public function view_option_html()
    {
        $selected_option = get_option('view_option');
        $table_view_selected = ($selected_option === 'table') ? 'checked' : '';
        $normal_view_selected = ($selected_option === 'normal') ? 'checked' : '';
        ?>
        <label>
            <input type="radio" name="view_option" value="table" <?php echo $table_view_selected; ?>>
            <img width="220" src="<?php echo plugins_url('/assets/img/tableview.png', dirname(__FILE__)); ?>" alt="Table View">
        </label>
        <label>
            <input type="radio" name="view_option" value="normal" <?php echo $normal_view_selected; ?>>
            <img width="220" src="<?php echo plugins_url('/assets/img/tableview.png', dirname(__FILE__)); ?>" alt="Table View">
        </label>
        <?php
    }

    /**
     * Create HTML for Notification checkbox field
     */
    public function checkbox2_html()
    {
        ?>
        <input class="my-custom-checkbox" type="checkbox" name="Enable_notification_checkbox" value="1" <?php checked(get_option('Enable_notification_checkbox'), '1'); ?>>
        <?php
    }

    /**
     * Create HTML for Notification checkbox field
     */
    public function number_page_html()
    {

        $numberperpage = get_option('number_id_setting');

        echo '<input class="form-control " type="text" name="number_id_setting" value="' . esc_attr($numberperpage) . '" />';
    }



    /**
     * Create HTML for multiselect1 field
     */
    public function multiselect1_html()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpforms_db2';
        $results_formids = $wpdb->get_results("SELECT DISTINCT form_id FROM $table_name");
        //error_log(print_r($results_formids, true));

        if (count($results_formids) > 0) {
            $selected_values = get_option('form_id_setting');
            ?>

            <?php
            //esc_attr_e('<h1><</h1>', 'khwpformsdb');
            $message = sprintf(esc_html__('To select multiple IDs, press and hold the Ctrl button while selecting IDs.'));
            $html_message = sprintf('<div class="information-text">%s</div>', wpautop($message));
            echo wp_kses_post($html_message); ?>

            </option>

            <select name="form_id_setting[]" multiple>
                <?php
                foreach ($results_formids as $form_id) {
                    //error_log(print_r($form_id, true));
                    $option_value = esc_attr($form_id->form_id);
                    // $selected = in_array($option_value, $selected_values) ? 'selected' : '';
                    echo "<option value='" . esc_html($option_value) . "' " . esc_html($this->cliowp_multiselected($selected_values, $option_value)) . ">
                Form ID: $option_value</option>";
                }
                ?>
            </select>

        <?php } else {

            /* translators: %s: PHP version */
            $message = sprintf(esc_html__('Currently, no data has been submitted. Kindly submit at least one form.'));
            $html_message = sprintf('<div class="warning-text">%s</div>', wpautop($message));
            echo wp_kses_post($html_message);

        }
    }

    /**
     * Utility function to check if value is selected
     *
     * @param array|string $selected_values Array (or empty string) returned by get_option().
     * @param string       $current_value Value to check if it is selected.
     *
     * @return string
     */
    private function cliowp_multiselected($selected_values, string $current_value): string
    {
        if (is_array($selected_values) && in_array($current_value, $selected_values, true)) {
            return 'selected';
        }

        return '';
    }

    /**
     * Create HTML for textarea1 field
     *
     * @param array $args Arguments passed.
     */
    public function textarea1_html(array $args)
    {
        ?>
        <textarea name="cliowp_sp_textarea1" rows="<?php echo esc_html($args['rows']); ?>"
            cols="<?php echo esc_html($args['cols']); ?>"><?php echo esc_attr(get_option('cliowp_sp_textarea1')); ?></textarea>
        <?php
    }

    /**
     * Create HTML for color1 field
     */
    public function color1_html()
    {
        ?>
        <input type="color" name="khwpforms_bg_color" value="<?php echo esc_attr(get_option('khwpforms_bg_color')); ?>">
        <?php
    }

    /**
     * Create HTML for color1 field
     */
    public function color_html2()
    {
        ?>
        <input type="color" name="khwpforms_text_color" value="<?php echo esc_attr(get_option('khwpforms_text_color')); ?>">
        <?php
    }

    /**
     * Create HTML for label color field
     */
    public function color_html3()
    {
        ?>
        <input type="color" name="khwpforms_label_color" value="<?php echo esc_attr(get_option('khwpforms_label_color')); ?>">
        <?php
    }

    /**
     * Create HTML for label color field
     */
    public function color_exportbg_html()
    {
        ?>
        <input type="color" name="khwpforms_exportbg_color"
            value="<?php echo esc_attr(get_option('khwpforms_exportbg_color')); ?>">
        <?php
    }

    /**
     * Create HTML for editor1 field
     */
    public function editor1_html()
    {
        wp_editor(
            wp_kses_post(get_option('cliowp_sp_editor1')),
            'cliowp_sp_editor1',
        );
    }

    /**
     * Create Settings Page HTML
     */
    public function settings_page_html()
    {
        ?>

        <div class="wrap">
            <h1>
                <?php echo esc_attr($this->page_title); ?>
            </h1>
            <form action="<?php echo esc_attr($this->form_action); ?>" method="POST">
                <?php
                settings_fields($this->option_group);
                do_settings_sections($this->menu_slug);
                submit_button();
                ?>
            </form>
        </div>

        <?php
    }

    /**
     * Loads plugin's translated strings.
     */
    public function load_languages()
    {
        /**
         * Params of load_plugin_textdomain
         *
         * @param  string       $domain          Unique identifier for retrieving translated strings
         * @param  string|false $deprecated      Optional. Deprecated. Use the $plugin_rel_path parameter instead.
         *                                       Default false.
         * @param  string|false $plugin_rel_path Optional. Relative path to WP_PLUGIN_DIR where the .mo file resides.
         *                                       Default false.
         * @return bool         True when textdomain is successfully loaded, false otherwise.
         */
        load_plugin_textdomain(
            'khwpformsdb',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
}

// instantiate ClioWP Settings Page plugin main class.
//$cliowp_settings_page = new KHSettings();