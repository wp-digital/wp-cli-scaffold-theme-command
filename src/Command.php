<?php

namespace InnocodeScaffoldTheme;

use WP_CLI;
use Scaffold_Command;
use Github;
use Gettext;


/**
 * Class Command
 * @package InnocodeScaffoldTheme
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
    public function theme( $args, $assoc_args )
    {
        global $wp_version;

        $theme_slug = $args[0];
        $theme_path = WP_CONTENT_DIR . '/themes';

        if ( ! preg_match( '/^[a-z_]\w+$/i', str_replace( '-', '_', $theme_slug ) ) ) {
            WP_CLI::error( 'Invalid theme slug specified. Theme slugs can only contain letters, numbers, underscores and hyphens, and can only start with a letter or underscore.' );
        }

        $data = wp_parse_args( $assoc_args, [
            'name'        => ucfirst( $theme_slug ),
            'version'     => '1.0.0',
            'description' => '',
            'author'      => 'Innocode',
            'author_uri'  => 'https://innocode.com/',
            'text_domain' => $theme_slug,
            'repo'        => $theme_slug,
        ] );

        $theme_dir = "$theme_path/$theme_slug";
        $force = WP_CLI\Utils\get_flag_value( $assoc_args, 'force' );
        $should_write_file = $this->prompt_if_files_will_be_overwritten( $theme_dir, $force );

        if ( ! $should_write_file ) {
            WP_CLI::log( 'No files created' );
            die;
        }

        if ( strpos( $data['repo'], '/' ) === false ) {
            $data['repo'] = INNOCODE_GITHUB_USERNAME . "/{$data['repo']}";
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
            INNOCODE_GITHUB_USERNAME,
        ];
        $data['tags'] = implode( ', ', $keywords );

        $gh_client = new Github\Client();
        $gh_token = null;

        if ( defined( 'GITHUB_PAT' ) ) {
            $gh_token = GITHUB_PAT;
        } else {
            $composer_auth_json_path = static::_get_home_dir() . '/.composer/auth.json';

            if ( file_exists( $composer_auth_json_path ) ) {
                $composer_auth_json = \GuzzleHttp\json_decode( file_get_contents( $composer_auth_json_path ), true );
                $gh_token = $composer_auth_json['github-oauth']['github.com'];
            }
        }

        if ( is_null( $gh_token ) ) {
            WP_CLI::error( 'It\'s not possible to authenticate to Github since constant GITHUB_PAT is not defined and there is no OAuth token in .composer/auth.json.' );
        }

        try {
            $gh_client->authenticate( $gh_token, Github\Client::AUTH_HTTP_TOKEN );
        } catch ( \Exception $exception ) {
            WP_CLI::error( $exception->getMessage() );
        }

        /**
         * @var Github\Api\Repo $gh_api_repo
         */
        $gh_api_repo = $gh_client->api( 'repo' );
        $archive = $gh_api_repo->contents()->archive( INNOCODE_GITHUB_USERNAME, 'wp-theme-skeleton', 'zipball', 'feature/version_2' );

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

            if ( ! $this->_delete_dir( $theme_subdir ) ) {
                WP_Cli::warning( "Could not fully remove the theme subdirectory '$theme_subdir'." );
            }
        }

        $gh_user = $gh_client->currentUser()->show();

        $composer_json_author = [];
        $package_json_contributor = [];

        if ( isset( $gh_user['name'] ) ) {
            $name = trim( (string) $gh_user['name'] );

            if ( $name ) {
                $package_json_contributor['name'] = $composer_json_author['name'] = $name;
            }
        }

        if ( isset( $gh_user['email'] ) ) {
            $email = sanitize_email( (string) $gh_user['email'] );

            if ( $email && is_email( $email ) ) {
                $package_json_contributor['email'] = $composer_json_author['email'] = $email;
            }
        }

        if ( isset( $gh_user['blog'] ) ) {
            $homepage = trim( (string) $gh_user['blog'] );

            if ( $homepage ) {
                $package_json_contributor['url'] = $composer_json_author['homepage'] = esc_url( $homepage );
            }
        }

        $composer_json_path = "$theme_dir/composer.json";

        if ( file_exists( $composer_json_path ) ) {
            $composer_json = \GuzzleHttp\json_decode( file_get_contents( $composer_json_path ), true );
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

            $composer_json['repositories'][] = [
                'type' => 'composer',
                'url'  => 'https://packages.metabox.io/33c17f3f0a8f6ebbdd359f1901ba46d5', // @TODO: move key to env
            ];

            if ( ! isset( $composer_json['authors'] ) ) {
                $composer_json['authors'] = [];
            }

            if ( ! empty( $composer_json_author ) && !static::_in_array_by_params( [
                'name',
                'email',
            ], $composer_json['authors'], $composer_json_author ) ) {
                $composer_json['authors'][] = $composer_json_author;
            }

            file_put_contents( $composer_json_path, \GuzzleHttp\json_encode( $composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
        }

        $package_json_path = "$theme_dir/package.json";

        if ( file_exists( $package_json_path ) ) {
            $package_json = \GuzzleHttp\json_decode( file_get_contents( $package_json_path ), true );
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

            if ( ! empty( $package_json_contributor ) && !static::_in_array_by_params( [
                'name',
                'email',
            ], $package_json['contributors'], $package_json_contributor ) ) {
                $package_json['contributors'][] = $package_json_contributor;
            }

            file_put_contents( $package_json_path, \GuzzleHttp\json_encode( $package_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
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

        $po_path = "$theme_dir/languages/nb_NO.po";

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

        foreach ( [
            "$theme_dir/composer.lock",
            "$theme_dir/package-lock.json",
        ] as $lock ) {
            if ( file_exists( $lock ) ) {
                unlink( $lock );
            }
        }

        WP_CLI::success( "Created theme '{$data['name']}'." );
        WP_Cli::line( "Remember to run `composer install` and `npm install` in $theme_dir." );

        switch ( true ) {
            case WP_CLI\Utils\get_flag_value( $assoc_args, 'activate' ):
                WP_CLI::run_command( [ 'theme', 'activate', $theme_slug ] );
                break;
            case WP_CLI\Utils\get_flag_value( $assoc_args, 'enable-network' ):
                WP_CLI::run_command( [ 'theme', 'enable', $theme_slug ], [ 'network' => true ] );
                break;
        }
    }

    /**
     * @param string $dir
     * @return bool
     */
    protected static function _delete_dir( $dir )
    {
        global $wp_filesystem;

        return $wp_filesystem->delete( $dir, true );
    }

    /**
     * @param array $params
     * @param array $arrays
     * @param array $array
     * @return bool
     */
    protected static function _in_array_by_params( array $params, array $arrays, array $array )
    {
        foreach ( $arrays as $existing_array ) {
            foreach ( $params as $param ) {
                if (
                    isset( $existing_array[ $param ] ) && isset( $array[ $param ] )
                    && $existing_array[ $param ] == $array[ $param ]
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gets the environment's HOME directory if available.
     *
     * @return null|string
     */
    protected static function _get_home_dir()
    {
        // On Linux/Unix-like systems, use the HOME environment variable
        $home_dir = getenv( 'HOME' );

        if ( $home_dir ) {
            return $home_dir;
        }

        // Get the HOMEDRIVE and HOMEPATH values for Windows hosts
        $home_drive = getenv( 'HOMEDRIVE' );
        $home_path = getenv( 'HOMEPATH' );

        return ( $home_drive && $home_path ) ? $home_drive . $home_path : null;
    }
}