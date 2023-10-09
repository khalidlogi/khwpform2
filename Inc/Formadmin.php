<?php



global $wpdb;
//check user role
//$this->mysetts->user_roles();
//check if wpforms is active
$formbyid = $this->myselectedformid;
// retrieve form values
$form_values = $this->mydb->retrieve_form_values($formbyid);

error_log('user_role_setting: ' . get_option('user_role_setting'));


//Check if there is at least one entry
if ($this->mydb->is_table_empty() === true) {
    ob_start();

    echo '<div style="text-align: center; color: red;">No data available! Please add a form and try again.';
    echo '  <a style="text-align: center; color: black;"href="' . admin_url('admin.php?page=khwplist.php') . '">Settings DB</a></div>';

    return ob_get_clean();

} else {
    ob_start();


    foreach ($form_values as $form_value) {
        $form_id = $form_value['form_id'];
        $id = $form_value['id'];
    }

    //include edit-form file
    include_once KHFORM_PATH . 'Inc/html/edit_popup.php';

    echo '<div class="form-wraper">';

    if (empty($formbyid)) {
        echo 'To proceed, please create a form and ensure that its ID is added<a href="' . admin_url('admin.php?page=khwplist.php') . '">Go to the settings page</a>|   to change the form ID value.';
    }


    if ($form_values) {
        echo '<div class="container">';
        echo 'Number of forms submitted: ' . $this->mydb->count_items($this->myselectedformid);
        echo '<br>';
        echo 'Default form id: ' . (($this->myselectedformid === '1') ? 'All' : $this->myselectedformid);
        echo '  <a style="text-align: center; color: black;"href="' . admin_url('admin.php?page=khwplist.php') . '">Change it here</a></div>';
        $role = (get_option('user_role_setting')) ? get_option('user_role_setting') : 'Admin';
        echo 'Who can access: ' . $role;


        foreach ($form_values as $form_value) {
            $form_id = $form_value['form_id'];
            $data = $form_value['data'];
            $id = $form_value['id'];

            echo '<div class="form-set-container" data-id="' . esc_attr($id) . '">';
            echo '<button class="delete-btn" data-form-id="' . esc_attr($id) . '"><i class="fas fa-trash"></i></button>';
            echo '<button class="edit-btn delete-btn2"  data-form-id="' . esc_attr($form_id) . '" data-id="' . esc_attr($id) . '"><i class="fas fa-edit"></i></button>';

            echo '<div class="form-id-container">';
            echo '<div style="color:black;" class="form-id-label id">ID: ' . esc_html($id) . '</div>';
            echo '<span class="form-id-label">Form ID:</span>';
            echo '<span class="form-id-value">' . esc_html($form_id) . '</span>';
            echo '</div>';

            foreach ($data as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                echo '<div class="form-data-container">';
                echo '<span class="field-label">' . esc_html($key) . ':</span>';
                echo '<span class="value">' . esc_html($value) . '</span>';
                echo '</div>';
            }

            echo '</div>';
        }

        echo '<button class="export-btn"><i class="fas fa-download"></i> Export as CSV</button>';
        echo '<button class="export-btn-pdf"><i class="fas fa-download"></i> Export as PDF</button>';

        echo '</div>';
        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    }
}