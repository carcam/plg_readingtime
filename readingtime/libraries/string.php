<?php
/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Substitute function for mb_str_word_count
 *
 * @param	$string		String with words to count
 * @param	$format		Format of the string
 * @param	$charlist	Charlist
 *
 * @return	int	Number of words
 */
function mb_str_word_count($string, $format = 0, $charlist = '[]')
{
	$string = trim($string);

	if (empty($string))
	{
		$words = array();
	}
	else
	{
		$words = preg_split('~[^\p{L}\p{N}\']+~u', $string);
	}

	switch ($format)
	{
		case 0:
			return count($words);
			break;
		case 1:
		case 2:
			return $words;
			break;
		default:
			return $words;
			break;
	}
}
