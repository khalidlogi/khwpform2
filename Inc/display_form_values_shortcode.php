<?php

defined('ABSPATH') || exit;

class ShortcodeClass {


    private $mydb;
    private $view_options;
    private $mysetts;
    private $table_name;

    private $mylink;
    private $text_color;
    private $label_color;
    private $bgcolor;
    private $formbyid;
    private $exportbgcolor;
    private $items_per_page;

    private $formCount;

    public function __construct() {

        global $wpdb;
        $this->table_name = $wpdb->prefix.'wpforms_db2';
        $this->view_options = get_option('view_option') ?: 'normal';
        $this->label_color = get_option('khwpforms_label_color');
        $this->text_color = get_option('khwpforms_text_color');
        $this->bgcolor = get_option('khwpforms_bg_color');
        $this->exportbgcolor = get_option('khwpforms_exportbg_color') ?: '#408c4f';
        $this->isnotif = get_option('Enable_notification_checkbox') ?: '0';
        $this->items_per_page = get_option('number_id_setting') ?: '10';

        $this->formbyid = KHdb::getInstance()->retrieve_form_id();
        $this->formCount = KHdb::getInstance()->count_items($this->formbyid);

        //Shortcode based on select view mode
        if($this->view_options === 'normal') {
            add_shortcode('display_form_values', array($this, 'display_form_values_shortcode'));
        } else {
            add_shortcode('display_form_values', array($this, 'display_form_values_shortcode_table'));

        }

    }

    function display_form_values_shortcode_table($atts) {

        global $wpdb;
        $is_wpforms_active = KHdb::getInstance()->is_wpforms_active();

        $atts = shortcode_atts(
            array(
                'id' => '',
            ),
            $atts
        );

        $current_page = max(1, get_query_var('paged'));
        $offset = ($current_page - 1) * $this->items_per_page;
        //$totalCount = KHdb::getInstance()->count_items();
        if($this->formCount != 0 && $this->items_per_page != 0) {

            $total_pages = ceil($this->formCount / $this->items_per_page);
        }

        // see if user do not have authorization 
        if(!current_user_can('manage_options')) {
            // Assuming you have a link that takes users to the login page, you can add the referer URL as a query parameter.

            ob_start();

            echo '<div style="text-align: center; color: red;">You are not authorized to access this page. <a href="'.wp_login_url(add_query_arg('redirect', 'wpfurl')).'">Login</a></div>';                //echo 'login: ' . wp_login_url();

            return ob_get_clean();

        } else {

            //get the form id
            if(!empty($atts['id'])) {
                $formbyid = $atts['id'];
            } else {
                $formbyid = $this->formbyid;
                error_log('formbyid'.$formbyid);

            }

            //error_log('display the changed form id'.$formbyid);
            // retrieve form values
            //$form_values = KHdb::getInstance()->retrieve_form_values($formbyid, $offset);
            $form_values = KHdb::getInstance()->retrieve_form_values($this->formbyid, $offset, $this->items_per_page, '');



            //Check if there is at least one entry
            if(KHdb::getInstance()->is_table_empty() === true) {
                ob_start();

                echo '<div style="text-align: center; color: red;">No data available! Please add entries to your form and try again.';
                echo ' <a style="text-align: center; color: black;" href="'.admin_url('admin.php?page=khwplist.php').'">Settings
                DB</a></div>';

                return ob_get_clean();

            } else {
                ob_start();

                //include edit-form file
                include_once KHFORM_PATH.'Inc/html/edit_popup.php';
                echo '<br>
                <div class="form-wraper">';
                if(!$is_wpforms_active) {
                    echo '<div style="color:red;"><i class="fas fa-exclamation-circle"></i> Wpforms is not ACTIVE</div>';
                }
                echo '
                    Visit the <a href="'.admin_url('admin.php?page=khwplist.php').
                    '"> settings page </a> to update the form ID value.';

                if($form_values) {
                    echo '<div class="container">';
                    echo 'Number of forms submitted: '.KHdb::getInstance()->count_items($formbyid);
                    if(!empty($formbyid)) {
                        echo '<br> Default form id: '.(($formbyid === '1') ? 'Show all forms' : $formbyid);
                    }
                }
                // Start table
                echo '<div class="form-data-container">';
                echo '<table style="border: 1px solid black;">';

                // Table header
                echo '<tr>';
                echo '<th>ID</th>';
                echo '<th>Form ID</th>';
                echo '<th>Data</th>';
                echo '</tr>';

                foreach($form_values as $form_value) {
                    $form_id = intval($form_value['form_id']);
                    $id = intval($form_value['id']);

                    // Table row
                    echo '<tr style="border: .5px solid black;" >';
                    echo '<td style="border: .5px solid black;  padding: 10px; text-align: center;">'.$id.'</td>';
                    echo '<td style="border: 1px solid black;  padding: 10px; text-align: center;">'.$form_id.'</td>';
                    echo '<td style="border: 1px solid black;">';

                    // Table data
                    foreach($form_value['data'] as $key => $value) {
                        if(empty($value)) {
                            continue;
                        }

                        echo '<div>';
                        echo '<span>'.$key.': </span>';
                        echo '<span>'.$value.' </span>';

                        echo '</div>';
                    }

                    echo '<div style="text-align:center; background:gray;" class="delete-edit-wraper">';
                    echo '<button class="deletebtn" data-form-id="'.esc_attr($id).'" data-nonce="'.wp_create_nonce('ajax-nonce').'">
                    <i class="fas fa-trash"></i></button>';
                    //<button class="delete-btn" data-form-id="' . esc_attr($id) . '"
                    //data-nonce="' . wp_create_nonce('ajax-nonce') . '">
                    //<i class="fas fa-trash"></i></button>
                    echo '<button class="editbtn" 
                    data-form-id="'.esc_attr($form_id).'" data-id="'.esc_attr($id).'"><i
                    class="fas fa-edit"></i></button>';
                    echo '</div>';


                    echo '</td>';
                    echo '</tr>';
                }

                // End table
                echo '</table>';

                echo '<div class="pagination-links">';
                echo paginate_links(
                    array(
                        'base' => esc_url(add_query_arg('paged', '%#%')),
                        'format' => '',
                        'prev_text' => __('&laquo; Previous'),
                        'next_text' => __('Next &raquo;'),
                        'total' => ceil($wpdb->get_var("SELECT COUNT(id) FROM $this->table_name WHERE form_id = {$formbyid}  ") / $this->items_per_page),
                        'current' => $current_page,
                    )
                );
                echo '</div>';

                echo '<button style="background:'.$this->exportbgcolor.';" class="export-btn"><i class="fas fa-download"></i> Export as CSV</button>';
                echo '<button style="background:'.$this->exportbgcolor.';" class="export-btn-pdf"><i class="fas fa-download"></i> Export as PDF</button>';
                echo '</div>';

                return ob_get_clean();
            }
        }
    }


