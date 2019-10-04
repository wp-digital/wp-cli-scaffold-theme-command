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
	protected $_archive;

	/**
	 * @return string
	 */
	public function get_archive() : string
	{
		if ( ! is_null( $this->_archive ) ) {
			return $this->_archive;
		}

		$this->_archive = '';

		if ( defined( 'INNOCODE_SCAFFOLD_THEME_SOURCE_URL' ) ) {
			$this->_archive = file_get_contents( INNOCODE_SCAFFOLD_THEME_SOURCE_URL );
		}

		return $this->_archive;
	}
}
