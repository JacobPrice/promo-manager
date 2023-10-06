<?php
class GitHubPluginUpdater {

private $slug; // plugin slug
private $github_repo; // user/repo
private $transient_name;
private $access_token;

public function __construct( $slug, $github_repo, $access_token = null ) {
    $this->slug = $slug;
    $this->github_repo = $github_repo;
    $this->transient_name = $slug . '_github_update';
    $this->access_token = $access_token;

    // Hooks for pre-set transients
    add_filter('pre_set_site_transient_update_plugins', array($this, 'set_update_transient'));
    add_filter('plugins_api', array($this, 'pluginInfo'), 20, 3);
}

public function set_update_transient( $transient ) {

    if( empty($transient->checked) ) {
        return $transient;
    }

    // Check for a cached version first
    $cached_response = get_transient($this->transient_name);

    if ( false === $cached_response ) {
        $release_data = $this->get_latest_release();
        
        if ( is_null($release_data) ) {
            return $transient;
        }

        $download_link = $release_data->zipball_url;
        $new_version = $release_data->tag_name;

        $package = array(
            'slug' => $this->slug,
            'new_version' => $new_version,
            'url' => $this->github_repo,
            'package' => $download_link,
        );

        // Check and set the new update info.
        $transient->response[$this->slug . '/' . $this->slug . '.php'] = (object) $package;

        // Cache the response
        set_transient($this->transient_name, $package, DAY_IN_SECONDS);
    } else {
        $transient->response[$this->slug . '/' . $this->slug . '.php'] = (object) $cached_response;
    }

    return $transient;
}

    public function pluginInfo( $false, $action, $args ) {
        if( $args->slug === $this->slug ) {
            return get_transient($this->transient_name);
        }

        return $false;
    }

    private function get_latest_release() {
        $args = array();
        if ( !is_null($this->access_token) ) {
            $args['headers'] = array(
                'Authorization' => 'token ' . $this->access_token
            );
        }

        $response = wp_remote_get('https://api.github.com/repos/' . $this->github_repo . '/releases/latest', $args);

        if ( is_wp_error($response) ) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body);
    }

}

// Usage:
// $updater = new GitHubPluginUpdater( 'my-plugin-slug', 'githubUsername/repoName' );
