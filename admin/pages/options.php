<?php print_r($_POST); ?>

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
            <input id="<?php echo $hash; ?>" name="<?php echo $hash; ?>" type="checkbox" />
            <label for="<?php echo $hash; ?>"><?php echo get_rest_url(null, $path); ?></label>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </fieldset>
    <?php endforeach; ?>

    <button type="submit" class="components-button editor-post-publish-button editor-post-publish-button__button is-primary">Update</button>
</form>