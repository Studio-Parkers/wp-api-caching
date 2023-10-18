<?php
require_once "lib/cache.php";

add_filter("rest_pre_dispatch", function(mixed $result, WP_REST_Server $server, WP_REST_Request $request): mixed
{
    $hash = base64_encode($request->get_route());
    $cache = read_cache($hash);

    if (!is_null($cache) && !empty($cache))
    {
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

    // Don't write cache when response is cached
    if (isset($response_headers["X-Cache"]) && $response_headers["X-Cache"] === "HIT")
        return $response;

    // TODO: Check if caching is enabled for the endpoint
    // $hash = base64_encode($request->get_route());
    // if ($response->status === 200 && isset($handler["methods"]["GET"]) && $handler["methods"]["GET"])
        // write_cache($hash, $response->data);

    return $response;
}, 10, 3);