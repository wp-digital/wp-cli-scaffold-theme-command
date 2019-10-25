<?php

namespace Innocode\ScaffoldTheme;

/**
 * Class Helpers
 * @package Innocoode\ScaffoldTheme
 */
final class Helpers
{
	/**
	 * @param string $dir
	 * @return bool
	 */
	public static function delete_dir( $dir )
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
	public static function in_array_by_params( array $params, array $arrays, array $array )
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
	 * @param array  $source
	 * @param string $path
	 * @param array  $destination
	 */
	public static function copy_composer_autoload( array $source, $path, array $destination )
	{
		if ( ! isset( $destination['autoload'] ) ) {
			$destination['autoload'] = [];
		}

		foreach ( $source['autoload'] as $standard => $rules ) {
			static::move_composer_autoload_paths( $standard, $rules, $path, $destination['autoload'] );
		}
	}

	/**
	 * @param string $standard
	 * @param array  $rules
	 * @param string $path
	 * @param array  $destination
	 */
	public static function move_composer_autoload_paths( $standard, array $rules, $path, array $destination )
	{
		if ( ! isset( $destination[ $standard ] ) ) {
			$destination[ $standard ] = [];
		}

		switch ( $standard ) {
			case 'psr-0':
			case 'psr-4':
				foreach ( $rules as $namespace => $rule ) {
					$destination[ $standard ][ $namespace ] = array_map( function ( $rule ) use ( $path ) {
						return "$path/$rule";
					}, (array) $rule );

					if ( count( $destination[ $standard ][ $namespace ] ) == 1 ) {
						$destination[ $standard ][ $namespace ] = $destination[ $standard ][ $namespace ][0];
					}
				}

				break;
			case 'classmap':
			case 'files':
			case 'exclude-from-classmap':
				foreach ( $rules as $rule ) {
					$destination[ $standard ][] = "$path/$rule";
				}

				break;
		}
	}
}
