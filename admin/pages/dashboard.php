<?php
if (!is_dir(WP_API_CACHE_FOLDER))
    die("No Caches available yet.");

require_once sprintf("%s/%s", dirname(__FILE__, 3), "lib/utilities.php");
require_once sprintf("%s/%s", dirname(__FILE__, 2), "components/CacheList.php");

$table = new Cache_List_Table();
$table->prepare_items();
?>
<div id="wp-api-caching-dashboard">
    <h1>Caches</h1>

    <form id="wpse-list-table-form" method="post">
    <?php $table->display(); ?>
    </form>
</div>