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

defined( '_JEXEC' ) or die;

jimport('joomla.application.component.model');

class ijoomeradvModelijoomeradv extends JModelLegacy{

	private $db;
	private $mainframe;

	function __construct(){
		parent::__construct();
		$this->db			= JFactory::getDBO();
		$this->mainframe	= & JFactory::getApplication();
	}

	/**
	 * @uses fetches ijoomeradv global config
	 */
	function getApplicationConfig(){
		$query="SELECT `name`,`value`
				FROM #__ijoomeradv_config";
		$this->db->setQuery($query);
		return $this->db->loadObjectList();
	}

	/**
	 * @uses fetches all published extensions
	 */
	public function getExtensions(){
		$query = 'SELECT *
				  FROM #__ijoomeradv_extensions
				  WHERE published=1';
		$this->db->setQuery($query);
		$components = $this->db->loadObjectList();
		return $components;
	}

	/**
	 * Method to get the available viewnames.
	 *
	 * @return	array	Array of viewnames.
	 * @since	1.6
	 */
	public function getViewNames(){
		jimport('joomla.filesystem.file');

		$components = $this->getExtensions();

		foreach ($components as $component){
			$mainXML = JPATH_SITE.'/components/com_ijoomeradv/extensions/'.$component->classname.'.xml';
			if (is_file($mainXML)) {
				$options[$component->classname] = $this->getTypeOptionsFromXML($mainXML);
			}
		}
		return $options;
	}

	/**
	 *
	 */
	private function getTypeOptionsFromXML($file){
		$options = array();

		if($xml = simplexml_load_file($file)){
			$views = $xml->xpath('views');

			if(!empty($views)){
				foreach ($views[0]->view as $value){
					$options[] = (string) $value->remoteTask;
				}
			}
		}
		return $options;
	}

	/**
	 * @uses fetches ijoomeradv Menu items
	 */
	function getMenus(){
		$menuArray = array();
		$positionScreens =array();
		$user = JFactory::getUser();
		$device = IJReq::getTaskData('device');
		if($device == 'android'){
			$menudevice=2;
			$device_type = IJReq::getTaskData('type','hdpi');
		}elseif ($device == 'iphone'){
			$menudevice=3;
			$device_type = IJReq::getTaskData('type','3');
			if($device_type==5){
				$device_type=4;
			}
		}
		$groups	= implode(',', $user->getAuthorisedViewLevels());

		$query = 'SELECT *
				  FROM #__ijoomeradv_menu_types';

		$this->db->setQuery($query);
		$menus = $this->db->loadObjectList();

		$i=0;
		if(!empty($menus)){
			foreach ($menus as $value){
				if ($value->position == 1){
					$screennames = json_decode('[]');
				}else{
					$screens = json_decode($value->screen);
					$screennames = array();
					if($screens){
						foreach ($screens as $val){
							foreach ($val as $screen){
								$screenname = (explode('.',$screen));
								$screennames[] = $screenname[2];
								$positionScreens[$value->position][] =  $screenname[2];
							}
						}
					}
				}

				if(($screennames && $value->position>1) || $value->position==1){
					$menuArray[$i]=array(	"menuid"		=> $value->id,
											"menuname"		=> $value->title,
											"menuposition"	=> $value->position,
											"screens"		=> $screennames
									);


					//Add IF condition for if menuitem for specific device avail or not
					//if global selected then check avaibility in menu
					$query="SELECT *
							FROM #__ijoomeradv_menu
							WHERE menutype=$value->id
							AND published=1
							AND access IN ($groups)
							/*AND (IF((menudevice=1),($value->menudevice=$menudevice),(menudevice=$menudevice)) OR menudevice=4
								OR IF((menudevice=1 AND $value->menudevice=1),true,false)) */
							ORDER BY ordering";

					$this->db->setQuery($query);
					$menuitems = $this->db->loadObjectList();

					$k=0;
					$menuArray[$i]["menuitem"] = array();
					if(!empty($menuitems)){
						foreach ($menuitems as $value1){
							$viewname = explode('.',$value1->views);

							$remotedata = json_decode($value1->menuoptions);
							if($remotedata){
								$remotedata=$remotedata->remoteUse;
							}else{
								$remotedata='';
							}

							$menuArray[$i]["menuitem"][$k]=array(	"itemid"		=> $value1->id,
																	"itemcaption"	=> $value1->title,
																	"itemview"		=> $viewname[3],
																	"itemdata"		=> $remotedata
															);

							if(($value->position == 1 or $value->position == 2) && ($value1->itemimage)){
								$menuArray[$i]["menuitem"][$k]["icon"]	= JURI::base().'administrator'.DS.'components'.DS.'com_ijoomeradv'.DS.'theme'.DS.'custom'.DS.$device.DS.$device_type.DS.$value1->itemimage.'_icon.png';
							}else if($value->position == 3 && $value1->itemimage){
								$menuArray[$i]["menuitem"][$k]["tab"]	= JURI::base().'administrator'.DS.'components'.DS.'com_ijoomeradv'.DS.'theme'.DS.'custom'.DS.$device.DS.$device_type.DS.$value1->itemimage.'_tab.png';
								$menuArray[$i]["menuitem"][$k]["tab_active"]	= JURI::base().'administrator'.DS.'components'.DS.'com_ijoomeradv'.DS.'theme'.DS.'custom'.DS.$device.DS.$device_type.DS.$value1->itemimage.'_tab_active.png';
							}

							$k++;
						}
					}
					$i++;
				}
			}
		}
		return $menuArray;
	}

	/**
	 * @uses Set request variable from menu id
	 *
	 */
	function setMenuRequest($menuid){
		$mainframe = & JFactory::getApplication();

		$query="SELECT *
				FROM #__ijoomeradv_menu
				WHERE id=".$menuid;
		$this->db->setQuery($query);
		$menuobject = $this->db->loadObject();

		if($menuobject){
			//Set reqObject as per menuid request
			$views = explode('.',$menuobject->views);
			$mainframe->IJObject->reqObject->extName = $views[0];
			$mainframe->IJObject->reqObject->extView = $views[1];
			$mainframe->IJObject->reqObject->extTask = $views[2];

			//Set required data for menu request
			$menuoptions = json_decode($menuobject->menuoptions);
			foreach($menuoptions->remoteUse as $key=>$value){
				$mainframe->IJObject->reqObject->taskData->$key = $value;
			}
		}
		return true;
	}

	/**
	 * @uses fetches ijoomeradv global config
	 *
	 */
	function getExtensionConfig($extName){
		$query="SELECT `name`,`value`
				FROM #__ijoomeradv_{$extName}_config";
		$this->db->setQuery($query);
		return $this->db->loadObjectList(); // return config list
	}

