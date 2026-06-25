<?php

if (session_status() === PHP_SESSION_NONE) {

    $custom_session_path = '/var/www/eoffice_kesdamjayav5/sessions';

    if (!file_exists($custom_session_path)) {
        mkdir($custom_session_path, 0755, true);
    }

    session_save_path($custom_session_path);

    ini_set('session.cookie_lifetime', 2592000);
    ini_set('session.gc_maxlifetime', 2592000);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_start();
}
