<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.helper
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * This Class Is IjoomeradvHelper
 *
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.controller
 * @since       1.0
 */
class IjoomeradvHelper
{
	private $db;

	private $mainframe;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();
		$this->mainframe = JFactory::getApplication();
	}

	/**
	 * The Function GetEncryption_Config
	 *
	 * @return  it will return $encryption
	 */
	public function getencryption_config()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('value')
			->from($this->db->qn('#__ijoomeradv_config'))
			->where($this->db->qn('name') . ' = ' . $this->db->q('IJOOMER_ENC_REQUIRED'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$encryption = $this->db->loadResult();

		return $encryption;
	}

	/**
	 * The Function GetRequestedObject
	 *
	 * @return  void
	 */
	public function getRequestedObject()
	{
		$encryption = $this->getencryption_config();



		if (JRequest::get('post'))
		{

			if ($encryption == 1)
			{
				require_once IJ_SITE . '/encryption/MCrypt.php';
				$encode = JRequest::getVar('reqObject');
				$RSA = new MCrypt;
				$decoded = $RSA->decrypt($encode);
				$this->mainframe->IJObject->reqObject = json_decode($decoded);
			}
			else
			{

				$this->mainframe->IJObject->reqObject = json_decode(JRequest::getVar('reqObject'));
			}
		}


	}

	/**
	 * The Function GetComponent
	 *
	 * @param   type  $option  $option
	 *
	 * @return  it will returns count($components)
	 */
	public function getComponent($option)
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('extension_id AS id, element AS option, params, enabled')
			->from($this->db->qn('#__extensions'))
			->where($this->db->qn('type') . ' = ' . $this->db->q('component'))
			->where($this->db->qn('element') . ' = ' . $this->db->q($option));

		// Set the query and load the result.
		$this->db->setQuery($query);

		$components = $this->db->loadObject();

		return (count($components) > 0 && $components->enabled == 1);
	}

	/**
	 * The Function GetJomSocialVersion
	 *
	 * @return  boolean it will returns the value in true or false
	 */
	public static function getJomSocialVersion()
	{
		$parser = JFactory::getXMLParser('Simple');
		$xml = JPATH_ADMINISTRATOR . '/components/com_community/community.xml';

		if (file_exists($xml))
		{
			$parser->loadFile($xml);
			$doc = $parser->document;
			$element = $doc->getElementByPath('version');
			$version = $element->data();

			$cv = explode('.', $version);
			$cversion = $cv[0] . $cv[1];

			return $cversion;
		}

		return true;
	}
}

/**
 * Class to get requested data
 *
 * @since  1.0
 */
class IJReq
{
	/**
	 * To get requested task
	 *
	 * @param   [type]  $default  $default
	 *
	 * @return  it will return a value
	 */
	public static function getTask($default = null)
	{
		$mainframe = JFactory::getApplication();


		return (isset($mainframe->IJObject->reqObject->task) && $mainframe->IJObject->reqObject->task) ? $mainframe->IJObject->reqObject->task : $default;
	}

