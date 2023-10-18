<?php
// Register custom admin style
add_action("admin_init", function(): void
{
    wp_enqueue_style("options-css", plugins_url("../stylesheets/options.css", __FILE__));
    wp_enqueue_script("options-js", plugins_url("../javascript/options.js", __FILE__), [], false, true);
});

// Register custom pages
add_action("admin_menu", function(): void
{
    add_menu_page("WP Cache", "WP Cache", "manage_options", "wp-api-caching/pages/dashboard.php");
    add_submenu_page("options-general.php", "API Caching Options", "API Caching settings", "manage_options", "wp-api-caching/pages/options.php");
});