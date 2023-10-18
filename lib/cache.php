<?php
/**
 * Get the content from the cache file.
 *
 * @param string $filename
 * @return string|null
 */
function read_cache(string $filename): ?string
{
    if (!is_dir(WP_API_CACHE_FOLDER))
        return null;

    $filename = sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $filename);
    if (!is_file($filename))
        return null;
    
    return file_get_contents($filename);
}

/**
 * Write content to cache file.
 *
 * @param string $filename
 * @param mixed $content
 * @return void
 */
function write_cache(string $filename, mixed $content): void
{
    if (!is_dir(WP_API_CACHE_FOLDER))
        mkdir(WP_API_CACHE_FOLDER, 0777, true);
    
    file_put_contents(sprintf("%s/%s.json", WP_API_CACHE_FOLDER, $filename), json_encode($content));
}