	/**
	 * @uses fetches ijoomeradv custom views detail
	 *
	 */
	public function getCustomView(){
		$query = "SELECT *
				  FROM #__ijoomeradv_menu
				  WHERE published=1
				  AND type='Custom'";
		$this->db->setQuery($query);
		$customView = $this->db->loadObjectList();

		return $customView;
	}

	/**
	 * @uses fetches ijoomeradv default home views
	 *
	 */
	public function getHomeMenu(){
		$query = "SELECT *
				  FROM #__ijoomeradv_menu
				  WHERE published=1
				  AND home=1";
		$this->db->setQuery($query);
		return $this->db->loadObject();
	}

	/**
	 * @uses check ioomer extension and related joomla component if installed and enabled
	 */
	function checkIJExtension($extName){
		$query="SELECT `option`
				FROM `#__ijoomeradv_extensions`
				WHERE `classname`='{$extName}'";
		$this->db->setQuery($query);
		$option = $this->db->loadResult(); // get component name from the extension name
		if(!$option){
			IJReq::setResponseCode(404);
			return false;
		}else{
			$IJHelperObj= new ijoomeradvHelper(); // create hepler object
			if(!$IJHelperObj->getComponent($option)){
				IJReq::setResponseCode(404);
				return false;
			}
		}
		return true;
	}


