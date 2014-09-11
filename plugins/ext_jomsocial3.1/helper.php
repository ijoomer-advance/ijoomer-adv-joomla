<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : Jomsocial_1.5 (compatible with Jomsocial 3.0)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );
class jomHelper{
	private $date_now;
	private $IJUserID;
	private $mainframe;
	private $db;
	private $my;
	private $config;

	function __construct(){
        $this->date_now		=	JFactory::getDate();
		$this->mainframe	=	& JFactory::getApplication();
		$this->db			=	& JFactory::getDBO(); // set database object
		$this->IJUserID		=	$this->mainframe->getUserState('com_ijoomeradv.IJUserID', 0); //get login user id
		$this->my			=	CFactory::getUser($this->IJUserID); // set the login user object
		$this->config		=	CFactory::getConfig();
	}

	function getName($obj){
		if(method_exists($obj,'getDisplayName')){
			$name = $obj->getDisplayName();
		}else{

			$name=($this->config->get('displayname')=='username') ? $obj->username : $obj->name;
		}
		return $name;
	}

	function isconnected($id1, $id2){
		if(($id1 == $id2) && ($id1 != 0))
			return true;

		if($id1 == 0 || $id2 == 0)
			return false;

		$query="SELECT count(*)
				FROM #__community_connection
				WHERE `connect_from`='{$id1}'
				AND `connect_to`='{$id2}'
				AND `status` = 1";
		$this->db->setQuery($query);
		$result = $this->db->loadResult();
		return $result;
	}

	function isMember($id1=0){
		if($id1 == 0)
			return false;

		$query="SELECT count(*)
				FROM #__community_users
				WHERE `userid`='{$id1}'";
		$this->db->setQuery($query);
		$result = $this->db->loadResult();
		return $result;
	}

	function getjomsocialversion(){

		/*$parser		=& JFactory::getXMLParser('Simple');
		$xml		= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_community'.DS.'community.xml';
		$parser->loadFile( $xml );
		$doc		=& $parser->document;
		$element	=& $doc->getElementByPath( 'version' );
		return	$version= $element->data();*/

		$xmlfile		= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_community'.DS.'community.xml';
		$xml = JFactory::getXML($xmlfile,1);
		$version = (string)$xml->version;
		return	$version;
	}

	function getNotificationParams($userid = 0){
		if($userid==0){
			$user = JFactory::getUser();
			$userid = $user->id;
		}

		$query="SELECT *
				FROM #__ijoomeradv_users
				WHERE `userid`='{$userid}'";
		$this->db->setQuery($query);
		$row = $this->db->loadObject();

		$result = array();
		if(!isset($row->jomsocial_params) || $row->jomsocial_params == ""){
			$result['pushFriendOnline'] = 1;
			$result['pushInboxMessage'] = 1;
			$result['pushFriendRequest'] = 1;
		}else{
			$array = explode("\n",$row->jomsocial_params);

			foreach($array as $r){
				$var = explode("=",$r);
				if(count($var)>1)
					$result[$var[0]] = (int) $var[1];
			}
		}
		return $result;
	}

