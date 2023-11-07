<?php
/**
 * Plugin Name: WP API Caching
 * Description: Cache API routes
 * Author: Dani Krokkee
 * Version: 0.0.1
 */

if (!defined("WP_API_CACHE_FOLDER"))
    define("WP_API_CACHE_FOLDER", sprintf("%s/api-cache", get_template_directory()));

require_once "admin/register.php";
require_once "middlewares.php";