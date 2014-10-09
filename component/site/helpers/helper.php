<?php
 /*--------------------------------------------------------------------------------
# com_ijoomeradv_1.5 - iJoomer Advanced
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * @uses this class is the base ijoomer helper class
 *
 */
class ijoomeradvHelper {

	private $db;
	private $mainframe;

	function __construct(){
		$this->db= & JFactory::getDBO();
		$this->mainframe = JFactory::getApplication();
	}

	public function getencryption_config(){
		$query="SELECT `value`
				FROM #__ijoomeradv_config
				WHERE `name`='IJOOMER_ENC_REQUIRED' ";
		$this->db->setQuery($query);
		$encryption = $this->db->loadResult();
		return $encryption;
	}


	public function getRequestedObject(){
		$encryption = $this->getencryption_config();

		if(JRequest::get('post')){
			if($encryption == 1){
				require_once IJ_SITE.'/encryption/MCrypt.php';
				$encode = JRequest::getVar('reqObject');
				$RSA = new MCrypt();
				$decoded =  $RSA->decrypt($encode);
				$this->mainframe->IJObject->reqObject=json_decode($decoded);
			}else{
				$this->mainframe->IJObject->reqObject=json_decode(JRequest::getVar('reqObject'));
			}
		}
	}

	public function getComponent($option){
		$query="SELECT `extension_id` AS `id`, `element` AS `option`, `params`, `enabled`
				FROM #__extensions
				WHERE `type`='component'
				AND `element`='{$option}'";
		$this->db->setQuery($query);
		$components = $this->db->loadObject();
		return (count($components)>0 && $components->enabled == 1);
	}

	public static function getJomSocialVersion(){
		$parser = JFactory::getXMLParser('Simple');
		$xml	= JPATH_ADMINISTRATOR . '/components/com_community/community.xml';

		if(file_exists($xml)){
			$parser->loadFile( $xml );
			$doc		=& $parser->document;
			$element	=& $doc->getElementByPath( 'version' );
			$version	= $element->data();

			$cv = explode('.',$version);
			$cversion = $cv[0].$cv[1];
			return $cversion;
		}
		return true;
	}
}

/**
 * @uses Class to get requested data
 *
 */
class IJReq{
	/**
	 * @uses to get requested task
	 *
	 */
	public static function getTask($default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->reqObject->task) && $mainframe->IJObject->reqObject->task) ? $mainframe->IJObject->reqObject->task : $default;
	}

	/**
	 * @uses to get requested view
	 *
	 */
	public static function getView($default='ijoomeradv'){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->reqObject->view) && $mainframe->IJObject->reqObject->view) ? $mainframe->IJObject->reqObject->view : $default;
	}

	/**
	 * @uses to get requested extension name
	 *
	 */
	public static function getExtName($default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->reqObject->extName) && $mainframe->IJObject->reqObject->extName) ? $mainframe->IJObject->reqObject->extName : $default;
	}

	/**
	 * @uses to get requested extension view
	 *
	 */
	public static function getExtView($default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->reqObject->extView) && $mainframe->IJObject->reqObject->extView) ? $mainframe->IJObject->reqObject->extView : $default;
	}

	/**
	 * @uses to get requested extension task
	 *
	 */
	public static function getExtTask($default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->reqObject->extTask) && $mainframe->IJObject->reqObject->extTask) ? $mainframe->IJObject->reqObject->extTask : $default;
	}

	/**
	 * @uses to get requested variable
	 *
	 */
	public static function getVar($name,$default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->reqObject->$name) && $mainframe->IJObject->reqObject->$name) ? $mainframe->IJObject->reqObject->$name : $default;
	}

	/**
	 * @uses to get requested task data
	 * @param $name : name of the requested task data
	 * @param $dafault
	 *
	 */
	public static function getTaskData($name,$default=NULL,$dataType='str'){
		$mainframe = JFactory::getApplication();
		$data=(isset($mainframe->IJObject->reqObject->taskData->$name) && $mainframe->IJObject->reqObject->taskData->$name) ? $mainframe->IJObject->reqObject->taskData->$name : $default;
		switch($dataType){
			case 'int':
				return intval($data);
				break;

			case 'float':
				return floatval($data);
				break;

			case 'bool':
				if($default===TRUE or $default===FALSE or strtolower($default)==='true' or strtolower($default)==='false'){
					return (isset($data) && !empty($data) && strtoupper($data)==="TRUE") ? TRUE : FALSE ;
				}else{
					return (isset($data) && !empty($data) && $data) ? 1 : 0 ;
				}
				break;

			default:
				return $data;
				break;
		}
	}

	public static function setResponse($code=NULL,$message=NULL){
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->code	=intval($code);
		$mainframe->IJObject->response->message	=$message;
	}

	/**
	 * @uses to set response code
	 *
	 */
	public static function setResponseCode($default=NULL){
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->code			= intval($default);
	}

	/**
	 * @uses to get response code
	 *
	 */
	public static function getResponseCode($default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->response->code) && $mainframe->IJObject->response->code) ? $mainframe->IJObject->response->code : $default;
	}

	/**
	 * @uses to set response message
	 *
	 */
	public static function setResponseMessage($default=NULL){
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->message=$default;
	}

	/**
	 * @uses to get response message
	 *
	 */
	public static function getResponseMessage($default=NULL){
		$mainframe = JFactory::getApplication();
		return (isset($mainframe->IJObject->response->message) && $mainframe->IJObject->response->message) ? $mainframe->IJObject->response->message : $default;
	}
}



