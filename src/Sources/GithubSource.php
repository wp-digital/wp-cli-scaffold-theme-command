<?php

namespace Innocode\ScaffoldTheme\Sources;

use Innocode\ScaffoldTheme\Interfaces\SourceInterface;
use Innocode\ScaffoldTheme\Interfaces\VCSInterface;
use Innocode\ScaffoldTheme\Packager;
use Innocode\ScaffoldTheme\Helpers;
use Exception;
use Github;
use WP_CLI;

/**
 * Class GithubSource
 */
final class GithubSource implements VCSInterface, SourceInterface
{
	/**
	 * @var string
	 */
	private $_username;
	/**
	 * @var string
	 */
	private $_repo;
	/**
	 * @var string
	 */
	private $_token;
	/**
	 * @var Github\Client
	 */
	private $_client;
	/**
	 * @var string
	 */
	private $_archive;
	/**
	 * @var array
	 */
	private $_current_user;

	/**
	 * GithubSource constructor.
	 * @param string $username
	 * @param string $repo
	 * @throws WP_CLI\ExitException
	 */
	public function __construct( string $username, string $repo )
	{
		$this->_username = $username;
		$this->_repo = $repo;
		$this->_client = new Github\Client();
		$this->_init_token();

		if ( $this->_token ) {
			$this->_auth();
		}
	}

	/**
	 * @return Github\Client
	 */
	public function get_client()
	{
		return $this->_client;
	}

	/**
	 * @return string
	 */
	public function get_archive() : string
	{
		if ( ! is_null( $this->_archive ) ) {
			return $this->_archive;
		}

		$this->_archive = $this->get_client()
			->repo()
			->contents()
			->archive( $this->_username, $this->_repo, 'zipball' );

		return $this->_archive;
	}

	/**
	 * @return Packager
	 */
	public function create_packager() : Packager
	{
		$current_user = $this->get_current_user();
		$packager = new Packager();

		if ( isset( $current_user['name'] ) ) {
			$packager->set_name( $current_user['name'] );
		}

		if ( isset( $current_user['email'] ) ) {
			$packager->set_email( $current_user['email'] );
		}

		if ( isset( $current_user['html_url'] ) ) {
			$packager->set_url( $current_user['html_url'] );
		}

		return $packager;
	}

	/**
	 * @return array
	 */
	public function get_current_user()
	{
		if ( is_null( $this->_current_user ) ) {
			$this->_init_current_user();
		}

		return $this->_current_user;
	}

	private function _init_token()
	{
		$this->_token = '';

		if ( defined( 'GITHUB_PAT' ) ) {
			$this->_token = GITHUB_PAT;

			return;
		}

		$composer_auth_json_path = Helpers::get_home_dir() . '/.composer/auth.json';

		if ( ! file_exists( $composer_auth_json_path ) ) {
			return;
		}

		$composer_auth_json = \GuzzleHttp\json_decode( file_get_contents( $composer_auth_json_path ), true );

		if ( ! isset( $composer_auth_json['github-oauth']['github.com'] ) ) {
			return;
		}

		$this->_token = $composer_auth_json['github-oauth']['github.com'];
	}

	/**
	 * @throws WP_CLI\ExitException
	 */
	private function _auth()
	{
		try {
			$this->_client->authenticate( $this->_token, Github\Client::AUTH_HTTP_TOKEN );
		} catch ( Exception $exception ) {
			WP_CLI::error( $exception->getMessage() );
		}
	}

	private function _init_current_user()
	{
		$this->_current_user = [];

		try {
			$this->_current_user = $this->get_client()
				->currentUser()
				->show();
		} catch ( Exception $exception ) {
			WP_CLI::line( $exception->getMessage() );
		}
	}
}