	function GetLatLong($addrss='',$city='', $state='', $country=''){
		$q_array = array();
		$address = urlencode($addrss);

		if(trim($address)!='')
			$q_array[] = $address;
		if(trim($city)!='')
			$q_array[] = $city;
		if(trim($state)!='')
			$q_array[] = $state;
		if(trim($country)!='')
			$q_array[] = $country;

		$q = implode("+",$q_array);
		$myKey = GOOGLEAPI;

		$url="http://maps.google.com/maps/geo?q={$q}&output=json&oe=utf8&sensor=true_or_false&key={$myKey}";

		$init = curl_init();
		curl_setopt($init, CURLOPT_URL, $url);
		curl_setopt($init, CURLOPT_HEADER,0);
		curl_setopt($init, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($init);
	    curl_close($init);

	    $l = ",";
	 	if(!empty($response)){
			$arr = json_decode($response, true);
			if(is_array($arr)){
				if($arr['Status']['code']==200){
					$l = implode(',',$arr['Placemark'][0]['Point']['coordinates']);
					if(trim($country)!=""){
						foreach($arr['Placemark'] As $placemark){
							if($country==$placemark['AddressDetails']['Country']['CountryName']){
								$l = implode(',',$placemark['Point']['coordinates']);
								break;
							}
						}
					}
				}
			}
	    }
		return $l;
	}

	// Send Push Notification In Android
	function googleAuthenticate($username, $password,  $service) {
	    // get an authorization token
	    $ch = curl_init();
	    if(!$ch){
	    	return false;
	    }

		curl_setopt($ch, CURLOPT_URL, "https://www.google.com/accounts/ClientLogin");
	  	$post_fields = array ( "Email" => $username, "Passwd" => $password, "accountType"=>"GOOGLE", "service" => $service );
	    curl_setopt($ch, CURLOPT_HEADER, true);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	    $response = curl_exec($ch);

	    curl_close($ch);
		if (strpos($response, '200 OK') === false) {
	        return false;
	    }

	    // find the auth code
	    preg_match("/(Auth=)([\w|-]+)/", $response, $matches);

	    if (!$matches[2]) {
	        return false;
	    }

	    return $matches[2];
	}

	function sendMessageToAndroid($authCode, $deviceRegistrationId, $msgType, $messageText,$totMsg='',$whentype) {
		if(!empty($authCode) && !empty($deviceRegistrationId)){
			$headers = array('Authorization: GoogleLogin auth=' . $authCode);
			$data = array(
	            'registration_id' => $deviceRegistrationId,
	            'collapse_key' =>  $msgType,
		    	'data.type' => $whentype,
		    	'data.totalcount' =>$totMsg,
		    	'data.badge' => 1,
	            'data.message' => $messageText //TODO Add more params with just simple data instead
	        );
	        $ch = curl_init();

	        curl_setopt($ch, CURLOPT_URL, 'https://android.apis.google.com/c2dm/send');
	        if ($headers){
	            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	        }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($ch, CURLOPT_POST, true);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        	$response = curl_exec($ch);
        	curl_close($ch);
			return true;
		} else {
			return false;
		}
    }

	// send push notification code start here
	function send_push_notification($device_token, $message='',$badge = 1,$type=''){
		$server = 'ssl://gateway.push.apple.com:2195';
		if(PUSH_SERVER=='1')
			$server = 'ssl://gateway.sandbox.push.apple.com:2195';
		$keyCertFilePath = JPATH_SITE.DS.'components'.DS.'com_ijoomeradv'.DS.'certificates'.DS.'certificates.pem';

		$sound = 'default';
		// Construct the notification payload
		$badge = (int) $badge;
		$body = array();
		$body['aps'] = array('alert' => $message);
		if ($badge)
		$body['aps']['badge'] = $badge;
		if ($sound)
		$body['aps']['sound'] = $sound;
		if($type!='')
		$body['aps']['type'] = $type;

		/* End of Configurable Items */
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);

		// assume the private key passphase was removed.
		//stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);

		$fp = stream_socket_client($server, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
		// for production change the server to ssl://gateway.push.apple.com:219

		if (!$fp){
			//print "Failed to connect $err $errstr\n";
			return;
		}
		//
		//$payload = '{"aps": {"badge": 1, "alert": "Hello from iJoomer!", "sound": "cow","type":"online"}}';//json_encode($body);
		$payload = json_encode($body);

		$msg = chr(0) . pack("n",32) . pack('H*', str_replace(' ', '', $device_token)) . pack("n",strlen($payload)) . $payload;
		fwrite($fp, $msg);
		fclose($fp);
	}

	function updateLatLong($uid=0,$lat=255,$long=255){
		$db =& JFactory::getDBO();
		if($uid==0)
			return false;

		$query="UPDATE #__community_users
				SET `latitude`='{$lat}', `longitude`='{$long}'
				WHERE `userid`='{$uid}'";
		$this->db->setQuery($query);
		$this->db->Query();
	}

	// get location from lat, long.
	function getaddress($lattitude,$longitude){
		$address = '';
		if($lattitude!='' && $longitude!=''){
			CFactory::load('helpers', 'remote');
			$url = 'http://maps.google.com/maps/api/geocode/json?latlng='.urlencode($lattitude.",".$longitude) .'&sensor=false';
			$content = CRemoteHelper::getContent($url);
			$status = null;
			if(!empty($content)){
				require_once (JPATH_SITE.DS.'plugins'.DS.'system'.DS.'azrul.system'.DS.'pc_includes'.DS.'JSON.php');
				$json = new Services_JSON();
				$data = $json->decode($content);

				if ($data->status == 'OK'){
					$address = $data->results[0]->formatted_address;
				}
			}
		}
		return $address;
	}

	// get title from location.
	function gettitle($location){
		if($location!=''){
			//
			CFactory::load('helpers', 'remote');
			$url = 'http://maps.google.com/maps/api/geocode/json?address='.urlencode($location) .'&sensor=false';
			$content = CRemoteHelper::getContent($url);

			$status = null;
			if(!empty($content)){
				require_once (JPATH_SITE.DS.'plugins'.DS.'system'.DS.'azrul.system'.DS.'pc_includes'.DS.'JSON.php');
				$json = new Services_JSON();
				$data = $json->decode($content);
				if ($data->status == 'OK'){
					$address = $data->results[0]->address_components;
					foreach($address as $adKe=>$adVal){

						if($adVal->types[0] == 'route' || $adVal->types[0] == 'neighborhood' || $adVal->types[0] == 'sublocality' || $adVal->types[0] == 'locality' || $adVal->types[0] == 'administrative_area_level_1'){
							$locality[] = $adVal->long_name;
						}if($adVal->types[0] == 'country'){
							$locality1 = $adVal->long_name;
						}
					}
					$title = $locality;
					$title[] = $locality1;
					if(count($title)){
						$add=implode(', ',$title);
						return  addslashes($add);
                	}else{
                		return '';
					}

				}else{
					return '';
				}
			}else{
				return '';
			}
		}else{
				return '';
			}
	}

	function timeLapse($date){
		jimport('joomla.utilities.date');
		require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'helpers'.DS.'string.php');
		$now = new JDate();
		$dateDiff = CTimeHelper::timeDifference($date->toUnix(), $now->toUnix());

		if( $dateDiff['days'] > 0){
			$lapse = JText::sprintf( (CStringHelper::isPlural($dateDiff['days'])) ? 'COM_COMMUNITY_LAPSED_DAY_MANY':'COM_COMMUNITY_LAPSED_DAY', $dateDiff['days']);
		}elseif( $dateDiff['hours'] > 0){
			$lapse = JText::sprintf( (CStringHelper::isPlural($dateDiff['hours'])) ? 'COM_COMMUNITY_LAPSED_HOUR_MANY':'COM_COMMUNITY_LAPSED_HOUR', $dateDiff['hours']);
		}elseif( $dateDiff['minutes'] > 0){
			$lapse = JText::sprintf( (CStringHelper::isPlural($dateDiff['minutes'])) ? 'COM_COMMUNITY_LAPSED_MINUTE_MANY':'COM_COMMUNITY_LAPSED_MINUTE', $dateDiff['minutes']);
		}else {
			if( $dateDiff['seconds'] == 0){
				$lapse = JText::_('COM_COMMUNITY_ACTIVITIES_MOMENT_AGO');
			}else{
				$lapse = JText::sprintf( (CStringHelper::isPlural($dateDiff['seconds'])) ? 'COM_COMMUNITY_LAPSED_SECOND_MANY':'COM_COMMUNITY_LAPSED_SECOND', $dateDiff['seconds']);
			}
		}
		return $lapse;
	}

	function getDate( $str = '',$off=0 ){
		require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');

		$extraOffset	= $this->config->get('daylightsavingoffset');
		//convert to utc time first.
		$utc_date	= new JDate($str);
		$date        = new JDate($utc_date->toUnix() + $off * 3600);

		$my		=& JFactory::getUser();
		$cMy	= CFactory::getUser();

		//J1.6 returns timezone as string, not integer offset.
		if(method_exists('JDate','getOffsetFromGMT')){
			$systemOffset = new JDate('now',$this->mainframe->getCfg('offset'));
			$systemOffset = $systemOffset->getOffsetFromGMT(true);
		} else {
			$systemOffset = $this->mainframe->getCfg('offset');
		}

		if(!$my->id){
			$date->setTimezone($systemOffset + $extraOffset);
		} else{
			if(!empty($my->params)){
				$pos = JString::strpos($my->params, 'timezone');

				$offset = $systemOffset + $extraOffset;
				if ($pos === false) {
				   $offset = $systemOffset + $extraOffset;
				} else {
					$offset 	= $my->getParam('timezone', -100);

					$myParams	= $cMy->getParams();
					$myDTS		= $myParams->get('daylightsavingoffset');
					$cOffset	= (! empty($myDTS)) ? $myDTS : $this->config->get('daylightsavingoffset');

					if($offset == -100)
						$offset = $systemOffset + $extraOffset;
					else
						$offset = $offset + $cOffset;
				}
				$date->setTimezone($offset);
			} else
				$date->setTimezone($systemOffset + $extraOffset);
		}

		return $date;
	}

