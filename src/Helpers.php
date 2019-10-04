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
	 * Gets the environment's HOME directory if available.
	 *
	 * @return null|string
	 */
	public static function get_home_dir()
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
