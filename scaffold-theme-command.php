<?php

if ( ! defined( 'INNOCODE_SCAFFOLD_THEME' ) ) {
    define( 'INNOCODE_SCAFFOLD_THEME', 'innocode_scaffold_theme' );
}

if ( ! defined( 'INNOCODE_SCAFFOLD_THEME_VERSION' ) ) {
    define( 'INNOCODE_SCAFFOLD_THEME_VERSION', '1.0.2' );
}

if ( ! defined( 'INNOCODE_GITHUB_USERNAME' ) ) {
    define( 'INNOCODE_GITHUB_USERNAME', 'innocode-digital' );
}

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

WP_CLI::add_command( 'scaffold theme', [ 'InnocodeScaffoldTheme\Command', 'theme' ] );