	/**
	 * To get requested view
	 *
	 * @param   string  $default  $default will contain the string
	 *
	 * @return  it will return a value
	 */
	public static function getView($default = 'ijoomeradv')
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->reqObject->view) && $mainframe->IJObject->reqObject->view) ? $mainframe->IJObject->reqObject->view : $default;
	}

	/**
	 * @uses
	 *
	 */
	/**
	 * To Get Requested Extension Name
	 *
	 * @param   [type]  $default  contains the value of default extension
	 *
	 * @return  it will returns the value of mainframe
	 */
	public static function getExtName($default = null)
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->reqObject->extName) && $mainframe->IJObject->reqObject->extName) ? $mainframe->IJObject->reqObject->extName : $default;
	}

	/**
	 * To Get Requested Extension View
	 *
	 * @param   [type]  $default  $default
	 *
	 * @return  it will return a value
	 */
	public static function getExtView($default = null)
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->reqObject->extView) && $mainframe->IJObject->reqObject->extView) ? $mainframe->IJObject->reqObject->extView : $default;
	}

	/**
	 * @uses
	 *
	 */
	/**
	 * To Get Requested Extension Task
	 *
	 * @param   [type]  $default  $default
	 *
	 * @return  it will return a value
	 */
	public static function getExtTask($default = null)
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->reqObject->extTask) && $mainframe->IJObject->reqObject->extTask) ? $mainframe->IJObject->reqObject->extTask : $default;
	}

	/**
	 * To Get Requested Variable
	 *
	 * @param   [type]  $name     Name of The Requested Variable
	 * @param   [type]  $default  $default
	 *
	 * @return  it will return a value
	 */
	public static function getVar($name, $default = null)
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->reqObject->$name) && $mainframe->IJObject->reqObject->$name) ? $mainframe->IJObject->reqObject->$name : $default;
	}

	/**
	 * To Get Requested Task Data
	 *
	 * @param   [type]  $name      Name of The Requested Task Data
	 * @param   [type]  $default   $default
	 * @param   string  $dataType  $datatype
	 *
	 * @return  it will return $data
	 */
	public static function getTaskData($name, $default = null, $dataType = 'str')
	{
		$mainframe = JFactory::getApplication();
		$data = (isset($mainframe->IJObject->reqObject->taskData->$name) && $mainframe->IJObject->reqObject->taskData->$name) ? $mainframe->IJObject->reqObject->taskData->$name : $default;

		switch ($dataType)
		{
			case 'int':
				return intval($data);
				break;

			case 'float':
				return floatval($data);
				break;

			case 'bool':
				if ($default === true or $default === false or strtolower($default) === 'true' or strtolower($default) === 'false')
				{
					return (isset($data) && !empty($data) && strtoupper($data) === "true") ? true : false;
				}
				else
				{
					return (isset($data) && !empty($data) && $data) ? 1 : 0;
				}
				break;

			default:
				return $data;
				break;
		}
	}

	/**
	 * Set Response
	 *
	 * @param   [type]  $code     $code
	 * @param   [type]  $message  $message
	 *
	 * @return  void
	 */
	public static function setResponse($code = null, $message = null)
	{
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->code = intval($code);
		$mainframe->IJObject->response->message = $message;
	}

	/**
	 * @uses to set response code
	 *
	 */
	/**
	 * SetResponseCode
	 *
	 * @param   [type]  $default  $default
	 *
	 * @return void
	 */
	public static function setResponseCode($default = null)
	{
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->code = intval($default);
	}

	/**
	 * To Get Response Code
	 *
	 * @param   [type]  $default  contains the value of default
	 *
	 * @return  it will return a value
	 */
	public static function getResponseCode($default = null)
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->response->code) && $mainframe->IJObject->response->code) ? $mainframe->IJObject->response->code : $default;
	}

	/**
	 * @uses to set response message
	 *
	 */
	/**
	 * Set Response Message
	 *
	 * @param   [type]  $default  $default
	 *
	 * @return void
	 */
	public static function setResponseMessage($default = null)
	{
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->message = $default;
	}

	/**
	 * To Get Response Message
	 *
	 * @param   [type]  $default  contains the value of default
	 *
	 * @return  it will return a value
	 */
	public static function getResponseMessage($default = null)
	{
		$mainframe = JFactory::getApplication();

		return (isset($mainframe->IJObject->response->message) && $mainframe->IJObject->response->message) ? $mainframe->IJObject->response->message : $default;
	}
}

/**
 * The Class For IJPushNot If
 *
 * @since  1.0
 */
class IJPushNotif
{
	/**
	 * To send push notification to iphone device
	 *
	 * @param   [type]  $options  contains the options
	 *
	 * @return  array
	 */
	public static function sendIphonePushNotification($options)
	{
		$server = ($options['live']) ? 'ssl://gateway.push.apple.com:2195' : 'ssl://gateway.sandbox.push.apple.com:2195';

		if($options['live'])
		{
			$keyCertFilePath = JPATH_SITE . '/components/com_ijoomeradv/certificates/pro_certificates.pem';
		}
		else
		{
			$keyCertFilePath = JPATH_SITE . '/components/com_ijoomeradv/certificates/dev_certificates.pem';
		}


		// Construct the notification payload
		$body = array();
		$body['aps'] = $options['aps'];
		$body['aps']['badge'] = (isset($options['aps']['badge']) && !empty($options['aps']['badge'])) ? $options['aps']['badge'] : 1;
		$body['aps']['sound'] = (isset($options['aps']['sound']) && !empty($options['aps']['sound'])) ? $options['aps']['sound'] : 'default';
		$payload = json_encode($body);

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);
		$fp = stream_socket_client($server, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

		if (!$fp)
		{
			// Global mainframe;
			print "Failed to connect " . $error . " " . $errorString;

			return;
		}

		$msg = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $options['device_token'])) . pack("n", strlen($payload)) . $payload;
		fwrite($fp, $msg);
		fclose($fp);
	}

	/*
	 *
	 *
	 * 	$options['registration_ids']	// Indexed Array, Android Registration Id
	 * 	$options['data]['message']	// Notification Text
	 *  $options['data]['type']		// Notification Type
	 * 	$options['data]['badge']	// Badge Count
	 */
	/**
	 * To send push notification to android device
	 *
	 * @param   [type]  $options  Contains The Options
	 *
	 * @return  void
	 */
	public static function sendAndroidPushNotification($options)
	{
		$url = 'https://android.googleapis.com/gcm/send';
		$options['data']['badge'] = (isset($options['data']['badge']) && !empty($options['data']['badge'])) ? $options['data']['badge'] : 1;
		$fields['registration_ids'] = $options['registration_ids'];
		$fields['data'] = $options['data'];

		$headers = array(
			'Authorization: key=' . IJOOMER_PUSH_API_KEY_ANDROID,
			'Content-Type: application/json'
		);

		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);

		if ($result === false)
		{
			die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);
	}
}

