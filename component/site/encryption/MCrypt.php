<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.encryption
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class MCrypt
 *
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */

class MCrypt
{
	// Same as in JAVA
	private $iv = 'fedcba9876543210';

	// Same as in JAVA
	private $key = '0123456789abcdef';

	/**
	 * Constructtor
	 */
	function __construct()
	{
		$this->_db = JFactory::getDBO();
	}

	/**
	 * The hex2bin Function
	 *
	 * @param   [type]  $hexdata  [description]
	 *
	 * @return  it will return $bindata
	 */
	protected function hex2bin($hexdata)
	{
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2)
		{
			$bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
	}

	/**
	 * The Encrtyption Function
	 *
	 * @param   [type]  $input  [description]
	 *
	 * @return  it will return $data
	 */
	function encrypt($input)
	{
		$query = "SELECT `value` FROM #__ijoomeradv_config WHERE `name`='IJOOMER_ENC_KEY' ";
		$this->_db->setQuery($query);
		$key = $this->_db->loadResult();

		$size = mcrypt_get_block_size('rijndael-128', 'cbc');
		$input = $this->pkcs5_pad($input, $size);
		$iv = '0000000000000000';
		$td = mcrypt_module_open('rijndael-128', '', 'cbc', '');

		// $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);

		return $data;
	}

	/**
	 * The Decrypt Function
	 *
	 * @param   [type]  $code  $code
	 *
	 * @return  void
	 */
	function decrypt($code)
	{
		$query = "SELECT `value` FROM #__ijoomeradv_config WHERE `name`='IJOOMER_ENC_KEY' ";
		$this->_db->setQuery($query);
		$key = $this->_db->loadResult();

		$code = base64_decode($code);
		$iv = '0000000000000000';
		$td = mcrypt_module_open('rijndael-128', '', 'cbc', '');

		mcrypt_generic_init($td, $key, $iv);
		$decrypted = mdecrypt_generic($td, $code);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$decrypted = $this->pkcs5_unpad($decrypted);

		return $decrypted;
	}

	/**
	 * The Pkcs5_pad Function
	 *
	 * @param   [type]  $text       [description]
	 * @param   [type]  $blocksize  [description]
	 *
	 * @return  returns the $text value
	 */
	function pkcs5_pad($text, $blocksize)
	{
		$pad = $blocksize - (strlen($text) % $blocksize);

		return $text . str_repeat(chr($pad), $pad);
	}

	/**
	 * The Function Pkcs5_unpad
	 *
	 * @param   [type]  $text  [description]
	 *
	 * @return  returns the substr
	 */
	function pkcs5_unpad($text)
	{
		$pad = ord($text{strlen($text) - 1});

		if ( $pad > strlen($text)) return false;

		if ( strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;

		return substr($text, 0, -1 * $pad);
	}
}