	function showDate($time, $mode = 'datetime_today', $tz = 'kunena', $offset=null) {
		require_once (JPATH_SITE.DS.'components'.DS.'com_kunena'.DS.'lib'.DS.'kunena.timeformat.class.php');

		$date = JFactory::getDate ( $time );

		if ($offset === null || strtolower ($tz) != 'utc') {
			$offset = JFactory::getUser()->getParam('timezone', $this->mainframe->getCfg ( 'offset', 0 ));
		}
		if (is_numeric($offset)) {
			$date->setTimezone($offset);
		} else {
			// Joomla 1.6 support
			$offset = new DateTimeZone($offset);
			$date->setTimezone($offset);
		}
		if ($date->toFormat('%Y')<1902) return JText::_('COM_KUNENA_DT_DATETIME_UNKNOWN');

		$modearr = explode ( '_', $mode );

		switch (strtolower ( $modearr [0] )) {
			case 'none' :
				return '';
			case 'time' :
				$usertime_format = JText::_('COM_KUNENA_DT_TIME_FMT');
				$today_format = JText::_('COM_KUNENA_DT_TIME_FMT');
				$yesterday_format = JText::_('COM_KUNENA_DT_TIME_FMT');
				break;
			case 'date' :
				$usertime_format = JText::_('COM_KUNENA_DT_DATE_FMT');
				$today_format = JText::_('COM_KUNENA_DT_DATE_TODAY_FMT');
				$yesterday_format = JText::_('COM_KUNENA_DT_DATE_YESTERDAY_FMT');
				break;
			case 'ago' :
				return CKunenaTimeformat::showTimeSince ( $date->toUnix() );
				break;
			case 'datetime':
				$usertime_format = JText::_('COM_KUNENA_DT_DATETIME_FMT');
				$today_format = JText::_('COM_KUNENA_DT_DATETIME_TODAY_FMT');
				$yesterday_format = JText::_('COM_KUNENA_DT_DATETIME_YESTERDAY_FMT');
				break;
			default:
				$usertime_format = $mode;
				$today_format = $mode;
				$yesterday_format = $mode;

		}

		// Today and Yesterday?
		if ($modearr [count ( $modearr ) - 1] == 'today') {
			$now = JFactory::getDate ( 'now' );
			$now = @getdate ( $now->toUnix() );
			$then = @getdate ( $date->toUnix() );

			// Same day of the year, same year.... Today!
			if ($then ['yday'] == $now ['yday'] &&
				$then ['year'] == $now ['year'])
				$usertime_format = $today_format;

			// Day-of-year is one less and same year, or it's the first of the year and that's the last of the year...
			if (($then ['yday'] == $now ['yday'] - 1 && $then ['year'] == $now ['year']) ||
				($now ['yday'] == 0 && $then ['year'] == $now ['year'] - 1) && $then ['mon'] == 12 && $then ['mday'] == 31)
				$usertime_format = $yesterday_format;
		}

		return $date->toFormat ( $usertime_format, true );
	}

	/**
	 * @uses to get the notification count for logged in user
	 *
	 */
	function getNotificationCount(){
		// get message notification count
		$query="SELECT count(b.`id`)
				FROM #__community_msg_recepient as a, #__community_msg as b
				WHERE a.`to` = {$this->IJUserID}
				AND `is_read` = 0
				AND a.`deleted` = 0
				AND b.`id` = a.`msg_id`";
		$this->db->setQuery($query);
		$unreadInbox = $this->db->loadResult();
		if((int)$unreadInbox>0){
	 		$jsonarray['notification']['messageNotification']=intval($unreadInbox);
		}

		// getting pending friend request count
		$friendModel=& CFactory::getModel('friends');
		$pendingFren	= $friendModel->countPending($this->IJUserID);
		$jsonarray['notification']['friendNotification']=intval($pendingFren);

		// get globaknotification
		$eventModel		= CFactory::getModel( 'events' );
        $groupModel		= CFactory::getModel( 'groups' );

		$frenHtml			= '';
		$ind				= 0;
		$globalinvItation	= 0;

		//getting pending event request
		$pendingEvent	= $eventModel->getPending($this->IJUserID);
		$globalinvItation += count($pendingEvent);
        //getting pending group request
        $pendingGroup   = $groupModel->getGroupInvites($this->IJUserID);
        $globalinvItation += count($pendingGroup);
		//geting pending private group join request
		//Find Users Groups Admin
		$allGroups = $groupModel->getAdminGroups( $this->IJUserID , COMMUNITY_PRIVATE_GROUP);
		$globalinvItation += count($allGroups);
		//non require action notification
		CFactory::load('helpers','content');
		$notifCount = 5;
		$notificationModel	= CFactory::getModel( 'notification' );
		$myParams			=&	$this->my->getParams();

		//$notifications = $notificationModel->getNotificationCount($this->IJUserID,'0',$myParams->get('lastnotificationlist',''));
		$sinceWhere = '';
		$type = 0;
		$since = $myParams->get('lastnotificationlist','');
		if(!empty($since)){
			$sinceWhere = ' AND ' . $this->db->quoteName('created') . ' >= ' . $this->db->Quote($since);
		}

		$query	= 'SELECT COUNT(*)  FROM '. $this->db->quoteName('#__community_notifications') . ' AS a '
				. 'WHERE a.'.$this->db->quoteName('target').'=' . $this->db->Quote( $this->IJUserID )
				. $sinceWhere
				. ' AND a.'.$this->db->quoteName('type').'=' . $this->db->Quote( $type )
				. ' AND a.'.$this->db->quoteName('cmd_type').'!='.$this->db->Quote('notif_inbox_create_message');
		$this->db->setQuery( $query );
		$total	= $this->db->loadResult();
		$globalinvItation += $total;

		$jsonarray['notification']['globalNotification']=intval($globalinvItation);

		return $jsonarray;
	}

