<?php

namespace Innocode\ScaffoldTheme;

use Gettext;
use GuzzleHttp;
use Innocode\ScaffoldTheme\Interfaces\VCSInterface;
use Innocode\ScaffoldTheme\Sources\GithubSource;
use Innocode\ScaffoldTheme\Sources\ZipSource;
use Localheinz\Json\Printer\Printer;
use Scaffold_Command;
use WP_CLI;


/**
 * Class Command
 * @package Innocode\ScaffoldTheme
 */
class Command extends Scaffold_Command
{
    /**
     * Generates starter code for a theme based on Innocode theme skeleton.
     *
     * See the [WP Theme Skeleton](https://github.com/innocode-digital/wp-theme-skeleton) for more details.
     *
     * ## OPTIONS
     *
     * <slug>
     * : The slug for the new theme, used for prefixing functions.
     *
     * [--activate]
     * : Activate the newly downloaded theme.
     *
     * [--enable-network]
     * : Enable the newly downloaded theme for the entire network.
     *
     * [--name=<title>]
     * : What to put in the 'Theme Name:' header in 'style.css'. Default is <slug> with uppercase first letter.
     *
     * [--version=<version>]
     * : What to put in the 'Version:' header in 'style.css' and in the 'version' property in 'composer.json' and 'package.json' files. Default is '1.0.0'.
     *
     * [--description=<text>]
     * : What to put in the 'Description:' header in 'style.css' and in the 'description' property in 'composer.json' and 'package.json' files. Default is ''.
     *
     * [--author=<full-name>]
     * : What to put in the 'Author:' header in 'style.css'. Default is 'Innocode'.
     *
     * [--author_uri=<uri>]
     * : What to put in the 'Author URI:' header in 'style.css'. Default is 'https://innocode.com/'.
     *
     * [--text_domain=<domain>]
     * : What to put in the 'Text Domain:' header in 'style.css'. Default is <slug>.
     *
     * [--repo=<slug>]
     * : What is a repo on Github for this project. Default is 'innocode-digital/<slug>'.
     *
     * [--force]
     * : Overwrite files that already exist.
	 *
	 * [--skeleton_source=<source>]
	 * : What is a source of skeleton theme. Possible values are 'github' and 'zip'. Default is 'github'.
	 *
	 * [--source_username=<username>]
	 * : What is a username on Github. Default is 'innocode-digital'.
	 *
	 * [--source_repo=<repo>]
	 * : What is a repository on Github. No need to use it when <skeleton_source> is 'zip'. Default is 'wp-theme-skeleton'.
	 *
	 * [--source_url=<url>]
	 * : What is an URL of source. Applicable only when <skeleton_source> is 'zip'.
	 *
	 * [--skip-env]
	 * : Don't generate .env file.
	 *
	 * [--skip-install-notice]
	 * : Don't show notice about need to run installation commands.
     *
     * ## EXAMPLES
     *
     *     # Generate a theme with name "Sample Theme" and author "John Doe"
     *     $ wp scaffold theme sample-theme --theme_name="Sample Theme" --author="John Doe"
     *     Success: Created theme 'Sample Theme'.
     *
     * @param array $args
     * @param array $assoc_args
     * @throws WP_CLI\ExitException
     */
    public function theme( array $args, array $assoc_args )
    {
        global $wp_version;

        $theme_slug = $args[0];
        $theme_path = WP_CONTENT_DIR . '/themes';

        if ( ! preg_match( '/^[a-z_]\w+$/i', str_replace( '-', '_', $theme_slug ) ) ) {
            WP_CLI::error( 'Invalid theme slug specified. Theme slugs can only contain letters, numbers, underscores and hyphens, and can only start with a letter or underscore.' );
        }

        $data = wp_parse_args( $assoc_args, [
			'name'            => ucwords( str_replace( [ '-', '_' ], ' ', $theme_slug ) ),
			'version'         => '1.0.0',
			'description'     => '',
			'author'          => 'Innocode',
			'author_uri'      => 'https://innocode.com/',
			'text_domain'     => $theme_slug,
			'repo'            => $theme_slug,
			'skeleton_source' => 'github',
			'source_username' => 'innocode-digital',
			'source_repo'     => 'wp-theme-skeleton',
			'source_url'      => '',
        ] );

        $theme_dir = "$theme_path/$theme_slug";
        $force = WP_CLI\Utils\get_flag_value( $assoc_args, 'force' );
        $should_write_file = $this->prompt_if_files_will_be_overwritten( $theme_dir, $force );

        if ( ! $should_write_file ) {
            WP_CLI::log( 'No files created' );
            die;
        }

        if (
        	strpos( $data['repo'], '/' ) === false &&
			$data['source_username']
		) {
            $data['repo'] = "{$data['source_username']}/{$data['repo']}";
        }

        $theme_uri = "https://github.com/{$data['repo']}";
        $theme_readme = "$theme_uri#readme";
        $theme_issues = "$theme_uri/issues";

        $keywords = [
            'wordpress',
            'wp',
            'theme',
            'wordpress-theme',
            'wp-theme',
            $theme_slug,
        ];
        $data['tags'] = implode( ', ', $keywords );

		switch ( $data['skeleton_source'] ) {
			case 'github':
				$source = new GithubSource( $data['source_username'], $data['source_repo'] );
				break;
			default:
				if ( ! $data['source_url'] ) {
					WP_CLI::error( 'Missing source URL.' );
				}

				$source = new ZipSource( $data['source_url'] );
				break;
		}

		$archive = $source->get_archive();

		if ( $source instanceof VCSInterface ) {
			$packager = $source->create_packager();
			$composer_package_author = $packager->get_composer_package_author();
			$npm_package_contributor = $packager->get_npm_package_contributor();
		}

        $tmpfname = wp_tempnam();
        $this->maybe_create_themes_dir();
        $this->init_wp_filesystem();
        file_put_contents( $tmpfname, $archive );
        $existing_dirs = is_dir( $theme_dir ) ? scandir( $theme_dir ) : [];
        $unzip_result = unzip_file( $tmpfname, $theme_dir );
        unlink( $tmpfname );

        if ( is_wp_error( $unzip_result ) ) {
            WP_CLI::error( "Could not decompress your theme files ('$tmpfname') at '$theme_path': {$unzip_result->get_error_message()}" );
        }

        $dirs = scandir( $theme_dir );

        foreach ( $dirs as $dir ) {
            $theme_subdir = "$theme_dir/$dir";

            if ( '.' == $dir[0] || ! is_dir( $theme_subdir ) || in_array( $dir, $existing_dirs ) ) {
                continue;
            }

            copy_dir( $theme_subdir, $theme_dir );

            if ( ! Helpers::delete_dir( $theme_subdir ) ) {
                WP_Cli::warning( "Could not fully remove the theme subdirectory '$theme_subdir'." );
            }
        }

		$json_printer = new Printer();
        $composer_json_path = "$theme_dir/composer.json";

        if ( file_exists( $composer_json_path ) ) {
            $composer_json = GuzzleHttp\json_decode( file_get_contents( $composer_json_path ), true );
            $composer_json['name'] = $data['repo'];
            $composer_json['version'] = $data['version'];
            $composer_json['description'] = $data['description'];
            $composer_json['homepage'] = $theme_uri;
            $composer_json['readme'] = $theme_readme;
            $composer_json['support'] = [
                'issues' => $theme_issues,
                'source' => $theme_uri,
            ];
            $composer_json['keywords'] = $keywords;

            if ( ! isset( $composer_json['repositories'] ) ) {
                $composer_json['repositories'] = [];
            }

            if ( ! isset( $composer_json['authors'] ) ) {
                $composer_json['authors'] = [];
            }

            if ( ! empty( $composer_package_author ) && ! Helpers::in_array_by_params( [
                'name',
                'email',
            ], $composer_json['authors'], $composer_package_author ) ) {
                $composer_json['authors'][] = $composer_package_author;
            }

            file_put_contents( $composer_json_path, $json_printer->print( $composer_json, '  ' ) );
            $root_dir = dirname( ABSPATH );
			$root_composer_json_path = "$root_dir/composer.json";

			if ( isset( $composer_json['autoload'] ) && file_exists( $root_composer_json_path ) ) {
				$root_composer_json = GuzzleHttp\json_decode( file_get_contents( $root_composer_json_path ), true );
				$relative_theme_dir = str_replace( WP_CLI\Utils\trailingslashit( $root_dir ), '', $theme_dir );
				Helpers::copy_composer_autoload( $composer_json, $relative_theme_dir, $root_composer_json );
				file_put_contents( $root_composer_json_path, $json_printer->print( $root_composer_json, '  ' ) );
			}
        }

        $package_json_path = "$theme_dir/package.json";

        if ( file_exists( $package_json_path ) ) {
            $package_json = GuzzleHttp\json_decode( file_get_contents( $package_json_path ), true );
            $package_json['name'] = $theme_slug;
            $package_json['version'] = $data['version'];
            $package_json['description'] = $data['description'];
            $package_json['homepage'] = $theme_readme;
            $package_json['bugs'] = [
                'url' => $theme_issues,
            ];
            $package_json['repository'] = "github:{$data['repo']}";
            $package_json['keywords'] = $keywords;

            if ( ! isset( $package_json['contributors'] ) ) {
                $package_json['contributors'] = [];
            }

            if ( ! empty( $npm_package_contributor ) && ! Helpers::in_array_by_params( [
                'name',
                'email',
            ], $package_json['contributors'], $npm_package_contributor ) ) {
                $package_json['contributors'][] = $npm_package_contributor;
            }

            file_put_contents( $package_json_path, $json_printer->print( $package_json, '  ' ) );
        }

        file_put_contents( "$theme_dir/style.css", "@charset \"UTF-8\";
/*
Theme Name: {$data['name']}
Theme URI: $theme_uri
Author: {$data['author']}
Author URI: {$data['author_uri']}
Description: {$data['description']}
Requires at least: WordPress $wp_version
Version: {$data['version']}
Text Domain: {$data['text_domain']}
Tags: {$data['tags']}
*/" );
        file_put_contents( "$theme_dir/README.md", "# {$data['name']}
        
{$data['description']}

Requires at least: WordPress $wp_version." );

        $po_path = "$theme_dir/languages/skeleton.pot";

        if ( file_exists( $po_path ) ) {
			$translations = Gettext\Translations::fromPoFile( $po_path );
            $translations->setHeader( 'Project-Id-Version', "$theme_slug {$data['version']}" );
            $translations->setHeader( 'Report-Msgid-Bugs-To', $theme_issues );
            $translations->setHeader( 'POT-Creation-Date', date( 'c' ) );
            $translations->setHeader( 'PO-Revision-Date', date( 'c' ) );
            Gettext\Generators\Po::toFile( $translations, $po_path );
        }

        $functions_php_path = "$theme_dir/functions.php";

        if ( file_exists( $functions_php_path ) ) {
            $functions_php = file_get_contents( $functions_php_path );
            $functions_php = str_replace( 'const TEXT_DOMAIN = \'\';', "const TEXT_DOMAIN = '{$data['text_domain']}';", $functions_php );
            file_put_contents( $functions_php_path, $functions_php );
        }

		$skip_dotenv = WP_CLI\Utils\get_flag_value( $assoc_args, 'skip-env' );

        if ( ! $skip_dotenv ) {
			file_put_contents( "$theme_dir/.env", '' );
		}

        foreach ( [
            "$theme_dir/composer.lock",
            "$theme_dir/package-lock.json",
        ] as $lock ) {
            if ( file_exists( $lock ) ) {
                unlink( $lock );
            }
        }

        WP_CLI::success( "Created theme '{$data['name']}'." );
		$skip_install_notice = WP_CLI\Utils\get_flag_value( $assoc_args, 'skip-install-notice' );

		if ( ! $skip_install_notice ) {
			WP_Cli::line( "Remember to run `composer install` and `npm install` in $theme_dir." );
		}

        switch ( true ) {
            case WP_CLI\Utils\get_flag_value( $assoc_args, 'activate' ):
                WP_CLI::run_command( [ 'theme', 'activate', $theme_slug ] );
                break;
            case WP_CLI\Utils\get_flag_value( $assoc_args, 'enable-network' ):
                WP_CLI::run_command( [ 'theme', 'enable', $theme_slug ], [ 'network' => true ] );
                break;
        }
    }
}
