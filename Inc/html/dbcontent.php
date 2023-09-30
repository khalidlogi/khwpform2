<?php

?>




<div id="edit-popup" class="edit-popup" style="display: none;">
    <div class="popup-content">
        <button class="dismiss-btn"><i class="fas fa-times"></i></button>
        <h1>Edit values</h1>
        <form id="edit-form" class="edit-form input-row"">

            <button id=" submit-button">Submit</button>

            <div id="result"></div>
            <!-- Form fields go here -->

            <!-- Add this button to your HTML where you want the update button to appear -->

        </form> <button type=" submit" data-form-id="<?php echo esc_attr($form_id); ?>""
                        class=" update-btn">Save</button>
    </div>
</div>


<?php