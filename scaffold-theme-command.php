<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

try {
	WP_CLI::add_command( 'scaffold theme', [ 'Innocode\ScaffoldTheme\Command', 'theme' ] );
} catch ( Exception $exception ) {
	trigger_error( $exception->getMessage(), E_USER_ERROR );
}
