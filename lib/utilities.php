<?php
function formatBytes(float $bytes, int $decimals = 2): string {
    if ($bytes == 0) {
        return "0 Bytes";
    }

    $k = 1024;
    $dm = $decimals < 0 ? 0 : $decimals;
    $sizes = ["Bytes", "KiB", "MiB", "GiB", "TiB", "PiB", "EiB", "ZiB", "YiB"];

    $i = (int)floor(log($bytes, $k));
    return sprintf("%.{$dm}f %s", ($bytes / pow($k, $i)), $sizes[$i]);
}