/**
 * The Class For IJException
 *
 * @since  1.0
 */
class IJException
{
	/**
	 * To Set Error
	 *
	 * @param   [type]  $file      file
	 * @param   [type]  $line      line
	 * @param   [type]  $class     class
	 * @param   [type]  $method    method
	 * @param   [type]  $function  function
	 *
	 * @return void
	 */
	public static function setErrorInfo($file, $line, $class, $method, $function)
	{
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->errClass = $class;
		$mainframe->IJObject->response->errFile = $file;
		$mainframe->IJObject->response->errFunction = $function;
		$mainframe->IJObject->response->errMethod = $method;
		$mainframe->IJObject->response->errLine = $line;
	}

	/**
	 * GetErrorInfo description]
	 *
	 * @return  $error
	 */
	public static function getErrorInfo()
	{
		$mainframe = JFactory::getApplication();

		$error = new stdClass;
		$error->class = ($mainframe->IJObject->response->errClass) ? $mainframe->IJObject->response->errClass : null;
		$error->file = ($mainframe->IJObject->response->errFile) ? $mainframe->IJObject->response->errFile : null;
		$error->function = ($mainframe->IJObject->response->errFunction) ? $mainframe->IJObject->response->errFunction : null;
		$error->method = ($mainframe->IJObject->response->errMethod) ? $mainframe->IJObject->response->errMethod : null;
		$error->line = ($mainframe->IJObject->response->errLine) ? $mainframe->IJObject->response->errLine : null;

		return $error;
	}

	/**
	 * Add Log To The File
	 *
	 * @return boolean it will return a value in true or false
	 */
	public static function addLog()
	{
		$mainframe = JFactory::getApplication();
		$error = self::getErrorInfo();
		$exception['code'] = IJReq::getResponseCode();
		$exception['message'] = (IJReq::getResponseMessage()) ? IJReq::getResponseMessage() : '-';
		$exception['file'] = str_replace(JPATH_SITE, '', $error->file);
		$exception['line'] = $error->line;
		$exception['class'] = $error->class;
		$exception['method'] = $error->method;
		$exception['function'] = $error->function;

		$json = json_encode($exception);

		$logpath = JPATH_ADMINISTRATOR . '/components/com_ijoomeradv/logs/com_ijoomeradv2.0.log.php';

		// If the file doesn't already exist we need to create it and generate the file header.
		if (!is_file($logpath))
		{
			// Make sure the folder exists in which to create the log file.
			JFolder::create(dirname($logpath));

			// Build the log file header.
			$head = self::generateFileHeader();
		}
		else
		{
			$head = false;
		}

		// Open the file for header writing (append mode).
		if ( $filehandle = fopen($logpath, 'a'))
		{
			if ($head)
			{
				fputs($filehandle, $head);
			}
			else
			{
				$message[] = "\n" . gmdate('Y-m-d H:i:s');
				$message[] = $exception['code'];
				$message[] = $exception['message'];
				$message[] = str_replace(JPATH_SITE, '', $error->file);
				$message[] = $error->line;
				$message[] = $error->class;
				$message[] = $error->method;
				$message[] = $error->function;
				$message[] = json_encode($mainframe->IJObject->reqObject);
				$message[] = $json;
				$fmessage = implode("\t", $message);
				fputs($filehandle, $fmessage);
			}
		}
	}

