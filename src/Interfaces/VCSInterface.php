<?php

namespace Innocode\ScaffoldTheme\Interfaces;

use Innocode\ScaffoldTheme\Packager;

/**
 * Interface VCSInterface
 * @package Innocode\ScaffoldTheme\Interfaces
 */
interface VCSInterface
{
	public function get_client();

	public function create_packager() : Packager;
}
