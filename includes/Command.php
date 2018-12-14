<?php

namespace InnocodeScaffoldTheme;

use WP_CLI;
use Scaffold_Command;

/**
 * Class Command
 * @package InnocodeScaffoldTheme
 */
class Command extends Scaffold_Command
{
    public function theme( $args, $assoc_args )
    {
        $theme_slug = $args[0];
        $theme_path = WP_CONTENT_DIR . '/themes';

        if ( ! preg_match( '/^[a-z_]\w+$/i', str_replace( '-', '_', $theme_slug ) ) ) {
            WP_CLI::error( 'Invalid theme slug specified. Theme slugs can only contain letters, numbers, underscores and hyphens, and can only start with a letter or underscore.' );
        }
    }
}