	/**
	 * Generate FileHeader Description
	 *
	 * @return  return $head
	 */
	protected function generateFileHeader()
	{
		// Initialize variables.
		$head = array();

		// Blank line to prevent information disclose: https://bugs.php.net/bug.php?id=60677
		$head[] = '#';
		$head[] = '#<?php die(\'Forbidden.\'); ?>';
		$head[] = '#Date: ' . gmdate('Y-m-d H:i:s') . ' UTC';
		$head[] = '';

		// Prepare the fields string
		$head[] = '#Fields: Date Time	Response-Code	Message		File	Line	Class	Method		Function 	Request-Object		JSON-Object';
		$head[] = '';

		return implode("\n", $head);
	}
}

/**
 * Copied from class/resize.class.php to remove class folder
 *
 * @since  1.0
 *
 * @return void
 */
class SimpleImage
{
	private $image;

	private $image_type;

	/**
	 * The Load Function
	 *
	 * @param   [type]  $filename  it will contain file name
	 *
	 * @return  void
	 */
	public function load($filename)
	{
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];

		if ($this->image_type == IMAGETYPE_JPEG)
		{
			$this->image = imagecreatefromjpeg($filename);
		}
		elseif ($this->image_type == IMAGETYPE_GIF)
		{
			$this->image = imagecreatefromgif($filename);
		}
		elseif ($this->image_type == IMAGETYPE_PNG)
		{
			$this->image = imagecreatefrompng($filename);
		}
	}

	/**
	 * The Save Function
	 *
	 * @param   [type]   $filename     it will contain the File Name
	 * @param   [type]   $image_type   it will contain the image type
	 * @param   integer  $compression  it will contain the compression
	 * @param   [type]   $permissions  it will contain the permissions
	 *
	 * @return  void
	 */
	public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
	{
		if ($image_type == IMAGETYPE_JPEG)
		{
			imagejpeg($this->image, $filename, $compression);
		}
		elseif ($image_type == IMAGETYPE_GIF)
		{
			imagegif($this->image, $filename);
		}
		elseif ($image_type == IMAGETYPE_PNG)
		{
			imagepng($this->image, $filename);
		}

		if ($permissions != null)
		{
			chmod($filename, $permissions);
		}
	}

	/**
	 * The Output Function
	 *
	 * @param   [type]  $image_type  it will contain image_type
	 *
	 * @return  void
	 */
	public function output($image_type = IMAGETYPE_JPEG)
	{
		if ($image_type == IMAGETYPE_JPEG)
		{
			imagejpeg($this->image);
		}
		elseif ($image_type == IMAGETYPE_GIF)
		{
			imagegif($this->image);
		}
		elseif ($image_type == IMAGETYPE_PNG)
		{
			imagepng($this->image);
		}
	}

	/**
	 * The Get Width Function
	 *
	 * @return  it will return imagesx
	 */
	public function getWidth()
	{
		return imagesx($this->image);
	}

	/**
	 * The Get Height Function
	 *
	 * @return  it will return imagesy
	 */
	public function getHeight()
	{
		return imagesy($this->image);
	}

	/**
	 * The Resize To Height Function
	 *
	 * @param   [type]  $height  it contains height
	 *
	 * @return  void
	 */
	public function resizeToHeight($height)
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width, $height);
	}

	/**
	 * The Resize To Width Function
	 *
	 * @param   [type]  $width  it contains the width
	 *
	 * @return  void
	 */
	public function resizeToWidth($width)
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width, $height);
	}

	/**
	 * The Scale Function
	 *
	 * @param   [type]  $scale  it will contain scale
	 *
	 * @return  void
	 */
	public function scale($scale)
	{
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getheight() * $scale / 100;
		$this->resize($width, $height);
	}

	/**
	 * The Resize Function
	 *
	 * @param   [type]  $width   contains the value of width
	 * @param   [type]  $height  contains the value of height
	 *
	 * @return  void
	 */
	public function resize($width, $height)
	{
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}
}

/*
 * copied from class.image-resize.php
 */

/**
 * The Class For The Img_Option
 *
 * @since  1.0
 */
class Img_Opt
{
	private $max_width;

	private $max_height;

	private $path;

	private $img;

	private $new_width;

	private $new_height;

	private $mime;

	private $image;

	private $width;

	private $height;

	/**
	 * The Max Width Function
	 *
	 * @param   [type]  $width  contains the width
	 *
	 * @return  void
	 */
	public function max_width($width)
	{
		$this->max_width = $width;
	}

	/**
	 * The Max Height Function
	 *
	 * @param   [type]  $height  contains the height
	 *
	 * @return  void
	 */
	public function max_height($height)
	{
		$this->max_height = $height;
	}

