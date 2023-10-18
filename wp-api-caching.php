<?php
/**
 * Plugin Name: WP API Caching
 * Description: Cache API routes
 * Author: Dani Krokkee
 * Version: 1.0.0
 */

if (!defined("WP_API_CACHE_FOLDER"))
    define("WP_API_CACHE_FOLDER", sprintf("%s/api-cache", get_template_directory()));

// Register custom admin style
add_action("admin_init", function(): void
{
    error_log(get_home_path());
    wp_enqueue_style("options-css", plugins_url("stylesheets/options.css", __FILE__));
    wp_enqueue_script("options-js", plugins_url("javascript/options.js", __FILE__), [], false, true);
});

// Register custom pages
add_action("admin_menu", function(): void
{
    add_menu_page("WP Cache", "WP Cache", "manage_options", "wp-api-caching/pages/dashboard.php");
    add_submenu_page("options-general.php", "API Caching Options", "API Caching settings", "manage_options", "wp-api-caching/pages/options.php");
});

function read_cache(string $filename)
{
    if (!is_dir(WP_API_CACHE_FOLDER))
        return null;

    $filename = sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $filename);
    if (!is_file($filename))
        return null;
    
    return file_get_contents($filename);
}

function write_cache(string $filename, $content): void
{
    if (!is_dir(WP_API_CACHE_FOLDER))
        mkdir(WP_API_CACHE_FOLDER, 0777, true);
    
    file_put_contents(sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $filename), json_encode($content));
}

add_filter("rest_pre_dispatch", function(mixed $result, WP_REST_Server $server, WP_REST_Request $request): mixed
{
    $hash = base64_encode($request->get_route());
    $cache = read_cache($hash);

    if (!is_null($cache) && !empty($cache))
    {
        // http_response_code(200);
        // header("Content-Type: application/json; charset=UTF-8", true);
        // header("X-Cache: HIT");
        // echo $cache;
        return new WP_REST_Response(json_decode($cache), 200, [
            "Content-Type"=> "application/json; charset=UTF-8",
            "X-Cache" => "HIT"
        ]);
    }

    return $result;
}, 10, 4);

add_filter("rest_post_dispatch", function(WP_HTTP_Response $response, WP_REST_Server $server, WP_REST_Request $request)
{
    $response_headers = $response->get_headers();

    // Returning 
    if (isset($response_headers["X-Cache"]) && $response_headers["X-Cache"] === "HIT")
        return $response;

    // TODO: Check if caching is enabled for the endpoint
    // $hash = base64_encode($request->get_route());
    // if ($response->status === 200 && isset($handler["methods"]["GET"]) && $handler["methods"]["GET"])
        // write_cache($hash, $response->data);

    return $response;
}, 10, 3);