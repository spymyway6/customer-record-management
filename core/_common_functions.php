<?php

function get_all_get_params() {
    return $_GET; // Returns an associative array of all GET parameters
}
function get_clean_url() {
    return strtok((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '?');
}