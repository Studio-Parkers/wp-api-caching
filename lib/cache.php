<?php
/**
 * Get the content from the cache file.
 *
 * @param string $filename
 * @return string|null
 */
function read_cache(string $filename): ?string
{
    if (!is_dir(WP_API_CACHE_FOLDER))
        return null;

    $filename = sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $filename);
    if (!is_file($filename))
        return null;
    
    return file_get_contents($filename);
}

/**
 * Write content to cache file.
 *
 * @param string $filename
 * @param mixed $content
 * @return void
 */
function write_cache(string $filename, mixed $content): void
{
    if (!is_dir(WP_API_CACHE_FOLDER))
        mkdir(WP_API_CACHE_FOLDER, 0777, true);
    
    file_put_contents(sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $filename), json_encode($content));
}

function should_cache_response(WP_HTTP_Response $response, WP_REST_Server $server, WP_REST_Request $request)
{
    // Don't update cache of cached response
    $response_headers = $response->get_headers();
    if (isset($response_headers["X-Cache"]) && $response_headers["X-Cache"] === "HIT")
        return false; 

    // Only cache succesfull requests
    if ($response->status !== 200)
        return false;

    // Only cache GET requests
    if ($request->get_method() !== "GET")
        return false;

    // Load options to check if request should be cached
    $options = get_option("wp-api-cache", null);
    if (!$options)
        return false;

    $route = $request->get_route();
    $route_params = $request->get_url_params();

    $options = json_decode($options, true);
    foreach ($options as $key => $value)
    {
        if (!isset($value["enabled"]) || !$value["enabled"])
            continue;
    
        $option_path = base64_decode($key);
        $option_path = preg_replace("/(\(\?P<(id)>(.*)\))/m", ":$2", $option_path);
        foreach ($route_params as $i=> $v)
            $option_path = str_replace(":$i", "$v", $option_path);

        if ($route === $option_path)
            return true;
    }

    return false;
}

function delete_cache(string $url_pattern): void
{
    $files = scandir(WP_API_CACHE_FOLDER);
    $files = array_values(array_diff($files, [".", ".."]));
    foreach ($files as $file)
    {
        $path = pathinfo($file, PATHINFO_FILENAME);
        $path = base64_decode($file);
        if (preg_match($url_pattern, $path) === 1)
            unlink(sprintf("%s/%s", WP_API_CACHE_FOLDER, $file));
    }
}