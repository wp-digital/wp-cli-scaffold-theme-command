<?php

define( 'INNOCODE_SCAFFOLD_THEME', 'innocode_scaffold_theme' );
define( 'INNOCODE_SCAFFOLD_THEME_VERSION', '0.0.1' );

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

WP_CLI::add_command( 'scaffold theme', [ 'InnocodeScaffoldTheme\Command', 'theme' ] );