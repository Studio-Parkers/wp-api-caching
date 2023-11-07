<?php
$option_name = "wp-api-cache";

// Update option when form is posted.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $tmp = [];
    foreach ($_POST as $key => $value)
    {
        if (str_ends_with($key, "_relations"))
        {
            $hash = str_replace("_relations", "", $key);
            foreach ($value as $i => $relation)
            {
                if (!isset($tmp[$hash]["relations"]))
                    $tmp[$hash]["relations"] = [];

                $tmp[$hash]["relations"][$relation] = [];
            }
        }
        else if(str_ends_with($key, "_related_posts"))
        {
            $hash = str_replace("_related_posts", "", $key);
            $relations = $_POST[sprintf("%s_relations", $hash)];
            $relation_keys = array_values($relations);
            foreach ($value as $i => $related_post)
                array_push($tmp[$hash]["relations"][$relation_keys[$i]], $related_post);
        }
        else
            $tmp[$key] = ["enabled"=> $value === "on"];
    }

    update_option($option_name, json_encode($tmp));
}

$options = get_option($option_name, null);
if (is_null($options))
{
    add_option($option_name, "[]");
    $options = get_option($option_name);
}
$options = json_decode($options, true);

$post_types = get_post_types();
$posts = array_map(function(string $post_type): array
{
    $tmp = get_posts([
        "post_type"=> $post_type,
        "numberposts"=> -1
    ]);

    return array_map(function(WP_Post $post): array
    {
        return [
            "ID"=> $post->ID,
            "post_type"=> $post->post_type,
            "post_title"=> $post->post_title
        ];
    }, $tmp);
}, $post_types);
?>

<script>
const postTypes = <?php echo json_encode(array_values($post_types)); ?>;
const posts = <?php echo json_encode($posts); ?>;
</script>

<h1>WP API Caching settings</h1>
<?php $rest = rest_get_server(); ?>
<form id="wp-api-caching-options" method="POST">
    <?php foreach ($rest->get_namespaces() as $namespace): ?>
    <fieldset collapsed="true">
        <legend><?php echo $namespace; ?></legend>
        <?php foreach ($rest->get_routes($namespace) as $path => $routes): ?>
            <?php $get_requests = array_filter($routes, function($route){return isset($route["methods"]["GET"]) && $route["methods"]["GET"] === true;}); ?>
            <!-- Only show GET requests -->
            <?php if (count($get_requests) > 0): ?>
            <?php $hash = base64_encode($path); ?>
            <div>
                <input id="<?php echo $hash; ?>" name="<?php echo $hash; ?>" <?php echo isset($options[$hash]) && $options[$hash]["enabled"] ? "checked" : ""; ?> type="checkbox" />
                <label for="<?php echo $hash; ?>"><?php echo get_rest_url(null, $path); ?></label>
                <fieldset class="related-types">
                    <legend>Related post types</legend>
                    <p>When the selected post types change the cache for the current selected route will be deleted.</p>
                    <div data-hash="<?php echo $hash; ?>" class="related-types-dropdowns">
                        <button type="button">Add relation</button>

                        <?php if (isset($options[$hash]["relations"]) && !empty($options[$hash]["relations"])): ?>
                        <?php foreach ($options[$hash]["relations"] as $relation => $relation_options): ?>
                        <div class="post-type-row">
                            <select name="<?php echo sprintf("%s_relations[]", $hash); ?>">
                                <?php foreach ($post_types as $post_type => $related_posts): ?>
                                <option <?php echo $relation === $post_type ? "selected" : ""; ?> value="<?php echo $post_type; ?>"><?php echo $post_type ?></option>
                                <?php endforeach; ?>
                            </select>

                            <?php $available_posts = isset(($posts[$relation])) ? $posts[$relation] : []; ?>
                            <select name="<?php echo sprintf("%s_related_posts[]", $hash); ?>">
                            <?php foreach ($available_posts as $post): ?>
                                <?php
                                $is_selected = false;
                                $related_post_types = null;
                                if (isset($options[$hash]["relations"][$post["post_type"]]))
                                    $related_post_types = $options[$hash]["relations"][$post["post_type"]];

                                if (!is_null($related_post_types) && in_array($post["ID"], $related_post_types))
                                    $is_selected = true;
                                ?>
                                <option <?php echo $is_selected ? "selected" : ""; ?> value="<?php echo $post["ID"]; ?>"><?php echo $post["ID"] === -1 ? $post["post_title"] : sprintf("%s - ID %s", $post["post_title"], $post["ID"]); ?></option>
                            <?php endforeach; ?>
                            </select>

                            <button name="remove-relation-btn" type="button">remove</button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </fieldset>
    <?php endforeach; ?>

    <button type="submit">Update</button>
</form>