class IJPushNotif{

	/*
	 * To send push notification to iphone device
	 *
	 * 	$options=array();
	 * 	$options['device_token']	// iPhone Device Token
	 * 	$options['live']			// boolean value 0: Sendbox, 1: Live
	 * 	$options['aps']['message']	// Notification Text
	 * 	$options['aps']['type']		// Notification Type
	 * 	$options['aps']['badge']	// Badge count
	 * 	$options['aps']['sound']	// Notification Sound default value is 'default'
	 *
	 */
	public static function sendIphonePushNotification($options){
		$server=($options['live']) ? 'ssl://gateway.push.apple.com:2195' : 'ssl://gateway.sandbox.push.apple.com:2195';
		$keyCertFilePath = JPATH_SITE.'/components/com_ijoomeradv/certificates/certificates.pem';
		// Construct the notification payload
		$body = array();
		$body['aps']= $options['aps'];
		$body['aps']['badge']=(isset($options['aps']['badge']) && !empty($options['aps']['badge'])) ? $options['aps']['badge'] : 1;
		$body['aps']['sound']=(isset($options['aps']['sound']) && !empty($options['aps']['sound'])) ? $options['aps']['sound'] : 'default';
		$payload = json_encode($body);

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);
		$fp = stream_socket_client($server, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

		if (!$fp){
			//global mainframe;
			print "Failed to connect ".$error." ".$errorString;
			return;
		}

		$msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $options['device_token'])) . pack("n",strlen($payload)) . $payload;
		fwrite($fp, $msg);
		fclose($fp);
	}


	/*
	 * To send push notification to android device
	 *
	 * 	$options['registration_ids']	// Indexed Array, Android Registration Id
	 * 	$options['data]['message']	// Notification Text
	 *  $options['data]['type']		// Notification Type
	 * 	$options['data]['badge']	// Badge Count
	 */
	public static function sendAndroidPushNotification($options){
		$url = 'https://android.googleapis.com/gcm/send';
		$options['data']['badge']=(isset($options['data']['badge']) && !empty($options['data']['badge'])) ? $options['data']['badge'] : 1;
		$fields['registration_ids']=$options['registration_ids'];
		$fields['data']=$options['data'];

		$headers = array(
            'Authorization: key='.IJOOMER_PUSH_API_KEY_ANDROID ,
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
        if ($result === FALSE) {
        	die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
	}
}

class IJException{
	/**
	 * @uses to set error
	 *
	 */
	public static function setErrorInfo($file,$line,$class,$method,$function){
		$mainframe = JFactory::getApplication();
		$mainframe->IJObject->response->errClass		= $class;
		$mainframe->IJObject->response->errFile			= $file;
		$mainframe->IJObject->response->errFunction		= $function;
		$mainframe->IJObject->response->errMethod		= $method;
		$mainframe->IJObject->response->errLine			= $line;
	}

	/**
	 * @uses to get response message
	 *
	 */
	public static function getErrorInfo(){
		$mainframe = JFactory::getApplication();

		$error = new stdClass();
		$error->class		= ($mainframe->IJObject->response->errClass) ? $mainframe->IJObject->response->errClass : NULL;
		$error->file		= ($mainframe->IJObject->response->errFile) ? $mainframe->IJObject->response->errFile : NULL;
		$error->function	= ($mainframe->IJObject->response->errFunction) ? $mainframe->IJObject->response->errFunction : NULL;
		$error->method		= ($mainframe->IJObject->response->errMethod) ? $mainframe->IJObject->response->errMethod : NULL;
		$error->line		= ($mainframe->IJObject->response->errLine) ? $mainframe->IJObject->response->errLine : NULL;

		return $error;
	}

	/**
	 * @uses to add log to the file
	 *
	 */
	public static function addLog(){
		$mainframe = JFactory::getApplication();
		$error = self::getErrorInfo();
		$exception['code']			= IJReq::getResponseCode();
		$exception['message']		= (IJReq::getResponseMessage()) ? IJReq::getResponseMessage() : '-';
		$exception['file']			= str_replace(JPATH_SITE,'',$error->file);
		$exception['line']			= $error->line;
		$exception['class']			= $error->class;
		$exception['method']		= $error->method;
		$exception['function']		= $error->function;

		$json = json_encode($exception);

		$logpath = JPATH_ADMINISTRATOR.'/components/com_ijoomeradv/logs/com_ijoomeradv2.0.log.php';

		// If the file doesn't already exist we need to create it and generate the file header.
		if (!is_file($logpath)){
			// Make sure the folder exists in which to create the log file.
			JFolder::create(dirname($logpath));
			// Build the log file header.
			$head = self::generateFileHeader();
		}else{
			$head = false;
		}

		// Open the file for header writing (append mode).
		if ($filehandle = fopen($logpath, 'a')){
			if ($head){
				fputs($filehandle, $head);
			}else{
				$message[] = "\n".gmdate('Y-m-d H:i:s');
				$message[] = $exception['code'];
				$message[] = $exception['message'];
				$message[] = str_replace(JPATH_SITE,'',$error->file);
				$message[] = $error->line;
				$message[] = $error->class;
				$message[] = $error->method;
				$message[] = $error->function;
				$message[] = json_encode($mainframe->IJObject->reqObject);
				$message[] = $json;
				$fmessage=implode("\t", $message);
				fputs($filehandle,$fmessage);
			}
		}

	}

	protected function generateFileHeader(){
		// Initialize variables.
		$head = array();

		// blank line to prevent information disclose: https://bugs.php.net/bug.php?id=60677
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


/*
 * Copied from class/resize.class.php to remove class folder
 */
class SimpleImage {
	private $image;
	private $image_type;

	public function load($filename) {
		$image_info = getimagesize($filename);
      	$this->image_type = $image_info[2];
      	if($this->image_type == IMAGETYPE_JPEG ) {
         	$this->image = imagecreatefromjpeg($filename);
      	}else if($this->image_type == IMAGETYPE_GIF ) {
         	$this->image = imagecreatefromgif($filename);
      	}else if( $this->image_type == IMAGETYPE_PNG ) {
         	$this->image = imagecreatefrompng($filename);
      	}
   	}

   	public function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      	if( $image_type == IMAGETYPE_JPEG ) {
         	imagejpeg($this->image,$filename,$compression);
      	}else if( $image_type == IMAGETYPE_GIF ) {
         	imagegif($this->image,$filename);
      	}else if( $image_type == IMAGETYPE_PNG ) {
         	imagepng($this->image,$filename);
      	}

      	if( $permissions != null) {
         	chmod($filename,$permissions);
      	}
   	}

   	public function output($image_type=IMAGETYPE_JPEG) {
      	if( $image_type == IMAGETYPE_JPEG ) {
         	imagejpeg($this->image);
      	}else if( $image_type == IMAGETYPE_GIF ) {
         	imagegif($this->image);
      	}else if( $image_type == IMAGETYPE_PNG ) {
         	imagepng($this->image);
      	}
   	}

   	public function getWidth() {
      	return imagesx($this->image);
   	}

   	public function getHeight() {
      	return imagesy($this->image);
   	}

   	public function resizeToHeight($height) {
      	$ratio = $height / $this->getHeight();
      	$width = $this->getWidth() * $ratio;
      	$this->resize($width,$height);
   	}

   	public function resizeToWidth($width) {
      	$ratio = $width / $this->getWidth();
      	$height = $this->getheight() * $ratio;
      	$this->resize($width,$height);
   	}

   	public function scale($scale) {
      	$width = $this->getWidth() * $scale/100;
      	$height = $this->getheight() * $scale/100;
      	$this->resize($width,$height);
   	}

   	public function resize($width,$height) {
      	$new_image = imagecreatetruecolor($width, $height);
      	imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
     	$this->image = $new_image;
   	}
}

/*
 * copied from class.image-resize.php
 */
class img_opt{
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

	public function max_width($width){
		$this->max_width = $width;
	}

	public function max_height($height){
		$this->max_height = $height;
	}

	public function image_path($path){
		$this->path = $path;
	}

	public function get_mime(){
		$img_data = getimagesize($this->path);
		$this->mime = $img_data['mime'];
	}

	public function create_image(){
		switch($this->mime){
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

	public function image_resize(){
		set_time_limit(120);
		$this->get_mime();
		$this->create_image();
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
		$this->set_dimension();
		$image_resized = imagecreatetruecolor($this->new_width,$this->new_height);
		imagecopyresampled($image_resized, $this->image, 0, 0, 0, 0, $this->new_width, $this->new_height,$this->width, $this->height);
		imagejpeg($image_resized,$this->path);
	}

	//######### FUNCTION FOR RESETTING DEMENSIONS OF IMAGE ###########
	public function set_dimension(){
		if($this->width==$this->height){
			$case = 'first';
		}else if($this->width > $this->height){
			$case = 'second';
		}else{
			$case = 'third';
		}

		if($this->width>$this->max_width && $this->height>$this->max_height){
			$cond = 'first';
		}else if($this->width>$this->max_width && $this->height<=$this->max_height){
			$cond = 'first';
		}else{
			$cond = 'third';
		}

		switch($case){
			case 'first':
				$this->new_width = $this->max_width;
				$this->new_height = $this->max_height;
			break;

			case 'second':
				$ratio = $this->width/$this->height;
				$amount = $this->width - $this->max_width;
				$this->new_width = $this->width - $amount;
				$this->new_height = $this->height - ($amount/$ratio);
			break;

			case 'third':
				$ratio = $this->height/$this->width;
				$amount = $this->height - $this->max_height;
				$this->new_height = $this->height - $amount;
				$this->new_width = $this->width - ($amount/$ratio);
			break;
		}
	}
}
class ijoomeradvError{
	function ijErrorHandler($errno, $errstr, $errfile, $errline )
	{
		if (!(error_reporting() & $errno)) {
	        return;
	    }

	    switch ($errno) {
		    case E_USER_ERROR:
		        $_SESSION['ijoomeradv_error'][]="<b>ERROR</b> [$errno] $errstr in $errfile on line $errline";
		        break;

		    case E_USER_WARNING:
		        $_SESSION['ijoomeradv_error'][]="<b>WARNING</b> [$errno] $errstr in $errfile on line $errline";
		        break;

		    case E_USER_NOTICE:
		        $_SESSION['ijoomeradv_error'][]="<b>NOTICE</b> [$errno] $errstr in $errfile on line $errline";
		        break;

			case E_ERROR:
		        $_SESSION['ijoomeradv_error'][]="<b>ERROR</b> [$errno] $errstr in $errfile on line $errline";
		        break;

		    case E_WARNING:
		        $_SESSION['ijoomeradv_error'][]="<b>WARNING</b> [$errno] $errstr in $errfile on line $errline";
		        break;

		    case E_NOTICE:
		        $_SESSION['ijoomeradv_error'][]="<b>NOTICE</b> [$errno] $errstr in $errfile on line $errline";
		        break;

		    case E_PARSE:
				$_SESSION['ijoomeradv_error'][]="<b>PARSE</b> [$errno] $errstr in $errfile on line $errline";
		        break;

		    default:
		        $_SESSION['ijoomeradv_error'][]="Unknown error type: [$errno] $errstr in $errfile on line $errline";
		        break;
	    }
	    return true;
	}
}