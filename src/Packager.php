<?php

namespace Innocode\ScaffoldTheme;

/**
 * Class Packager
 * @package Innocode\ScaffoldTheme
 */
class Packager
{
	/**
	 * @var string
	 */
	protected $_name;
	/**
	 * @var string
	 */
	protected $_email;
	/**
	 * @var string
	 */
	protected $_url;

	/**
	 * @return string
	 */
	public function get_name() : string
	{
		return $this->_name;
	}

	/**
	 * @param string $name
	 */
	public function set_name( string $name )
	{
		$this->_name = trim( $name );
	}

	/**
	 * @return string
	 */
	public function get_email() : string
	{
		return is_email( $this->_email ) ? $this->_email : '';
	}

	/**
	 * @param string $email
	 */
	public function set_email( string $email )
	{
		$this->_email = sanitize_email( $email );
	}

	/**
	 * @return string
	 */
	public function get_url() : string
	{
		return esc_url( $this->_url );
	}

	/**
	 * @param string $url
	 */
	public function set_url( string $url )
	{
		$this->_url = trim( $url );
	}

	/**
	 * @return array
	 */
	public function to_array()
	{
		$params = [];

		foreach ( [
			'name',
			'email',
			'url',
	  	] as $param ) {
			$value = $this->{"get_$param"}();

			if ( $value ) {
				$params[ $param ] = $value;
			}
		}

		return $params;
	}

	/**
	 * @return array
	 */
	public function get_composer_package_author() : array
	{
		$params = $this->to_array();

		if ( isset( $params['url'] ) ) {
			$params['homepage'] = $params['url'];
			unset( $params['url'] );
		}

		return $params;
	}

	/**
	 * @return array
	 */
	public function get_npm_package_contributor() : array
	{
		return $this->to_array();
	}
}
