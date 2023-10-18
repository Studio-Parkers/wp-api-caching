<?php
/**
 * Plugin Name: WP API Caching
 * Description: Cache API routes
 * Author: Dani Krokkee
 * Version: 1.0.0
 */

// Register custom admin style
add_action("admin_init", function(): void
{
    wp_enqueue_style("options-css", plugins_url("stylesheets/options.css", __FILE__));
    wp_enqueue_script("options-js", plugins_url("javascript/options.js", __FILE__), [], false, true);
});

add_filter("rest_request_before_callbacks", function($response, array $handler, \WP_REST_Request $request)
{
    // TODO: Check if cache file exists
    return $response;
}, 10, 3);


add_filter("rest_request_after_callbacks", function($response, array $handler, \WP_REST_Request $request)
{
    // TODO: Check response status, only cache 200 status and check request method, only cache GET 
    // Create cache if check passes
    return $response;
}, 10, 3);

add_action("admin_menu", function()
{
    add_menu_page("WP Cache", "WP Cache", "manage_options", "wp-api-caching/pages/dashboard.php");
    add_submenu_page("options-general.php", "API Caching Options", "API Caching settings", "manage_options", "wp-api-caching/pages/options.php");
});