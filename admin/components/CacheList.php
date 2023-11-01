<?php
if (!class_exists("WP_List_Table"))
    require_once ABSPATH."wp-admin/includes/class-wp-list-table.php";

require_once sprintf("%s/%s", dirname(__FILE__, 3), "lib/utilities.php");

class Cache_List_Table extends WP_List_Table
{
    protected function column_cb($item): string
    {
        return sprintf("<input type=\"checkbox\" name=\"caches[]\" value=\"%s\" />", $item["name"]);
    }

    public function get_columns(): array
    {
        return [
            "cb"=> "<input type=\"checkbox\" />",
            "column_route"=> __("Route"),
            "column_file"=> __("File"),
            "column_filesize"=> __("FIle size"),
            "column_date"=> __("Last modified"),
        ];
    }

    public function get_sortable_columns(): array
    {
        return [
            "column_route" => ["column_route", false],
            "column_file" => ["column_file", false],
            "column_filesize" => ["column_filesize", false],
            "column_date" => ["column_date", false]
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name)
        {
            case "column_route":
                return base64_decode($item["name"]);
            case "column_file":
                return $item["fullpath"];
            case "column_filesize":
                return formatBytes($item["size"]);
            case "column_date":
                return date("d-m-Y H:i:s", $item["date_modified"]);
            default:
                return $item[$column_name];
        }
    }

    function get_bulk_actions(): array
    {
        return [
            "delete" => __("Delete")
        ];
    }

    function process_bulk_action()
    {
        if (!isset($_POST["caches"]) || empty($_POST["caches"]))
            return;
        
        if (!isset($_POST["_wpnonce"]) || empty($_POST["_wpnonce"]))
            return $this->invalid_nonce_redirect();

        $nonce = $_POST["_wpnonce"];
        if (!wp_verify_nonce($nonce, "bulk-wp-api-cachingadminpagesdashboard"))
            return $this->invalid_nonce_redirect();

        $action = $this->current_action();
        if ($action !== "delete")
            return;

        $caches = $_POST["caches"];
        if (!is_array($caches))
            $caches = [$caches];
        
        foreach ($caches as $cache)
            unlink(sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $cache));
    }

    public function no_items()
    {
        _e("No caches available");
    }

    public function prepare_items()
    {
        // Setup columns
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
            $this->get_primary_column_name()
        ];

        // Handle actions
        $this->process_bulk_action();

        // Content
        $files = scandir(WP_API_CACHE_FOLDER);
        $files = array_values(array_diff($files, [".", ".."]));
        $files = array_map(function($name) {
            $filepath = sprintf("%s/%s", WP_API_CACHE_FOLDER, $name);
            $fileInfo = new SplFileInfo($filepath);
            return [
                "name" => pathinfo($name, PATHINFO_FILENAME),
                "filename" => $name,
                "fullpath" => $filepath,
                "size" => $fileInfo->getSize(),
                "date_modified" => $fileInfo->getMTime()
            ];
        }, $files);

        // Sorting
        $sort_key = "name";
        $sort_type = isset($_GET["order"]) ? $_GET["order"] : "asc";
        switch (isset($_GET["orderby"]) ? $_GET["orderby"] : "column_route")
        {
            case "column_file":
                $sort_key = "fullpath";
                break;

            case "column_filesize":
                $sort_key = "size";
                break;

            case "column_date":
                $sort_key = "date_modified";
                break;
            default:
                $sort_key = "name";
                break;
        }

        usort($files, function(array $a, array $b) use ($sort_key, $sort_type)
        {
            $first = $sort_type === "asc" ? $a : $b;
            $second = $sort_type === "asc" ? $b : $a;
 
            if ($sort_key === "name")
                return strcmp($first["name"], $second["name"]);

            if ($sort_key === "fullpath")
                return strcmp($first["fullpath"], $second["fullpath"]);

            return $first[$sort_key] - $second[$sort_key];
        });

        // Pagination
        $total_items = count($files);
        $items_per_page = $this->get_items_per_page("users_per_page");
        $current_page = $this->get_pagenum();	
        $this->items = array_slice($files, ($current_page - 1) * $items_per_page, $items_per_page);

        $this->set_pagination_args([
            "total_items" => $total_items,
            "per_page" => $items_per_page,
            "total_pages" => ceil($total_items / $items_per_page)
        ]);
    }
};