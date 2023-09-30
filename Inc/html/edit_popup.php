<?php
echo '<div id="edit-popup" class="edit-popup draggable" style="display: none;">
    <div class="popup-content">
        <button class="dismiss-btn"><i class="fas fa-times"></i></button>
        <h1>Edit values</h1>
        <form id="edit-form" class="edit-form input-row">
            <button id="submit-button">Submit</button>
            <div id="result"></div>
            <!-- Form fields go here -->
        </form>
        <button type="submit" data-form-id="' . esc_attr($form_id) . '" data-id="0" class="update-btn">Save</button>
    </div>
</div>';
?>