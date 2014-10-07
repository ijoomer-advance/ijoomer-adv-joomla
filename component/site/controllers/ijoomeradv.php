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

class ijoomeradvControllerijoomeradv exten'/' JControllerLegacy{

	private $mainframe;
	private $session_pass=0;
	private $IJUserID=null;

	function __construct( $default = array()){
		$this->mainframe = JFactory::getApplication();
		parent::__construct( $default );
		$this->defineApplicationConfig();
	}

	/**
	 * @uses defines ijoomeradv application configuration
	 *
	 */
	private function defineApplicationConfig(){
		$model = $this->getModel('ijoomeradv');
		$result = $model->getApplicationConfig(); // get application config
		foreach($result as $value){
			defined($value->name) or define($value->name,$value->value);
		}
	}

	/**
	 * @uses defines extension configuration
	 *
	 */
	private function defineExtensionConfig($extName){
		$model = $this->getModel('ijoomeradv');
		$result = $model->getExtensionConfig($extName); // get extension config
		foreach($result as $value){
			defined($value->name) or define($value->name,$value->value);
		}
	}

	/**
	 * @uses Generates and displays JSON output with JSON mime type
	 *
	 */
	private function outputJSON($jsonarray){
		//set all warning/notice in json response
		$jsonarray['php_server_error'] = ($_SESSION['ijoomeradv_error'])?$_SESSION['ijoomeradv_error']:'';
		unset($_SESSION['ijoomeradv_error']);

		header ("content-type: application/json"); // set the header content type to JSON format
		require_once IJ_HELPER . '/helper.php'; // import ijoomeradv helper file
		$IJHelperObj= new ijoomeradvHelper(); // create hepler object
		$encryption = $IJHelperObj->getencryption_config();
		if($encryption == 1){
			$json = json_encode($jsonarray);// output the JSON encoded string
			// add  code for replace back slases to forward slases.
			$json = str_replace('\\\\','/',$json);
			require_once IJ_SITE.'/encryption/MCrypt.php';
			$RSA = new MCrypt();
			$encoded =  $RSA->encrypt($json);
			echo $encoded; exit;
		}else{
			echo json_encode(str_replace("\\\\","/",$jsonarray));
			if(!empty($jsonarray['pushNotificationData'])){
				$db = JFactory::getDBO();

				$memberlist = $jsonarray['pushNotificationData']['to'];
				if($memberlist)
				{
					$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
							FROM #__ijoomeradv_users
							WHERE `userid` IN ({$memberlist})";
					$db->setQuery($query);
					$puserlist=$db->loadObjectList();

					foreach ($puserlist as $puser){
						//check config allow for jomsocial
						if(!empty($jsonarray['pushNotificationData']['configtype']) and $jsonarray['pushNotificationData']['configtype']!=''){
							$ijparams = json_decode($puser->jomsocial_params);
							$configallow = $jsonarray['pushNotificationData']['configtype'];
						}else{
							$configallow = 1;
						}

						if($configallow && $puser->userid!=$this->IJUserID && !empty($puser)){
							if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){

								$options=array();
								$options['device_token']	= $puser->device_token;
								$options['live']			= intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
								$options['aps']['alert']	= strip_tags($jsonarray['pushNotificationData']['message']);
								$options['aps']['type']		= $jsonarray['pushNotificationData']['type'];
								$options['aps']['id']		= ($jsonarray['pushNotificationData']['id']!=0)?$jsonarray['pushNotificationData']['id']:$jsonarray['pushNotificationData']['multiid'][$puser->userid];
								IJPushNotif::sendIphonePushNotification($options);
							}

							if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
								$options=array();
								$options['registration_i'/'']	= array($puser->device_token);
								$options['data']['message']		= strip_tags($jsonarray['pushNotificationData']['message']);
								$options['data']['type']		= $jsonarray['pushNotificationData']['type'];
								$options['data']['id']			= ($jsonarray['pushNotificationData']['id']!=0)?$jsonarray['pushNotificationData']['id']:$jsonarray['pushNotificationData']['multiid'][$puser->userid];
								IJPushNotif::sendAndroidPushNotification($options);
							}
						}
					}
				}
				unset($jsonarray['pushNotificationData']);
			}
			exit; // output the JSON encoded string
		}
	}

	/**
	 * @uses this function is used to check session
	 *
	 */
	private function checkSession($whiteListTask){
		$extTask=IJReq::getExtTask(); // get requested extension task (function inside view file)
		$extView=IJReq::getExtView(); // get requested extension view (file name of extension)
		$my =& JFactory::getUser();

		if($my->id>0){
			$this->session_pass = 1;
			$this->IJUserID = $my->id;
			$this->mainframe->setUserState('com_ijoomeradv.IJUserID',$my->id);
			$_SESSION['IJUserID'] = $my->id;
		}else{
			$this->session_pass = 0;
			$this->IJUserID = null;
			$this->mainframe->setUserState('com_ijoomeradv.IJUserID', null);
			unset($_SESSION['IJUserID']);
		}
		if((IJOOMER_GC_LOGIN_REQUIRED && $this->session_pass == 1) || (in_array($extView..$extTask,$whiteListTask)) || !IJOOMER_GC_LOGIN_REQUIRED){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @uses this function will be used to any other request which is part of the installed extension
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"ping"
	 * 	}
	 *
	 */
	function ping(){
		$model = $this->getModel('ijoomeradv');
		$results = $model->getExtensions();
		if(count($results)>0){
			$jsonarray['code']=200;
		}else{
			$jsonarray['code']=204;
			$this->outputJSON($jsonarray);
		}
		foreach ($results as $result){
			$jsonarray['extensions'][]=$result->name;
		}
		$this->outputJSON($jsonarray);
	}

	/**
	 * @uses this function will be used to get url params from url to send data in pushnotification from admin side by passing url
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"getUrlContent",
	 * 		"taskData":{
	 * 			"url":""//url
	 * 		}
	 * 	}
	 *
	 */
	function getUrlContent(){
		$url = IJReq::getTaskData('url');
		$options['mode']=1;
		$router = JApplication::getRouter('site',$options);
		$results = $router->parse(JURI::getInstance($url));

		//define('JROUTER_MODE_SEF',1);
		$model = $this->getModel('ijoomeradv');
		$extensions = $model->getExtensions();
		$isExtAvail = false;
		foreach($extensions as $extension){
			if($extension->option==$results['option']){
				$isExtAvail=true;
				break;
			}
		}

		//set url as external weblink if component not found
		if(!$isExtAvail){
			$jsonarray['itemview'] = 'Web';
			$jsonarray['url'] = $url;
		}

		switch ($results['option']){
			case 'com_content':
				require_once JPATH_COMPONENT . '/extensions/icms/helper.php';
				$helperClass = new icms_helper();
				$urlResults  = $helperClass->getParseData($results);
			break;
		}

		if(!empty($urlResults)){
			$jsonarray=$urlResults;
		}else{
			$jsonarray['itemview'] = 'Web';
			$jsonarray['url'] = $url;
		}

		$this->outputJSON($jsonarray);
	}

	/**
	 * @uses this function will be used to any other request which is part of the installed extension
	 * @example the json string will be like, :
	 * 	{
	 * 		"taskData":{
	 * 			"extName":"jomsocial",
	 * 			"extView":"user",
	 * 			"extTask":"userDetail",
	 * 			"taskData":{
	 * 			}
	 * 		}
	 * 	}
	 *
	 */
	function display(){
		$model = $this->getModel('ijoomeradv'); // get ijoomeradv model object

		$menuid=IJReq::getTaskData('menuId',''); // get requested extension task (function inside view file)
		//Set request variable from manu
		if(!empty($menuid)){
			$model->setMenuRequest($menuid);
		}

		$extName=IJReq::getExtName(); // get requested extension name
		$extView=IJReq::getExtView(); // get requested extension view (file name of extension)
		$extTask=IJReq::getExtTask(); // get requested extension task (function inside view file)

		$jsonarray=array();
		if(!$model->checkIJExtension($extName)){ // check ijoomeradv extension and related component status from extension name passed in the request
			$jsonarray['code']=IJReq::getResponseCode();
			$jsonarray['message']=IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}

		$extensionmain = IJ_EXTENSION . '/' . $extName . '/' . $extName . ".php"; // main existance file
		$extensionview = IJ_EXTENSION . '/' . $extName . '/' . $extView .".php"; // extension view file
		if(!file_exists($extensionview) or !file_exists($extensionmain)){
			$jsonarray['code']=404;
			$jsonarray['message']=NULL; //'Extension File Not Found.';
			$this->outputJSON($jsonarray);
		}

		$this->defineExtensionConfig($extName); // define extension configuration so it can be directly used

		include_once $extensionmain; // include main extension file
		$extMainObj = new $extName(); // create main extension class object

		if(!$this->checkSession($extMainObj->sessionWhiteList)){ // checkSession checks the session sent in task data and if session found it will all needed data.
			$jsonarray['code']=704;
			$jsonarray['message']=NULL; //'Method Not Found.';
			$this->outputJSON($jsonarray);
		}

		if(method_exists($extMainObj,'init')){ // check if initialization method exists
			$extMainObj->init(); // call init method
		}

		include_once $extensionview;
		$extObj = new $extView();
		if(!method_exists($extObj,$extTask)){ // check if method exists
			$jsonarray['code']=404;
			$jsonarray['message']=NULL; //'Method Not Found.';
			$this->outputJSON($jsonarray);
		}

		$jsonarray = $extObj->$extTask();
		if(!$jsonarray){ // if anything goes wrong; return error code and message in response
			$jsonarray['code']=IJReq::getResponseCode();
			$jsonarray['message']=IJReq::getResponseMessage();

			// edd exception to log file
			IJException::addLog();
		}
		$this->outputJSON($jsonarray); // send data array to create jason string and output
	}

	/**
	 * @uses this function is used to fetch application (global) config.
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"applicationConfig",
	 * 		"taskData": {
	 * 			"device":"android/iphone",
	 * 			"type":"device type"
	 * 		}
	 * 	}
	 *
	 */
	function applicationConfig(){
		$model = $this->getModel('ijoomeradv');
		$result = $model->getApplicationConfig(); // get application config
		$jsonarray=array();
		if($result){
			$jsonarray['code']=200; // response ok
			foreach($result as $value){
				$jsonarray['configuration']['globalconfig'][$value->name]=$value->value;
				if($value->name=='IJOOMER_GC_REGISTRATION'){
					switch($value->value){
						case 'jomsocial':
							require_once  JPATH_ROOT . '/components/com_community/libraries/core.php';
							require_once JPATH_COMPONENT_SITE . '/extensionsjomsocial/'."helper.php";
							$jomHelper	=	new jomHelper();
							$jomsocial_version = $jomHelper->getjomsocialversion();

							if($jomsocial_version >= 3)
							{
								$jsonarray['configuration']['globalconfig']['defaultAvatar']=JURI::base().'components/com_community/assets/user-Male.png';
								$jsonarray['configuration']['globalconfig']['defaultAvatarFemale']=JURI::base().'components/com_community/assets/user-Female.png';
							}
							else {
								$jsonarray['configuration']['globalconfig']['defaultAvatar']=JURI::base().'components/com_community/assets/user.png';
							}
							break;
					}
				}
			}
		}else{
			$jsonarray['code']=204; // No data
			$jsonarray['message']=JText::_('COM_IJOOMERADV_NO_CONFIGURATION_FOUND');
		}

		// get all extension config
		$results = $model->getExtensions();
		foreach($results as $result){
			require_once IJ_EXTENSION.'/'.$result->classname.'/'.$result->classname.".php";
			$classobj= new $result->classname();
			$extconfig=$classobj->getconfig();
			foreach($extconfig as $key=>$value){
				$jsonarray['configuration']['extentionconfig'][$result->classname][$key]=$value;
			}
		}

		$jsonarray['configuration']['globalconfig']['timeStamp']		= time();
		$jsonarray['configuration']['globalconfig']['offset']			= (date_offset_get(new DateTime)/3600);
		$jsonarray['configuration']['globalconfig']['offsetLocation']	= date_default_timezone_get();

		$homeMenu = $model->getHomeMenu();
		if($homeMenu){
			$homeMenuobj = new stdClass();
			$homeMenuobj->itemid = $homeMenu->id;
			$homeMenuobj->itemcaption = $homeMenu->title;
			$viewname = explode(,$homeMenu->views);
			$homeMenuobj->itemview = $viewname[3];

			$remotedata = json_decode($homeMenu->menuoptions);
			$remotedata = ($remotedata)?$remotedata->remoteUse:'';

			$homeMenuobj->itemdata = $remotedata;
			$jsonarray['configuration']['globalconfig']['default_landing_screen']	= $homeMenuobj;
		}else{
			$jsonarray['configuration']['globalconfig']['default_landing_screen']	= '';
		}

		// application get extension version info

		if(file_exists(JPATH_COMPONENT_SITE . '/extensions/jomsocial/'."helper.php") && file_exists(JPATH_SITE.'/components/com_community/'."community.php")){
			require_once JPATH_COMPONENT_SITE . '/extensions/jomsocial/'."helper.php";
			$jomHelper	=	new jomHelper();
			$jomsocial_version = $jomHelper->getjomsocialversion();
			$jsonarray['configuration']['versioninfo']["jomsocial"]	= $jomsocial_version;
		}
		//$jsonarray['configuration']['versioninfo']["jomsocial"]	= "3.0";


		// application theme list
		$jsonarray['configuration']['theme']			= $this->statictheme();

		// application menu list
		$jsonarray['configuration']['menus']			= $model->getMenus();

		$this->outputJSON($jsonarray); // send data array to create jason string and output
	}


	// calls from applicationConfig()
	private function statictheme(){
		$device = IJReq::getTaskData('device');
		$theme 	= IJOOMER_THM_SELECTED_THEME;
		$model 	= $this->getModel('ijoomeradv');
		$viewnames	= $model->getViewNames();

		if($device == 'android'){
			$device_type = IJReq::getTaskData('type','hdpi');

		}elseif ($device == 'iphone'){
			$device_type = IJReq::getTaskData('type','3');
		}

		$i=0;
		foreach ($viewnames as $key=>$value){
			foreach ($value as $ky=>$val){
				$themearray['theme'][$i]['viewname']=$val;
				$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/'.$key.'/'.$device.'/'.$device_type.'/'.$val.'_icon.png';
				$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/'.$key.'/'.$device.'/'.$device_type.'/'.$val.'_tab.png';
				$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/'.$key.'/'.$device.'/'.$device_type.'/'.$val.'_tab_active.png';
				$i++;
			}
		}

		$themearray['theme'][$i]['viewname']='Home';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Home_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Home_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Home_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='More';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/More_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/More_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='Registration';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Registration_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Registration_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Registration_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='Web';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Web_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Web_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Web_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='Login';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Login_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Login_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Login_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='Logout';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Logout_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Logout_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/Logout_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='PluginsContactUs';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsContactUs_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsContactUs_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsContactUs_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='PluginsFacebookNearByVenues';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsFacebookNearByVenues_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsFacebookNearByVenues_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsFacebookNearByVenues_tab_active.png';
		$i++;

		$themearray['theme'][$i]['viewname']='PluginsYoutubePlaylist';
		$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsYoutubePlaylist_icon.png';
		$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsYoutubePlaylist_tab.png';
		$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/PluginsYoutubePlaylist_tab_active.png';
		$i++;

		$customView = $model->getCustomView();
		foreach ($customView as $key=>$value){
			$viewname	=	explode(,$value->views);
			$themearray['theme'][$i]['viewname']=$viewname[3];
			$themearray['theme'][$i]['icon']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/defaultActivity_icon.png';
			$themearray['theme'][$i]['tab']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/defaultActivity_tab.png';
			$themearray['theme'][$i]['tab_active']=JURI::base().'administrator/components/com_ijoomeradv/theme/'.$theme.'/default/'.$device.'/'.$device_type.'/defaultActivity_tab_active.png';
			$i++;
		}

		return $themearray['theme'];
	}

	/**
	 * @uses this function is used to log into the application
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"login",
	 * 		"taskData":{
	 * 			"username":"abc",
	 * 			"password":"xyz",
	 * 			"lat":"23.00",
	 * 			"long":"72.40",
	 * 			"devicetoken"/"android_devicetoken"/"bb_devicetoken":"abc123xyz"
	 * 		}
	 * 	}
	 *
	 */
	function login(){
		if(!IJReq::getTaskData('username') or !IJReq::getTaskData('password')){ // check if username or password not blank
			$jsonarray['code']=400;
			$jsonarray['message']=NULL;
			$this->outputJSON($jsonarray); // send data array to create jason string and output
		}

		$credentials = array();
		$credentials['username'] = IJReq::getTaskData('username'); // get username
		$credentials['password'] = IJReq::getTaskData('password'); // get password

		if($this->mainframe->login($credentials) == '1'){
			$model = $this->getModel('ijoomeradv');
			$jsonarray = $model->loginProccess();
		}else{
			$jsonarray['code']=401;
			$jsonarray['message']=JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE');
		}
		$this->outputJSON($jsonarray); // send data array to create jason string and output
	}

	/**
	 * @uses this function will use to log out of the application
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"logout"
	 * 	}
	 *
	 */
	function logout(){
		$my =& JFactory::getUser();
		if(!$my->id){
			$jsonarray['code']=400; // if userid not passed or null
			$jsonarray['message']=NULL;
			$this->outputJSON($jsonarray);
		}

		if($this->mainframe->logout($my->id)){
			ob_end_clean();
			$jsonarray['code']=200; // logout success
			$jsonarray['message']=NULL;
		}else{
			$jsonarray['code']=500; // logout unsuccess
			$jsonarray['message']=JText::_('COM_IJOOMERADV_UNABLE_LOGOUT');
		}
		$this->outputJSON($jsonarray);
	}

	/**
	 * @uses this function will use to get pushnotification from id
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"getPushNotification"
	 * 		"taskData":{
	 * 						"id":
	 * 				   }
	 * 	}
	 *
	 */
	function getPushNotification(){
		$id 	= IJReq::getTaskData('id',0);
		$user 	= JFactory::getUser();
		$db 	= JFactory::getDBO();

		$pushDataQuery = "SELECT *
							FROM #__ijoomeradv_push_notification_data
							WHERE id='{$id}'";

		$db->setQuery($pushDataQuery);
		$pushData = $db->loadObject();
		if(!empty($pushData)){
			$query = "UPDATE #__ijoomeradv_push_notification_data
						SET readcount=readcount+1
						WHERE id='{$id}'";
			$db->setQuery($query);
			$db->Query();

			$pushOptions=gzuncompress($pushData->detail);
			$jsonarrayDetail = json_decode($pushOptions,true);
			$jsonarray['code'] = 200;
			$jsonarray['data'] = $jsonarrayDetail['detail'];
		}else{
			$jsonarray['code'] = 204;
		}
		$this->outputJSON($jsonarray);
	}

	/**
	 * @uses this function used to log in with Facebook
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"fblogin",
	 * 		"taskData":{
	 * 			"name":"name",
	 * 			"username":"username",
	 * 			"password":"password", // fbid as password
	 * 			"email":"email",
	 * 			"lat":"lat",
	 * 			"long":"long",
	 * 			"bigpic":"bigpic",
	 * 			"devicetoken/android_devicetoken/bb_devicetoken":"devicetoken",
	 * 			"regopt":"regopt", // 0: Check if user exist, 1: Existing user, 2: New user
	 * 			"fbid":"fbid" // facebook userid
	 * 		}
	 * 	}
	 *
	 */
	function fblogin(){
		$model = $this->getModel('ijoomeradv');
		$jsonarray = $model->fblogin();
		if(!$jsonarray){
			$jsonarray['code']=IJReq::getResponseCode();
			$jsonarray['message']=IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}
		$this->outputJSON($jsonarray);
	}

	/**
	 * @uses this function is used to register new user
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"registration",
	 * 		"taskData":{
	 * 			"name":"name",
	 * 			"username":"username",
	 * 			"password":"password",
	 * 			"email":"email",
	 * 			"full":"0/1", // 0: for default registration form 1: for jomsocial extra fiel'/'
	 * 			"type":"type" // profile type if any otherwise "default" pass
	 * 		}
	 * 	}
	 *
	 */
	function registration(){
		$model = $this->getModel('ijoomeradv');
		$jsonarray = $model->registration();
		if(!$jsonarray){
			$jsonarray['code']=IJReq::getResponseCode();
			$jsonarray['message']=IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}
		$this->outputJSON($jsonarray);
	}


	/**
	 * @uses this function is used to retrive password
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"resetPassword",
	 * 		"taskData":{
	 * 			"step":"1/2/3",
	 * 			"email":"email", (if step1),
	 * 			"username":"username", (if step2)
	 * 			"token":"token", (if step2)
	 * 			"crypt":"crypt", (if step3)
	 * 			"userid":"userid", (if step3)
	 * 			"password":"password" (if step3)
	 * 		}
	 * 	}
	 *
	 */
	function resetPassword(){
		$model = $this->getModel('ijoomeradv');
		$step=IJReq::getTaskData('step',1,'int');
		switch($step){
			case 3:
				$jsonarray = $model->resetPassword();
				break;
			case 2:
				$jsonarray = $model->validateToken();
				break;
			case 1:
			default:
				$jsonarray = $model->retriveToken();
				break;
		}
		if(!$jsonarray){
			$jsonarray['code']=IJReq::getResponseCode();
			$jsonarray['message']=IJReq::getResponseMessage();
			$this->outputJSON($jsonarray);
		}
		$this->outputJSON($jsonarray);
	}


	/**
	 * @uses this function is use to retrive username
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"retriveUsername",
	 * 		"taskData":{
	 * 			"email":"email"
	 * 		}
	 * 	}
	 *
	 */
	function retriveUsername(){
		$model = $this->getModel('ijoomeradv');
		$jsonarray = $model->retriveUsername();
		if(!$jsonarray){ // if return value is false
			$jsonarray['code']=IJReq::getResponseCode(); // get response code
			$jsonarray['message']=IJReq::getResponseMessage(); // get response message
			$this->outputJSON($jsonarray);
		}
		$this->outputJSON($jsonarray);
	}
	/**
	 * @uses this function is use to mail of contactUs Form
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"contactUs",
	 * 		"taskData":{
	 * 			"form":"form"(1/0)(if 1 then form,toID,menuID)
	 * 			"toID":"toID",
	 * 			"menuID":"menuID"
	 * 			"name":"name",
	 * 			"email":"email",
	 * 			"subject":"subject",
	 * 			"message":"message"
	 * 		}
	 * 	}
	 *
	 */

	//{"task":"contactUs","taskdata":{"form":"0","toID":"6","menuID":"24","name":"name","email":"email","subject":"subject","message":"message"}}
	//{"task":"contactUs","taskData":{"form":"1","toID":"6","menuID":"24"}}
	function contactUs(){
		$form    	=IJReq::getTaskData('form');
		$toID    	=IJReq::getTaskData('toID');
		$menuID    	=IJReq::getTaskData('menuID');
		$db =JFactory::getDbo();
		$query="SELECT menuoptions
				FROM #__ijoomeradv_menu
				WHERE id={$menuID}";
		$db->setQuery($query);
		$options = $db->loadObjectList();
		$menuoptions= json_decode($options[0]->menuoptions);
		$serverUse=$menuoptions->serverUse;
		$remoteUse=$menuoptions->remoteUse;

		if($form==1){
			$query="SELECT *
					FROM #__contact_details
					WHERE id={$toID}";
			$db->setQuery($query);
			$row = $db->loadObject();

			$count=count($row);
        	if($count<=0){
				$jsonarray['code']=204;
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				$this->outputJSON($jsonarray);
			}else{
				$jsonarray['code'] = 200;
			}

			$jsonarray['contact']['id']			= $row->id;
			$jsonarray['contact']['name']		= ($serverUse->showName==1) ? $row->name : "";
			$jsonarray['contact']['position']	= ($serverUse->showPosition==1) ? $row->con_position : "";
			$jsonarray['contact']['address'] 	= ($serverUse->showStreet==1) ? $row->address : "";
			$jsonarray['contact']['state'] 	 	= ($serverUse->showState==1) ? $row->state : "";;
			$jsonarray['contact']['country'] 	= ($serverUse->showCountry==1) ? $row->country : "";
			$jsonarray['contact']['postcode'] 	= ($serverUse->showPostalCode==1) ? $row->postcode : "";
			$jsonarray['contact']['city'] 	 	= ($serverUse->showCity==1) ? $row->suburb : "";
			$jsonarray['contact']['telephone'] 	= ($serverUse->showTelephone==1) ? $row->telephone : "";
			$jsonarray['contact']['fax'] 	    = ($serverUse->showFax==1) ? $row->fax : "";
			$jsonarray['contact']['mobile'] 	= ($serverUse->showMobile==1) ? $row->mobile : "";
			$jsonarray['contact']['webpage'] 	= ($serverUse->showWebpage==1) ? $row->webpage : "";
			$jsonarray['contact']['misc'] 	    = ($serverUse->showMiscInfo==1) ? strip_tags($row->misc) : "";
			$jsonarray['contact']['emailTo'] 	= ($serverUse->showEmail==1) ? $row->email_to : "";
			$jsonarray['contact']['image'] 	    = ($serverUse->showMiscImage==1) ? JURI::base().$row->image : "";
			//$jsonarray['contact']['showContactForm'] 	= intval($remoteUse->showContactForm);

			//$explode= explode(',',$remoteUse->subjectLine);
			//$jsonarray['contact']['subjectLine'] = $explode;

			$decodeParams = json_decode($row->params);
			if($decodeParams->linka_name || $decodeParams->linka){
				$jsonarray['contact']['links'][0]['caption']	 = $decodeParams->linka_name;
				$jsonarray['contact']['links'][0]['url'] 	     = $decodeParams->linka;
			}
			if($decodeParams->linkb_name || $decodeParams->linkb){
				$jsonarray['contact']['links'][1]['caption'] 	 = $decodeParams->linkb_name;
				$jsonarray['contact']['links'][1]['url'] 	     = $decodeParams->linkb;
			}
			if($decodeParams->linkc_name || $decodeParams->linkc){
				$jsonarray['contact']['links'][2]['caption'] 	 = $decodeParams->linkc_name;
				$jsonarray['contact']['links'][2]['url'] 	     = $decodeParams->linkc;
			}
			if($decodeParams->linkd_name || $decodeParams->linkd){
				$jsonarray['contact']['links'][3]['caption'] 	 = $decodeParams->linkd_name;
				$jsonarray['contact']['links'][3]['url'] 	     = $decodeParams->linkd;
			}
			if($decodeParams->linke_name || $decodeParams->linke){
				$jsonarray['contact']['links'][4]['caption'] 	 = $decodeParams->linke_name;
				$jsonarray['contact']['links'][4]['url'] 	     = $decodeParams->linke;
			}
			$this->outputJSON($jsonarray);
		}else{
			$name    =IJReq::getTaskData('name');
			$email   =IJReq::getTaskData('email');
			$subject =IJReq::getTaskData('subject');
			$message =IJReq::getTaskData('message');
			$thankYouText=$serverUse->thankYouText;
			$sendCopy =$serverUse->sendCopy;
			$data    = array();
			$data['contact_name']    = $name;
			$data['contact_email']   = $email;
			$data['contact_subject'] = $subject;
			$data['contact_message'] = $message;
			if($sendCopy=='on'){
			$data['contact_email_copy'] = $sendCopy;
			}

			$app	 = JFactory::getApplication();
			require_once JPATH_SITE.'/components/com_contact/models/contact.php';
			$ContactModelContact = new ContactModelContact();
			$params  = JComponentHelper::getParams('com_contact');
			$contact = $ContactModelContact->getItem($toID);
			$params->merge($contact->params);

			// Send the email
			$sent = false;
			if (!$params->get('custom_reply')) {
				$sent = $this->_sendEmail($data, $contact);
				$jsonarry['code']=200;
				$jsonarry['message']=$thankYouText;
				$this->outputJSON($jsonarry);
			}/*else{
				$jsonarry['code']=400;
				$jsonarry['message']='Invalid Email';
				$this->outputJSON($jsonarry);
			}*/
			return true;
		}

	}
	function _sendEmail($data, $contact)
	{
			$app		= JFactory::getApplication();
			$params 	= JComponentHelper::getParams('com_contact');
			if ($contact->email_to == '' && $contact->user_id != 0) {
				$contact_user = JUser::getInstance($contact->user_id);
				$contact->email_to = $contact_user->get('email');
			}
			$mailfrom	= $app->getCfg('mailfrom');
			$fromname	= $app->getCfg('fromname');
			$sitename	= $app->getCfg('sitename');
			$copytext 	= JText::sprintf('COM_IJOOMERADV_COPYTEXT_OF', $contact->name, $sitename);

			$name		= $data['contact_name'];
			$email		= $data['contact_email'];
			$subject	= $data['contact_subject'];
			$body		= $data['contact_message'];

			// Prepare email body
			$prefix = JText::sprintf('COM_IJOOMERADV_ENQUIRY_TEXT', JURI::base());
			//$body	= $prefix."\n".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($body);
			$body	= $prefix."\n"."from:".$name.' <'.$email.'>'."\r\n\r\n".stripslashes($body);

			$mail = JFactory::getMailer();
			$mail->addRecipient($contact->email_to);
			$mail->addReplyTo(array($email, $name));
			$mail->setSender(array($mailfrom, $fromname));
			$mail->setSubject($sitename.': '.$subject);
			$mail->setBody($body);
			$sent = $mail->Send();

			//If we are supposed to copy the sender, do so.
			// check whether email copy function activated
			if ( array_key_exists('contact_email_copy', $data)  ) {

				$copytext		= JText::sprintf('COM_IJOOMERADV_COPYTEXT_OF', $contact->name, $sitename);
				$copytext		.= "\r\n\r\n".$body;
				$copysubject	= JText::sprintf('COM_IJOOMERADV_COPYSUBJECT_OF', $subject);

				$mail = JFactory::getMailer();
				$mail->addRecipient($email);
				$mail->addReplyTo(array($email, $name));
				$mail->setSender(array($mailfrom, $fromname));
				$mail->setSubject($copysubject);
				$mail->setBody($copytext);
				$sent = $mail->Send();
			}

			return $sent;
	}

	function verbose(){
		echo '<b>iJoomer Advance : <b>';
		echo IJADV_VERSION;
		echo '<br/><br/>Extensions:<br/>';
		$model = $this->getModel('ijoomeradv');
		$extensions=$model->getExtensions();
		foreach($extensions as $extension){
			echo '<br/>&nbsp;&nbsp;&nbsp;'.$extension->name.' : ';
			$mainXML = JPATH_SITE.'/components/com_ijoomeradv/extensions/'.$extension->classname.'.xml';
			if (is_file($mainXML)) {
				if($xml = simplexml_load_file($mainXML)){
					$version = $xml->xpath('version');
					$version = (double)$version[0][0];
				}
			}
			echo $version;
			if($extension->name!='ICMS'){
				$db=&JFactory::getDBO();
				$query="SELECT `manifest_cache`
						FROM #__extensions
						WHERE `element`='{$extension->option}'";
				$db->setQuery($query);
				$extension=$db->loadResult($query);
				$extension=json_decode($extension);
				if($extension->version){
					echo ' / '.$extension->version;
				}
			}else{
				echo ' / '.IJ_JOOMLA_VERSION;
			}
		}
	}
}