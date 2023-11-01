<?php
if (!class_exists("WP_List_Table"))
    require_once ABSPATH."wp-admin/includes/class-wp-list-table.php";

require_once sprintf("%s/%s", dirname(__FILE__, 3), "lib/utilities.php");

class Cache_List_Table extends WP_List_Table
{
    public function get_columns(): array
    {
        return [
           "column_route"=> __("Route"),
           "column_file"=> __("File"),
           "column_filesize"=> __("FIle size"),
           "column_date"=> __("Last modified"),
        ];
    }

    public function prepare_items()
    {
        $this->_column_headers = [
            $this->get_columns(),
            [], // hidden columns
            $this->get_sortable_columns(),
            $this->get_primary_column_name(),
        ];

        $files = scandir(WP_API_CACHE_FOLDER);
        $files = array_values(array_diff($files, [".", ".."]));
        $this->items = array_map(function($name) {
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
        }
    }

    // function prepare_items()
    // {
    //     global $_wp_column_headers;
    //     $screen = get_current_screen();

    //     $columns = $this->get_columns();
    //     $_wp_column_headers[$screen->id] = $columns;
    //     $this->items = [["a", "b", "c", "d"]];
    // }

    // /**
    //  * Display the rows of records in the table
    //  * @return string, echo the markup of the rows
    //  */
    // function display_rows()
    // {
    //     //Get the columns registered in the get_columns and get_sortable_columns methods
    //     list($columns, $hidden) = $this->get_column_info();
    //     print_r($columns);

    //     //Loop for each record
    //     if (empty($this->items))
    //         return;

    //     foreach ($this->items as $item)
    //     {
    //         //Open the line
    //         echo "<tr>";
    //         foreach ($columns as $column_name => $column_display_name)
    //         {
    //             $attributes = sprintf("class=\"%s column-%s\" ", $column_name, $column_name);
    //             //Style attributes for each col
    //             if (in_array($column_name, $hidden))
    //                 $attributes .= "style=\"display: none;\"";

    //             //Display the cell
    //             switch ($column_name)
    //             {
    //                 case "column_route":
    //                     echo sprintf("<td %s>Route</td>", $attributes);
    //                     break;
    //                 case "column_file":
    //                     echo sprintf("<td %s>File</td>", $attributes);
    //                     break;
    //                 case "column_filesize":
    //                     echo sprintf("<td %s>filesize</td>", $attributes);
    //                     break;
    //                 case "column_date":
    //                     echo sprintf("<td %s>date</td>", $attributes);
    //                     break;
    //             }
    //         }

    //         //Close the line
    //         echo "</tr>";
    //     }
    // }

    public function no_items()
    {
        return "No caches available";
    }
};