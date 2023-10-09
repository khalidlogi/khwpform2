<?php


class VersionCheck
{

    private const CURRENT_VERSION = '1.0.0';

    public function __construct()
    {
        add_action('admin_notices', [$this, 'check_for_updates']);
    }

    public function check_for_updates()
    {

        $remote_version = $this->get_remote_version();

        if ($this->needs_update($remote_version)) {
            $this->display_update_notice($remote_version);
        }

    }

    private function get_remote_version()
    {
        // Make API request
        $response = wp_remote_get('https://api.example.com/plugins/myplugin');

        // Decode JSON response
        $data = json_decode($response['body']);

        return $data->version;
    }

    private function needs_update(string $remote_version)
    {
        return version_compare(self::CURRENT_VERSION, $remote_version, '<');
    }

    private function display_update_notice(string $remote_version)
    {
        $message = sprintf(
            'A new version (%s) of My Plugin is available!',
            $remote_version
        );

        echo '<div class="notice notice-warning">' . $message . '</div>';
    }

}