	/**
	 * Like an item. Update ajax count
	 * @param string $element   Can either be core object (photo/album/videos/profile/profile.status) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 * @filesource com_community/controllers/system.php
	 * @method ajaxLike
	 *
	 */
	function Like( $element, $itemId ){
		$filter = JFilterInput::getInstance();
		$element = $filter->clean($element, 'string');
		$itemId = $filter->clean($itemId, 'int');

		if (!COwnerHelper::isRegisteredUser()){
			IJReq::setResponse(401); // if user is not logged in or not registered one.
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$like	=   new CLike();

		if($element=='groups.discussion' || $element=='groups.discussion.reply' || $element=='photos.album' || $element=='albums' || $element=='photos.wall.create' || $element== 'cover.upload'){
			$act =& JTable::getInstance('Activity', 'CTable');
			$act->load($itemId);
			$itemId=$act->like_id;
		}else{
			if( !$like->enabled($element) ){
				IJReq::setResponse(500); // if element on which like applied is not enabled/bloked to like.
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$like->addLike( $element, $itemId ); // add like

		// Send push notification params
		if($element=='profile'){
			$userid=$itemId;
		}else {
			$act =& JTable::getInstance('Activity', 'CTable');
			$act->load($itemId);
			$userid = $act->actor;
		}

		//===========================================================
		//Send push notification
		$sendpushflag = false;
		switch($element){
			case 'photo':
				$photo			=& JTable::getInstance( 'Photo' , 'CTable' );
				$photo->load( $itemId );
				if($photo->id){
					CFactory::load ( 'helpers', 'group' );
					$album = & JTable::getInstance ( 'Album', 'CTable' );
					$album->load ( $photo->albumid );
					$pushcontentdata['albumdetail']['id'] = $album->id;
					$pushcontentdata['albumdetail']['deleteAllowed'] = intval ( ($photo->creator == $album->creator or COwnerHelper::isCommunityAdmin($photo->creator)));
					if($photo->creator == $album->creator){
						$uid=0;
					}else{
						$uid=$album->creator;
					}
					$pushcontentdata['albumdetail']['user_id'] = $uid;
					$pushcontentdata['photodetail']['id'] = $photo->id;
					$pushcontentdata['photodetail']['caption'] = $photo->caption;

					$p_url = JURI::base ();
					if ($photo->storage == 's3') {
						$s3BucketPath = $this->config->get ( 'storages3bucket' );
						if (! empty ( $s3BucketPath ))
							$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
					} else {
						if (! file_exists ( JPATH_SITE . DS . $photo->image ))
							$photo->image = $photo->original;
					}
					$pushcontentdata['photodetail']['thumb'] = $p_url . $photo->thumbnail;
					$pushcontentdata['photodetail']['url'] = $p_url . $photo->image;
					if (SHARE_PHOTOS == 1) {
						$pushcontentdata['photodetail']['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$userId}&albumid={$albumID}#photoid={$photo->id}";
					}

					//likes
					$likes = $this->getLikes ( 'photo', $photo->id, $this->IJUserID );
					$pushcontentdata['photodetail']['likes'] = $likes->likes;
					$pushcontentdata['photodetail']['dislikes'] = $likes->dislikes;
					$pushcontentdata['photodetail']['liked'] = $likes->liked;
					$pushcontentdata['photodetail']['disliked'] = $likes->disliked;

					//comments
					$count = $this->getCommentCount ( $photo->id, 'photos' );
					$pushcontentdata['photodetail']['commentCount'] = $count;

					$query = "SELECT count(id)
							FROM #__community_photos_tag
							WHERE `photoid`={$photo->id}";
					$this->db->setQuery ( $query );
					$count = $this->db->loadResult ();
					$pushcontentdata['photodetail']['tags'] = $count;
					$pushcontentdata['type'] = 'photos';

					$query="SELECT `jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid`={$photo->creator}";
					$this->db->setQuery($query);
					$puser=$this->db->loadObject();
					$ijparams = new CParameter($puser->jomsocial_params);
					if($ijparams->get('pushnotif_photos_like')==1 && $photo->creator!=$this->IJUserID && !empty($puser)){
						$sendpushflag = true;
						$usr=$this->getUserDetail($this->IJUserID);
						$search = array('{actor}','{photo}');
						$replace = array($usr->name,JText::_('COM_COMMUNITY_SINGULAR_PHOTO'));
						$message = str_replace($search,$replace,JText::_('COM_COMMUNITY_PHOTO_LIKE_EMAIL_SUBJECT'));
					}
				}
				break;
			case 'album':
				/*$album	=& JTable::getInstance( 'Album' , 'CTable' );
				$album->load( $itemId );
				if($album->id){
					$query="SELECT `jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid`={$album->creator}";
					$this->db->setQuery($query);
					$puser=$this->db->loadObjectList();
					$ijparams = new CParameter($puser->jomsocial_params);
					if($ijparams->get('pushnotif_videos_like')==1 && $album->creator!=$this->IJUserID && !empty($puser)){
						$sendpushflag = true;

						$usr=$this->getUserDetail($this->IJUserID);
						$search = array('{actor}','{album}');
						$replace = array($usr->name,$album->title);
						$message = str_replace($search,$replace,JText::_('COM_COMMUNITY_ALBUM_LIKE_EMAIL_SUBJECT'));
					}
				}*/
				break;
			case 'videos':
				$video			=& JTable::getInstance( 'Video' , 'CTable' );
				$video->load( $itemId );
				if($video->id){
					$video_file = $video->path;
					$p_url = JURI::root ();
					if ($video->type == 'file') {
						$ext = JFile::getExt ( $video->path );

						if ($ext == 'mov' && file_exists ( JPATH_SITE . DS . $video->path )) {
							$video_file = JURI::root () . $video->path;
						} else {
							$lastpos = strrpos ( $video->path, '.' );

							$vname = substr ( $video->path, 0, $lastpos );

							if ($video->storage == 's3') {
								$s3BucketPath = $this->config->get ( 'storages3bucket' );
								if (! empty ( $s3BucketPath ))
									$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
							}
							$video_file = $p_url . $vname . ".mp4";
						}
					}

					$pushcontentdata['id'] = $video->id;
					$pushcontentdata['caption'] = $video->title;
					$pushcontentdata['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components' . DS . 'com_community' . DS . 'assets' . DS . 'video_thumb.png';
					$pushcontentdata['url'] = $video_file;
					$pushcontentdata['description'] = $video->description;
					$pushcontentdata['date'] = $this->timeLapse ( $this->getDate ( $video->created ) );
					$pushcontentdata['location'] = $video->location;
					$pushcontentdata['permissions'] = $video->permissions;
					$pushcontentdata['categoryId'] = $video->category_id;

					$usr = $this->getUserDetail ( $video->creator );
					$pushcontentdata['user_id'] = 0;
					$pushcontentdata['user_name'] = $usr->name;
					$pushcontentdata['user_avatar'] = $usr->avatar;
					$pushcontentdata['user_profile'] = $usr->profile;

					//likes
					$likes = $this->getLikes ( 'videos', $video->id, $this->IJUserID );
					$pushcontentdata['likes'] = $likes->likes;
					$pushcontentdata['dislikes'] = $likes->dislikes;
					$pushcontentdata['liked'] = $likes->liked;
					$pushcontentdata['disliked'] = $likes->disliked;

					//comments
					$count = $this->getCommentCount ( $video->id, 'videos' );
					$pushcontentdata['commentCount'] = $count;
					$pushcontentdata['deleteAllowed'] = intval(($video->creator or COwnerHelper::isCommunityAdmin($video->creator)));
					if (SHARE_VIDEOS) {
						$pushcontentdata['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
					}
					$pushcontentdata['type'] = 'videos';

					$query="SELECT `jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid`={$video->creator}";
					$this->db->setQuery($query);
					$puser=$this->db->loadObject();
					$ijparams = new CParameter($puser->jomsocial_params);

					if($ijparams->get('pushnotif_videos_like')==1 && $video->creator!=$this->IJUserID && !empty($puser)){
						$sendpushflag = true;

						$usr=$this->getUserDetail($this->IJUserID);
						$search = array('{actor}','{video}');
						$replace = array($usr->name,$video->title);
						$message = str_replace($search,$replace,JText::_('COM_COMMUNITY_VIDEO_LIKE_EMAIL_SUBJECT'));
					}
				}
				break;
			case 'profile':
				$profile = CFactory::getUser($itemId);
				if($profile->id){
					$query="SELECT `jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid`={$profile->id}";
					$this->db->setQuery($query);
					$puser=$this->db->loadObject();
					$ijparams = new CParameter($puser->jomsocial_params);
					if($ijparams->get('pushnotif_profile_like')==1 && $profile->id!=$this->IJUserID && !empty($puser)){
						$sendpushflag = true;

						$usr=$this->getUserDetail($this->IJUserID);
						$message = str_replace('{actor}',$usr->name,JText::_('COM_COMMUNITY_PROFILE_LIKE_EMAIL_SUBJECT'));
					}
				}
				break;
			case 'profile.status':
				$stream			=& JTable::getInstance( 'Activity' , 'CTable' );
				$stream->load( $itemId );

				if($stream->id){
					$profile = CFactory::getUser($stream->actor);
					$query="SELECT `jomsocial_params`,`device_token`,`device_type`
							FROM #__ijoomeradv_users
							WHERE `userid`={$profile->id}";
					$this->db->setQuery($query);
					$puser=$this->db->loadObject();
					$ijparams = new CParameter($puser->jomsocial_params);

					if($ijparams->get('pushnotif_profile_stream_like')==1 && $profile->id!=$this->IJUserID && !empty($puser)){
						$sendpushflag = true;

						$usr=$this->getUserDetail($this->IJUserID);
						$search = array('{actor}','{stream}');
						$replace = array($usr->name,JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
						$message = str_replace($search,$replace,JText::_('COM_COMMUNITY_PROFILE_STREAM_LIKE_EMAIL_SUBJECT'));
					}
				}
				break;
		}

		if($sendpushflag){
			if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
				$options=array();
				$options['device_token']=$puser->device_token;
				$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
				$options['aps']['message']=$message;
				$options['aps']['type']=$element;
				$options['aps']['content_data']=$pushcontentdata;
				IJPushNotif::sendIphonePushNotification($options);
			}

			if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
				$options=array();
				$options['registration_ids']=array($puser->device_token);
				$options['data']['message']=$message;
				$options['data']['type']=($element == 'photo')?'photos':$element;
				$options['data']['content_data']=$pushcontentdata;
				IJPushNotif::sendAndroidPushNotification($options);
			}
		}

		return true;
	}

	/**
	 * Dislike an item
	 * @param string $element   Can either be core object (photo/album/videos/profile/profile.status) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 * @filesource com_community/controllers/system.php
	 * @method ajaxDislike
	 *
	 */
	function Dislike( $element, $itemId ){
		$filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

		if (!COwnerHelper::isRegisteredUser()){
			IJReq::setResponse(401); // if user is not logged in or not registered one.
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$dislike   =   new CLike();

		if($element=='groups.discussion' || $element=='groups.discussion.reply' || $element=='photos.album'){
			$act =& JTable::getInstance('Activity', 'CTable');
			$act->load($itemId);
			$itemId=$act->like_id;
		}else{
			if( !$dislike->enabled($element) ){
				IJReq::setResponse(500); // if element on which like applied is not enabled/bloked to like.
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$dislike->addDislike( $element, $itemId );
		return true;
	}

	/**
	 * Unlike an item
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 * @filesource com_community/controllers/system.php
	 * @method ajaxDislike
	 *
	 */
	function Unlike( $element, $itemId ){
		$filter = JFilterInput::getInstance();
        $itemId = $filter->clean($itemId, 'int');
        $element = $filter->clean($element, 'string');

		if (!COwnerHelper::isRegisteredUser()){
			IJReq::setResponse(401); // if user is not logged in or not registered one.
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$unlike	    =   new CLike();

		if($element=='groups.discussion' || $element=='groups.discussion.reply' || $element=='photos.album' || $element=='albums' || $element=='photos.wall.create' || $element== 'cover.upload' ){
			$act =& JTable::getInstance('Activity', 'CTable');
			$act->load($itemId);
			$itemId=$act->like_id;
		}else{
			if( !$unlike->enabled($element) ){
				IJReq::setResponse(500); // if element on which like applied is not enabled/bloked to like.
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$unlike->unlike( $element, $itemId );
		return true;
	}


	/**
	 * get like details
	 *
	 * @param string $element   Can either be core object (photo/album/videos/profile/profile.status) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 * @param mixed $userId
	 *
	 */
	function getLikes($element,$itemId,$userId){
		require_once (JPATH_SITE.DS.'components'.DS.'com_community'.DS.'tables'.DS.'like.php');
		$like	=&  JTable::getInstance( 'Like' , 'CTable' );
		$like->loadInfo($element, $itemId);
		CFactory::load('libraries','like');
		$likes=new CLike();
		$result->userLiked	    	= $likes->userLiked($element,$itemId,$userId);
		$result->likesInArray	    = array();
		$result->dislikesInArray    = array();
		$result->likes		    	= 0;
		$result->dislikes	    	= 0;
		$result->liked				= intval($result->userLiked > 0);
		$result->disliked			= intval(!$result->userLiked > 0);

		if( !empty ($like->like) ) {
			$result->likesInArray	=   explode( ',', trim( $like->like, ',' ) );
			$result->likes			=	count( $result->likesInArray );
		}

		if( !empty ($like->dislike) ) {
			$result->dislikesInArray    =   explode( ',', trim( $like->dislike, ',' ) );
			$result->dislikes	    	=	count( $result->dislikesInArray );
		}
		return $result;
	}


	/**
	 * This function returns the user permission over friend permission
	 *
	 * @param $userID : the user who will be affected by the user permission.
	 * @param $friendID : the user who set the permission.
	 *
	 */
	function getUserAccess($userID=null,$friendID=null){
		$userID = (isset($userID) && $userID) ? $userID : $this->IJUserID;
		$friendID = (isset($friendID) && $friendID) ? $friendID : $this->IJUserID;
		$user = CFactory::getUser($userID);
		$access_limit = 0;

		if($user->id > 0){
			$access_limit = PRIVACY_MEMBERS; // access level for member
		}

		$isfriend = $user->isFriendWith($friendID);
		if($isfriend){
			$access_limit = PRIVACY_FRIENDS; // access level for friends
		}

		if($friendID == $this->IJUserID && $user->id != 0){
			$access_limit = PRIVACY_PRIVATE; // access level for private
		}
		return $access_limit;
	}


	/**
	 * This function returns comment count
	 *
	 * @param $uniqueID : id of the element.
	 * @param $type : type of the comment. // videos, albums, photos, profile.status,
	 *
	 */
	function getCommentCount($uniqueID,$type){
		$query="SELECT COUNT(*)
				FROM {$this->db->quoteName('#__community_wall')}
				WHERE {$this->db->quoteName('contentid')}={$this->db->Quote($uniqueID)}
				AND {$this->db->quoteName( 'type' )}={$this->db->Quote($type)}";
 		$this->db->setQuery($query);
 		$count	= $this->db->loadResult();
 		return $count;
	}

	/**
	 * This function only retrieve following details
	 * id, name, avatar, profile
	 */
	function getUserDetailMini($userID,$frontUser=NULL)
	{
		$userObj = CFactory::getUser ( $userID);

		$query = 'SELECT c.userid,u.name,c.avatar,c.params FROM `#__community_users` as c JOIN `#__users` as u '
			.' ON u.id=c.userid WHERE u.id = '.$userID;
		$this->db->setQuery($query);
		$user_detail = $this->db->loadObject();

		$frontUser = ($frontUser) ? $frontUser : $this->IJUserID;
		//get storage path
		if($this->config->get('user_avatar_storage') == 'file'){
			$p_url	= JURI::base();
		}else{
			$s3BucketPath = $this->config->get('storages3bucket');
			if(!empty($s3BucketPath))
				$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
			else
				$p_url	= JURI::base();
		}

		// get access level and profile view permission.
		$params	= new JRegistry($user_detail->params);
		$access_limit = $this->getUserAccess($frontUser,$userObj->_userid);
		$profileview = $params->get('privacyProfileView'); // get profile view access

		$user = new stdClass();
		$user->id			= ($this->IJUserID == $userObj->id) ? 0 : intval($userObj->id);
		$user->name			= $user_detail->name;
		$user->avatar		= ($user_detail->avatar) ? $p_url.$user_detail->avatar : JURI::base().'components/com_community/assets/user_thumb.png';
		$user->profile		= ($profileview==40 OR $profileview>$access_limit) ? 0 : 1;
		return $user;
	}

	/**
	 * This function is use to get user details
	 */
	function getUserDetail($userID,$frontUser=NULL){
		$userObj = CFactory::getUser ( $userID);
		$frontUser = ($frontUser) ? $frontUser : $this->IJUserID;

		//get storage path
		if($this->config->get('user_avatar_storage') == 'file'){
			$p_url	= JURI::base();
		}else{
			$s3BucketPath = $this->config->get('storages3bucket');
			if(!empty($s3BucketPath))
				$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
			else
				$p_url	= JURI::base();
		}

		// get access level and profile view permission.
		$params	=& $userObj->getParams();
		$access_limit = $this->getUserAccess($frontUser,$userObj->_userid);
		$profileview = $params->get('privacyProfileView'); // get profile view access

		//get latitude longitude
		if($userObj->latitude != '255' && $userObj->longitude != '255' && $userObj->latitude != '' && $userObj->longitude != ''){
			$latitude = $userObj->latitude;
			$longitude = $userObj->longitude;
		}else{
			$query="SELECT *
					FROM #__community_fields_values as cfv
					LEFT JOIN #__community_fields as cf ON cfv.field_id=cf.id
					WHERE cfv.user_id={$userID}";
			$this->db->setQuery($query);
			$user_detail = $this->db->loadObjectList();

			if($user_detail){
				foreach($user_detail as $detail){
					$addrss		= ($detail->fieldcode == $this->config->get('fieldcodestreet')) ? $detail->value : '';
					$city		= ($detail->fieldcode == $this->config->get('fieldcodecity')) ? $detail->value : '';
					$state		= ($detail->fieldcode == $this->config->get('fieldcodestate')) ? $detail->value : '';
					$country	= ($detail->fieldcode == $this->config->get('fieldcodecountry')) ? $detail->value : '';
				}
			}else{
				$addrss	= $city = $state = $country	= '';
			}
			$latlong = $this->GetLatLong($addrss,$city,$state, $country);
			$value = explode(',',$latlong);
			$latitude = $value[1];
			$longitude = $value[0];
			$this->updateLatLong($userID,$latitude,$longitude);
		}

		$user = new stdClass();
		$user->id			= ($this->IJUserID == $userObj->id) ? 0 : intval($userObj->id);
		$user->name			= $this->getName($userObj);
		$user->status		= $userObj->_status;
		$user->avatar		= $userObj->getAvatar();//($userObj->_avatar) ? $p_url.$userObj->_avatar : JURI::base().'components/com_community/assets/user_thumb.png';
		$user->latitude		= $latitude;
		$user->longitude	= $longitude;
		$user->online		= ($userObj->_isonline != '') ? 1 : 0 ;
		$user->profile		= ($profileview==40 OR $profileview>$access_limit) ? 0 : 1;
		$user->view			= $userObj->_view;
		$user->cover		= $userObj->getCover();
		$user->points  		= $userObj->_points;
		return $user;
	}

	public function getTitleTag($html_data){
		$titletag = isset($html_data->title) ? $html_data->title : '';
		$user = CFactory::getUser($html_data->actor);
		$username = $user->getDisplayName();
		$param = new JRegistry($html_data->params);
		$action = $param->get('action');

		switch ($html_data->app){
			case 'friends.connect':
					$user1 = CFactory::getUser($act->actor);
					$user2 = CFactory::getUser($act->target);

					$my = CFactory::getUser();
					$you = null;
					$other = null;

					if($my->id == $user1->id) {
						$you = $user1;
						$other = $user2;
					}

					if($my->id == $user2->id) {
						$you = $user2;
						$other = $user1;
					}

					if(!is_null($you)){
						$titletag= JText::sprintf('COM_COMMUNITY_STREAM_MY_FRIENDS', $other->getDisplayName(), CUrlHelper::userLink($other->id));
					}else{
						$titletag= JText::sprintf('COM_COMMUNITY_STREAM_OTHER_FRIENDS', $user1->getDisplayName(),$user2->getDisplayName(), CUrlHelper::userLink($user1->id), CUrlHelper::userLink($user2->id));
					}
				break;

			case 'profile.avatar.upload':
					$titletag=  $username.JText::_('COM_COMMUNITY_ACTIVITIES_NEW_AVATAR');
				break;

			case 'photos':
					if($param->get('style') == COMMUNITY_STREAM_STYLE || strpos($html_data->title, '{multiple}') ){
						$count = $param->get('count', 1);
						if(CStringHelper::isPlural($count)){
							$titletag = $username.JText::sprintf( 'COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE_MANY' , $count, '', CStringHelper::escape($html_data->album->name) );
						}else{
							$titletag = $username.JText::sprintf( 'COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE' , '', CStringHelper::escape($html_data->album->name) );;
						}
					}
				break;

			case 'photos.comment';
					$photo = JTable::getInstance('Photo','CTable');
					$photo->load($html_data->cid);
					$titletag = $username.' '.JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_PHOTO', $photo->getPhotoLink(), $photo->caption);
				break;

			case 'events':
					$event = JTable::getInstance('Event', 'CTable');
					$event->load($html_data->eventid);
					$actors = $param->get('actors');

					$titletag = $username.JText::sprintf('COM_COMMUNITY_EVENTS_ACTIVITIES_NEW_EVENT' , CUrlHelper::eventLink($event->id), $event->title);
				break;

			case 'events.attend':
					$event = JTable::getInstance('Event', 'CTable');
					$event->load($html_data->eventid);
					if($action == 'events.attendence.attend'){
						$actors = $param->get('actors');
						$users = explode(',', $actors);
						foreach ($users as $actor){
							if (!$actor) {
								$actor = $html_data->actor;
							}
							$user = CFactory::getUser($actor);
							$actorsHTML[] = $user->getDisplayName();
						}
						$titletag = implode(', ', $actorsHTML).JText::sprintf('COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND' , $event->getLink(), $event->title);
					}
				break;

			case 'videos':
					$titletag = CVideos::getActivityTitleHTML($html_data);
				break;

			case 'groups':
			case 'groups.join':
			case 'groups.discussion':
			case 'groups.discussion.reply':
					$group = JTable::getInstance('Group', 'CTable');
					$group->load($html_data->groupid);
					$actors = $param->get('actors');

					switch ($action){
						case 'group.create':
							$titletag = $username.JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP' , $group->getLink(), $group->name);
							break;

						case 'group.join':
							$users = explode(',', $actors);
							foreach($users as $actor) {
								$user = CFactory::getUser($actor);
								$actorsHTML[] = $user->getDisplayName();
							}
							$users = implode(', ', $actorsHTML);
							$titletag = $users.JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_JOIN' , $group->getLink(), $group->name);
							break;

						case 'group.discussion.create':
						case 'group.discussion.reply':
							$config = CFactory::getConfig();
							$discussion = JTable::getInstance('Discussion' , 'CTable' );
							$discussion->load($html_data->cid);
							$discussionLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id );

							$titletag = $username;
							$titletag .= ($action == 'group.discussion.create')?JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION' , $discussionLink, $discussion->title):JText::sprintf('COM_COMMUNITY_GROUPS_REPLY_DISCUSSION' , CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$discussion->groupid.'&topicid='.$discussion->id), $discussion->title );
							$titletag .= '➜'.$group->name."\n";
							$titletag .= JHTML::_('string.truncate', ''/*$discussion->message*/, $config->getInt('streamcontentlength'), true, false );
							break;
					}
				break;

			case 'groups.bulletin':
					$this->db->setQuery('SELECT `name` FROM `#__community_groups` WHERE id = '.$html_data->groupid);
					$groupName = $this->db->loadResult();
					$config = CFactory::getConfig();
					$bulletin = JTable::getInstance('Bulletin', 'CTable');
					$bulletin->load($html_data->cid);

					$titletag = $username;
					$titletag .= JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_NEWS' , CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $html_data->groupid . '&bulletinid=' . $bulletin->id ), $bulletin->title );
					$titletag .= '➜'.$groupName."\n";
					$titletag .= JHTML::_('string.truncate', $bulletin->message, $config->getInt('streamcontentlength'), true, false );
				break;
			case 'groups.update':
					$this->db->setQuery('SELECT `name` FROM `#__community_groups` WHERE id = '.$html_data->groupid);
					$groupName = $this->db->loadResult();
					$group = JTable::getInstance('Group', 'CTable');
					$group->load($html_data->groupid);
					$titletag = JText::sprintf('COM_COMMUNITY_GROUPS_GROUP_UPDATED' , CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$html_data->groupid ) , $groupName );
			break;


			case 'albums.comment':
			case 'albums':
					$album	= JTable::getInstance( 'Album' , 'CTable' );
					$album->load( $html_data->cid );
					$wall = JTable::getInstance('Wall', 'CTable');
					$wall->load($param->get('wallid'));

					$titletag = $users.JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_ALBUM', CRoute::_($album->getURI()), $album->name);
				break;

			case 'system.message':
			case 'system.videos.popular':
			case 'system.photos.popular':
			case 'system.members.popular':
			case 'system.photos.total':
			case 'system.groups.popular':
			case 'system.members.registered':
				switch ($action){
					case 'registered_users':
						$usersModel   = CFactory::getModel( 'user' );
						$now          = new JDate();
						$date         = CTimeHelper::getDate();

						$users 			 = $usersModel->getUserRegisteredByMonth($now->format('Y-m'));
						$totalRegistered = count($users); //$usersModel->getTotalRegisteredByMonth($now->format('Y-m'));

						$titletag	= JText::_('COM_COMMUNITY_TOTAL_USERS_REGISTERED_THIS_MONTH');
						$titletag   .= "\n".JText::sprintf('COM_COMMUNITY_TOTAL_USERS_REGISTERED_THIS_MONTH_ACTIVITY_TITLE',$totalRegistered,$date->monthToString($now->format('%m')));
						break;

					case 'total_photos':
						$photosModel = CFactory::getModel( 'photos' );
						$total       = $photosModel->getTotalSitePhotos();
						$titletag = JText::sprintf('COM_COMMUNITY_TOTAL_PHOTOS_ACTIVITY_TITLE', CRoute::_('index.php?option=com_community&view=photos') ,$total);
						break;

					case 'top_videos':
						$titletag = JText::_('COM_COMMUNITY_ACTIVITIES_TOP_VIDEOS');
						break;

					case 'top_photos':
						$titletag = JText::_('COM_COMMUNITY_ACTIVITIES_TOP_PHOTOS');
						break;

					case 'top_users':
						$titletag = JText::_('COM_COMMUNITY_ACTIVITIES_TOP_MEMBERS');
						break;

					case 'top_groups':
						$groupsModel = CFactory::getModel('groups');
						$activeGroup = $groupsModel->getMostActiveGroup();

						if(is_null($activeGroup)) {
							$titletag 	= JText::_('COM_COMMUNITY_GROUPS_NONE_CREATED');
						} else {
							$titletag	= JText::sprintf('COM_COMMUNITY_MOST_POPULAR_GROUP_ACTIVITY_TITLE', CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$activeGroup->id), $activeGroup->name);

						}
						break;

					case 'message':
						$titletag	= CActivities::format($html_data->title);
						break;
				}
				break;

			case 'profile.like':
			case 'groups.like':
			case 'events.like':
			case 'photo.like':
			case 'videos.like':
			case 'album.like':
					$param = new CParameter($html_data->params);
					$actors = $param->get('actors');
					$user 		= CFactory::getUser($html_data->actor);
					$users 		= explode(',', $actors);
					$userCount 	= count($users);
					switch($html_data->app){
						case 'profile.like':
							$cid 		= CFactory::getUser($html_data->cid);
							$urlLink 	= CUrlHelper::userLink($cid->id);
							$name 		= $cid->getDisplayName();
							$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PROFILE';
						break;
						case 'groups.like':
							$cid = JTable::getInstance('Group', 'CTable');
							$cid->load($html_data->groupid);
							$urlLink 	= $cid->getLink();
							$name 		= $cid->name;
							$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_GROUP';
						break;
						case 'events.like':
							$cid = JTable::getInstance('Event','CTable');
							$cid->load($html_data->eventid);
							$urlLink 	= $cid->getLink();
							$name 		= $cid->title;
							$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_EVENT';
						case 'photo.like':
							$cid = JTable::getInstance('Photo','CTable');
							$cid->load($html_data->cid);

							$urlLink 	= $cid->getPhotoLink();
							$name 		= $cid->caption;
							$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_PHOTO';
						break;
						case 'videos.like':
							$cid = JTable::getInstance('Video','CTable');
							$cid->load($html_data->cid);

							$urlLink 	= $cid->getViewURI();
							$name 		= $cid->getTitle();
							$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_VIDEO';
						break;
						case 'album.like':
							$cid = JTable::getInstance('Album','CTable');
							$cid->load($html_data->cid);

							$urlLink 	= $cid->getURI();
							$name 		= $cid->name;
							$element	= 'COM_COMMUNITY_STREAM_LIKES_ELEMENT_ALBUM';
						break;
					}

					foreach($users as $actor) {
						$user = CFactory::getUser($actor);
						$actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName().'</a>';
					}

					$others = '';
					if($userCount > 2)
					{
						$others = JText::sprintf('COM_COMMUNITY_STREAM_OTHERS_JOIN_GROUP' , $userCount-1);
					}
					$jtext =($userCount>1) ? 'COM_COMMUNITY_STREAM_LIKES_PLURAL' : 'COM_COMMUNITY_STREAM_LIKES_SINGULAR';

					$titletag = implode( ' '. JText::_('COM_COMMUNITY_AND') . ' ' , $actorsHTML).$others.JText::sprintf($jtext,$urlLink,$name,JText::_($element)) ;
				break;

			case 'cover.upload':
				$user 	= CFactory::getUser($html_data->actor);
				//$params = new JRegistry($html_data->params);
				$params = new CParameter($html_data->params);
				$type 	= $params->get('type');

				//$type = "event";
				$extraMessage = '';
				if(strtolower($type) !=='profile')
				{
					$id = $type.'id';
					$cTable = JTable::getInstance(ucfirst($type),'CTable');
					$cTable->load($html_data->$id);

					//$act =& JTable::getInstance('Activity', 'CTable');
					//$act->load($itemId);

					if($type == 'group')
					{
						$extraMessage = JText::sprintf('COM_COMMUNITY_PHOTOS_COVER_TYPE_LINK' , $cTable->getLink(), $cTable->name);
					}
					if($type == 'event')
					{
						$extraMessage = JText::sprintf('COM_COMMUNITY_PHOTOS_COVER_TYPE_LINK' , CUrlHelper::eventLink($cTable->id), $cTable->title);
					}
				}

				$titletag = $user->getDisplayName().' '.JText::sprintf('COM_COMMUNITY_PHOTOS_COVER_UPLOAD',strtolower($type)).$extraMessage;
				break;

		}
		return trim(strip_tags($titletag));
	}

	public function getAlbumContent($html_data) {
		$db = JFactory::getDBO();
		$param = new CParameter($html_data->params);
		$photoid = $param->get('photoid', false);
		$count = $param->get('count', 1);

		$photos	=& JTable::getInstance( 'photo' , 'CTable' );
		$photos->load($photoid);

		$album	=& JTable::getInstance( 'Album' , 'CTable' );
		$album->load($photos->albumid);

		if($count == 1 && !empty($html_data->title)){
			$sql = "SELECT * FROM #__community_photos WHERE `albumid`=".$album->id
				  ." AND `id`=" . $photoid ;
			$db->setQuery($sql);
			$photoresult = $db->loadObjectList();
		} else {
			$sql = "SELECT * FROM #__community_photos WHERE `albumid`=".$album->id
				  ." ORDER BY `id` DESC LIMIT 0, $count";
			$db->setQuery($sql);
			$photoresult = $db->loadObjectList();
		}

		$photos = array();
		foreach($photoresult as $row)
		{
			$photo	= JTable::getInstance( 'Photo' , 'CTable' );
			$photo->bind($row);
			$photos[] = $photo;
		}

		return $photos;
	}

	public function getVideos($html_data){
		$video = array();
		if($html_data->app == 'videos'){
			$data = CVideos::getActivityTitleHTML($html_data);
			$video['video_icon'] = JUri::base().$html_data->video->thumb;
			$video['video_path'] = $html_data->video->path;
		}
		return $video;
	}

	public function uploadAudioFile(){
		jimport('joomla.filesystem.file');

		$audiofile = JRequest::getVar('voice', null, 'files', 'array');
		$randomname = 'ijoomeradv_'.substr(md5(microtime()),rand(0,26),5);

		$filename = JFile::makeSafe($audiofile['name']);
		$fileext= strtolower(JFile::getExt($filename));
		$src	= $audiofile['tmp_name'];
		$dest3gp= JPATH_COMPONENT_SITE.DS.'assets'.DS.'voice'.DS.$randomname.'.'.$fileext;
		$destmp3= JPATH_COMPONENT_SITE.DS.'assets'.DS.'voice'.DS.$randomname.'.mp3';

		if($fileext == '3gp' or $fileext == 'aac' or $fileext == 'm4a'){
			if(JFile::upload($src, $dest3gp)){
				$cmd = 'ffmpeg -i '.$dest3gp.' -acodec mp3 '.$destmp3.'|ffmpeg -i '.$dest3gp.' -sameq '.$destmp3;
				shell_exec($cmd);
				$durationresult = shell_exec("ffmpeg -i ".$destmp3.' 2>&1 | grep -o \'Duration: [0-9:.]*\'');
    			$duration 		= explode(':',str_replace('Duration: ','',$durationresult));
    			$minute = $duration[1];
    			$sec 	= explode('.',$duration[2]);

    			$voicefiletext = $randomname.'.'.$fileext;
    			//$durationtext = $minute.':'.$sec[0];
    			$durationtext = (($minute*60)+$sec[0]);

    			$fileinfo['voicetext'] 	=  '{voice}'.$voicefiletext.'&'.$durationtext.'{/voice}';
    			$fileinfo['voice3gppath']= $this->addAudioFile('{voice}'.$voicefiletext.'&'.$durationtext.'{/voice}');
				return $fileinfo;
			}else{
				//TODO File not uploded sucessfully
				return false;
			}
		}else{
			//TODO bad extension for file uppload
			return false;
		}
	}

	public function addAudioFile($content){
		preg_match_all('/{voice}(.*?){\/voice}/',$content, $matches);
		$i=0;
		foreach($matches[1] as $match){
			$content=preg_replace('/{voice}(.*?){\/voice}/','{voice}'.JURI::base().'components'.DS.'com_ijoomeradv'.DS.'assets'.DS.'voice'.DS.$match.'{/voice}',$content,1);
			$content=str_replace('amp;','',$content);
			$i++;
		}
		return $content;
	}
}