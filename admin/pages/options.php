<?php
$option_name = "wp-api-cache";

// Update option when form is posted.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $tmp = [];
    foreach ($_POST as $key => $value)
        $tmp[$key] = ["enabled"=> $value === "on"];

    update_option($option_name, json_encode($tmp));
}

$options = get_option($option_name, null);
if (is_null($options))
{
    add_option($option_name, "[]");
    $options = get_option($option_name);
}

$options = json_decode($options, true);
?>

<h1>WP API Caching settings</h1>
<?php $rest = rest_get_server(); ?>
<form id="wp-api-caching-options" method="POST">
    <?php foreach ($rest->get_namespaces() as $namespace): ?>
    <fieldset collapsed="true">
        <legend><?php echo $namespace; ?></legend>
        <?php foreach ($rest->get_routes($namespace) as $path => $routes): ?>

        <!-- Only show GET requests -->
        <?php if (count(array_filter($routes, function($route){return isset($route["methods"]["GET"]) && $route["methods"]["GET"] === true;})) > 0): ?>
        <?php $hash = base64_encode($path); ?>
        <div>
            <input id="<?php echo $hash; ?>" name="<?php echo $hash; ?>" <?php echo isset($options[$hash]) && $options[$hash]["enabled"] ? "checked" : ""; ?> type="checkbox" />
            <label for="<?php echo $hash; ?>"><?php echo get_rest_url(null, $path); ?></label>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </fieldset>
    <?php endforeach; ?>

    <button type="submit" class="components-button editor-post-publish-button editor-post-publish-button__button is-primary">Update</button>
</form>