	/**
	 * The Image Path Function
	 *
	 * @param   [type]  $path  contains the path
	 *
	 * @return  void
	 */
	public function image_path($path)
	{
		$this->path = $path;
	}

	/**
	 * The Get Mime Function
	 *
	 * @return  void
	 */
	public function get_mime()
	{
		$img_data = getimagesize($this->path);
		$this->mime = $img_data['mime'];
	}

	/**
	 * The Create_Image Function
	 *
	 * @return  void
	 */
	public function create_image()
	{
		switch ($this->mime)
		{
			case 'image/jpeg':
				$this->image = imagecreatefromjpeg($this->path);
				break;

			case 'image/gif':
				$this->image = imagecreatefromgif($this->path);
				break;

			case 'image/png':
				$this->image = imagecreatefrompng($this->path);
				break;
		}
	}

	/**
	 * The Function Image_Resize
	 *
	 * @return  void
	 */
	public function image_resize()
	{
		set_time_limit(120);
		$this->get_mime();
		$this->create_image();
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
		$this->set_dimension();
		$image_resized = imagecreatetruecolor($this->new_width, $this->new_height);
		imagecopyresampled($image_resized, $this->image, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->width, $this->height);
		imagejpeg($image_resized, $this->path);
	}

	/**
	 * FUNCTION FOR RESETTING DEMENSIONS OF IMAGE
	 *
	 * @return void
	 */
	public function set_dimension()
	{
		if ($this->width == $this->height)
		{
			$case = 'first';
		}
		elseif ($this->width > $this->height)
		{
			$case = 'second';
		}
		else
		{
			$case = 'third';
		}

		if ($this->width > $this->max_width && $this->height > $this->max_height)
		{
			$cond = 'first';
		}
		elseif ($this->width > $this->max_width && $this->height <= $this->max_height)
		{
			$cond = 'first';
		}
		else
		{
			$cond = 'third';
		}

		switch ($case)
		{
			case 'first':
				$this->new_width = $this->max_width;
				$this->new_height = $this->max_height;
				break;

			case 'second':
				$ratio = $this->width / $this->height;
				$amount = $this->width - $this->max_width;
				$this->new_width = $this->width - $amount;
				$this->new_height = $this->height - ($amount / $ratio);
				break;

			case 'third':
				$ratio = $this->height / $this->width;
				$amount = $this->height - $this->max_height;
				$this->new_height = $this->height - $amount;
				$this->new_width = $this->width - ($amount / $ratio);
				break;
		}
	}
}

/**
 * The Class For IJoomeradv Error
 *
 * @since  1.0
 */
class IjoomeradvError
{
	/**
	 * IjErrorHandler Function
	 *
	 * @param   [type]  $errno    contains error number
	 * @param   [type]  $errstr   contains error string
	 * @param   [type]  $errfile  contains error file
	 * @param   [type]  $errline  contains error line
	 *
	 * @return  boolean it will return a value in true or false
	 */
	public function ijErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno))
		{
			return;
		}

		switch ($errno)
		{
			case E_USER_ERROR:
				$_SESSION['ijoomeradv_error'][] = "<b>ERROR</b> [$errno] $errstr in $errfile on line $errline";
				break;

			case E_USER_WARNING:
				$_SESSION['ijoomeradv_error'][] = "<b>WARNING</b> [$errno] $errstr in $errfile on line $errline";
				break;

			case E_USER_NOTICE:
				$_SESSION['ijoomeradv_error'][] = "<b>NOTICE</b> [$errno] $errstr in $errfile on line $errline";
				break;

			case E_ERROR:
				$_SESSION['ijoomeradv_error'][] = "<b>ERROR</b> [$errno] $errstr in $errfile on line $errline";
				break;

			case E_WARNING:
				$_SESSION['ijoomeradv_error'][] = "<b>WARNING</b> [$errno] $errstr in $errfile on line $errline";
				break;

			case E_NOTICE:
				$_SESSION['ijoomeradv_error'][] = "<b>NOTICE</b> [$errno] $errstr in $errfile on line $errline";
				break;

			case E_PARSE:
				$_SESSION['ijoomeradv_error'][] = "<b>PARSE</b> [$errno] $errstr in $errfile on line $errline";
				break;

			default:
				$_SESSION['ijoomeradv_error'][] = "Unknown error type: [$errno] $errstr in $errfile on line $errline";
				break;
		}

		return true;
	}
}
