<?php
require_once "lib/cache.php";

add_filter("rest_pre_dispatch", function(mixed $result, WP_REST_Server $server, WP_REST_Request $request): WP_HTTP_Response
{
    $hash = base64_encode($request->get_route());
    if ($request->get_method() === "GET")
    {
        $cache = read_cache($hash);

        if (!is_null($cache) && !empty($cache))
        {
            return new WP_REST_Response(json_decode($cache), 200, [
                "Content-Type"=> "application/json; charset=UTF-8",
                "X-Cache" => "HIT"
            ]);
        }
    }

    return $result;
}, 10, 4);

add_filter("rest_post_dispatch", function(WP_HTTP_Response $response, WP_REST_Server $server, WP_REST_Request $request): WP_HTTP_Response
{
    if (!should_cache_response($response, $server, $request))
        return $response;

    // Add cache miss header when not returning cached content
    $response->header("X-Cache", "MISS");

    write_cache(base64_encode($request->get_route()), $response->data);
    return $response;
}, 10, 3);

add_filter("save_post", function(int $post_id, WP_Post $post, bool $updated): void
{
    // TODO: Find all cache relations and delete related caches
});