    /**
     * display form values shortcode
     *
     * @since 1.0.0
     */
    //

    function generate_pagination($formCount, $items_per_page) {
        $current_page = max(1, get_query_var('paged'));
        $total_pages = ceil($formCount / $items_per_page);

        echo paginate_links(
            array(
                'base' => esc_url(add_query_arg('paged', '%#%')),
                'format' => '',
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
                'total' => $total_pages,
                'current' => $current_page,
            )
        );
    }

    function shortcode_function() {
        // Retrieve $formCount and $items_per_page variables

        // ...


    }

    function display_form_values_shortcode($atts) {
        global $wpdb;
        $is_wpforms_active = KHdb::getInstance()->is_wpforms_active();

        $atts = shortcode_atts(
            array(
                'id' => '',
            ),
            $atts
        );

        $current_page = max(1, get_query_var('paged'));
        $offset = ($current_page - 1) * $this->items_per_page;
        //$totalCount = KHdb::getInstance()->count_items();
        if($this->formCount != 0 && $this->items_per_page != 0) {

            $total_pages = ceil($this->formCount / $this->items_per_page);
        }

        // see if user do not have authorization
        if(!current_user_can('manage_options')) {
            // Assuming you have a link that takes users to the login page, you can add the referer URL as a query parameter.

            ob_start();

            echo '<div style="text-align: center; color: red;">You are not authorized to access this page. <a
        href="'.wp_login_url(add_query_arg('redirect', 'wpfurl')).'">Login</a></div>'; //echo 'login: ' .
            wp_login_url();

            return ob_get_clean();

        } else {

            //get the form id
            if(!empty($atts['id'])) {
                $formbyid = $atts['id'];
            } else {
                $formbyid = $this->formbyid;
                error_log('formbyid'.$formbyid);

            }
            // retrieve form values
            $form_values = KHdb::getInstance()->retrieve_form_values($this->formbyid, $offset, $this->items_per_page, '');

            //Check if there is at least one entry
            if(KHdb::getInstance()->is_table_empty() === true) {
                ob_start();
                echo '<div style="text-align: center; color: red;">No data available! Please add etries to your form and try again.';
                echo ' <a style="text-align: center; color: black;" href="'.admin_url('admin.php?page=khwplist.php').'">Settings
                DB</a></div>';
                return ob_get_clean();

            } else {
                ob_start();
                foreach($form_values as $form_value) {
                    $form_id = intval($form_value['form_id']);
                    $id = intval($form_value['id']);
                }

                //include edit-form file
                include_once KHFORM_PATH.'Inc/html/edit_popup.php';

                echo '<br><div class="form-wraper">';
                echo 'Visit the <a href="'.admin_url('admin.php?page=khwplist.php').
                    '"> settings page </a> to update the form ID value.';

                if($form_values) {
                    echo '<div class="container">';
                    echo 'Number of forms submitted: '.$this->formCount;
                    if(!empty($formbyid)) {
                        echo '<br> Default form id: '.(($formbyid === '1') ? 'Show all forms' : $formbyid);
                    }

                    foreach($form_values as $form_value) {
                        $form_id = $form_value['form_id'];
                        $data = $form_value['data'];
                        $id = $form_value['id'];

                        //Delete button
                        echo '<div class="form-set-container" style="background:'.$this->bgcolor.';"
                        data-id="'.esc_attr($id).'">';
                        echo '<button class="delete-btn" data-form-id="'.esc_attr($id).'"
                        data-nonce="'.wp_create_nonce('ajax-nonce').'">
                        <i class="fas fa-trash"></i></button>';

                        //Edit button
                        echo '<button class="edit-btn delete-btn2" data-form-id="'.esc_attr($form_id).'"
                        data-id="'.esc_attr($id).'"><i class="fas fa-edit"></i></button>';

                        echo '<div class="form-id-container">';
                        echo '<div class="form-id-label id">
                        <span style="color:'.$this->label_color.';"> ID </span>: <span
                        style="color:'.$this->text_color.';"> '.esc_html($id).' </span>
                        </div>';
                        echo '<span style="color:'.$this->label_color.';" class="form-id-label">Form ID:</span>';
                        echo '<span style="color:'.$this->text_color.';" class="form-id-value">'.esc_html($form_id).
                            '</span>';
                        echo '</div>';

                        foreach($data as $key => $value) {
                            if(empty($value)) {
                                continue;
                            }

                            echo '<div class="form-data-container">';
                            echo '<span class="field-label" style="color:'.$this->label_color.';">'.esc_html($key).':
                            </span>';
                            // Check if $key is 'ADMIN_NOTE'
                            if(strtoupper($key) === 'ADMIN_NOTE') {
                                echo '<span class="value" style="color: red; font-weight:bold;">'.esc_html($value).'</span>';
                            } else {
                                echo '<span style="color:'.$this->text_color.';" class="value">'.esc_html($value).'</span>';
                            }

                            echo '</div>';
                        }

                        echo '</div>';
                    }


                    echo '<div class="pagination-links">';
                    echo '<div class="pagination-links">';

                    if($this->formCount != 0 && $this->items_per_page != 0) {
                        $this->generate_pagination($this->formCount, $this->items_per_page);
                    }

                    /*echo paginate_links(
                        array(
                            'base' => esc_url(add_query_arg('paged', '%#%')),
                            'format' => '',
                            'prev_text' => __('&laquo; Previous'),
                            'next_text' => __('Next &raquo;'),
                            'total' => ceil($wpdb->get_var("SELECT COUNT(id) FROM $this->table_name WHERE form_id = {$formbyid}  ") / $this->items_per_page),
                            'current' => $current_page,
                        )
                    );*/
                    echo '</div>';

                    echo '<button style="background:'.$this->exportbgcolor.';" class="export-btn"><i class="fas fa-download"></i> Export as CSV</button>';
                    echo '<button style="background:'.$this->exportbgcolor.';" class="export-btn-pdf"><i class="fas fa-download"></i> Export as PDF</button>';
                    echo '</div>';

                    return ob_get_clean();
                }
            }
        }

    }
}

new ShortcodeClass();