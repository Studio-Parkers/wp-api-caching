<?php
$option_name = "wp-api-cache";

// Update option when form is posted.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $tmp = [];
    foreach ($_POST as $key => $value) {
        if (str_ends_with($key, "_relations"))
            $tmp[str_replace("_relations", "", $key)]["relations"] = $value;
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
error_log(json_encode($options));
$post_types = get_post_types();
?>

<script>
const postTypes = <?php echo json_encode(array_values($post_types)); ?>;
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
                        <?php foreach ($options[$hash]["relations"] as $relation): ?>
                        <select>
                            <?php foreach ($post_types as $post_type): ?>
                            <option <?php echo $relation === $post_type ? "selected" : ""; ?> value="<?php echo $post_type; ?>"><?php echo $post_type ?></option>
                            <?php endforeach; ?>
                        </select>
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