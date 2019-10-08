<?php

namespace Innocode\ScaffoldTheme\Sources;

use Innocode\ScaffoldTheme\Interfaces\SourceInterface;

/**
 * Class ZipSource
 * @package Innocode\ScaffoldTheme\Sources
 */
class ZipSource implements SourceInterface
{
	/**
	 * @var string
	 */
	protected $_url;
	/**
	 * @var string
	 */
	protected $_archive;

	/**
	 * ZipSource constructor.
	 * @param string $url
	 */
	public function __construct( string $url )
	{
		$this->_url = $url;
	}

	/**
	 * @return string
	 */
	public function get_url() : string
	{
		return $this->_url ? esc_url( $this->_url ) : '';
	}

	/**
	 * @return string
	 */
	public function get_archive() : string
	{
		if ( ! is_null( $this->_archive ) ) {
			return $this->_archive;
		}

		$this->_archive = file_get_contents( $this->get_url() );

		return $this->_archive;
	}
}
