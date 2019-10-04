<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if ( ! defined( 'INNOCODE_SCAFFOLD_THEME_SOURCE' ) ) {
	define( 'INNOCODE_SCAFFOLD_THEME_SOURCE', 'github' );
}

if ( in_array( INNOCODE_SCAFFOLD_THEME_SOURCE, [
	'github',
] ) ) {
	if ( ! defined( 'INNOCODE_SCAFFOLD_THEME_SOURCE_USERNAME' ) ) {
		define( 'INNOCODE_SCAFFOLD_THEME_SOURCE_USERNAME', 'innocode-digital' );
	}

	if ( ! defined( 'INNOCODE_SCAFFOLD_THEME_SOURCE_REPOSITORY' ) ) {
		define( 'INNOCODE_SCAFFOLD_THEME_SOURCE_REPOSITORY', 'wp-theme-skeleton' );
	}
} elseif ( ! defined( 'INNOCODE_SCAFFOLD_THEME_SOURCE_URL' ) ) {
	trigger_error( 'Missing INNOCODE_SCAFFOLD_THEME_SOURCE_URL constant.', E_USER_ERROR );
}

require_once __DIR__ . '/vendor/autoload.php';

try {
	WP_CLI::add_command( 'scaffold theme', [ 'Innocode\ScaffoldTheme\Command', 'theme' ] );
} catch ( Exception $exception ) {
	trigger_error( $exception->getMessage(), E_USER_ERROR );
}