	function loginProccess(){
		$data['latitude'] 		= IJReq::getTaskData('lat');
		$data['longitude']  	= IJReq::getTaskData('long');
		$data['device_token'] 	= IJReq::getTaskData('devicetoken');
		$data['device_type']	= IJReq::getTaskData('type');

		$my = & JFactory::getUser(); //get current user

		//TODO : extension levels default params
		$defaultParams='{"pushnotif_profile_activity_add_comment":"1","pushnotif_profile_activity_reply_comment":"1","pushnotif_profile_status_update":"1","pushnotif_profile_like":"1","pushnotif_profile_stream_like":"1","pushnotif_friends_request_connection":"1","pushnotif_friends_create_connection":"1","pushnotif_inbox_create_message":"1","pushnotif_groups_invite":"1","pushnotif_groups_discussion_reply":"1","pushnotif_groups_wall_create":"1","pushnotif_groups_create_discussion":"1","pushnotif_groups_create_news":"1","pushnotif_groups_create_album":"1","pushnotif_groups_create_video":"1","pushnotif_groups_create_event":"1","pushnotif_groups_sendmail":"1","pushnotif_groups_member_approved":"1","pushnotif_groups_member_join":"1","pushnotif_groups_notify_creator":"1","pushnotif_groups_discussion_newfile":"1","pushnotif_events_invite":"1","pushnotif_events_invitation_approved":"1","pushnotif_events_sendmail":"1","pushnotif_event_notify_creator":"1","pushnotif_event_join_request":"1","pushnotif_videos_submit_wall":"1","pushnotif_videos_reply_wall":"1","pushnotif_videos_tagging":"1","pushnotif_videos_like":"1","pushnotif_photos_submit_wall":"1","pushnotif_photos_reply_wall":"1","pushnotif_photos_tagging":"1","pushnotif_photos_like":"1"}';

		$query="SELECT count(1)
				FROM `#__ijoomeradv_users`
				WHERE `userid`='{$my->id}'";
		$this->db->setQuery($query);
		$user=$this->db->loadResult();

		if($user){
			$query="UPDATE `#__ijoomeradv_users`
					SET `device_token`='{$data['device_token']}', `device_type`='{$data['device_type']}'
					WHERE `userid`={$my->id}";
		}else{
			$query="INSERT INTO `#__ijoomeradv_users` (`userid`,`jomsocial_params`,`device_token`,`device_type`)
					VALUES ('{$my->id}','{$defaultParams}','{$data['device_token']}','{$data['device_type']}')";
		}
		$this->db->setQuery($query);
		$this->db->query();

		$jsonarray['code'] = 200;
		$jsonarray['profile'] = IJOOMER_GC_REGISTRATION;

		if(strtolower(IJOOMER_GC_REGISTRATION)==='jomsocial' && file_exists(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php')){
			require_once JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php';
			// update jomsocial latitude & longitude if not 0
			if($data['latitude']!=0 && $data['longitude']!=0){
				$query="UPDATE #__community_users
						SET `latitude`='{$data['latitude']}',`longitude`='{$data['longitude']}'
						WHERE `userid`='{$my->id}'";
				$this->db->setQuery($query);
				$this->db->Query();
			}
		}

		//change for id based push notification
		$component = JComponentHelper::getComponent('com_community');
		if(!empty($component->id) && $component->enabled==1){
			$friendsModel 	= CFactory::getModel('friends');
			$friends 		= $friendsModel->getFriendIds($my->id);
			$pushOptions['detail']=array();
			$pushOptions = gzcompress(json_encode($pushOptions));
			$message = JText::sprintf('COM_IJOOMERADV_USER_ONLINE',$my->name);
			$obj = new stdClass();
			$obj->id 		= null;
			$obj->detail 	= $pushOptions;
			$obj->tocount  	= count($friends);
			$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
			if($obj->id){
				$jsonarray['pushNotificationData']['id'] 		= $obj->id;
				$jsonarray['pushNotificationData']['to'] 		= implode(',',$friends);
				$jsonarray['pushNotificationData']['message'] 	= $message;
				$jsonarray['pushNotificationData']['type'] 		= 'online';
				$jsonarray['pushNotificationData']['configtype'] 	= '';
			}
		}
		return $jsonarray;
	}


	/**
	 * @uses this function is use to log into with facebook
	 *
	 */
	function fblogin(){
		jimport('joomla.user.helper');

		$data['relname']		= IJReq::getTaskData('name');
		$data['user_nm']		= IJReq::getTaskData('username');
		$data['email']			= IJReq::getTaskData('email');
		$data['pic_big'] 		= IJReq::getTaskData('bigpic');
		$password_set			= IJReq::getTaskData('password');
		$reg_opt 				= IJReq::getTaskData('regopt',0,'int');
		$fbid 					= IJReq::getTaskData('fbid');
		$time = time();
		if($reg_opt===0){// first check if fbuser in db logged in
			$query="SELECT u.id,u.username
					FROM #__users AS u,#__community_connect_users AS cu
					WHERE u.id = cu.userid
					AND cu.`connectid`='{$password_set}'";
			$this->db->setQuery($query);
			$userinfo = $this->db->loadObject();

			if(isset($userinfo->id) && $userinfo->id > 0){
				$salt  = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($password_set.$time, $salt);
				$data['password'] = $crypt.':'.$salt;

				$query="UPDATE #__users
						SET `password`='{$data['password']}'
						WHERE `id`='{$userinfo->id}'";
				$this->db->setQuery($query);
				$this->db->Query();

				$usersipass['username'] = $userinfo->username;
				$usersipass['password'] = $password_set.$time;
				if($this->mainframe->login($usersipass)=='1'){
					$jsonarray = $this->loginProccess();
					return $jsonarray;
				}else{
					IJReq::setResponseCode(401);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE'));
					return false;
				}
			}else{
				IJReq::setResponseCode(703); // Facebook user not found, need to create new user
				return false;
			}
		}else if($reg_opt===1){//registration option 1 if already user
			$credentials = array();
			$credentials['username'] = $data['user_nm'];
			$credentials['password'] = $password_set;

			if($this->mainframe->login($credentials) == '1' && $fbid!=""){
				// connect fb user to site user...
				$user = & JFactory::getUser();
				if(strtolower(IJOOMER_GC_REGISTRATION)==='community' && file_exists(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php')){
					require_once JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php';
					$query="INSERT INTO #__community_connect_users
							SET userid='{$user->id}',connectid='{$fbid}',type='facebook'";
					$this->db->setQuery($query);
					$this->db->Query();

					$salt  = JUserHelper::genRandomPassword(32);
					$crypt = JUserHelper::getCryptedPassword($password_set.$time, $salt);
					$data['password'] = $crypt.':'.$salt;

					$query="UPDATE #__users
							SET `password`='{$data['password']}'
							WHERE `id`='{$user->id}'";
					$this->db->setQuery($query);
					$this->db->Query();

					// store user image...
					CFactory::load( 'libraries' , 'facebook' );
					$facebook		= new CFacebook();
					// edited by Salim (Date: 08-09-2011)
					$data['pic_big'] = str_replace('profile.cc.fbcdn','profile.ak.fbcdn',$data['pic_big']);
					$data['pic_big'] = str_replace('hprofile-cc-','hprofile-ak-',$data['pic_big']);

					$facebook->mapAvatar( $data['pic_big'] , $user->id , $config->get('fbwatermark') );
				}
				$jsonarray = $this->loginProccess();
			}else{
				IJReq::setResponseCode(401);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE'));
				return false;
			}
		}else{
			$query="SELECT u.id
					FROM #__users AS u
					WHERE u.`email`='{$data['email']}'";
			$this->db->setQuery($query);
			$uid = $this->db->loadResult();

			if($uid>0){ // if user exists with email address send email id already exists
				$query="SELECT u.id
						FROM #__users AS u,#__community_connect_users AS cu
						WHERE u.id = cu.userid AND u.`email`='{$data['email']}'
						AND cu.`connectid`='{$password_set}'";
				$this->db->setQuery($query);
				$uid = $this->db->loadResult();
				if(empty($uid)){
					IJReq::setResponseCode(702);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EMAIL_ALREADY_EXIST'));
					return false;
				}
			}

			$query="SELECT id
					FROM #__users
					WHERE `username`='".$data['user_nm']."'";
			$this->db->setQuery($query);
			$uid = $this->db->loadResult();

			if($uid>0){
				IJReq::setResponseCode(701);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USERNAME_ALREADY_EXIST'));
				return false;
			}else{
				jimport('joomla.user.helper');
				$user = new JUser;
				$fbData = IJReq::getTaskData('fb');
				$data['name']		= $fbData->name;
				$data['username']	= trim(str_replace("\n","",$data['user_nm']));
				$data['password1']	= $data['password2'] = trim(str_replace("\n","",$password_set.$time));
				$data['email1']		= $data['email2'] = trim(str_replace("\n","",$fbData->email));
				$data['latitude']	= IJReq::getTaskData('lat');
				$data['longitude']  = IJReq::getTaskData('long');

				$user->bind($data);
				if(!$user->save()){
					IJReq::setResponseCode(500);
					return false;
				}
				$aclval = $user->id;
				// store usegroup for user...
				$query="INSERT INTO #__user_usergroup_map
						SET group_id='2',user_id='{$aclval}'";
				$this->db->setQuery($query);
				$this->db->Query();

				if(strtolower(IJOOMER_GC_REGISTRATION)==='jomsocial' && file_exists(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php')){
					require_once JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php';
					$query="INSERT INTO #__community_connect_users
							SET userid='{$aclval}',connectid='{$password_set}',type='facebook'";
					$this->db->setQuery($query);
					$this->db->Query();
					$config		=& CFactory::getConfig();
					// store user image...
					CFactory::load( 'libraries' , 'facebook' );
					$facebook		= new CFacebook();

					// edited by Salim (Date: 08-09-2011)
					$data['pic_big'] = str_replace('profile.cc.fbcdn','profile.ak.fbcdn',$data['pic_big']);
					$data['pic_big'] = str_replace('hprofile-cc-','hprofile-ak-',$data['pic_big']);

					$facebook->mapAvatar( $data['pic_big'] , $aclval , $config->get('fbwatermark') );
				}
				// update password again...
				$salt  = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($password_set.$time, $salt);
				$data['password'] = $crypt.':'.$salt;

				$query="UPDATE #__users
						SET `password`='{$data['password']}'
						WHERE `id`='{$aclval}'";
				$this->db->setQuery($query);
				$this->db->Query();

				$usersipass['username'] = trim(str_replace("\n","",$data['user_nm']));
				$usersipass['password'] = trim(str_replace("\n","",$password_set.$time));
				if($this->mainframe->login($usersipass) == '1'){
					if($jsonarray = $this->loginProccess()){
						$this->fbFieldSet($aclval);
						return $jsonarray;
					}else{
						return false;
					}

				}else{
					IJReq::setResponseCode(401);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_UNABLE_TO_AUTHENTICATE'));
					return false;
				}
			}
		}
		return $jsonarray;
	}


	private function fbFieldSet($userid){
		$fb=IJReq::getTaskData('fb');
		$fieldConnection=array(	'FB_USERID'					=> (isset($fb->uid)) ? number_format($fb->uid,0,'','') : NULL,
								'FB_USERNAME'				=> (isset($fb->username)) ? $fb->username : NULL,
								'FB_3PARTY_ID'				=> (isset($fb->third_party_id)) ? $fb->third_party_id : NULL,
								'FB_FNAME'					=> (isset($fb->first_name)) ? $fb->first_name : NULL,
								'FB_MNAME'					=> (isset($fb->middle_name)) ? $fb->middle_name : NULL,
								'FB_LNAME'					=> (isset($fb->last_name)) ? $fb->last_name : NULL,
								'FB_PIC'					=> (isset($fb->pic)) ? $fb->pic : NULL,
								'FB_PIC_SMALL'				=> (isset($fb->pic_small)) ? $fb->pic_small : NULL,
								'FB_PIC_COVER'				=> (isset($fb->pic_cover->source)) ? $fb->pic_cover->source : NULL,
								'FB_VERIFIED'				=> (isset($fb->verified)) ? $fb->verified : NULL,
								'FB_SEX'					=> (isset($fb->sex)) ? $fb->sex : NULL,
								'FB_BIRTH_DATE'				=> (isset($fb->birthday_date)) ? $fb->birthday_date : NULL,
								'FB_STATUS'					=> (isset($fb->status->message)) ? $fb->status->message : NULL,
								'FB_ABOUT_ME'				=> (isset($fb->about_me)) ? $fb->about_me : NULL,
								'FB_TIMEZONE'				=> (isset($fb->timezone)) ? $fb->timezone : NULL,
								'FB_ISMINOR'				=> (isset($fb->is_minor)) ? $fb->is_minor : NULL,
								'FB_POLITICAL'				=> (isset($fb->political)) ? $fb->political : NULL,
								'FB_QUOTES'					=> (isset($fb->quotes)) ? $fb->quotes : NULL,
								'FB_RELATION_STATUS'		=> (isset($fb->relationship_status)) ? $fb->relationship_status : NULL,
								'FB_RELIGION'				=> (isset($fb->religion)) ? $fb->religion : NULL,
								'FB_TV_SHOW'				=> (isset($fb->tv)) ? $fb->tv : NULL,
								'FB_SPORTS'					=> (isset($fb->sports[0]->name)) ? $fb->sports[0]->name : NULL,
								'FB_WORK'					=> (isset($fb->work[0])) ? $fb->work[0] : NULL,
								'FB_EDUCATION'				=> (isset($fb->education[0]->school)) ? $fb->education[0]->school : NULL,
								'FB_EMAIL'					=> (isset($fb->email)) ? $fb->email : NULL,
								'FB_WEBSITE'				=> (isset($fb->website)) ? $fb->website : NULL,
								'FB_CURRENT_STREET'			=> (isset($fb->current_address->street)) ? $fb->current_address->street : NULL,
								'FB_CURRENT_CITY'			=> (isset($fb->current_address->city)) ? $fb->current_address->city : NULL,
								'FB_CURRENT_STATE'			=> (isset($fb->current_address->state)) ? $fb->current_address->state : NULL,
								'FB_CURRENT_COUNTRY'		=> (isset($fb->current_address->country)) ? $fb->current_address->country : NULL,
								'FB_CURRENT_ZIP'			=> (isset($fb->current_address->zip)) ? $fb->current_address->zip : NULL,
								'FB_CURRENT_LATITUDE'		=> (isset($fb->current_address->latitude)) ? $fb->current_address->latitude : NULL,
								'FB_CURRENT_LONGITUDE'		=> (isset($fb->current_address->longitude)) ? $fb->current_address->longitude : NULL,
								'FB_CURRENT_LOCATION_NAME'	=> (isset($fb->current_address->name)) ? $fb->current_address->name : NULL,
								'FB_HOMETOWN_STREET'		=> (isset($fb->hometown_location->street)) ? $fb->hometown_location->street : NULL,
								'FB_HOMETOWN_CITY'			=> (isset($fb->hometown_location->city)) ? $fb->hometown_location->city : NULL,
								'FB_HOMETOWN_STATE'			=> (isset($fb->hometown_location->state)) ? $fb->hometown_location->state : NULL,
								'FB_HOMETOWN_COUNTRY'		=> (isset($fb->hometown_location->country)) ? $fb->hometown_location->country : NULL,
								'FB_HOMETOWN_ZIP'			=> (isset($fb->hometown_location->zip)) ? $fb->hometown_location->zip : NULL,
								'FB_HOMETOWN_LATITUDE'		=> (isset($fb->hometown_location->latitude)) ? $fb->hometown_location->latitude : NULL,
								'FB_HOMETOWN_LONGITUDE'		=> (isset($fb->hometown_location->longitude)) ? $fb->hometown_location->longitude : NULL,
								'FB_HOMETOWN_LOCATION_NAME'	=> (isset($fb->hometown_location->name)) ? $fb->hometown_location->name : NULL,
							);

		foreach($fieldConnection as $key=>$value){
			$query="SELECT value
					FROM #__ijoomeradv_jomsocial_config
					WHERE name='{$key}'";
			$this->db->setQuery($query);
			$fieldid = $this->db->loadResult();

			if($fieldid){
				$query="SELECT id
						FROM #__community_fields_values
						WHERE user_id={$userid}
						AND field_id={$fieldid}";
				$this->db->setQuery($query);
				$field = $this->db->loadResult();

				if($field){
					$query="UPDATE `#__community_fields_values`
							SET `value`={$value}
							WHERE `id`={$field}";
				}else{
					$query="INSERT INTO `#__community_fields_values` (`id`,`user_id`,`field_id`,`value`,`access`)
							VALUES (NULL, '{$userid}', '{$fieldid}', '{$value}', 0)";
				}
				$this->db->setQuery($query);
				$this->db->Query();

			}
		}

		$query="UPDATE #__community_users
				SET `status`='{$fieldConnection['FB_STATUS']}'
				WHERE `userid`={$userid}";
		$this->db->setQuery($query);
		$this->db->Query();
	}

	/**
	 * @uses this function is use to register a new user
	 * @example the json string will be like, :
	 * 	{
	 * 		"task":"registration",
	 * 		"taskData": {
	 * 			"name":"name",
	 * 			"username":"username",
	 * 			"password":"password",
	 * 			"email":"email",
	 * 			"full":"0/1",(if 0=jomsocial full form sent)
	 * 			"type":"profile type (default is 'default')"
	 * 		}
	 * 	}
	 *
	 */
	function registration(){
		$post['relname']	= IJReq::getTaskData('name');
		$post['username']	= IJReq::getTaskData('username');
		$post['password']	= IJReq::getTaskData('password');
		$post['email']		= IJReq::getTaskData('email');
		$post['type']		= IJReq::getTaskData('type',0,'int');
		$Full_flag			= IJReq::getTaskData('full',0,'int');
		$lang =& JFactory::getLanguage();
		$lang->load('com_users');

		if(strtolower(IJOOMER_GC_REGISTRATION)==='no'){ // if registration not allowed
			IJReq::setResponseCode(401);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_REGISTRATION_NOT_ALLOW'));
			return false;
		}

		$query="SELECT id
				FROM `#__users`
				WHERE username='".str_replace("\n","",trim($post['username']))."'";
		$this->db->setQuery($query);
	 	if($this->db->loadResult() > 0){ // check if user already exist
			IJReq::setResponseCode(701);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USERNAME_ALREADY_EXIST'));
			return false;
		}

		$query="SELECT id
				FROM `#__users`
				WHERE email='".str_replace("\n","",trim($post['email']))."'";
		$this->db->setQuery($query);
		if($this->db->loadResult() > 0){ // check if email id already exist
			IJReq::setResponseCode(702);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EMAIL_ALREADY_EXIST'));
			return false;
		}

		if($Full_flag!= 1 && strtolower(IJOOMER_GC_REGISTRATION)=== 'jomsocial' && file_exists(JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' .DS. 'core.php')){
			require_once JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' .DS. 'core.php'; // include jomsocial core file from library

			if($post['type']>0){
				$query="SELECT cp.id,cp.name,cf.fieldcode,cf.options,cpf.parent,cpf.field_id,cf.id as id,cf.type,cf.name,cf.required,cf.registration,cf.published,cf.tips
	 					FROM `#__community_profiles` AS cp,`#__community_profiles_fields` AS cpf,`#__community_fields` AS cf
	 					WHERE cp.id=cpf.parent
	 					AND cp.id={$post['type']}
	 					AND cf.registration=1
	 					AND cf.published=1
	 					AND cpf.field_id=cf.id
	 					order by cf.`ordering`,cpf.field_id";
			}else{
				$query="SELECT *
						FROM `#__community_fields`
						WHERE published=1 and registration=1
						order by `ordering`";
			}
			$this->db->setQuery($query);
			$fields = $this->db->loadObjectList();

			$inc=-1;
			$incj=null;
			if(count($fields)>0){
				$jsonarray['code']=200;
				$jsonarray['full'] = 1;

				foreach($fields as $field){
					if($field->type=='group'){
						$inc++;
						$jsonarray['fields']['group'][$inc]['group_name']=$field->name;
						$incj = 0;
					}else{
						if($incj===null){
							$incj=0;
						}
						$jsonarray['fields']['group'][$inc]['field'][$incj]['id'] = $field->id;
						$jsonarray['fields']['group'][$inc]['field'][$incj]['caption'] = $field->name;
						//$jsonarray['fields']['group'][$inc]['field'][$incj]['fieldcode'] = $field->fieldcode;
						$jsonarray['fields']['group'][$inc]['field'][$incj]['required'] = $field->required;
						$jsonarray['fields']['group'][$inc]['field'][$incj]['value'] = '';

						if($field->type == 'birthdate'){
							$field->type = "date";
						}

						if($field->type == 'checkbox' || $field->type == 'list'){
							$field->type = "multipleselect";
						}

						if($field->type == 'singleselect' || $field->type == 'radio' || $field->type == 'country' || $field->type == 'gender'){
							$field->type = 'select';
						}

						if($field->type == 'email' || $field->type == 'url'){
							$field->type = 'text';
						}

						if($field->fieldcode == 'FIELD_CITY' || $field->fieldcode == 'FIELD_STATE'){
							$field->type = 'map';
						}

						$jsonarray['fields']['group'][$inc]['field'][$incj]['type'] = $field->type;
						if(isset($field->options) and !empty($field->options)){
							$option = explode("\n",$field->options);
							foreach($option as $i=>$val){
								$jsonarray['fields']['group'][$inc]['field'][$incj]['options'][$i]['value']=JText::_($val);
							}
						}
						$jsonarray['fields']['group'][$inc]['field'][$incj]['privacy']['value']= 0;
						$jsonarray['fields']['group'][$inc]['field'][$incj]['privacy']['options']= array(
																											0=>array('value'=>0,'caption'=>'Public'),
																											1=>array('value'=>20,'caption'=>'Site Members'),
																											2=>array('value'=>30,'caption'=>'Friend'),
																											3=>array('value'=>40,'caption'=>'Only Me'));
						$incj++;
					}
				}
				return $jsonarray;
			}
		}

		$params	= JComponentHelper::getParams('com_users');
		$system	= $params->get('new_usertype', 2);
		$useractivation = $params->get('useractivation');
		$sendpassword = $params->get('sendpassword', 1);

		$user = new JUser; // Initialise the table with JUser.
		$post['name'] = trim(str_replace("\n","",$post['relname']));
		$post['username'] = trim(str_replace("\n","",$post['username']));
		$post['password'] = $post['password1'] = $post['password2'] = trim(str_replace("\n","",$post['password']));
		$post['email'] = $post['email1'] = $post['email2'] = trim(str_replace("\n","",$post['email']));
		$post['groups'][0]=$system;
		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2)) {
			$post['activation'] = JApplication::getHash(JUserHelper::genRandomPassword());
			$post['block'] = 1;
		}
		$user->bind($post);

		if(!$user->save()){
			IJReq::setResponseCode(500);
			return false;
		}

		$aclval = $user->id;
		if(!$aclval){
			IJReq::setResponseCode(500);
			return false;
		}

		// Compile the notification mail values.
		$data = $user->getProperties();
		$config = JFactory::getConfig();
		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::root();

		// Handle account activation/confirmation emails.
		if ($useractivation == 2){
			// Set the link to confirm the user email.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			if ($sendpassword){
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			}else{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username']
				);
			}
		}elseif ($useractivation == 1){
			// Set the link to activate the user account.
			$uri = JURI::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			if ($sendpassword){
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username'],
					$data['password_clear']
				);
			}else{
				$emailBody = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
					$data['name'],
					$data['sitename'],
					$data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'],
					$data['siteurl'],
					$data['username']
				);
			}
		}else{
			$emailSubject	= JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBody = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_BODY',
				$data['name'],
				$data['sitename'],
				$data['siteurl']
			);
		}

		// Send the registration email.
		$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

		//Send Notification mail to administrators
		if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1)) {
			$emailSubject = JText::sprintf(
				'COM_USERS_EMAIL_ACCOUNT_DETAILS',
				$data['name'],
				$data['sitename']
			);

			$emailBodyAdmin = JText::sprintf(
				'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
				$data['name'],
				$data['username'],
				$data['siteurl']
			);

			// get all admin users
			$query='SELECT name, email, sendEmail
					FROM #__users
					WHERE sendEmail=1';
			$this->db->setQuery( $query );
			$rows = $this->db->loadObjectList();

			// Send mail to all superadministrators id
			foreach( $rows as $row ){
				$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

				// Check for an error.
				if ($return !== true) {
					IJReq::setResponseCode(500);
					IJReq::setResponseMessage(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));
					return false;
				}
			}
		}

		// Check for an error.
		if ($return !== true) {
			// Send a system message to administrators receiving system mails
			$query="SELECT id
					FROM #__users
					WHERE block = 0
					AND sendEmail = 1";
			$this->db->setQuery($query);
			$sendEmail = $this->db->loadColumn();
			if (count($sendEmail) > 0) {
				$jdate = new JDate();
				// Build the query to add the messages
				$query="INSERT INTO {$this->db->quoteName('#__messages')} ({$this->db->quoteName('user_id_from')}, {$this->db->quoteName('user_id_to')}, {$this->db->quoteName('date_time')}, {$this->db->quoteName('subject')}, {$this->db->quoteName('message')}) VALUES ";
				$messages = array();

				foreach ($sendEmail as $userid) {
					$messages[] = "({$userid}, {$userid}, '{$jdate->toSql()}', '".JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
				}
				$query .= implode(',', $messages);
				$this->db->setQuery($query);
				$this->db->query();
			}
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));
			return false;
		}

		//if community installed
		if(strtolower(IJOOMER_GC_REGISTRATION)==='jomsocial' && file_exists(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php')){
			//require_once JPATH_ROOT. DS . 'components' . DS . 'com_community' . DS . 'helpers' .DS. 'image.php';
			jimport('joomla.filesystem.file');
			jimport('joomla.utilities.utility');
			CFactory::load( 'helpers' , 'image' );

			$my			= CFactory::getUser($aclval);
			$userid		= $my->id;
			$config			= CFactory::getConfig();
			$uploadLimit	= (double) $config->get('maxuploadsize');
			$uploadLimit	= ( $uploadLimit * 1024 * 1024 );

			$file = JRequest::getVar('image','','FILES','array');

			if(IJ_JOMSOCIAL_VERSION == 1.6 || IJ_JOMSOCIAL_VERSION == 1.8){
				// @rule: Limit image size based on the maximum upload allowed.
				if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 ){
					IJReq::setResponseCode(416);
					IJReq::setResponseMessage(JText::_('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED'));
					return false;
				}
				//if( !CImageHelper::isValidType( $file['type'] ) )
				if( !cValidImageType( $file['type'] ) ){
					IJReq::setResponseCode(415);
					IJReq::setResponseMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'));
					return false;
	           	}

				//if( !CImageHelper::isValid($file['tmp_name'] ) )
				if( !cValidImage($file['tmp_name'] ) ){
					IJReq::setResponseCode(415);
					IJReq::setResponseMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'));
					return false;
				}else{
					// @todo: configurable width?
					$imageMaxWidth	= 160;

					$lang =& JFactory::getLanguage();
					$lang->load('com_community');

					$profileType = isset($post['type']) ? $post['type'] : 0;

					$fileName		= JApplication::getHash( $file['tmp_name'] . time() );
					$hashFileName	= JString::substr( $fileName , 0 , 24 );

					$storage = JPATH_ROOT . DS . 'images' . DS . 'avatar';
					$storageImage	= $storage . DS . $hashFileName . cImageTypeToExt( $file['type'] );
					$storageThumbnail = $storage . DS . 'thumb_' . $hashFileName . cImageTypeToExt( $file['type'] );
					$image	= 'images/avatar/' . $hashFileName . cImageTypeToExt( $file['type'] );
					$thumbnail = 'images/avatar/' . 'thumb_' . $hashFileName . cImageTypeToExt( $file['type'] );

					$userModel = CFactory::getModel( 'user' );

					// Generate full image
					if(!cImageResizePropotional( $file['tmp_name'] , $storageImage , $file['type'] , $imageMaxWidth ) ){
						IJReq::setResponseCode(500);
						return false;
					}

					// Generate thumbnail
					if(!cImageCreateThumb( $file['tmp_name'] , $storageThumbnail , $file['type'] )){
						IJReq::setResponseCode(500);
						return false;
					}

					$userModel->setImage( $userid , $image , 'avatar' );
					$userModel->setImage( $userid , $thumbnail , 'thumb' );

					// Update the user object so that the profile picture gets updated.
					$my->set( '_avatar' , $image );
					$my->set( '_thumb'	, $thumbnail );
				}

				if(isset($post['type']) && ($post['type']) > 0) {
					$query="SELECT cf.fieldcode,cfd.field_id AS id
							FROM #__community_profiles_fields as cfd
							LEFT JOIN #__community_fields as cf ON cf.id = cfd.field_id
					     	WHERE cf.type != 'group'
					     	AND cfd.parent=".$post['type'];
				}else{
					$query="SELECT fieldcode,id
							FROM `#__community_fields`
							WHERE published=1
							AND registration=1";
				}
				$this->db->setQuery($query);
				$fields = $this->db->loadObjectList();

				foreach($fields as $field){
					$fid=$field->id;
					$fvalue=IJReq::getTaskData('f'.$fid,'');
					$query="INSERT INTO #__community_fields_values
							SET user_id='{$userid}', field_id='{$fid}', value='".addslashes($fvalue)."'";
					$this->db->setQuery($query);
					$this->db->query();
				}
			}else{
				// @rule: Limit image size based on the maximum upload allowed.
				if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 ){
					IJReq::setResponseCode(416);
					IJReq::setResponseMessage(JText::_('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED'));
					return false;
				}

				if( !CImageHelper::isValidType( $file['type'] ) ){
					IJReq::setResponseCode(416);
					IJReq::setResponseMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'));
					return false;
	           	}

	           	if( !CImageHelper::isValid($file['tmp_name'] ) ){
					IJReq::setResponseCode(416);
					IJReq::setResponseMessage(JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'));
					return false;
				}else{
					$config				= CFactory::getConfig();
					$imageMaxWidth		= 160;
					$profileType		= isset($post['type']) ? $post['type'] : 0;
					$fileName			= JApplication::getHash( $file['tmp_name'] . time() );
					$hashFileName		= JString::substr( $fileName , 0 , 24 );
					$multiprofile		= & JTable::getInstance( 'MultiProfile' , 'CTable' );
					$multiprofile->load( $profileType );
					$useWatermark		= $profileType != COMMUNITY_DEFAULT_PROFILE && $config->get('profile_multiprofile') && !empty( $multiprofile->watermark ) ? true : false;
					$storage			= JPATH_ROOT . DS . $config->getString('imagefolder') . DS . 'avatar';
					$storageImage		= $storage . DS . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$storageThumbnail	= $storage . DS . 'thumb_' . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$image				= $config->getString('imagefolder') . '/avatar/' . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$thumbnail			= $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$userModel			= CFactory::getModel( 'user' );

					// Only resize when the width exceeds the max.
					if( !CImageHelper::resizeProportional( $file['tmp_name'] , $storageImage , $file['type'] , $imageMaxWidth ) ){
						IJReq::setResponseCode(500);
						IJReq::setResponseMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE' , $storageImage));
						return false;
					}

					// Generate thumbnail
					if(!CImageHelper::createThumb( $file['tmp_name'] , $storageThumbnail , $file['type'] )){
						IJReq::setResponseCode(500);
						IJReq::setResponseMessage(JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE' , $storageImage));
						return false;
					}

					if( $useWatermark ){
						// @rule: Before adding the watermark, we should copy the user's original image so that when the admin tries to reset the avatar,
						// it will be able to grab the original picture.
						JFile::copy( $storageImage , JPATH_ROOT . DS . 'images' . DS . 'watermarks' . DS . 'original' . DS . md5( $my->id . '_avatar' ) . CImageHelper::getExtension( $file['type'] ) );
						JFile::copy( $storageThumbnail , JPATH_ROOT . DS . 'images' . DS . 'watermarks' . DS . 'original' . DS . md5( $my->id . '_thumb' ) . CImageHelper::getExtension( $file['type'] ) );

						$watermarkPath	= JPATH_ROOT . DS . JString::str_ireplace('/' , DS , $multiprofile->watermark);

						list( $watermarkWidth , $watermarkHeight )	= getimagesize( $watermarkPath );
						list( $avatarWidth , $avatarHeight ) 		= getimagesize( $storageImage );
						list( $thumbWidth , $thumbHeight ) 			= getimagesize( $storageThumbnail );

						$watermarkImage		= $storageImage;
						$watermarkThumbnail	= $storageThumbnail;

						// Avatar Properties
						$avatarPosition	= CImageHelper::getPositions( $multiprofile->watermark_location , $avatarWidth , $avatarHeight , $watermarkWidth , $watermarkHeight );

						// The original image file will be removed from the system once it generates a new watermark image.
						CImageHelper::addWatermark( $storageImage , $watermarkImage , 'image/jpg' , $watermarkPath , $avatarPosition->x , $avatarPosition->y );

						//Thumbnail Properties
						$thumbPosition	= CImageHelper::getPositions( $multiprofile->watermark_location , $thumbWidth , $thumbHeight , $watermarkWidth , $watermarkHeight );

						// The original thumbnail file will be removed from the system once it generates a new watermark image.
						CImageHelper::addWatermark( $storageThumbnail , $watermarkThumbnail , 'image/jpg' , $watermarkPath , $thumbPosition->x , $thumbPosition->y );

						$my->set( '_watermark_hash' , $multiprofile->watermark_hash );
						if(!$my->save()){
							IJReq::setResponseCode(500);
							return false;
						}
					}
					$userModel->setImage( $userid , $image , 'avatar' );
					$userModel->setImage( $userid , $thumbnail , 'thumb' );

					// Update the user object so that the profile picture gets updated.
					$my->set( '_avatar' , $image );
					$my->set( '_thumb'	, $thumbnail );
				}

				if(isset($post['type']) && ($post['type']) > 0){
					$query="SELECT cf.fieldcode,cfd.field_id AS id
							FROM #__community_profiles_fields as cfd
							LEFT JOIN #__community_fields as cf ON cf.id = cfd.field_id
					     	WHERE cf.type != 'group'
					     	AND cfd.parent=".$post['type'];
				}else{
					$query="SELECT fieldcode,id
							FROM `#__community_fields`
							WHERE published=1
							AND registration=1";
				}
				$this->db->setQuery($query);
				$fields = $this->db->loadObjectList();

				foreach($fields as $field){
					$fid=$field->id;
					$fvalue=IJReq::getTaskData('f'.$fid,'');
					$query="INSERT INTO #__community_fields_values
							SET user_id='{$userid}', field_id='{$fid}', value='".addslashes($fvalue[0])."', access='{$fvalue[1]}'";
					$this->db->setQuery($query);
					$this->db->query();
				}
			}
		}

		// store kunena profile in kunena table...
		if(strtolower(IJOOMER_GC_REGISTRATION)=== 'kunena'){
			$file = JRequest::getVar('image', '', 'files', 'array');
			$ext = JFile::getExt($file['name']);
			$commanpath = JPATH_SITE.DS.'media'.DS.'kunena'.DS.'avatars'.DS;
			$filename = 'users'.DS.'avatar'.$aclval.'.'.$ext;
			$thumbArray = array('resized'.DS.'size36'=>36,'resized'.DS.'size72'=>72,'resized'.DS.'size90'=>90,'resized'.DS.'size144'=>144,'resized'.DS.'size200'=>200);

			if(JFile::upload($file['tmp_name'], $commanpath.$filename)){
				$image=new SimpleImage();
				foreach($thumbArray AS $path => $size){
					copy($commanpath.$filename,$commanpath.$path.$filename);
					$image->load($commanpath.$path.$filename);
					$width=$image->getWidth();
					$height=$image->getheight();
					if($width>$height){
						$image->resizeToWidth($size);
					}else{
						$image->resizeToHeight($size);
					}
					$image->save($commanpath.$path.$filename);
				}
			}

			$query="INSERT INTO #__kunena_users
					SET userid='{$aclval}', avatar='{$filename}'";
			$this->db->setQuery($query);
			$this->db->query();
		}

		$jsonarray['code'] = 200;
		return $jsonarray;
	}

	/**
	 * @uses function to request token to reset password
	 *
	 */
	function retriveToken() {
		$email = IJReq::getTaskData('email');

		jimport('joomla.mail.helper');
		jimport('joomla.user.helper');

		if (!JMailHelper::isEmailAddress($email)){ // Make sure the e-mail address is valid
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_EMAIL'));
			return false;
		}

		// Build a query to find the user
		$query="SELECT id FROM #__users
				WHERE email={$this->db->Quote($email)}
				AND block=0";
		$this->db->setQuery($query);
		if (!($id = $this->db->loadResult())){ // check if user exist of given email
			IJReq::setResponseCode(401);
			return false;
		}

		if(IJ_JOOMLA_VERSION===1.5){
			$token = JApplication::getHash(JUserHelper::genRandomPassword()); // Generate a new token
			$salt = JUserHelper::getSalt('crypt-md5');
			$hashedToken = md5($token.$salt).':'.$salt;
		}else{
			$token = JApplication::getHash(JUserHelper::genRandomPassword()); // Set the confirmation token.
			$salt = JUserHelper::getSalt('crypt-md5');
			$hashedToken = md5($token.$salt).':'.$salt;
		}

		$query="UPDATE #__users
				SET activation={$this->db->Quote($hashedToken)}
				WHERE id={$id} AND block = 0";
		$this->db->setQuery($query);
		if (!$this->db->query()){ // Save the token
			IJReq::setResponseCode(500);
			return false;
		}

		if (!$this->_sendConfirmationMail($email, $token)){ // Send the token to the user via e-mail
			IJReq::setResponseCode(500);
			return false;
		}

		$jsonarray['code'] = 200;
		return $jsonarray;
	}

	/**
	 * @uses function to validate token against username
	 *
	 */
	function validateToken(){
		$token = IJReq::getTaskData('token');
		$username = IJReq::getTaskData('username');

		jimport('joomla.user.helper');

		if(strlen($token) != 32){
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_TOKEN'));
			return false;
		}

		$query='SELECT id, activation
				FROM #__users
				WHERE block = 0
				AND username = '.$this->db->Quote($username);
		$this->db->setQuery($query);
		if (!($row = $this->db->loadObject())){ // Verify the token
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_TOKEN'));
			return false;
		}

		$parts	= explode( ':', $row->activation );
		$crypt	= $parts[0];

		if (!isset($parts[1])){
			IJReq::setResponseCode(401);
			return false;
		}
		$salt	= $parts[1];
		$testcrypt = JUserHelper::getCryptedPassword($token, $salt);
		// Verify the token
		if (!($crypt == $testcrypt)){
			IJReq::setResponseCode(401);
			return false;
		}

		// Push the token and user id into the session
		$jsonarray['code'] = 200;
		$jsonarray['userid'] = $row->id;
		$jsonarray['crypt'] = $crypt.':'.$salt;
		return $jsonarray;
	}

	/**
	 * @uses function is used to reset password
	 *
	 */
	function resetPassword(){
		$token = IJReq::getTaskData('crypt');
		$userid = IJReq::getTaskData('userid',0,'int');
		$password1 = IJReq::getTaskData('password');

		// Make sure that we have a pasword
		if(!$token || !$userid || !$password1){
			IJReq::setResponseCode(400);
			return false;
		}

		jimport('joomla.user.helper');

		// Get the necessary variables
		$salt		= JUserHelper::genRandomPassword(32);
		$crypt		= JUserHelper::getCryptedPassword($password1, $salt);
		$password	= $crypt.':'.$salt;

		$user = new JUser($userid); // Get the user object

		JPluginHelper::importPlugin('user'); // Fire the onBeforeStoreUser trigger
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeStoreUser', array($user->getProperties(), false));

		$query="UPDATE #__users
				SET password={$this->db->Quote($password)}, activation=''
				WHERE id={$userid}
				AND activation={$this->db->Quote($token)}
				AND block = 0";
		$this->db->setQuery($query);
		if (!$result = $this->db->query()){ // Save the password
			IJReq::setResponseCode(500);
			return false;
		}

		// Update the user object with the new values.
		$user->password			= $password;
		$user->activation		= '';
		$user->password_clear	= $password1;

		if(IJ_JOOMLA_VERSION===1.5){
			$dispatcher->trigger('onAfterStoreUser', array($user->getProperties(), false, $result,'')); // Fire the onAfterStoreUser trigger
		}else{
			$app	= JFactory::getApplication();
			if(!$user->save(true)){
				IJReq::setResponseCode(500);
				return false;
			}
			// Flush the user data from the session.
			$app->setUserState('com_users.reset.token', null);
			$app->setUserState('com_users.reset.user', null);
		}

		$jsonarray['code'] = 200;
		return $jsonarray;
	}

	/**
	 * @uses function use to retrive userid
	 *
	 */
	function retriveUsername() {
		$email = IJReq::getTaskData('email');

		jimport('joomla.mail.helper');
		jimport('joomla.user.helper');

		// Make sure the e-mail address is valid
		if (!JMailHelper::isEmailAddress($email)){
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_INVALID_TOKEN'));
			return false;
		}

		// Build a query to find the user
		$query="SELECT *
				FROM #__users
				WHERE email={$this->db->Quote($email)}
				AND block = 0";
		$this->db->setQuery($query);
		$user = $this->db->loadObject();

		// Set the e-mail parameters
		$lang =& JFactory::getLanguage();
		$lang->load('com_users');
		$config	= JFactory::getConfig();

		// Assemble the login link.
		include_once(JPATH_ROOT.DS.'components'.DS.'com_users'.DS.'helpers'.DS.'route.php');
		$itemid = UsersHelperRoute::getLoginRoute();
		$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
		$link	= 'index.php?option=com_users&view=login'.$itemid;
		$mode	= $config->get('force_ssl', 0) == 2 ? 1 : -1;

		$data = JArrayHelper::fromObject($user);
		$fromname	= $config->get('fromname');
		$mailfrom	= $config->get('mailfrom');
		$sitename	= $config->get('sitename');
		$link_text	= JRoute::_($link, false, $mode);
		//$link_html	= JRoute::_($link, true, $mode);
		$username = $data['username'];
		$subject = JText::sprintf('COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT',$sitename);
		$body = JText::sprintf('COM_USERS_EMAIL_USERNAME_REMINDER_BODY',$sitename,$username,$link_text);

		// Send the token to the user via e-mail
		$return = JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $body);
		if (!$return){
			IJReq::setResponseCode(500);
			return false;
		}

		$jsonarray['code'] = 200;
		return $jsonarray;
	}

	function _sendConfirmationMail($email, $token){
		$config		= &JFactory::getConfig();

		if(IJ_JOOMLA_VERSION===1.5){
			$url		= JRoute::_('index.php?option=com_user&view=reset&layout=confirm',true,-1);
			$sitename	= $config->getValue('sitename');

			// Set the e-mail parameters
			$lang =& JFactory::getLanguage();
			$lang->load('com_user');

			$from		= $config->getValue('mailfrom');
			$fromname	= $config->getValue('fromname');
			$subject	= sprintf(JText::_('PASSWORD_RESET_CONFIRMATION_EMAIL_TITLE'), $sitename);
			$body		= sprintf(JText::_( 'PASSWORD_RESET_CONFIRMATION_EMAIL_TEXT'), $sitename, $token, $url);

			// Send the e-mail
			if (!JUtility::sendMail($from, $fromname, $email, $subject, $body)){
				return false;
			}
		}else{
			// Set the e-mail parameters
			$lang =& JFactory::getLanguage();
			$lang->load('com_users');
			include_once(JPATH_ROOT.DS.'components'.DS.'com_users'.DS.'helpers'.DS.'route.php');


			$mode = $config->get('force_ssl', 0) == 2 ? 1 : -1;
			$itemid = UsersHelperRoute::getLoginRoute();
			$itemid = $itemid !== null ? '&Itemid='.$itemid : '';
			$link = 'index.php?option=com_users&view=reset&layout=confirm'.$itemid;

			$fromname	= $config->get('fromname');
			$mailfrom	= $config->get('mailfrom');
			$sitename	= $config->get('sitename');
			$link_text	= JRoute::_($link, false, $mode);

			$subject = JText::sprintf('COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT',$sitename);
			$body = JText::sprintf('COM_USERS_EMAIL_PASSWORD_RESET_BODY',$sitename,$token,$link_text);

			// Send the password reset request email.
			$return = JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $body);
			if (!$return) {
				return false;
			}
		}
		return true;
	}
}