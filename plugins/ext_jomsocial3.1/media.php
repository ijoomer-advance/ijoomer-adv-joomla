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

defined('_JEXEC') or die;

class media {
	private $jomHelper;
	private $date_now;
	private $IJUserID;
	private $mainframe;
	private $db;
	private $my;
	private $config;
	private $jsonarray = array ();

	function __construct() {
		$this->jomHelper = new jomHelper ( );
		$this->date_now = JFactory::getDate ();
		$this->mainframe = JFactory::getApplication ();
		$this->db = JFactory::getDBO (); // set database object
		$this->IJUserID = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my = CFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->config = CFactory::getConfig ();
		$notification = $this->jomHelper->getNotificationCount ();
		if (isset ( $notification ['notification'] )) {
			$this->jsonarray ['notification'] = $notification ['notification'];
		}
	}

	/**
	 * @uses get comment user list
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"allAlbums",
	 * 		"taskData":{
	 * 			"groupID":"groupID", // optional
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 */
	function allAlbums() {
		$groupID = IJReq::getTaskData ( 'groupID', 0, 'int' );
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );

		if ($pageNO == 0 || $pageNO == 1) {
			$startFrom = 0;
		} else {
			$startFrom = (PAGE_ALBUM_LIMIT * ($pageNO - 1));
		}

		$isAdmin = ( int ) COwnerHelper::isCommunityAdmin ();

		$groupquery = "";
		if ($groupID) {
			$photoModel = CFactory::getModel ( 'photos' );
			$photoModel->setState ( 'limit', PAGE_ALBUM_LIMIT );
			$photoModel->setState ( 'limitstart', $startFrom );
			$result = $photoModel->getGroupAlbums ( $groupID, true );
			$total = $photoModel->total;
		} else {
			$subQuery = "SELECT COUNT( DISTINCT(s.id) )
						FROM {$this->db->quoteName ( '#__community_photos_albums' )} AS s
						RIGHT JOIN {$this->db->quoteName ( '#__community_groups_members' )} AS t ON s.groupid=t.groupid
						WHERE t.memberid={$this->db->Quote ( $this->IJUserID )} AND t.approved=1";

			$query = 'SELECT x.*, ' . ' COUNT( DISTINCT(b.id) ) AS count, MAX(b.created) AS lastupdated
					FROM ' . '(SELECT a.`id`, a.`creator`, a.`name`, a.`description`, a.`permissions`, a.`created`, ' . ' a.`path` , a.`type` , a.`groupid`, a.`location`, ' . 'c.thumbnail, ' . 'c.storage, ' . 'c.id as photoid, ' . 'IF( a.groupid>0, IF( d.approvals=0,true,(' . $subQuery . ') ), true ) as display, ' . 'CASE a.permissions ' . '	WHEN 0 THEN ' . '	  true ' . '	WHEN 20 THEN ' . '	  (SELECT COUNT(u.id) FROM ' . $this->db->quoteName ( '#__users' ) . ' AS u WHERE u.block=0 AND u.id=' . $this->db->Quote ( $this->IJUserID ) . ') ' . '	WHEN 30 THEN ' . '	  IF( a.creator=' . $this->db->Quote ( $this->IJUserID ) . ' or ' . $isAdmin . ', true, (SELECT COUNT(v.connection_id) FROM ' . $this->db->quoteName ( '#__community_connection' ) . ' AS v WHERE v.connect_from=a.creator AND v.connect_to=' . $this->db->Quote ( $this->IJUserID ) . ' AND v.status=1) ) ' . '	WHEN 40 THEN ' . '	  IF(a.creator=' . $this->db->Quote ( $this->IJUserID ) . ' or ' . $isAdmin . ',true,false) ' . '	END ' . '	AS privacy ' . 'FROM ' . $this->db->quoteName ( '#__community_photos_albums' ) . ' AS a ' . 'LEFT JOIN ' . $this->db->quoteName ( '#__community_photos' ) . ' AS c ' . 'ON a.photoid=c.id ' . 'LEFT JOIN ' . $this->db->quoteName ( '#__community_groups' ) . ' AS d ' . 'ON a.groupid=d.id ' . 'GROUP BY a.id ' . 'HAVING display=true AND privacy=true ' . 'ORDER BY a.`created` DESC ' . ') AS x ' . 'INNER JOIN `#__community_photos` AS b ' . 'ON x.id=b.albumid ' . 'GROUP BY x.id ' . 'ORDER BY x.`created` DESC ';
			$this->db->setQuery ( $query );
			$result = $this->db->loadObjectList ();
			if ($this->db->getErrorNum ()) {
				IJReq::setResponse( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$total = count ( $result );
			$result = array_slice ( $result, $startFrom, PAGE_ALBUM_LIMIT );
		}

		if (count ( $result ) > 0) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['pageLimit'] = PAGE_ALBUM_LIMIT;
			$this->jsonarray ['total'] = $total;
		} else {
			IJReq::setResponse( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$groupModel = CFactory::getModel ( 'groups' );
		$isAdmin = $groupModel->isAdmin ( $this->my->id, $groupID );

		foreach ( $result as $key => $value ) {
			if (! $groupID) {
				if ($value->count <= 0) {
					continue;
				}
			}
			$this->jsonarray ['albums'] [$key] ['id'] = $value->id;
			$this->jsonarray ['albums'] [$key] ['name'] = $value->name;
			$this->jsonarray ['albums'] [$key] ['description'] = $value->description;
			$this->jsonarray ['albums'] [$key] ['permission'] = $value->permissions;
			if ($groupID) {
				$this->jsonarray ['albums'] [$key] ['thumb'] = str_replace ( 'default_thumb.jpg', 'photo_thumb.png', $value->thumbnail );
			} else {
				$this->jsonarray ['albums'] [$key] ['thumb'] = JURI::base () . str_replace ( 'default_thumb.jpg', 'photo_thumb.png', $value->thumbnail );
			}
			$this->jsonarray ['albums'] [$key] ['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $value->lastupdated ) );

			$user = &CFactory::getUser ( $value->creator );
			$usr = $this->jomHelper->getUserDetail ( $value->creator );
			$this->jsonarray ['albums'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['albums'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['albums'] [$key] ['user_avatar'] = $usr->avatar;
			$this->jsonarray ['albums'] [$key] ['user_profile'] = $usr->profile;
			$this->jsonarray ['albums'] [$key] ['count'] = $value->count;
			$this->jsonarray ['albums'] [$key] ['location'] = $value->location;

			//likes
			$likes = $this->jomHelper->getLikes ( 'album', $value->id, $this->IJUserID );
			$this->jsonarray ['albums'] [$key] ['likes'] = $likes->likes;
			$this->jsonarray ['albums'] [$key] ['dislikes'] = $likes->dislikes;
			$this->jsonarray ['albums'] [$key] ['liked'] = $likes->liked;
			$this->jsonarray ['albums'] [$key] ['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $value->id, 'albums' );
			$this->jsonarray ['albums'] [$key] ['commentCount'] = $count;
			$this->jsonarray ['albums'] [$key] ['deleteAllowed'] = intval ( ($this->IJUserID == $value->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
			$this->jsonarray ['albums'] [$key] ['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=album&albumid={$value->id}&userid={$value->creator}";
			$this->jsonarray ['albums'] [$key] ['editAlbum'] = ($groupID) ? intval ( $isAdmin ) : intval ( $this->IJUserID == $value->creator );
		}
		return $this->jsonarray;
	}

	/**
	 * @uses get comment user list
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"myAlbums",
	 * 		"taskData":{
	 * 			"userID":"userID", // optional: if wanted to see user album
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 *
	 * copied from photos model _getAlbums()
	 */
	function myAlbums() {
		$userID = IJReq::getTaskData ( 'userID', $this->IJUserID, 'int' );
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		$type = IJReq::getTaskData ( 'type', PHOTOS_USER_TYPE );
		$limit = PAGE_ALBUM_LIMIT;

		//change permission for album list
		$access_limit = $this->jomHelper->getUserAccess($this->IJUserID,$userID);
		$query = "SELECT params
					FROM #__community_users
					WHERE userid=".$userID;
		$this->db->setQuery($query);
		$params = new CParameter($this->db->loadResult());

		if($access_limit<$params->get('privacyPhotoView')){
			IJReq::setResponse( 706 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//CFactory::load ( 'libraries', 'privacy' );
		//$permission = CPrivacy::getAccessLevel ( null, $userID );

		$permission = ($userID == 0) ? 0 : 20;
        $permission = COwnerHelper::isCommunityAdmin() ? 40 : $permission;

        // a.type = {$this->db->Quote($type)}
        $query = "WHERE a.creator = {$this->db->Quote($userID)}
				AND ( a.permissions <= {$this->db->Quote($permission)} OR (a.creator= {$this->db->Quote($userID)} AND a.permissions <= {$this->db->Quote(40)} ) ) ";

		// Get total albums
		CFactory::load ( 'models', 'photos' );
		$photosModelObject = new CommunityModelPhotos ( );
		$total = $photosModelObject->getAlbumCount ( $query );
		$result = $photosModelObject->getAlbumPhotoCount ( $query );

		if (count ( $result ) > 0) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['pageLimit'] = PAGE_ALBUM_LIMIT;
			$this->jsonarray ['total'] = $total;
		} else {
			IJReq::setResponse( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		foreach ( $result as $key => $value ) {
			$this->jsonarray ['albums'] [$key] ['id'] = $value->id;
			$this->jsonarray ['albums'] [$key] ['name'] = $value->name;
			$this->jsonarray ['albums'] [$key] ['description'] = $value->description;
			$this->jsonarray ['albums'] [$key] ['permission'] = $value->permissions;
			$this->jsonarray ['albums'] [$key] ['thumb'] = JURI::base () . $value->thumbnail;
			$this->jsonarray ['albums'] [$key] ['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $value->lastupdated ) );
			$usr = $this->jomHelper->getUserDetail ( $value->creator );
			$this->jsonarray ['albums'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['albums'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['albums'] [$key] ['user_avatar'] = $usr->avatar;
			$this->jsonarray ['albums'] [$key] ['user_profile'] = $usr->profile;
			$this->jsonarray ['albums'] [$key] ['count'] = $value->count;
			$this->jsonarray ['albums'] [$key] ['location'] = $value->location;

			//likes
			$likes = $this->jomHelper->getLikes ( 'album', $value->id, $this->IJUserID );
			$this->jsonarray ['albums'] [$key] ['likes'] = $likes->likes;
			$this->jsonarray ['albums'] [$key] ['dislikes'] = $likes->dislikes;
			$this->jsonarray ['albums'] [$key] ['liked'] = $likes->liked;
			$this->jsonarray ['albums'] [$key] ['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $value->id, 'albums' );
			$this->jsonarray ['albums'] [$key] ['commentCount'] = $count;
			$this->jsonarray ['albums'] [$key] ['deleteAllowed'] = intval ( ($this->IJUserID == $value->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
			$this->jsonarray ['albums'] [$key] ['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=album&albumid={$value->id}&userid={$value->creator}";
		}
		return $this->jsonarray;
	}

	/**
	 * @uses create album
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"addAlbum",
	 * 		"taskData":{
	 * 			"albumID":"albumID",
	 * 			"name":"name",
	 * 			"desc":"desc",
	 * 			"lat":"lat",
	 * 			"long":"long",
	 * 			"groupID":"groupID" // optional for user album
	 * 			"privacy":"privacy" // optional for group albums
	 * 		}
	 * 	}
	 *
	 */
	function addAlbum($aname = '', $adesc = '', $alat = '', $along = '') {
		require_once JPATH_ROOT . '/components/com_community/controllers/controller.php';
		require_once JPATH_ROOT . '/components/com_community/controllers/photos.php';

		$albumID = IJReq::getTaskData ( 'albumID', null, 'int' );
		$name = IJReq::getTaskData ( 'name', $aname );
		$desc = IJReq::getTaskData ( 'desc', $adesc );
		$lat = IJReq::getTaskData ( 'lat', $alat);
		$long = IJReq::getTaskData ( 'long', $along);
		$groupID = IJReq::getTaskData ( 'groupID', 0, 'int' );
		if ($groupID) {
			$type = 'group';
		} else {
			$type = 'user';
		}
		$permission = IJReq::getTaskData ( 'privacy', 0, 'int' );
		//get location from latlong
		$temp_loc = $this->jomHelper->getaddress ( $lat, $long );
		$location = $this->jomHelper->gettitle ( $temp_loc );

		if ($name == '') {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load models, libraries
		CFactory::load ( 'models', 'photos' );
		CFactory::load ( 'helpers', 'url' );

		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $albumID );

		//$postData	= JRequest::get('POST');
		$postData = array ();
		$postData ['name'] = ($name) ? $name : $album->name;
		$postData ['description'] = ($desc) ? $desc : $album->description;
		$postData ['type'] = ($type) ? $type : $album->type;
		$postData ['groupid'] = ($groupID) ? $groupID : $album->groupid;
		$postData ['location'] = ($location) ? $location : $album->location;
		$postData ['latitude'] = ($lat) ? $lat : $album->latitude;
		$postData ['longitude'] = ($long) ? $long : $album->longitude;
		$postData ['albumid'] = ($albumID) ? $albumID : '';
		$postData ['permissions'] = $permission;
		if($aname){
			$postData ['default']	= 1;
		}

		$handler = $this->_getHandler ( $album );
		$handler->bindAlbum ( $album, $postData );
		// @rule: New album should not have any id's.
		if (! $albumID) {

			$album->creator = $this->my->id;
		}

		$albumPath = $handler->getAlbumPath ( $album->id );
		$albumPath = CString::str_ireplace ( JPATH_ROOT . '/', '', $albumPath );
		$albumPath = CString::str_ireplace ( '\\', '/', $albumPath );
		$album->path = $albumPath;

		// update permissions in activity streams as well
		$activityModel = CFactory::getModel ( 'activities' );
		$activityModel->updatePermission ( $album->permissions, null, $this->my->id, 'photos', $album->id );
		$activityModel->update ( array ('cid' => $album->id, 'app' => 'photos', 'actor' => $this->my->id ), array ('location' => $album->location ) );

		$appsLib = & CAppPlugins::getInstance ();
		$saveSuccess = $appsLib->triggerEvent ( 'onFormSave', array ('jsform-photos-newalbum' ) );

		$album->store ();

		//Update inidividual Photos Permissions
		$photos = CFactory::getModel ( 'photos' );
		$photos->updatePermissionByAlbum ( $album->id, $album->permissions );

		if ($album->id) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['albumid'] = $album->id;

			//Send push notification
			if($groupID){
				$groupsModel	= CFactory::getModel( 'groups' );
				$group		=& JTable::getInstance( 'Group' , 'CTable' );
				$group->load($groupID);

				$memberCount 	= $groupsModel->getMembersCount($groupID);
				$members 		= $groupsModel->getMembers($groupID, $memberCount , true , false , SHOW_GROUP_ADMIN );
				$membersArray = array();
				foreach($members as $row){
					$membersArray[] = $row->id;
				}

				$memberslist = implode(',',$membersArray);
				$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid` IN ({$memberslist})";
				$this->db->setQuery($query);
				$puserlist=$this->db->loadObjectList();

				$albumdata['groupid'] = $group->id;
				$albumdata['id'] = $album->id;
				$albumdata['name'] = $album->name;
				$albumdata['description'] = $album->description;
				$albumdata['permission'] = $album->permissions;
				$albumdata['thumb'] = JURI::base () . $album->thumbnail;
				$albumdata['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $album->lastupdated ) );
				$albumuser = $this->jomHelper->getUserDetail ( $album->creator );
				$albumdata['user_id'] = $albumuser->id;
				$albumdata['user_name'] = $albumuser->name;
				$albumdata['user_avatar'] = $albumuser->avatar;
				$albumdata['user_profile'] = $albumuser->profile;
				$albumdata['count'] = 0;
				$albumdata['location'] = ($album->location)?$album->location:'';

				//likes
				$likes = $this->jomHelper->getLikes ( 'album', $album->id, $this->IJUserID );
				$albumdata['likes'] = $likes->likes;
				$albumdata['dislikes'] = $likes->dislikes;
				$albumdata['liked'] = $likes->liked;
				$albumdata['disliked'] = $likes->disliked;

				//comments
				$count = $this->jomHelper->getCommentCount ( $album->id, 'albums' );
				$albumdata['commentCount'] = $count;
				$albumdata['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=album&albumid={$album->id}&userid={$album->creator}";

				$photoModel	=& CFactory::getModel( 'photos' );
				$albums		= $photoModel->getGroupAlbums($group->id);

				//$pushcontentdata['id']	= $group->id;
				CFactory::load ( 'helpers', 'group' );
				$allowManagePhotos	= CGroupHelper::allowManagePhoto( $group->id );
				if( $allowManagePhotos  && $this->config->get('groupphotos') && $this->config->get('enablephotos') ) {
					$albumdata['uploadPhoto'] = ( $albums ) ? 1 : 0;
					//$albumdata['createAlbum'] = 1;
				}else{
					$albumdata['uploadPhoto'] = 0;
					//$albumdata['createAlbum'] = 0;
				}

				foreach ($puserlist as $puser){
					$ijparams = new CParameter($puser->jomsocial_params);
					if($ijparams->get('pushnotif_groups_create_album')==1 && $puser->userid!=$this->IJUserID && !empty($puser)){
						$usr=$this->jomHelper->getUserDetail($puser->userid);
						$albumdata['deleteAllowed'] = intval (COwnerHelper::isCommunityAdmin($usr->id));
						$search = array('{target}','{url}');
						$replace = array($usr->name,'');
						$message = str_replace($search,$replace,JText::sprintf( 'COM_COMMUNITY_EMAIL_GROUP_ALBUM_TEXT' , $group->name , $album->name ));
						if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
							$options=array();
							$options['device_token']=$puser->device_token;
							$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
							$options['aps']['message']=strip_tags($message);//str_replace('{target}',$usr->name,JText::sprintf('COM_COMMUNITY_EMAIL_GROUP_ALBUM_TEXT'));
							$options['aps']['type']=($type) ? $type : $album->type;
							$options['aps']['content_data']=$albumdata;
							$options['aps']['content_data']['type']='album';
							IJPushNotif::sendIphonePushNotification($options);
						}

						if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
							$options=array();
							$options['registration_ids']=array($puser->device_token);
							$options['data']['message']=strip_tags($message);//str_replace('{target}',$usr->name,JText::sprintf('COM_COMMUNITY_EMAIL_GROUP_ALBUM_TEXT'));
							$options['data']['type']=($type) ? $type : $album->type;
							$options['data']['content_data']=$albumdata;
							$options['data']['content_data']['type']='album';
							IJPushNotif::sendAndroidPushNotification($options);
						}
					}
				}
			}

			return $this->jsonarray;
		} else {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}

	/**
	 * @uses To remove album with its all photos inside
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"removeAlbum",
	 * 		"taskData":{
	 * 			"albumID":"albumID", // optional.
	 * 		}
	 * 	}
	 *
	 */
	function removeAlbum() {
		// Get the album id.
		$albumID = IJReq::getTaskData ( 'albumID', 0, 'int' );

		// Load libraries
		CFactory::load ( 'models', 'photos' );
		CFactory::load ( 'libraries', 'activities' );
		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $albumID );
		$handler = $this->_getHandler ( $album );
		CFactory::load ( 'helpers', 'owner' );

		if (! $handler->hasPermission ( $album->id, $album->groupid )) {
			IJReq::setResponse( 706 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'libraries', 'apps' );
		$appsLib = & CAppPlugins::getInstance ();
		$appsLib->loadApplications ();

		$params = array ();
		$params [] = $album;

		if ($album->delete ()) {
			$appsLib->triggerEvent ( 'onAfterAlbumDelete', $params );

			// @rule: remove from featured item if item is featured
			CFactory::load ( 'libraries', 'featured' );
			$featured = new CFeatured ( FEATURED_ALBUMS );
			$featured->delete ( $album->id );

			//add user points
			CFactory::load ( 'libraries', 'userpoints' );
			CUserPoints::assignPoint ( 'album.remove' );

			// Remove from activity stream
			CActivityStream::remove ( 'photos', $albumID );
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		}
		IJReq::setResponse( 500 );
		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
		return false;
	}

	/**
	 * @uses create album
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"comments",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // album id, photo id, or video id depending on type.
	 * 			"pageNO":"pageNO",
	 * 			"type":"type" // 'albums'(default), 'photos', 'videos'
	 * 		}
	 * 	}
	 *
	 */
	function comments() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$type = IJReq::getTaskData ( 'type', 'albums' );
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		$limit = PAGE_COMMENT_LIMIT;

		if ($pageNO == 0 || $pageNO == 1) {
			$startFrom = 0;
		} else {
			$startFrom = ($limit * ($pageNO - 1));
		}

		$wallModel = & CFactory::getModel ( 'wall' );
		$comments = $wallModel->getPost ( $type, $uniqueID, $limit, $startFrom );
		$count = $this->jomHelper->getCommentCount ( $uniqueID, $type );
		if (count ( $comments ) > 0) {
			$this->jsonarray ['code'] = 200;
		} else {
			$this->jsonarray ['code'] = 204;
		}

		$this->jsonarray ['pageLimit'] = $limit;
		$this->jsonarray ['total'] = $count;

		switch ($type) {
			case 'videos' :
				$video = JTable::getInstance ( 'Video', 'CTable' );
				$video->load ( $uniqueID );
				$deleteAllowed = intval ( ($this->IJUserID == $video->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
				break;
			case 'photos' :
				CFactory::load ( 'models', 'photos' );
				$photos = new CommunityModelPhotos ( );
				$creator = intval ( $photos->isCreator ( $uniqueID, $this->IJUserID ) > 0 );
				$deleteAllowed = intval ( ($creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
				break;
			case 'albums' :
			default :
				$album = JTable::getInstance ( 'Album', 'CTable' );
				$album->load ( $uniqueID );
				$deleteAllowed = intval ( ($this->IJUserID == $album->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
				break;
		}

		foreach ( $comments as $key => $comment ) {
			$post_by = $comment->post_by;
			$userdetail = CFactory::getUser ( $post_by );

			if ($this->config->get ( 'user_avatar_storage' ) == 'file') {
				$p_url = JURI::base ();
			} else {
				$s3BucketPath = $this->config->get ( 'storages3bucket' );
				if (! empty ( $s3BucketPath ))
					$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
				else
					$p_url = JURI::base ();
			}

			$this->jsonarray ['comments'] [$key] ['id'] = $comment->id;
			$comment->comment = $this->jomHelper->addAudioFile($comment->comment);
			$this->jsonarray ['comments'] [$key] ['comment'] = strip_tags ( $comment->comment );
			$createdTime = $this->jomHelper->getDate ( $comment->date );
			$createdTime = $this->jomHelper->timeLapse ( $createdTime );
			$this->jsonarray ['comments'] [$key] ['date'] = $createdTime;
			$usr = $this->jomHelper->getUserDetail ( $comment->post_by );
			$this->jsonarray ['comments'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['comments'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['comments'] [$key] ['user_avatar'] = $usr->avatar;
			$this->jsonarray ['comments'] [$key] ['user_profile'] = $usr->profile;
			$this->jsonarray ['comments'] [$key] ['deleteAllowed'] = intval ( $deleteAllowed );
		}
		return $this->jsonarray;
	}

	/**
	 * @uses add comment
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"addComment",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // album id, photo id, or video id depending on type.
	 * 			"message":"message",
	 * 			"type":"type" // 'albums'(default), 'photos', 'videos'
	 * 		}
	 * 	}
	 *
	 */
	function addComment() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$message = IJReq::getTaskData ( 'message', NULL );

		$audiofileupload = $this->jomHelper->uploadAudioFile();
		if($audiofileupload){
			$message = $message.$audiofileupload['voicetext'];
		}

		$type = IJReq::getTaskData ( 'type', NULL );
		if (! $uniqueID or ! $type) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (! $message) {
			IJReq::setResponse(400,JText::_ ( 'COM_COMMUNITY_WALL_EMPTY_MESSAGE' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		switch ($type) {
			case 'albums' :
				$this->jsonarray = $this->addAlbumComment ( $message, $uniqueID, NULL );
				break;

			case 'photos' :
				$this->jsonarray = $this->addPhotoComment ( $message, $uniqueID, NULL );
				break;

			case 'videos' :
				$this->jsonarray = $this->addVideoComment ( $message, $uniqueID, NULL );
				break;

			default :
				IJReq::setResponse( 400 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}

		if (! $this->jsonarray) {
			return false;
		} else {
			return $this->jsonarray;
		}
	}

	// called by addComment
	// copied from com_community/controller/photos.php and modified
	private function addAlbumComment($message, $uniqueId, $appId = null) {
		$filter = JFilterInput::getInstance ();
		$message = strip_tags ( $filter->clean ( $message, 'string' ) );
		$uniqueId = $filter->clean ( $uniqueId, 'int' );
		$appId = $filter->clean ( $appId, 'int' );

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//Load Libs
		CFactory::load ( 'libraries', 'wall' );
		CFactory::load ( 'helpers', 'url' );
		CFactory::load ( 'libraries', 'activities' );
		CFactory::load ( 'helpers', 'owner' );
		CFactory::load ( 'helpers', 'friends' );
		CFactory::load ( 'helpers', 'group' );

		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $uniqueId );

		$handler = $this->_getHandler ( $album );

		// If the content is false, the message might be empty.
		if ($this->config->get ( 'antispam_akismet_walls' )) {
			CFactory::load ( 'libraries', 'spamfilter' );

			$filter = CSpamFilter::getFilter ();
			$filter->setAuthor ( $this->my->getDisplayName () );
			$filter->setMessage ( $message );
			$filter->setEmail ( $this->my->email );
			$filter->setURL ( CRoute::_ ( 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id ) );
			$filter->setType ( 'message' );
			$filter->setIP ( $_SERVER ['REMOTE_ADDR'] );

			if ($filter->isSpam ()) {
				IJReq::setResponse( 705,JText::_ ( 'COM_COMMUNITY_WALLS_MARKED_SPAM' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$wall = CWallLibrary::saveWall ( $uniqueId, $message, 'albums', $this->my, ($this->my->id == $album->creator), 'photos,album' );
		$param = new CParameter ( '' );
		$url = $handler->getAlbumURI ( $album->id, false );
		$param->set ( 'photoid', $uniqueId );
		$param->set ( 'action', 'wall' );
		$param->set ( 'wallid', $wall->id );
		$param->set ( 'url', $url );

		// Get the album type
		$app = $album->type;

		// Add activity logging based on app's type
		$permission = $this->_getAppPremission ( $app, $album );

		if (($app == 'user' && $permission == '0') || // Old defination for public privacy
($app == 'user' && $permission == PRIVACY_PUBLIC) || ($app == 'user' && $permission == PRIVACY_MEMBERS)) {
			$group = JTable::getInstance ( 'Group', 'CTable' );
			$group->load ( $album->groupid );

			$event = null;
			$this->_addActivity ( 'photos.wall.create', $my->id, 0, JText::sprintf ( 'COM_COMMUNITY_ACTIVITIES_WALL_POST_ALBUM', '{url}', $album->name ), $message, 'albums', $uniqueId, $group, $event, $param->toString (), $permission );
		}

		CFactory::load ( 'libraries', 'notification' );
		$params = new CParameter ( '' );
		$params->set ( 'url', $url );
		$params->set ( 'message', $message );

		$params->set ( 'album', $album->name );
		$params->set ( 'album_url', $url );

		// @rule: Send notification to the photo owner.
		if ($this->my->id !== $album->creator) {
			// Add notification
			CNotificationLibrary::add ( 'photos_submit_wall', $this->my->id, $album->creator, JText::sprintf ( 'COM_COMMUNITY_ALBUM_WALL_EMAIL_SUBJECT' ), '', 'album.wall', $params );
		} else {
			//for activity reply action
			//get relevent users in the activity
			$wallModel = CFactory::getModel ( 'wall' );
			$users = $wallModel->getAllPostUsers ( 'albums', $uniqueId, $album->creator );
			if (! empty ( $users )) {
				CNotificationLibrary::add ( 'photos_reply_wall', $this->my->id, $users, JText::sprintf ( 'COM_COMMUNITY_ALBUM_WALLREPLY_EMAIL_SUBJECT' ), '', 'album.wallreply', $params );
			}
		}

		//add user points
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'photos.wall.create' );

		//$response->addScriptCall( 'joms.walls.insert' , $wall->content );
		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	// called by addComment
	// copied from com_community/controller/photos.php
	private function addPhotoComment($message, $uniqueId, $appId = null) {
		$filter = JFilterInput::getInstance ();
		$message = strip_tags ( $filter->clean ( $message, 'string' ) );
		$uniqueId = $filter->clean ( $uniqueId, 'int' );
		$appId = $filter->clean ( $appId, 'int' );

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load libraries
		CFactory::load ( 'models', 'photos' );
		CFactory::load ( 'libraries', 'wall' );
		CFactory::load ( 'helpers', 'url' );
		CFactory::load ( 'libraries', 'activities' );
		CFactory::load ( 'helpers', 'owner' );
		CFactory::load ( 'helpers', 'friends' );
		CFactory::load ( 'helpers', 'group' );

		$photo = JTable::getInstance ( 'Photo', 'CTable' );
		$photo->load ( $uniqueId );

		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $photo->albumid );

		$handler = $this->_getHandler ( $album );

		if (! $handler->isWallsAllowed ( $photo->id )) {
			IJReq::setResponse( 706,JText::_ ( 'COM_COMMUNITY_NOT_ALLOWED_TO_POST_COMMENT' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// If the content is false, the message might be empty.
		// @rule: Spam checks
		if ($this->config->get ( 'antispam_akismet_walls' )) {
			CFactory::load ( 'libraries', 'spamfilter' );

			$filter = CSpamFilter::getFilter ();
			$filter->setAuthor ( $this->my->getDisplayName () );
			$filter->setMessage ( $message );
			$filter->setEmail ( $this->my->email );
			$filter->setURL ( CRoute::_ ( 'index.php?option=com_community&view=photos&task=photo&albumid=' . $photo->albumid ) . '#photoid=' . $photo->id );
			$filter->setType ( 'message' );
			$filter->setIP ( $_SERVER ['REMOTE_ADDR'] );

			if ($filter->isSpam ()) {
				IJReq::setResponse( 705,JText::_ ( 'COM_COMMUNITY_WALLS_MARKED_SPAM' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$wall = CWallLibrary::saveWall ( $uniqueId, $message, 'photos', $this->my, ($this->my->id == $photo->creator), 'photos,photo' );
		$url = $photo->getRawPhotoURI ();
		$param = new CParameter ( '' );
		$param->set ( 'photoid', $uniqueId );
		$param->set ( 'action', 'wall' );
		$param->set ( 'wallid', $wall->id );
		$param->set ( 'url', $url );

		// Get the album type
		$app = $album->type;

		// Add activity logging based on app's type
		$permission = $this->_getAppPremission ( $app, $album );

		if (($app == 'user' && $permission == '0') || // Old defination for public privacy
($app == 'user' && $permission == PRIVACY_PUBLIC)) {
			$group = JTable::getInstance ( 'Group', 'CTable' );
			$group->load ( $album->groupid );

			$event = null;
			$this->_addActivity ( 'photos.wall.create', $this->my->id, 0, JText::sprintf ( 'COM_COMMUNITY_ACTIVITIES_WALL_POST_PHOTO', $url, $photo->caption ), $message, 'photos', $uniqueId, $group, $event, $param->toString (), 0 );
		}

		// Add notification
		CFactory::load ( 'libraries', 'notification' );

		$params = new CParameter ( '' );
		$params->set ( 'url', $photo->getRawPhotoURI () );
		$params->set ( 'message', $message );
		$params->set ( 'photo', JText::_ ( 'COM_COMMUNITY_SINGULAR_PHOTO' ) );
		$params->set ( 'photo_url', $url );
		// @rule: Send notification to the photo owner.
		if ($this->my->id !== $photo->creator) {
			CNotificationLibrary::add ( 'photos_submit_wall', $this->my->id, $photo->creator, JText::sprintf ( 'COM_COMMUNITY_PHOTO_WALL_EMAIL_SUBJECT' ), '', 'photos.wall', $params );

			//Send pushnotification
			// get user push notification params and user device token and device type
			$query="SELECT `jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid`={$photo->creator}";
			$this->db->setQuery($query);
			$puser=$this->db->loadObject();
			$ijparams = new CParameter($puser->jomsocial_params);

			CFactory::load ( 'helpers', 'group' );
			$album = JTable::getInstance ( 'Album', 'CTable' );
			$album->load ($photo->albumid);
			$pushcontentdata['albumdetail']['id'] = $album->id;
			$pushcontentdata['albumdetail']['deleteAllowed'] = intval ( ($photo->creator == $album->creator or COwnerHelper::isCommunityAdmin ( $photo->creator )) );
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
				if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
					$photo->image = $photo->original;
			}
			$pushcontentdata['photodetail']['thumb'] = $p_url . $photo->thumbnail;
			$pushcontentdata['photodetail']['url'] = $p_url . $photo->image;
			if (SHARE_PHOTOS == 1) {
				$pushcontentdata['photodetail']['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$userId}&albumid={$albumID}#photoid={$photo->id}";
			}

			//likes
			$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
			$pushcontentdata['photodetail']['likes'] = $likes->likes;
			$pushcontentdata['photodetail']['dislikes'] = $likes->dislikes;
			$pushcontentdata['photodetail']['liked'] = $likes->liked;
			$pushcontentdata['photodetail']['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
			$pushcontentdata['photodetail']['commentCount'] = $count;

			$query = "SELECT count(id)
					FROM #__community_photos_tag
					WHERE `photoid`={$photo->id}";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$pushcontentdata['photodetail']['tags'] = $count;

			if($ijparams->get('pushnotif_photos_submit_wall')==1 && $photo->creator!=$this->IJUserID && !empty($puser)){
				$usr=$this->jomHelper->getUserDetail($this->IJUserID);
				$match = array('{actor}','{photo}');
				$replace = array($usr->name,JText::_ ( 'COM_COMMUNITY_SINGULAR_PHOTO' ));
				$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_PHOTO_WALL_EMAIL_SUBJECT'));

				if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
					$options=array();
					$options['device_token']=$puser->device_token;
					$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
					$options['aps']['message']=strip_tags($message);
					$options['aps']['type']='photos';
					$options['aps']['content_data']=$pushcontentdata;
					$options['aps']['content_data']['type']='photos';
					IJPushNotif::sendIphonePushNotification($options);
				}

				if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
					$options=array();
					$options['registration_ids']=array($puser->device_token);
					$options['data']['message']=strip_tags($message);
					$options['data']['type']='photos';
					$options['data']['content_data']=$pushcontentdata;
					$options['data']['content_data']['type']='photos';
					IJPushNotif::sendAndroidPushNotification($options);
				}
			}
		} else {
			//for activity reply action
			//get relevent users in the activity
			$wallModel = CFactory::getModel ( 'wall' );
			$users = $wallModel->getAllPostUsers ( 'photos', $photo->id, $photo->creator );
			if (! empty ( $users )) {
				CNotificationLibrary::add ( 'photos_reply_wall', $this->my->id, $users, JText::sprintf ( 'COM_COMMUNITY_PHOTO_WALLREPLY_EMAIL_SUBJECT' ), '', 'photos.wallreply', $params );

				CFactory::load ( 'helpers', 'group' );
				$album = JTable::getInstance ( 'Album', 'CTable' );
				$album->load ($photo->albumid);
				$pushcontentdata['albumdetail']['id'] = $album->id;

				$pushcontentdata['photodetail']['id'] = $photo->id;
				$pushcontentdata['photodetail']['caption'] = $photo->caption;

				$p_url = JURI::base ();
				if ($photo->storage == 's3') {
					$s3BucketPath = $this->config->get ( 'storages3bucket' );
					if (! empty ( $s3BucketPath ))
						$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
				} else {
					if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
						$photo->image = $photo->original;
				}
				$pushcontentdata['photodetail']['thumb'] = $p_url . $photo->thumbnail;
				$pushcontentdata['photodetail']['url'] = $p_url . $photo->image;
				if (SHARE_PHOTOS == 1) {
					$pushcontentdata['photodetail']['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$userId}&albumid={$albumID}#photoid={$photo->id}";
				}

				//likes
				$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
				$pushcontentdata['photodetail']['likes'] = $likes->likes;
				$pushcontentdata['photodetail']['dislikes'] = $likes->dislikes;
				$pushcontentdata['photodetail']['liked'] = $likes->liked;
				$pushcontentdata['photodetail']['disliked'] = $likes->disliked;

				//comments
				$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
				$pushcontentdata['photodetail']['commentCount'] = $count;

				$query = "SELECT count(id)
						FROM #__community_photos_tag
						WHERE `photoid`={$photo->id}";
				$this->db->setQuery ( $query );
				$count = $this->db->loadResult ();
				$pushcontentdata['photodetail']['tags'] = $count;

				//Send push notification
				// get user push notification params and user device token and device type
				$memberslist = implode(',',$users);
				$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid` IN ({$memberslist})";
				$this->db->setQuery($query);
				$puserlist=$this->db->loadObjectList();

				foreach ($puserlist as $puser){
					$ijparams = new CParameter($puser->jomsocial_params);
					if($ijparams->get('pushnotif_photos_reply_wall')==1 && $puser->userid!=$this->IJUserID && !empty($puser)){
						$usr=$this->jomHelper->getUserDetail($this->IJUserID);
						$pushcontentdata['albumdetail']['deleteAllowed'] = intval ( ($puser->userid == $album->creator or COwnerHelper::isCommunityAdmin ( $puser->userid )) );
						if($puser->userid == $album->creator){
							$uid=0;
						}else{
							$uid=$album->creator;
						}
						$pushcontentdata['albumdetail']['user_id'] = $uid;

						if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
							$options=array();
							$options['device_token']=$puser->device_token;
							$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
							$options['aps']['message']=strip_tags(str_replace('{photo}',JText::_ ( 'COM_COMMUNITY_SINGULAR_PHOTO' ),JText::sprintf('COM_COMMUNITY_PHOTO_WALLREPLY_EMAIL_SUBJECT')));
							$options['aps']['type']='photos';
							$options['aps']['content_data']=$pushcontentdata;
							$options['aps']['content_data']['type']='photos';
							IJPushNotif::sendIphonePushNotification($options);
						}

						if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
							$options=array();
							$options['registration_ids']=array($puser->device_token);
							$options['data']['message']=strip_tags(str_replace('{photo}',JText::_ ( 'COM_COMMUNITY_SINGULAR_PHOTO' ),JText::sprintf('COM_COMMUNITY_PHOTO_WALLREPLY_EMAIL_SUBJECT')));
							$options['data']['type']='photos';
							$options['data']['content_data']=$pushcontentdata;
							$options['data']['content_data']['type']='photos';
							IJPushNotif::sendAndroidPushNotification($options);
						}
					}
				}
			}
		}

		//add user points
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'photos.wall.create' );

		//$response->addScriptCall( 'joms.walls.insert' , $wall->content );
		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	// called by addComment
	// copied from com_community/contrillers/videos.php
	private function addVideoComment($message, $uniqueId) {
		$filter = JFilterInput::getInstance ();
		$uniqueId = $filter->clean ( $uniqueId, 'int' );

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$video = JTable::getInstance ( 'Video', 'CTable' );
		$video->load ( $uniqueId );

		// @rule: Spam checks
		if ($this->config->get ( 'antispam_akismet_walls' )) {
			CFactory::load ( 'libraries', 'spamfilter' );

			$filter = CSpamFilter::getFilter ();
			$filter->setAuthor ( $this->my->getDisplayName () );
			$filter->setMessage ( $message );
			$filter->setEmail ( $this->my->email );
			$filter->setURL ( CRoute::_ ( 'index.php?option=com_community&view=videos&task=video&videoid=' . $uniqueId ) );
			$filter->setType ( 'message' );
			$filter->setIP ( $_SERVER ['REMOTE_ADDR'] );

			if ($filter->isSpam ()) {
				IJReq::setResponse( 705,JText::_ ( 'COM_COMMUNITY_WALLS_MARKED_SPAM' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		CFactory::load ( 'libraries', 'wall' );
		$wall = CWallLibrary::saveWall ( $uniqueId, $message, 'videos', $this->my, ($this->my->id == $video->creator) );

		// Add activity logging
		$url = $video->getViewUri ( false );

		$params = new CParameter ( '' );
		$params->set ( 'videoid', $uniqueId );
		$params->set ( 'action', 'wall' );
		$params->set ( 'wallid', $wall->id );
		$params->set ( 'video_url', $url );

		$act = new stdClass ( );
		$act->cmd = 'videos.wall.create';
		$act->actor = $this->my->id;
		$act->access = $video->permissions;
		$act->target = 0;
		$act->title = JText::sprintf ( 'COM_COMMUNITY_VIDEOS_ACTIVITIES_WALL_POST_VIDEO', '{video_url}', $video->title );
		$act->app = 'videos';
		$act->cid = $uniqueId;
		$act->params = $params->toString ();

		CFactory::load ( 'libraries', 'activities' );
		CActivityStream::add ( $act );
		// Add notification
		CFactory::load ( 'libraries', 'notification' );

		$params = new CParameter ( '' );
		$params->set ( 'url', $url );
		$params->set ( 'message', $message );
		$params->set ( 'video', $video->title );
		$params->set ( 'video_url', $url );
		if ($this->my->id !== $video->creator) {
			CNotificationLibrary::add ( 'videos_submit_wall', $this->my->id, $video->creator, JText::sprintf ( 'COM_COMMUNITY_VIDEO_WALL_EMAIL_SUBJECT' ), '', 'videos.wall', $params );
		// get user push notification params and user device token and device type
			$query="SELECT `jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid`={$video->creator}";
			$this->db->setQuery($query);
			$puser=$this->db->loadObject();
			$ijparams = new CParameter($puser->jomsocial_params);
			if($ijparams->get('pushnotif_videos_submit_wall')==1 && $video->creator!=$this->IJUserID && !empty($puser)){
				//video detail
				$video_file = $video->path;
				$p_url = JURI::root ();
				if ($video->type == 'file') {
					$ext = JFile::getExt ( $video->path );

					if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
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

				$videodata['id'] = $video->id;
				$videodata['caption'] = $video->title;
				$videodata['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
				$videodata['url'] = $video_file;
				$videodata['description'] = $video->description;
				$videodata['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
				$videodata['location'] = $video->location;
				$videodata['permissions'] = $video->permissions;
				$videodata['categoryId'] = $video->category_id;
				$usr = $this->jomHelper->getUserDetail ( $video->creator );
				$videodata['user_id'] = 0;
				$videodata['user_name'] = $usr->name;
				$videodata['user_avatar'] = $usr->avatar;
				$videodata['user_profile'] = $usr->profile;

				//likes
				$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
				$videodata['likes'] = $likes->likes;
				$videodata['dislikes'] = $likes->dislikes;
				$videodata['liked'] = $likes->liked;
				$videodata['disliked'] = $likes->disliked;

				//comments
				$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
				$videodata['commentCount'] = $count;
				$videodata['deleteAllowed'] = intval (($video->creator or COwnerHelper::isCommunityAdmin($video->creator)));

				if (SHARE_VIDEOS) {
					$videodata['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
				}

				$query = "SELECT count(id)
						FROM #__community_videos_tag
						WHERE `videoid`={$video->id}";
				$this->db->setQuery ( $query );
				$count = $this->db->loadResult ();
				$videodata['tags'] = $count;

				$usr=$this->jomHelper->getUserDetail($this->IJUserID);
				$search = array('{actor}','{video}');
				$replace = array($usr->name,$video->title);
				$message = str_replace($search,$replace,JText::sprintf('COM_COMMUNITY_VIDEO_WALL_EMAIL_SUBJECT'));
				if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
					$options=array();
					$options['device_token']=$puser->device_token;
					$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
					$options['aps']['message']=strip_tags($message);
					$options['aps']['type']='videos';
					$options['aps']['content_data']=$videodata;
					$options['aps']['content_data']['type']='videos';
					IJPushNotif::sendIphonePushNotification($options);
				}

				if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
					$options=array();
					$options['registration_ids']=array($puser->device_token);
					$options['data']['message']=strip_tags($message);
					$options['data']['type']='videos';
					$options['data']['content_data']=$videodata;
					$options['data']['content_data']['type']='videos';
					IJPushNotif::sendAndroidPushNotification($options);
				}
			}
		} else {
			//for activity reply action
			//get relevent users in the activity
			$wallModel = CFactory::getModel ( 'wall' );
			$users = $wallModel->getAllPostUsers ( 'videos', $video->id, $video->creator );
			if (! empty ( $users )) {
				CNotificationLibrary::add ( 'videos_reply_wall', $this->my->id, $users, JText::sprintf ( 'COM_COMMUNITY_VIDEO_WALLREPLY_EMAIL_SUBJECT' ), '', 'videos.wallreply', $params );

			//Send push notification
				// get user push notification params and user device token and device type
				$memberslist = implode(',',$users);
				$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid` IN ({$memberslist})";
				$this->db->setQuery($query);
				$puserlist=$this->db->loadObjectList();

				//video detail
				$video_file = $video->path;
				$p_url = JURI::root ();
				if ($video->type == 'file') {
					$ext = JFile::getExt ( $video->path );

					if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
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
				//video detail
				$videodata['id'] = $video->id;
				$videodata['caption'] = $video->title;
				$videodata['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
				$videodata['url'] = $video_file;
				$videodata['description'] = $video->description;
				$videodata['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
				$videodata['location'] = $video->location;
				$videodata['permissions'] = $video->permissions;
				$videodata['categoryId'] = $video->category_id;

				$usr = $this->jomHelper->getUserDetail ( $video->creator );
				$videodata['user_id'] = $usr->id;
				$videodata['user_name'] = $usr->name;
				$videodata['user_avatar'] = $usr->avatar;
				$videodata['user_profile'] = $usr->profile;

				//likes
				$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
				$videodata['likes'] = $likes->likes;
				$videodata['dislikes'] = $likes->dislikes;
				$videodata['liked'] = $likes->liked;
				$videodata['disliked'] = $likes->disliked;

				//comments
				$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
				$videodata['commentCount'] = $count;

				if (SHARE_VIDEOS) {
					$videodata['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
				}

				$query = "SELECT count(id)
						FROM #__community_videos_tag
						WHERE `videoid`={$video->id}";
				$this->db->setQuery ( $query );
				$count = $this->db->loadResult ();
				$videodata['tags'] = $count;

				foreach ($puserlist as $puser){
					$ijparams = new CParameter($puser->jomsocial_params);
					if($ijparams->get('pushnotif_videos_reply_wall')==1 && $puser->userid!=$this->IJUserID && !empty($puser)){
						$usr=$this->jomHelper->getUserDetail($this->IJUserID);
						$videodata['deleteAllowed'] = intval ( ($puser->userid == $video->creator or COwnerHelper::isCommunityAdmin ( $puser->userid )) );
						if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
							$options=array();
							$options['device_token']=$puser->device_token;
							$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
							$options['aps']['message']=strip_tags(str_replace('{video}',$video->title,JText::sprintf('COM_COMMUNITY_VIDEO_WALLREPLY_EMAIL_SUBJECT')));
							$options['aps']['type']='videos';
							IJPushNotif::sendIphonePushNotification($options);
						}

						if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
							$options=array();
							$options['registration_ids']=array($puser->device_token);
							$options['data']['message']=strip_tags(str_replace('{video}',$video->title,JText::sprintf('COM_COMMUNITY_VIDEO_WALLREPLY_EMAIL_SUBJECT')));
							$options['data']['type']='videos';
							$options['data']['content_data']=$videodata;
							$options['data']['content_data']['type']='videos';
							IJPushNotif::sendAndroidPushNotification($options);
						}
					}
				}
			}
		}

		//add user points
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'videos.wall.create' );
		//$response->addScriptCall( 'joms.walls.insert' , $wall->content );


		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	// called by addAlbumComment
	// copied from com_community/controller/photos.php
	private function _addActivity($command, $actor, $target, $title, $content, $app, $cid, $group, $event, $param = '', $permission) {
		CFactory::load ( 'libraries', 'activities' );

		$act = new stdClass ( );
		$act->cmd = $command;
		$act->actor = $actor;
		$act->target = $target;
		$act->title = $title;
		$act->content = $content;
		$act->app = $app;
		$act->cid = $cid;
		$act->access = $permission;

		$act->groupid = $group->id;
		$act->group_access = $group->approvals;

		// Not used here
		$act->eventid = null;
		$act->event_access = null;

		// Allow comment on the album
		$act->comment_type = $command;
		$act->comment_id = CActivities::COMMENT_SELF;

		// Allow like on the album
		$act->like_type = $command;
		$act->like_id = CActivities::LIKE_SELF;

		CActivityStream::add ( $act, $param );
	}

	// called by addAlbumComment
	// copied from com_community/controller/photos.php
	private function _getAppPremission($app, $album) {
		switch ($app) {
			case 'user' :
				$permission = $album->permissions;
				break;
			case 'event' :
			case 'group' :
				CFactory::load ( 'models', 'group' );
				$group = &  JTable::getInstance ( 'Group', 'CTable' );
				$group->load ( $album->groupid );
				$permission = $group->approvals;
				break;
		}

		return $permission;
	}

	/**
	 * @uses to add like to the user profile
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"removeComment",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // optional, if not passed then logged in user id will be used
	 * 			"type":"type", // album, photo, videos
	 * 		}
	 * 	}
	 *
	 */
	function removeComment() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$type = IJReq::getTaskData ( 'type', NULL );
		if (! $uniqueID or ! $type) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		switch ($type) {
			case 'albums' :
				$this->jsonarray = $this->removeAlbumComment ( $uniqueID );
				break;

			case 'photos' :
				$this->jsonarray = $this->removePhotoComment ( $uniqueID );
				break;

			case 'videos' :
				$this->jsonarray = $this->removeVideoComment ( $uniqueID );
				break;

			default :
				IJReq::setResponse( 400 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}

		if (! $this->jsonarray) {
			return false;
		} else {
			return $this->jsonarray;
		}
	}

	// called by removeComment
	// copied from com_community/controller/photos.php
	private function removeAlbumComment($wallId) {
		require_once JPATH_SITE .'/'. 'components/com_community/libraries/activities.php';

		$filter = JFilterInput::getInstance ();
		$wallId = $filter->clean ( $wallId, 'int' );

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$wallsModel = & CFactory::getModel ( 'wall' );
		$wall = $wallsModel->get ( $wallId );
		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $wall->contentid );

		if ($this->my->id == $album->creator || COwnerHelper::isCommunityAdmin ()) {
			if (! $wallsModel->deletePost ( $wallId )) {
				IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			} else {
				$this->jsonarray ['code'] = 200;
				CActivities::removeWallActivities ( array ('app' => 'albums', 'cid' => $wall->contentid, 'createdAfter' => $wall->date ), $wallId );
			}
		} else {
			IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return $this->jsonarray;
	}

	// called by removePhotoComment
	// copied from com_community/controllers/photos.php
	private function removePhotoComment($wallId) {
		require_once JPATH_SITE .'/'. 'components/com_community/libraries/activities.php';

		$filter = JFilterInput::getInstance ();
		$wallId = $filter->clean ( $wallId, 'int' );

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$wallsModel = & CFactory::getModel ( 'wall' );
		$wall = $wallsModel->get ( $wallId );
		$photo = JTable::getInstance ( 'Photo', 'CTable' );
		$photo->load ( $wall->contentid );

		if ($this->my->id == $photo->creator || COwnerHelper::isCommunityAdmin ()) {
			if ($wallsModel->deletePost ( $wallId )) {
				CActivities::removeWallActivities ( array ('app' => 'photos', 'cid' => $wall->contentid, 'createdAfter' => $wall->date ), $wallId );

				//add user points
				if ($wall->post_by != 0) {
					CFactory::load ( 'libraries', 'userpoints' );
					CUserPoints::assignPoint ( 'wall.remove', $wall->post_by );
				}
				$this->jsonarray ['code'] = 200;
			} else {
				IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		} else {
			IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return $this->jsonarray;
	}

	// called by removeComment
	// copied from com_community/controllers/photos.php
	private function removeVideoComment($wallId) {
		require_once JPATH_SITE .'/'. 'components/com_community/libraries/activities.php';

		$filter = JFilterInput::getInstance ();
		$wallId = $filter->clean ( $wallId, 'int' );

		CFactory::load ( 'helpers', 'owner' );

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Only allow wall removal by admin or owner of the video.
		$wallsModel = & CFactory::getModel ( 'wall' );
		$wall = $wallsModel->get ( $wallId );
		$video = JTable::getInstance ( 'Video', 'CTable' );
		$video->load ( $wall->contentid );

		if (COwnerHelper::isCommunityAdmin () || ($this->my->id == $video->creator)) {
			if ($wallsModel->deletePost ( $wallId )) {
				// Remove activity wall.
				CActivities::removeWallActivities ( array ('app' => 'videos', 'cid' => $wall->contentid, 'createdAfter' => $wall->date ), $wallId );

				if ($wall->post_by != 0) {
					CFactory::load ( 'libraries', 'userpoints' );
					CUserPoints::assignPoint ( 'wall.remove', $wall->post_by );
				}
				$this->jsonarray ['code'] = 200;
			} else {
				IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR' ) );
				return false;
			}
		} else {
			IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_GROUPS_REMOVE_WALL_ERROR' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return $this->jsonarray;
	}

	/**
	 * @uses to add like to the user profile
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"like",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // optional, if not passed then logged in user id will be used
	 * 			"type":"type", // album, photo, videos
	 * 		}
	 * 	}
	 *
	 */
	function like() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$type = IJReq::getTaskData ( 'type' );
		if ($this->jomHelper->Like ( $type, $uniqueID )) {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		} else {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}

	/**
	 * @uses to add dislike to the user profile
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"dislike",
	 * 		"taskData":{
	 * 			"userID":"userID", // optional, if not passed then logged in user id will be used
	 * 			"type":"type", // album, photo, videos
	 * 		}
	 * 	}
	 *
	 */
	function dislike() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$type = IJReq::getTaskData ( 'type' );
		if ($this->jomHelper->Dislike ( $type, $uniqueID )) {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		} else {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}

	/**
	 * @uses to unlike like/dislike value to the user profile
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"unlike",
	 * 		"taskData":{
	 * 			"userID":"userID", // optional, if not passed then logged in user id will be used
	 * 			"type":"type", // album, photo, videos
	 * 		}
	 * 	}
	 *
	 */
	function unlike() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$type = IJReq::getTaskData ( 'type' );
		if ($this->jomHelper->Unlike ( $type, $uniqueID )) {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		} else {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}

	/**
	 * @uses get photos from album
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"photos",
	 * 		"taskData":{
	 * 			"userID":"userID", // optional.
	 * 			"albumID":"albumID",
	 * 			"pageNO":"pageNO",
	 * 			"limit":"limit"
	 * 		}
	 * 	}
	 *
	 */
	function photos() {
		$userID = IJReq::getTaskData ( 'userID', $this->IJUserID, 'int' );
		$albumID = IJReq::getTaskData ( 'albumID', 0, 'int' );
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		$limit = IJReq::getTaskData ( 'limit', 20, 'int' );

		if ($pageNO == 0 || $pageNO == 1) {
			$startFrom = 0;
		} else {
			$startFrom = ($limit * ($pageNO - 1));
		}

		require_once JPATH_SITE .'/'. 'components/com_community/models/photos.php';
		$obj = new CommunityModelPhotos ( );
		$photos = $obj->getPhotos ( $albumID, $limit, $startFrom, false );

		if (count ( $photos ) > 0) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['pageLimit'] = $limit;
			$this->jsonarray ['total'] = $obj->_pagination->total;
		} else {
			$this->jsonarray ['code'] = 204;
		}

		foreach ( $photos as $key => $photo ) {
			$this->jsonarray ['photos'] [$key] ['id'] = $photo->id;
			$photo->caption = $this->jomHelper->addAudioFile($photo->caption);
			$this->jsonarray ['photos'] [$key] ['caption'] = $photo->caption;

			$p_url = JURI::base ();
			if ($photo->storage == 's3') {
				$s3BucketPath = $this->config->get ( 'storages3bucket' );
				if (! empty ( $s3BucketPath ))
					$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
			} else {
				if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
					$photo->image = $photo->original;
			}
			$this->jsonarray ['photos'] [$key] ['thumb'] = $p_url . $photo->thumbnail;
			$this->jsonarray ['photos'] [$key] ['url'] = $p_url . $photo->image;
			if (SHARE_PHOTOS == 1) {
				$this->jsonarray ['photos'] [$key] ['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$userId}&albumid={$albumID}#photoid={$photo->id}";
			}

			//likes
			$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
			$this->jsonarray ['photos'] [$key] ['likes'] = $likes->likes;
			$this->jsonarray ['photos'] [$key] ['dislikes'] = $likes->dislikes;
			$this->jsonarray ['photos'] [$key] ['liked'] = $likes->liked;
			$this->jsonarray ['photos'] [$key] ['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
			$this->jsonarray ['photos'] [$key] ['commentCount'] = $count;

			$query = "SELECT count(id)
					FROM #__community_photos_tag
					WHERE `photoid`={$photo->id}";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$this->jsonarray ['photos'] [$key] ['tags'] = $count;
		}

		return $this->jsonarray;
	}

	/**
	 * @uses to upload photos to the user album
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"uploadPhoto",
	 * 		"taskData":{
	 * 			"albumID":"albumID", // optional if profile = true
	 * 			"isDefault":"isDefault", // boolean: true/false
	 * 			"profile":"profile" // boolean true/false
	 * 		}
	 * 	}
	 *
	 * photos will be post with name "image"
	 *
	 */
	function uploadPhoto() {
		$photos = JRequest::getVar ( 'image', '', 'FILES', 'array' );
		$albumID = IJReq::getTaskData ( 'albumID', 0, 'int' );
		$profile = IJReq::getTaskData ( 'profile', false, 'bool' );
		$caption = IJReq::getTaskData ( 'caption', '');
		$audiofileupload = $this->jomHelper->uploadAudioFile();
		if($audiofileupload){
			$caption = $caption.$audiofileupload['voicetext'];
		}

		CFactory::load ( 'libraries', 'limits' );

		if (CLimitsLibrary::exceedDaily ( 'photos', $this->my->id )) {
			IJReq::setResponse( 416,JText::_ ( 'COM_COMMUNITY_PHOTOS_LIMIT_PERDAY_REACHED' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// We can't use blockUnregister here because practically, the CFactory::getUser() will return 0
		if ($this->my->id == 0) {
			IJReq::setResponse( 401,JText::_ ( 'COM_COMMUNITY_PERMISSION_DENIED_WARNING' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load up required models and properties
		CFactory::load ( 'libraries', 'photos' );
		CFactory::load ( 'models', 'photos' );
		CFactory::load ( 'helpers', 'image' );

		$album = JTable::getInstance ( 'Album', 'CTable' );

		if ($profile) { // upload photo from profile
			$query = "SELECT id
					FROM #__community_photos_albums
					WHERE `creator`={$this->IJUserID}
					AND `default`=1";
			$this->db->setQuery ( $query );
			$albumID = $this->db->loadResult ();

			if (!$albumID) {
				$usr = $this->jomHelper->getUserDetail ( $this->IJUserID );
				$albums = $this->addAlbum ( $usr->name."'s Photos", '', 255, 255 );
				$album->load ( $albums ['albumid'] );
			}else{
				$album->load ( $albumID );
			}
		} else {
			$album->load ( $albumID );
		}

		$handler = $this->_getHandler ( $album );
		$result = $this->_checkUploadedFile ( $photos, $album, $handler );
		if(!$result){
			return false;
		}

		//assign the result of the array and assigned to the right variable
		$photoTable = $result ['photoTable'];

		//code for add caption for photo when photo upload
		if($audiofileupload){
			$photoTable->caption = $caption;
		}

		// Remove the filename extension from the caption
		/*if (JString::strlen ( $photoTable->caption ) > 4) {
			$photoTable->caption = JString::substr ( $photoTable->caption, 0, JString::strlen ( $photoTable->caption ) - 4 );
		}*/

		// @todo: configurable options?
		// Permission should follow album permission
		$photoTable->published = '1';
		$photoTable->permissions = $album->permissions;

		// Set the relative path.
		// @todo: configurable path?
		$storedPath = $handler->getStoredPath ( $result ['storage'], $album->id );
		$storedPath = $storedPath .'/'. $result ['albumPath'] . $result ['hashFilename'] . CImageHelper::getExtension ( $photos ['type'] );

		$photoTable->image = CString::str_ireplace ( JPATH_ROOT . '/', '', $storedPath );
		$photoTable->thumbnail = CString::str_ireplace ( JPATH_ROOT . '/', '', $result ['thumbPath'] );

		//In joomla 1.6, CString::str_ireplace is not replacing the path properly. Need to do a check here
		if ($photoTable->image == $storedPath)
			$photoTable->image = str_ireplace ( JPATH_ROOT . '/', '', $storedPath );
		if ($photoTable->thumbnail == $result ['thumbPath'])
			$photoTable->thumbnail = str_ireplace ( JPATH_ROOT . '/', '', $result ['thumbPath'] );

		//photo filesize, use sprintf to prevent return of unexpected results for large file.
		$photoTable->filesize = sprintf ( "%u", filesize ( $result ['originalPath'] ) );

		// @rule: Set the proper ordering for the next photo upload.
		$photoTable->setOrdering ();

		// Store the object
		$photoTable->store ();

		// We need to see if we need to rotate this image, from EXIF orientation data
		// Only for jpeg image.
		if ($this->config->get ( 'photos_auto_rotate' ) && $result ['imgType'] == 'image/jpeg') {
			$this->_rotatePhoto ( $photos, $photoTable, $storedPath, $result ['thumbPath'] );
		}

		// Trigger for onPhotoCreate
		CFactory::load ( 'libraries', 'apps' );
		$apps = & CAppPlugins::getInstance ();
		$apps->loadApplications ();
		$params = array ();
		$params [] = $photoTable;
		$apps->triggerEvent ( 'onPhotoCreate', $params );

		// Set image as default if necessary
		// Load photo album table
		if ($result ['isDefaultPhoto']) {
			// Set the photo id
			$album->photoid = $photoTable->id;
			$album->store ();
		}

		// @rule: Set first photo as default album cover if enabled
		if (! $result ['isDefaultPhoto'] && $this->config->get ( 'autoalbumcover' )) {
			$photosModel = CFactory::getModel ( 'Photos' );
			$totalPhotos = $photosModel->getTotalPhotos ( $album->id );

			if ($totalPhotos <= 1) {
				$album->photoid = $photoTable->id;
				$album->store ();
			}
		}

		// Generate activity stream
		$act = new stdClass ( );
		$act->cmd = 'photo.upload';
		$act->actor = $this->my->id;
		$act->access = $album->permissions;
		$act->target = 0;
		if($profile){
			$act->title = $photoTable->caption;
		}else{
			$act->title = '';//$this->my->getDisplayName () . " ► " . JText::sprintf ( $handler->getUploadActivityTitle (), '{multiUrl}', $album->name );
		}
		$act->content = ''; // Gegenerated automatically by stream. No need to add anything
		$act->app = 'photos';
		$act->cid = $album->id;
		$act->location = $album->location;

		// Store group info
		// I hate to load group here, but unfortunately, album does
		// not store group permission setting
		$group = JTable::getInstance ( 'Group', 'CTable' );
		$group->load ( $album->groupid );

		$act->groupid = $album->groupid;
		$act->group_access = $group->approvals;

		// Allow comment on the album
		$act->comment_type = 'albums';
		$act->comment_id = $photoTable->id;

		// Allow like on the album
		$act->like_type = 'albums';
		$act->like_id = $photoTable->id;

		$params = new CParameter ( '' );
		$params->set ( 'multiUrl', $handler->getAlbumURI ( $album->id, false ) );
		$params->set ( 'photoid', $photoTable->id );
		$params->set ( 'action', 'upload' );
		$params->set ( 'style', COMMUNITY_STREAM_STYLE );
		$params->set ( 'photo_url', $photoTable->getThumbURI () );

		// Add activity logging
		CFactory::load ( 'libraries', 'activities' );
		CActivityStream::add ( $act, $params->toString () );

		//add user points
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'photo.upload' );

		$this->jsonarray ['code'] = 200;
		$this->jsonarray ['id'] = $photoTable->id;

		return $this->jsonarray;
	}

	/**
	 * @uses to upload videos
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"removePhoto",
	 * 		"taskData":{
	 * 			"photoID":"photoID"
	 * 		}
	 * 	}
	 *
	 * photos will be post with name "image"
	 *
	 */
	function removePhoto() {
		$photoID = IJReq::getTaskData ( 'photoID', 0, 'int' );
		$filter = JFilterInput::getInstance ();
		$photoID = $filter->clean ( $photoID, 'int' );

		CFactory::load ( 'helpers', 'owner' );
		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$model = CFactory::getModel ( 'photos' );
		$photo = $model->getPhoto ( $photoID );

		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $photo->albumid );
		$handler = $this->_getHandler ( $album );

		if (! $handler->hasPermission ( $album->id )) {
			IJReq::setResponse( 706,JText::_ ( 'COM_COMMUNITY_PERMISSION_DENIED_WARNING' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'libraries', 'apps' );
		$appsLib = & CAppPlugins::getInstance ();
		$appsLib->loadApplications ();

		$params = array ();
		$params [] = $photo;

		$appsLib->triggerEvent ( 'onBeforePhotoDelete', $params );
		$photo->delete ();
		$appsLib->triggerEvent ( 'onAfterPhotoDelete', $params );

		//add user points
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'photo.remove' );

		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	/**
	 * @uses to tag photos
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"tags",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // photo id
	 * 			"type":"type" // photos, videos
	 * 		}
	 * 	}
	 *
	 * photos will be post with name "image"
	 *
	 */
	function tags() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', null, 'int' );
		$type = IJReq::getTaskData ( 'type', null );

		if (! $uniqueID or ! $type) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		switch ($type) {
			case 'photos' :
				$this->jsonarray = $this->photoTags ( $uniqueID );
				if (! $this->jsonarray) {
					return false;
				}
				break;

			case 'videos' :
				$this->jsonarray = $this->videoTags ( $uniqueID );
				if (! $this->jsonarray) {
					return false;
				}
				break;

			default :
				IJReq::setResponse( 400 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}

		return $this->jsonarray;
	}

	// called by tags
	private function photoTags($uniqueID) {
		$query = "SELECT pt.*, p.creator
				FROM #__community_photos_tag as pt
				LEFT JOIN #__community_photos as p ON p.`id`=pt.`photoid`
				WHERE pt.`photoid`={$uniqueID}";
		$this->db->setQuery ( $query );
		$result = $this->db->loadObjectList ();
		if (count ( $result ) > 0) {
			$this->jsonarray ['code'] = 200;
		} else {
			IJReq::setResponse( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		foreach ( $result as $key => $value ) {
			$this->jsonarray ['tags'] [$key] ['id'] = $value->id;
			$this->jsonarray ['tags'] [$key] ['position'] = $value->position;
			$this->jsonarray ['tags'] [$key] ['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $value->created ) );
			$usr = $this->jomHelper->getUserDetail ( $value->userid );
			$this->jsonarray ['tags'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['tags'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['tags'] [$key] ['user_profile'] = $usr->profile;
			$this->jsonarray ['tags'] [$key] ['deleteAllowed'] = intval ( ($this->IJUserID == $value->creator or $this->IJUserID == $value->userid or $this->IJUserID == $value->created_by or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
		}
		return $this->jsonarray;
	}

	// called by tags
	private function videoTags($uniqueID) {
		$query = "SELECT *
				FROM #__community_videos_tag
				WHERE `videoid`={$uniqueID}";
		$this->db->setQuery ( $query );
		$result = $this->db->loadObjectList ();
		if (count ( $result ) > 0) {
			$this->jsonarray ['code'] = 200;
		} else {
			IJReq::setResponse( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		foreach ( $result as $key => $value ) {
			$this->jsonarray ['tags'] [$key] ['id'] = $value->id;
			$this->jsonarray ['tags'] [$key] ['position'] = $value->position;
			$this->jsonarray ['tags'] [$key] ['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $value->created ) );
			$user = &CFactory::getUser ( $value->userid );
			$this->jsonarray ['tags'] [$key] ['user_id'] = ($this->IJUserID == $user->id) ? 0 : $user->id;
			$this->jsonarray ['tags'] [$key] ['user_name'] = $this->jomHelper->getName ( $user );
			/*if ($user->_thumb) {
				$this->jsonarray ['tags'] [$key] ['user_thumb'] = JURI::base () . $user->_thumb;
			} else {
				$this->jsonarray ['tags'] [$key] ['user_thumb'] = JURI::base () . 'components/com_community/assets/photo_thumb.png';
			}*/

			$access_limit = $this->jomHelper->getUserAccess ( $this->IJUserID, $user->id );
			$params = $user->getParams ();
			$profileview = $params->get ( 'privacyProfileView' ); // get profile view access
			if ($profileview == 40 or $profileview > $access_limit) {
				$profileview = 0;
			} else {
				$profileview = 1;
			}
			$this->jsonarray ['tags'] [$key] ['profile'] = $profileview;
			$deleteAllowed = intval ( ($this->IJUserID == $value->userid or $this->IJUserID == $value->created_by or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
			$this->jsonarray ['tags'] [$key] ['deleteAllowed'] = $deleteAllowed;
		}
		return $this->jsonarray;
	}

	/**
	 * @uses to get friend list to tag
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"tagFriends",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // photo/video id
	 * 			"pageNO":"pageNO",
	 * 			"type":"type" // photos, videos
	 * 		}
	 * 	}
	 *
	 */
	function tagFriends() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' ); //  cid
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		$limit = PAGE_MEMBER_LIMIT;
		$type = IJReq::getTaskData ( 'type', 'photos' ); // callback
		if ($type == 'videos') {
			$type = 'videos,inviteUsers';
		}

		if ($pageNO == 0 || $pageNO == 1) {
			$startFrom = 0;
		} else {
			$startFrom = ($limit * ($pageNO - 1));
		}

		$filter = JFilterInput::getInstance ();
		$type = $filter->clean ( $type, 'string' );
		$uniqueID = $filter->clean ( $uniqueID, 'int' );
		$handlerName = '';
		$callbackOptions = explode ( ',', $type );
		if (isset ( $callbackOptions [0] )) {
			$handlerName = $callbackOptions [0];
		}

		$handler = CFactory::getModel ( $handlerName );

		$handlerFunc = 'getInviteListByName';
		$friends = '';
		$friends = $handler->$handlerFunc ( '', $this->IJUserID, $uniqueID, $startFrom, $limit );
		$total = count ( $friends );
		if ($total > 0) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['pageLimit'] = $limit;
			$this->jsonarray ['total'] = $total;
		} else {
			IJReq::setResponse( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		foreach ( $friends as $key => $user ) {
			$usr = $this->jomHelper->getUserDetail ( $user );
			$this->jsonarray ['member'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['member'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['member'] [$key] ['user_avatar'] = $usr->avatar;
		}
		return $this->jsonarray;
	}

	/**
	 * @uses to upload videos
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"addTag",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"position":"position", // optiona: if video tagging
	 * 			"userID":"userID",
	 * 			"type":"type"
	 * 		}
	 * 	}
	 *
	 */
	function addTag() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$position = IJReq::getTaskData ( 'position', null );
		$userID = IJReq::getTaskData ( 'userID', $this->IJUserID, 'int' );
		$type = IJReq::getTaskData ( 'type' );

		if (! $uniqueID or ! $type) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		switch ($type) {
			case 'photos' :
				$this->jsonarray = $this->addPhotoTag ( $uniqueID, $userID, $position );
				if (! $this->jsonarray) {
					return false;
				}
				// for push notification
				$message= COM_COMMUNITY_EMAIL_PHOTOS_TAGGING_TEXT;
				$photo			=& JTable::getInstance( 'Photo' , 'CTable' );
				$photo->load( $uniqueID );
				//album detail
				$album = JTable::getInstance ( 'Album', 'CTable' );
				$album->load ( $photo->albumid );
				$pushcontentdata['albumdetail']['id'] = $album->id;
				$pushcontentdata['albumdetail']['deleteAllowed'] = intval ( ($userID == $album->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
				if($userID == $album->creator){
					$uid=0;
				}else{
					$uid=$album->creator;
				}
				$pushcontentdata['albumdetail']['user_id'] = $uid;
				//Photo detail
				$pushcontentdata['photodetail']['id'] = $photo->id;
				$pushcontentdata['photodetail']['caption'] = $photo->caption;

				$p_url = JURI::base ();
				if ($photo->storage == 's3') {
					$s3BucketPath = $this->config->get ( 'storages3bucket' );
					if (! empty ( $s3BucketPath ))
						$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
				} else {
					if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
						$photo->image = $photo->original;
				}
				$pushcontentdata['photodetail']['thumb'] = $p_url . $photo->thumbnail;
				$pushcontentdata['photodetail']['url'] = $p_url . $photo->image;
				if (SHARE_PHOTOS == 1) {
					$pushcontentdata['photodetail']['shareLink'] = JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$userId}&albumid={$albumID}#photoid={$photo->id}";
				}

				//likes
				$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
				$pushcontentdata['photodetail']['likes'] = $likes->likes;
				$pushcontentdata['photodetail']['dislikes'] = $likes->dislikes;
				$pushcontentdata['photodetail']['liked'] = $likes->liked;
				$pushcontentdata['photodetail']['disliked'] = $likes->disliked;

				//comments
				$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
				$pushcontentdata['photodetail']['commentCount'] = $count;

				$query = "SELECT count(id)
						FROM #__community_photos_tag
						WHERE `photoid`={$photo->id}";
				$this->db->setQuery ( $query );
				$count = $this->db->loadResult ();
				$pushcontentdata['photodetail']['tags'] = $count;
				break;

			case 'videos' :
				$this->jsonarray = $this->addVideoTag ( $uniqueID, $userID, $position );
				if (! $this->jsonarray) {
					return false;
				}
				// for push notification
				$message= COM_COMMUNITY_EMAIL_VIDEOS_TAGGING_TEXT;
				//video detail
				$video = JTable::getInstance ( 'Video', 'CTable' );
				$video->load ($uniqueID);

				$video_file = $video->path;
				$p_url = JURI::root ();
				if ($video->type == 'file') {
					$ext = JFile::getExt ( $video->path );

					if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
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
				$pushcontentdata['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
				$pushcontentdata['url'] = $video_file;
				$pushcontentdata['description'] = $video->description;
				$pushcontentdata['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
				$pushcontentdata['location'] = $video->location;
				$pushcontentdata['permissions'] = $video->permissions;
				$pushcontentdata['categoryId'] = $video->category_id;

				$usr = $this->jomHelper->getUserDetail ( $video->creator );
				if($userID == $video->creator){
					$uid=0;
				}else{
					$uid=$usr->id;
				}
				$pushcontentdata['user_id'] = $uid;
				$pushcontentdata['user_name'] = $usr->name;
				$pushcontentdata['user_avatar'] = $usr->avatar;
				$pushcontentdata['user_profile'] = $usr->profile;

				//likes
				$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
				$pushcontentdata['likes'] = $likes->likes;
				$pushcontentdata['dislikes'] = $likes->dislikes;
				$pushcontentdata['liked'] = $likes->liked;
				$pushcontentdata['disliked'] = $likes->disliked;

				//comments
				$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
				$pushcontentdata['commentCount'] = $count;
				$pushcontentdata['deleteAllowed'] = intval ( ($userID == $video->creator or COwnerHelper::isCommunityAdmin ( $userID )) );

				if (SHARE_VIDEOS) {
					$pushcontentdata['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
				}

				$query = "SELECT count(id)
						FROM #__community_videos_tag
						WHERE `videoid`={$video->id}";
				$this->db->setQuery ( $query );
				$count = $this->db->loadResult ();
				$pushcontentdata['tags'] = $count;
				break;

			default :
				IJReq::setResponse( 400 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}
		//Send push notification
		$query="SELECT `jomsocial_params`,`device_token`,`device_type`
			FROM #__ijoomeradv_users
			WHERE `userid`={$userID}";
		$this->db->setQuery($query);
		$puser=$this->db->loadObject();
		$ijparams = new CParameter($puser->jomsocial_params);
		if($ijparams->get("pushnotif_{$type}_like")==1 && $userID!=$this->IJUserID && !empty($puser)){
			$actor=$this->jomHelper->getUserDetail($this->IJUserID);
			$target=$this->jomHelper->getUserDetail($userID);
			$search = array('{target}','{actor}','{url}');
			$replace = array($target->name,$actor->name,'');
			$typeupper = strtoupper($type);
			$message = str_replace($search,$replace,JText::_("COM_COMMUNITY_EMAIL_{$typeupper}_TAGGING_TEXT"));

			if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
				$options=array();
				$options['device_token']=$puser->device_token;
				$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
				$options['aps']['message']=strip_tags($message);
				$options['aps']['type']=$type;
				$options['aps']['content_data']=$pushcontentdata;
				$options['aps']['content_data']['type']=$type;
				IJPushNotif::sendIphonePushNotification($options);
			}

			if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
				$options=array();
				$options['registration_ids']=array($puser->device_token);
				$options['data']['message']=strip_tags($message);
				$options['data']['type']=$type;
				$options['data']['content_data']=$pushcontentdata;
				$options['data']['content_data']['type']=$type;
				IJPushNotif::sendAndroidPushNotification($options);
			}
		}
		return $this->jsonarray;
	}

	// called by addTag
	private function addPhotoTag($uniqueID, $userID, $position) {
		$query = "SELECT count(*)
				FROM #__community_photos_tag
				WHERE `photoid` = {$uniqueID}
				AND `userid` = {$userID}";
		$this->db->setQuery ( $query );
		$result = $this->db->loadResult ();

		if ($result > 0) {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		} else {
			$query = "INSERT INTO #__community_photos_tag
					(`photoid`, `userid`, `position`, `created_by`, `created`)
					VALUES ('{$uniqueID}', '{$userID}', '{$position}', '{$this->IJUserID}', now())";
			$this->db->setQuery ( $query );
			$this->db->Query ();
			if ($this->db->getErrorNum ()) {
				IJReq::setResponse( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			} else {
				$this->jsonarray ['code'] = 200;
				return $this->jsonarray;
			}
		}
	}

	// called by addTag
	private function addVideoTag($uniqueID, $userID, $position) {
		$query = "SELECT count(*)
				FROM #__community_videos_tag
				WHERE `videoid` = {$uniqueID}
				AND `userid` = {$userID}";
		$this->db->setQuery ( $query );
		$result = $this->db->loadResult ();
		if ($result > 0) {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		} else {
			$query = "INSERT INTO #__community_videos_tag
					(`videoid`, `userid`, `position`, `created_by`, `created`)
					VALUES ('{$uniqueID}', '{$userID}', '{$position}', '{$this->IJUserID}', now())";
			$this->db->setQuery ( $query );
			$this->db->Query ();
			if ($this->db->getErrorNum ()) {
				IJReq::setResponse( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			} else {
				$this->jsonarray ['code'] = 200;
				return $this->jsonarray;
			}
		}
	}

	/**
	 * @uses to remove tags
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"removeTag",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"type":"type"
	 * 		}
	 * 	}
	 *
	 */
	function removeTag() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$type = IJReq::getTaskData ( 'type' );

		if (! $uniqueID or ! $type) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		switch ($type) {
			case 'photos' :
				$this->jsonarray = $this->removePhotoTag ( $uniqueID );
				if (! $this->jsonarray) {
					return false;
				}
				break;

			case 'videos' :
				$this->jsonarray = $this->removeVideoTag ( $uniqueID );
				if (! $this->jsonarray) {
					return false;
				}
				break;

			default :
				IJReq::setResponse( 400 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}
		return $this->jsonarray;
	}

	// called by removeTag()
	private function removePhotoTag($uniqueID) {
		$query = "DELETE FROM `#__community_photos_tag`
				WHERE `id`={$uniqueID}";
		$this->db->setQuery ( $query );
		$this->db->Query ();
		if ($this->db->getErrorNum ()) {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} else {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		}
	}

	// called by removeTag()
	private function removeVideoTag($uniqueID) {
		$query = "DELETE FROM `#__community_videos_tag`
				WHERE `id`={$uniqueID}";
		$this->db->setQuery ( $query );
		$this->db->Query ();
		if ($this->db->getErrorNum ()) {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} else {
			$this->jsonarray ['code'] = 200;
			return $this->jsonarray;
		}
	}

	/**
	 * @uses to upload videos
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"uploadVideo",
	 * 		"taskData":{
	 * 			"videoID":"videoID" // to edit video information
	 * 			"title":"title",
	 * 			"description":"description",
	 * 			"caption":"caption", // caption is used when video added from profile screen
	 * 			"categoryID":"categoryID",
	 * 			"groupID":"groupID" // optional if uploading user video.
	 * 			"lat":"lat",
	 * 			"long":"long",
	 * 			"privacy":"privacy"
	 * 		}
	 * 	}
	 *
	 * photos will be post with name "image"
	 *
	 */

	function uploadVideo() {
		if (! $this->checkVideoAccess ()) {
			return false;
		}

		$videoID = IJReq::getTaskData ( 'videoID', null, 'int' );
		$caption = IJReq::getTaskData('caption','');
		$lat = IJReq::getTaskData ( 'lat' );
		$long = IJReq::getTaskData ( 'long' );
		$temp_loc = $this->jomHelper->getaddress ( $lat, $long );
		$location = $this->jomHelper->gettitle ( $temp_loc );
		$videos = JRequest::getVar ( 'video', '', 'FILES', 'array' );
		$groupID = IJReq::getTaskData ( 'groupID', 0, 'int' );
		if ($groupID) {
			$creatorType = 'group';
		} else {
			$creatorType = 'user';
		}

		CFactory::load ( 'helpers', 'videos' );
		CFactory::load ( 'libraries', 'videos' );

		// 	@rule: Do not allow users to add more videos than they are allowed to
		CFactory::load ( 'libraries', 'limits' );
		CFactory::load ( 'models', 'videos' );

		CFactory::load ( 'models', 'videos' );
		$video = JTable::getInstance ( 'Video', 'CTable' );
		if ($videoID) {
			$video->load ( $videoID );
		} else {
			// Process according to video creator type
			if (! empty ( $groupID )) {
				CFactory::load ( 'helpers', 'group' );
				$allowManageVideos = CGroupHelper::allowManageVideo ( $groupID );
				$creatorType = VIDEO_GROUP_TYPE;
				$videoLimit = $this->config->get ( 'groupvideouploadlimit' );
				//CError::assert($allowManageVideos, '', '!empty', __FILE__ , __LINE__ );
			} else {
				$creatorType = VIDEO_USER_TYPE;
				$videoLimit = $this->config->get ( 'videouploadlimit' );
			}

			// Check is video upload is permitted
			CFactory::load ( 'helpers', 'limits' );

			if (! $this->config->get ( 'enablevideos' )) {
				IJReq::setResponse( 706,JText::_ ( 'COM_COMMUNITY_VIDEOS_VIDEO_DISABLED' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if (! $this->config->get ( 'enablevideosupload' )) {
				IJReq::setResponse( 706,JText::_ ( 'COM_COMMUNITY_VIDEOS_VIDEO_DISABLED' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$videoFile = (! empty ( $videos ['name'] )) ? $videos : array ();

			if (empty ( $videos ) || (empty ( $videoFile ['name'] ) && $videoFile ['size'] < 1)) {
				IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_VIDEOS_UPLOAD_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$fileType = $videoFile ['type'];
			$allowable = CVideosHelper::getValidMIMEType ();
			if (! in_array ( $fileType, $allowable )) {
				IJReq::setResponse( 415,JText::_ ( 'COM_COMMUNITY_VIDEOS_FILETYPE_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// Check if the video file exceeds file size limit
			$uploadLimit = $this->config->get ( 'maxvideouploadsize' ) * 1024 * 1024;
			$videoFileSize = sprintf ( "%u", filesize ( $videoFile ['tmp_name'] ) );
			if (($uploadLimit > 0) && ($videoFileSize > $uploadLimit)) {
				IJReq::setResponse( 415,JText::_ ( 'COM_COMMUNITY_VIDEOS_FILE_SIZE_EXCEEDED' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// Passed all checking, attempt to save the video file
			CFactory::load ( 'helpers', 'file' );
			$folderPath = CVideoLibrary::getPath ( $this->my->id, 'original' );
			$randomFileName = CFileHelper::getRandomFilename ( $folderPath, $videoFile ['name'], '' );
			$destination = JPATH::clean ( $folderPath .'/'. $randomFileName );
			$tempFile = JPATH::clean ( $folderPath .'/'. "temp_" . $randomFileName );

			if (! CFileHelper::upload ( $videoFile, $destination )) {
				IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_VIDEOS_UPLOAD_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			} else {
				$orientation = JRequest::getVar ( 'orientation' );
				if ($orientation == 'portrait') {
					jimport ( 'joomla.filesystem.file' );
					JFile::copy ( $destination, $tempFile );
					$ffmpeg = $config->get ( 'ffmpegPath' );
					if ($ffmpeg != '') {
						if (shell_exec ( $ffmpeg . ' -y -i ' . $tempFile . ' -vf "transpose=1" -sameq ' . $destination )) {
							JFile::delete ( $tempFile );
						}
					}
				}
			}

			$videofolder = $this->config->get ( 'videofolder' );

			$video->set ( 'creator', $this->my->id );
			$video->set ( 'creator_type', $creatorType );
			$video->set ( 'path', $videofolder . '/originalvideos/' . $this->my->id . '/' . $randomFileName );
			$video->set ( 'groupid', $groupID );
			$video->set ( 'filesize', $videoFileSize );
		}

		$video->set ( 'title', IJReq::getTaskData ( 'title', 'untitled' ) );
		$video->set ( 'description', IJReq::getTaskData ( 'description', '' ) );
		$video->set ( 'permissions', IJReq::getTaskData ( 'privacy', 0, 'int' ) );
		$video->set ( 'category_id', IJReq::getTaskData ( 'categoryID', 0, 'int' ) );
		$video->set ( 'location', $location );
		$video->set ( 'latitude', $lat );
		$video->set ( 'longitude', $long );

		if (! $video->store ()) {
			IJReq::setResponse( 500,JText::_ ( 'COM_COMMUNITY_VIDEOS_SAVE_ERROR' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} else {
			$this->jsonarray ['code'] = 200;
		}

		//add notification: New group album is added
		if ($video->groupid != 0) {
			CFactory::load ( 'libraries', 'notification' );
			$group = JTable::getInstance ( 'Group', 'CTable' );
			$group->load ( $video->groupid );

			CFactory::load ( 'models', 'groups' );
			//$modelGroup			=& $this->getModel( 'groups' );
			$modelGroup = new CommunityModelGroups ( );
			$groupMembers = array ();
			$groupMembers = $modelGroup->getMembersId ( $video->groupid, true );

			$params = new CParameter ( '' );
			$params->set ( 'title', $video->title);
			$params->set ( 'group', $group->name );
			$params->set ( 'url', 'index.php?option=com_community&view=videos&task=video&videoid=' . $video->id );
			CNotificationLibrary::add ( 'etype_groups_create_video', $this->my->id, $groupMembers, JText::sprintf ( 'COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION', $this->my->getDisplayName (), $group->name ).$caption, '', 'groups.video', $params );

			$memberlist = implode(',',$groupMembers);
			$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid` IN ({$memberlist})";
			$this->db->setQuery($query);
			$puserlist=$this->db->loadObjectList();

			$video_file = $video->path;
			$p_url = JURI::root ();
			if ($video->type == 'file') {
				$ext = JFile::getExt ( $video->path );

				if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
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

			$videodata['groupid'] = $group->id;
			$videodata['id'] = $video->id;
			$videodata['caption'] = $video->title;
			$videodata['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
			$videodata['url'] = $video_file;
			$videodata['description'] = $video->description;
			$videodata['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
			$videodata['location'] = $video->location;
			$videodata['permissions'] = $video->permissions;
			$videodata['categoryId'] = $video->category_id;

			$usr = $this->jomHelper->getUserDetail($video->creator);
			$videodata['user_id'] = $usr->id;
			$videodata['user_name'] = $usr->name;
			$videodata['user_avatar'] = $usr->avatar;
			$videodata['user_profile'] = $usr->profile;

			//likes
			$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
			$videodata['likes'] = $likes->likes;
			$videodata['dislikes'] = $likes->dislikes;
			$videodata['liked'] = $likes->liked;
			$videodata['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
			$videodata['commentCount'] = $count;

			if (SHARE_VIDEOS) {
				$videodata['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
			}

			/*$photoModel	=& CFactory::getModel( 'photos' );
			$albums		= $photoModel->getGroupAlbums($group->id);

			$pushcontentdata['id']	= $group->id;
			$allowManageVideos	= CGroupHelper::allowManageVideo( $group->id );
			if( $allowManageVideos && $this->config->get('groupvideos') && $this->config->get('enablevideos') ){
				$pushcontentdata['addVideo'] = 1;
			}else{
				$pushcontentdata['addVideo'] = 0;
			}*/

			$query = "SELECT count(id)
					FROM #__community_videos_tag
					WHERE `videoid`={$video->id}";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$videodata['tags'] = $count;

			foreach ($puserlist as $puser){
				$ijparams = new CParameter($puser->jomsocial_params);
				if($ijparams->get('pushnotif_groups_create_video')==1 && $puser->userid!=$this->IJUserID && !empty($puser)){
					//$usr = $this->jomHelper->getUserDetail ($puser->userid);
					$videodata['deleteAllowed'] = intval (COwnerHelper::isCommunityAdmin($puser->userid));
					$match = array('{actor}','{video}','{group}');
					$replace = array($this->my->getDisplayName(),$video->title,$group->name);
					$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION'));
					if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
						$options=array();
						$options['device_token']=$puser->device_token;
						$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
						$options['aps']['message']=strip_tags($message);
						$options['aps']['type']=($groupID)?'group':'user';
						$options['aps']['content_data']=$videodata;
						$options['aps']['content_data']['type']='video';
						IJPushNotif::sendIphonePushNotification($options);
					}

					if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
						$options=array();
						$options['registration_ids']=array($puser->device_token);
						$options['data']['message']=strip_tags($message);
						$options['data']['type']=($groupID)?'group':'user';
						$options['data']['content_data']=$videodata;
						$options['data']['content_data']['type']='video';
						IJPushNotif::sendAndroidPushNotification($options);
					}
				}
			}
		}

		$query = "SELECT cv.type,cv.path
				FROM #__community_videos AS cv
				WHERE cv.id = '{$video->id}' ";
		$this->db->setQuery ( $query );
		$videos = $this->db->loadObject ();
		$p_url = JURI::base ();

		if ($videos->type == 'file') {
			$ext = JFile::getExt ( $video->path );

			if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $videos->path )) {
				$video_file = JURI::root () . $videos->path;
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
		$this->jsonarray ['url'] = $video_file;
		return $this->jsonarray;
	}

	/**
	 * @uses get Video category listing
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"videoCategories",
	 * 		"taskData":{
	 * 		}
	 * 	}
	 *
	 */
	function videoCategories() {
		$query = "SELECT *
				FROM #__community_videos_category
				WHERE `published`='1'";
		$this->db->setQuery ( $query );
		$categories = $this->db->loadObjectlist ();

		if (count ( $categories ) > 0) {
			$this->jsonarray ['code'] = 200;
		} else {
			$this->jsonarray ['code'] = 204;
		}

		foreach ( $categories as $key => $category ) {
			$this->jsonarray ['categories'] [$key] ['id'] = $category->id;
			$this->jsonarray ['categories'] [$key] ['name'] = $category->name;
			$this->jsonarray ['categories'] [$key] ['desc'] = $category->description;
			$query = "SELECT count(*)
					FROM #__community_videos
					WHERE category_id={$category->id}
					AND `status`='ready'
					AND `permissions`<=20";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$this->jsonarray ['categories'] [$key] ['count'] = $count;
		}
		return $this->jsonarray;
	}

	/**
	 * @uses get all video listing
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"allVideos",
	 * 		"taskData":{
	 * 			"categoryID":"categoryID",
	 * 			"privacy":"privacy", // 20 (default)
	 * 			"groupPrivacy":"groupPrivacy",
	 * 			"sort":"sort", // latest(default), mostwalls, mostviews, title
	 * 			"pageNO":"pageNO",
	 * 			"withLimit":"withLimit" // TRUE(default), FALSE
	 * 		}
	 * 	}
	 *
	 */
	function allVideos() {
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		$groupID = IJReq::getTaskData ( 'groupID', 0, 'int' );
		if ($groupID) {
			$filters = array ('groupid' => $groupID, 'published' => 1, 'status' => 'ready', 'category_id' => IJReq::getTaskData ( 'categoryID', NULL ), 'creator_type' => VIDEO_GROUP_TYPE, 'sorting' => IJReq::getTaskData ( 'sort', 'latest' ), 'limitstart' => ($pageNO == 0 || $pageNO == 1) ? 0 : (PAGE_VIDEO_LIMIT * ($pageNO - 1)) );
		} else {
			$filters = array ('limitstart' => ($pageNO == 0 || $pageNO == 1) ? 0 : (PAGE_VIDEO_LIMIT * ($pageNO - 1)), 'status' => 'ready', 'category_id' => IJReq::getTaskData ( 'categoryID', NULL ), 'permissions' => IJReq::getTaskData ( 'privacy', 0, 'int' ), 'sorting' => IJReq::getTaskData ( 'sort', 'latest' ) );
		}

		//$filters ['or_group_privacy'] = IJReq::getTaskData ( 'groupPrivacy', 0, 'int' );
		$withlimit = (IJReq::getTaskData ( 'withLimit', 'true', 'bool' )) ? TRUE : FALSE;

		$this->jsonarray = $this->videos ( $filters, PAGE_VIDEO_LIMIT, $withlimit );
		if (! $this->jsonarray) {
			return false;
		}

		return $this->jsonarray;
	}

	/**
	 * @uses get my video listing
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"myVideos",
	 * 		"taskData":{
	 * 			"userID":"userID",
	 * 			"sort":"sort", // latest(default), mostwalls, mostviews, title
	 * 			"pageNO":"pageNO",
	 * 			"withLimit":"withLimit" // TRUE(default), FALSE
	 * 		}
	 * 	}
	 *
	 */
	function myVideos() {
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		$filters = array ();
		if ($pageNO == 0 || $pageNO == 1) {
			$filters ['limitstart'] = 0;
		} else {
			$filters ['limitstart'] = (PAGE_VIDEO_LIMIT * ($pageNO - 1));
		}

		$filters ['status'] = 'ready';
		$filters ['creator'] = IJReq::getTaskData ( 'userID', $this->IJUserID );
		$filters ['sorting'] = IJReq::getTaskData ( 'sort', 'latest' );
		$withlimit = (IJReq::getTaskData ( 'withLimit', 'true', 'bool' )) ? TRUE : FALSE;

		$access_limit = $this->jomHelper->getUserAccess($this->IJUserID,$filters ['creator']);
		$query = "SELECT params
					FROM #__community_users
					WHERE userid=".$filters ['creator'];
		$this->db->setQuery($query);
		$params = new CParameter($this->db->loadResult());
		//echo $access_limit;echo $params->get('privacyFriendsView');exit;
		if($access_limit<$params->get('privacyVideoView')){
			IJReq::setResponse( 706 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray = $this->videos ( $filters, PAGE_VIDEO_LIMIT, $withlimit );
		if (! $this->jsonarray) {
			return false;
		}

		return $this->jsonarray;
	}

	// video fached for allvideo and myvideo.
	private function Videos($filters, $limit, $limitSetting) {
		$where = array ();
		foreach ( $filters as $field => $value ) {
			if ($value || $value === 0) {
				switch (strtolower ( $field )) {
					case 'id' :
						if (is_array ( $value )) {
							JArrayHelper::toInteger ( $value );
							$value = implode ( ',', $value );
						}
						$where [] = 'v.' . $this->db->quoteName ( 'id' ) . ' IN (' . $value . ')';
						break;
					case 'title' :
						$where [] = 'v.' . $this->db->quoteName ( 'title' ) . '  LIKE ' . $this->db->quote ( '%' . $value . '%' );
						break;
					case 'type' :
						$where [] = 'v.' . $this->db->quoteName ( 'type' ) . ' = ' . $this->db->quote ( $value );
						break;
					case 'description' :
						$where [] = 'v.' . $this->db->quoteName ( 'description' ) . ' LIKE ' . $this->db->quote ( '%' . $value . '%' );
						break;
					case 'creator' :
						$where [] = 'v.' . $this->db->quoteName ( 'creator' ) . ' = ' . $this->db->quote ( ( int ) $value );
						break;
					case 'creator_type' :
						$where [] = 'v.' . $this->db->quoteName ( 'creator_type' ) . ' = ' . $this->db->quote ( $value );
						break;
					case 'created' :
						$value = JFactory::getDate ( $value )->toSql ();
						$where [] = 'v.' . $this->db->quoteName ( 'created' ) . ' BETWEEN ' . $this->db->quote ( '1970-01-01 00:00:01' ) . ' AND ' . $this->db->quote ( $value );
						break;
					case 'permissions' :
						$where [] = 'v.' . $this->db->quoteName ( 'permissions' ) . ' <= ' . $this->db->quote ( ( int ) $value );
						break;
					case 'category_id' :
						if (is_array ( $value )) {
							JArrayHelper::toInteger ( $value );
							$value = implode ( ',', $value );
						}
						$where [] = 'v.' . $this->db->quoteName ( 'category_id' ) . ' IN (' . $value . ')';
						break;
					case 'hits' :
						$where [] = 'v.' . $this->db->quoteName ( 'hits' ) . ' >= ' . $this->db->quote ( ( int ) $value );
						break;
					case 'published' :
						$where [] = 'v.' . $this->db->quoteName ( 'published' ) . ' = ' . $this->db->quote ( ( bool ) $value );
						break;
					case 'featured' :
						$where [] = 'v.' . $this->db->quoteName ( 'featured' ) . ' = ' . $this->db->quote ( ( bool ) $value );
						break;
					case 'duration' :
						$where [] = 'v.' . $this->db->quoteName ( 'duration' ) . ' >= ' . $this->db->quote ( ( int ) $value );
						break;
					case 'status' :
						$where [] = 'v.' . $this->db->quoteName ( 'status' ) . ' = ' . $this->db->quote ( $value );
						break;
					case 'groupid' :
						$where [] = 'v.' . $this->db->quoteName ( 'groupid' ) . ' = ' . $this->db->quote ( $value );
						break;
				}
			}
		}

		$where = count ( $where ) ? ' WHERE ' . implode ( ' AND ', $where ) : '';

		// Joint with group table
		$join = '';
		if (isset ( $filters ['or_group_privacy'] )) {
			$approvals = ( int ) $filters ['or_group_privacy'];
			$join = ' LEFT JOIN ' . $this->db->quoteName ( '#__community_groups' ) . ' AS g';
			$join .= ' ON g.' . $this->db->quoteName ( 'id' ) . ' = v.' . $this->db->quoteName ( 'groupid' );
			$where .= ' AND (g.' . $this->db->quoteName ( 'approvals' ) . ' = ' . $this->db->Quote ( '0' ) . ' OR g.' . $this->db->quoteName ( 'approvals' ) . ' IS NULL)';
		}

		$order = '';
		$sorting = isset ( $filters ['sorting'] ) ? $filters ['sorting'] : 'latest';

		switch ($sorting) {
			case 'mostwalls' :
			// mostwalls is sorted below using JArrayHelper::sortObjects
			// since in db vidoes doesn't has wallcount field
			case 'mostviews' :
				$order = ' ORDER BY v.' . $this->db->quoteName ( 'hits' ) . ' DESC';
				break;
			case 'title' :
				$order = ' ORDER BY v.' . $this->db->quoteName ( 'title' ) . ' ASC';
				break;
			case 'latest' :
			default :
				$order = ' ORDER BY v.' . $this->db->quoteName ( 'created' ) . ' DESC';
				break;
		}

		if ($limitSetting == true) {
			$limiter = ' LIMIT ' . $filters ['limitstart'] . ', ' . $limit;
		} else {
			$limiter = '';
		}

		$query = ' SELECT v.*, v.' . $this->db->quoteName ( 'created' ) . ' AS lastupdated' . ' FROM ' . $this->db->quoteName ( '#__community_videos' ) . ' AS v' . $join . $where . $order . $limiter;
		$this->db->setQuery ( $query );
		$result = $this->db->loadObjectList ();
		if ($this->db->getErrorNum ()) {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Get total of records to be used in the pagination
		$query = ' SELECT COUNT(*)' . ' FROM ' . $this->db->quoteName ( '#__community_videos' ) . ' AS v' . $join . $where;
		$this->db->setQuery ( $query );
		$total = $this->db->loadResult ();
		if ($this->db->getErrorNum ()) {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if ($total > 0) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['total'] = $total;
			$this->jsonarray ['pageLimit'] = $limit;
		} else {
			IJReq::setResponse( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Add the wallcount property for sorting purpose
		foreach ( $result as $video ) {
			// Wall post count
			$query = "SELECT COUNT(*)
					FROM {$this->db->quoteName('#__community_wall')}
					WHERE {$this->db->quoteName ( 'type' )} = {$this->db->quote ( 'videos' )}
					AND {$this->db->quoteName ( 'published' )} = {$this->db->quote ( 1 )}
					AND {$this->db->quoteName ( 'contentid' )} = {$this->db->quote ( $video->id )}";
			$this->db->setQuery ( $query );
			$video->wallcount = $this->db->loadResult ();
		}

		// Sort videos according to wall post count
		if ($sorting == 'mostwalls')
			JArrayHelper::sortObjects ( $result, 'wallcount', - 1 );

		foreach ( $result as $key => $video ) {
			$video_file = $video->path;
			$p_url = JURI::root ();
			if ($video->type == 'file') {
				$ext = JFile::getExt ( $video->path );

				if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
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

			$this->jsonarray ['videos'] [$key] ['id'] = $video->id;
			$this->jsonarray ['videos'] [$key] ['caption'] = $video->title;
			$this->jsonarray ['videos'] [$key] ['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
			$this->jsonarray ['videos'] [$key] ['url'] = $video_file;
			$this->jsonarray ['videos'] [$key] ['description'] = $video->description;
			$this->jsonarray ['videos'] [$key] ['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
			$this->jsonarray ['videos'] [$key] ['location'] = $video->location;
			$this->jsonarray ['videos'] [$key] ['permissions'] = $video->permissions;
			$this->jsonarray ['videos'] [$key] ['categoryId'] = $video->category_id;

			$usr = $this->jomHelper->getUserDetail ( $video->creator );
			$this->jsonarray ['videos'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['videos'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['videos'] [$key] ['user_avatar'] = $usr->avatar;
			$this->jsonarray ['videos'] [$key] ['user_profile'] = $usr->profile;

			//likes
			$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
			$this->jsonarray ['videos'] [$key] ['likes'] = $likes->likes;
			$this->jsonarray ['videos'] [$key] ['dislikes'] = $likes->dislikes;
			$this->jsonarray ['videos'] [$key] ['liked'] = $likes->liked;
			$this->jsonarray ['videos'] [$key] ['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
			$this->jsonarray ['videos'] [$key] ['commentCount'] = $count;
			$this->jsonarray ['videos'] [$key] ['deleteAllowed'] = intval ( ($this->IJUserID == $video->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );

			if (SHARE_VIDEOS) {
				$this->jsonarray ['videos'] [$key] ['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
			}

			$query = "SELECT count(id)
					FROM #__community_videos_tag
					WHERE `videoid`={$video->id}";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$this->jsonarray ['videos'] [$key] ['tags'] = $count;
		}
		return $this->jsonarray;
	}

	/**
	 * @uses remove video
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"removeVideo",
	 * 		"taskData":{
	 * 			"videoID":"videoID"
	 * 		}
	 * 	}
	 *
	 */
	function removeVideo() {
		$videoID = IJReq::getTaskData ( 'videoID', 0, 'int' );

		if (! $videoID) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load libraries
		CFactory::load ( 'models', 'videos' );
		$video = JTable::getInstance ( 'Video', 'CTable' );
		$video->load ( ( int ) $videoID );

		if (! empty ( $video->groupid )) {
			CFactory::load ( 'helpers', 'group' );
			$allowManageVideos = CGroupHelper::allowManageVideo ( $video->groupid );
		}

		// @rule: Add point when user removes a video
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'video.remove', $video->creator );

		if ($video->delete ()) {
			// Delete all videos related data
			$this->_deleteVideoWalls ( $video->id );
			$this->_deleteVideoActivities ( $video->id );
			$this->_deleteFeaturedVideos ( $video->id );
			if (! $this->_deleteVideoFiles ( $video )) {
				return false;
			}
			if (! $this->_deleteProfileVideo ( $video->creator, $video->id )) {
				return false;
			}

			if (! empty ( $video->groupid )) {
				$this->jsonarray ['code'] = 200;
			} else {
				$this->jsonarray ['code'] = 200;
			}

		} else {
			IJReq::setResponse( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return $this->jsonarray;
	}

	// called from removeVideo
	private function _deleteVideoWalls($id = 0) {
		CFactory::load ( 'helpers', 'owner' );
		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$video = CFactory::getModel ( 'Videos' );
		$video->deleteVideoWalls ( $id );
	}

	// called from removeVideo
	private function _deleteVideoActivities($id = 0) {
		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$video = CFactory::getModel ( 'Videos' );
		$video->deleteVideoActivities ( $id );
	}

	// called from removeVideo
	private function _deleteFeaturedVideos($id = 0) {
		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'libraries', 'featured' );
		$featuredVideo = new CFeatured ( FEATURED_VIDEOS );
		$featuredVideo->delete ( $id );
	}

	// called from removeVideo
	private function _deleteVideoFiles($video) {
		if (! $video) {
			IJReq::setResponse( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'libraries', 'storage' );
		$storage = CStorage::getStorage ( $video->storage );

		if ($storage->exists ( $video->thumb )) {
			$storage->delete ( $video->thumb );
		}

		if ($storage->exists ( $video->path )) {
			$storage->delete ( $video->path );
		}
		return true;
	}

	// called from removeVideo
	private function _deleteProfileVideo($creator, $deletedvideoid) {
		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Only the video creator can use the video as his/her profile video
		$user = CFactory::getUser ( $creator );

		// Set params to default(0 for no profile video)
		$params = $user->getParams ();

		$videoid = $params->get ( 'profileVideo', 0 );

		// Check if the current profile video id same with the deleted video id
		if ($videoid == $deletedvideoid) {
			$params->set ( 'profileVideo', 0 );
			$user->save ( 'params' );
		}

		return true;
	}


	/**
	 * @uses remove video
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"linkVideo",
	 * 		"taskData":{
	 * 			"url":"url",
	 * 			"lat":"lat",
	 * 			"long":"long",
	 * 			"categoryID":"categoryID",
	 * 			"groupID":"groupID", // optional for user video.
	 * 			"privacy":"privacy", // optional for group videos.
	 * 			"caption":"caption"
	 * 		}
	 * 	}
	 *
	 */
	function linkVideo() {
		if (! $this->checkVideoAccess ()) {
			return false;
		}
		$url		= IJReq::getTaskData ( 'url', NULL );
		$lat		= IJReq::getTaskData ( 'lat' );
		$long		= IJReq::getTaskData ( 'long' );
		$temp_loc	= $this->jomHelper->getaddress ( $lat, $long );
		$location	= $this->jomHelper->gettitle ( $temp_loc );
		$caption	= IJReq::getTaskData('caption',NULL);
		$categoryID	= IJReq::getTaskData ( 'categoryID', 0, 'int' );
		$permission	= IJReq::getTaskData ( 'privacy', 0, 'int' );
		$groupID	= IJReq::getTaskData ( 'groupID', 0, 'int' );
		$type 		= ($groupID) ? VIDEO_GROUP_TYPE : VIDEO_USER_TYPE;

		require_once JPATH_ROOT . '/components/com_community/helpers/remote.php';
		require_once JPATH_ROOT . '/components/com_community/helpers/videos.php';
		require_once JPATH_ROOT . '/components/com_community/helpers/limits.php';
		require_once JPATH_ROOT . '/components/com_community/libraries/videos.php';
		require_once JPATH_ROOT . '/components/com_community/models/models.php';
		require_once JPATH_ROOT . '/components/com_community/models/videos.php';

		// Preset the redirect url according to group type or user type
		CFactory::load ( 'helpers', 'videos' );
		//$redirect = CVideosHelper::getVideoReturnUrlFromRequest ();
		$group = JTable::getInstance ( 'Group', 'CTable' );
		$group->load ( $groupID );

		if ($group->approvals) {
			$permission = 40;
		}

		if (! CRemoteHelper::curlExists ()) {
			IJReq::setResponse ( 500 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (CLimitsHelper::exceededVideoUpload ( $my->id, $type )) {
			IJReq::setResponse ( 416,JText::sprintf ( 'COM_COMMUNITY_VIDEOS_CREATION_LIMIT_ERROR', $videoLimit ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (empty ( $url )) {
			IJReq::setResponse ( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$videoLib = new CVideoLibrary ( );
		$video = JTable::getInstance ( 'Video', 'CTable' );
		$isValid = $video->init ( $url );

		if (! $isValid) {
			IJReq::setResponse ( 400,JText::_ ( 'COM_COMMUNITY_VIDEOS_INVALID_VIDEO_LINKS' ) );
			return false;
		}

		$video->set ( 'creator', $this->my->id );
		$video->set ( 'creator_type', $type );
		$video->set ( 'permissions', $permission );
		$video->set ( 'category_id', $categoryID );
		$video->set ( 'location', $location );
		$video->set ( 'groupid', $groupID );

		if (! $video->store ()) {
			$this->jsonarray ['code'] = 500;
			return false;
		}

		//add notification: New group album is added
		if ($video->groupid != 0) {
			CFactory::load ( 'libraries', 'notification' );
			$group = JTable::getInstance ( 'Group', 'CTable' );
			$group->load ( $video->groupid );

			$modelGroup = & CFactory::getModel ( 'groups' );
			$groupMembers = array ();
			$groupMembers = $modelGroup->getMembersId ( $video->groupid, true );

			$params = new CParameter ( '' );
			$params->set ( 'title', $video->title );
			$params->set ( 'group', $group->name );
			$params->set ( 'group_url', 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );
			$params->set ( 'video', $video->title );
			$params->set ( 'video_url', 'index.php?option=com_community&view=videos&task=videos&groupid=' . $group->id . '&videoid=' . $video->id );
			$params->set ( 'url', 'index.php?option=com_community&view=videos&task=video&groupid=' . $group->id . '&videoid=' . $video->id );
			CNotificationLibrary::add ( 'groups_create_video', $this->my->id, $groupMembers, JText::sprintf ( 'COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION' ), '', 'groups.video', $params );

			$memberlist = implode(',',$groupMembers);
			$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid` IN ({$memberlist})";
			$this->db->setQuery($query);
			$puserlist=$this->db->loadObjectList();

			$video_file = $video->path;
			$p_url = JURI::root ();
			if ($video->type == 'file') {
				$ext = JFile::getExt ( $video->path );

				if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
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

			$videodata['groupid'] = $group->id;
			$videodata['id'] = $video->id;
			$videodata['caption'] = $video->title;
			$videodata['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
			$videodata['url'] = $video_file;
			$videodata['description'] = $video->description;
			$videodata['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
			$videodata['location'] = $video->location;
			$videodata['permissions'] = $video->permissions;
			$videodata['categoryId'] = $video->category_id;

			$usr = $this->jomHelper->getUserDetail($video->creator);
			$videodata['user_id'] = $usr->id;
			$videodata['user_name'] = $usr->name;
			$videodata['user_avatar'] = $usr->avatar;
			$videodata['user_profile'] = $usr->profile;

			//likes
			$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
			$videodata['likes'] = $likes->likes;
			$videodata['dislikes'] = $likes->dislikes;
			$videodata['liked'] = $likes->liked;
			$videodata['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
			$videodata['commentCount'] = $count;

			if (SHARE_VIDEOS) {
				$videodata['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
			}

			$query = "SELECT count(id)
					FROM #__community_videos_tag
					WHERE `videoid`={$video->id}";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$videodata['tags'] = $count;

			foreach ($puserlist as $puser){
				$ijparams = new CParameter($puser->jomsocial_params);
				if($ijparams->get('pushnotif_groups_create_video')==1 && $puser->userid!=$this->IJUserID && !empty($puser)){
					$videodata['deleteAllowed'] = intval (COwnerHelper::isCommunityAdmin ( $puser->userid ));
					$match = array('{actor}','{video}','{group}');
					$replace = array($this->my->getDisplayName(),$video->title,$group->name);
					$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_GROUP_NEW_VIDEO_NOTIFICATION'));
					if(IJOOMER_PUSH_ENABLE_IPHONE==1 && $puser->device_type=='iphone'){
						$options=array();
						$options['device_token']=$puser->device_token;
						$options['live']=intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
						$options['aps']['message']=strip_tags($message);
						$options['aps']['type']=($groupID)?'group':'user';
						$options['aps']['content_data']=$videodata;
						$options['aps']['content_data']['type']='videos';
						IJPushNotif::sendIphonePushNotification($options);
					}

					if(IJOOMER_PUSH_ENABLE_ANDROID==1 && $puser->device_type=='android'){
						$options=array();
						$options['registration_ids']=array($puser->device_token);
						$options['data']['message']=strip_tags($message);
						$options['data']['type']=($groupID)?'group':'user';
						$options['data']['content_data']=$videodata;
						$options['data']['content_data']['type']='videos';
						IJPushNotif::sendAndroidPushNotification($options);
					}
				}
			}
		}

		// Trigger for onVideoCreate
		$this->_triggerEvent ( 'onVideoCreate', $video );

		// Fetch the thumbnail and store it locally,
		// else we'll use the thumbnail remotely
		if (! empty ( $video->thumb )) {
			if ($this->_fetchThumbnail ( $video->id )) {
				//return false;
			}
		}

		// Add activity logging
		$url = $video->getViewUri ( false );

		$act = new stdClass ( );
		$act->cmd = 'videos.upload';
		$act->actor = $this->my->id;
		$act->access = $video->permissions;
		$act->target = 0;
		$act->title = $this->my->getDisplayName () . " ► " . JText::_ ( 'COM_COMMUNITY_ACTIVITIES_UPLOAD_VIDEO' ).$caption; // since 2.4, sharing video will hide the title subject
		$act->app = 'videos';
		$act->content = '';
		$act->cid = $video->id;
		$act->location = $video->location;
		$act->comment_id = $video->id;
		$act->comment_type = 'videos';
		$act->like_id = $video->id;
		$act->like_type = 'videos';
		$act->groupid = ($video->groupid != 0) ? $video->groupid : 0;

		$params = new CParameter ( '' );
		$params->set ( 'video_url', $url );

		CFactory::load ( 'libraries', 'activities' );
		CActivityStream::add ( $act, $params->toString () );

		// @rule: Add point when user adds a new video link
		CFactory::load ( 'libraries', 'userpoints' );
		CUserPoints::assignPoint ( 'video.add', $video->creator );
		$this->jsonarray ['code'] = 200;
		//$this->jsonarray['video_id'] = $video->id;
		return $this->jsonarray;
	}

	private function _triggerEvent($event, $args) {
		// Trigger for onVideoCreate
		CFactory::load ( 'libraries', 'apps' );
		$apps = & CAppPlugins::getInstance ();
		$apps->loadApplications ();
		$params = array ();
		$params [] = $args;
		$apps->triggerEvent ( $event, $params );
	}

	private function _fetchThumbnail($id = 0, $returnThumb = false) {
		if (! $id) {
			IJReq::setResponse ( 400 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'models', 'videos' );
		$table = JTable::getInstance ( 'Video', 'CTable' );
		$table->load ( $id );

		CFactory::load ( 'helpers', 'videos' );
		CFactory::load ( 'libraries', 'videos' );

		if ($table->type == 'file') {
			// We can only recreate the thumbnail for local video file only
			// it's not possible to process remote video file with ffmpeg
			if ($table->storage != 'file') {
				IJReq::setResponse ( 404,JText::_ ( 'COM_COMMUNITY_INVALID_FILE_REQUEST' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$videoLib = new CVideoLibrary ( );

			$videoFullPath = JPATH::clean ( JPATH_ROOT .'/'. $table->path );
			if (! JFile::exists ( $videoFullPath )) {
				IJReq::setResponse ( 404 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// Read duration
			$videoInfo = $videoLib->getVideoInfo ( $videoFullPath );

			if (! $videoInfo) {
				IJReq::setResponse ( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			} else {
				$videoFrame = CVideosHelper::formatDuration ( ( int ) ($videoInfo ['duration'] ['sec'] / 2), 'HH:MM:SS' );

				// Create thumbnail
				$oldThumb = $table->thumb;
				$thumbFolder = CVideoLibrary::getPath ( $table->creator, 'thumb' );
				$thumbSize = CVideoLibrary::thumbSize ();
				$thumbFilename = $videoLib->createVideoThumb ( $videoFullPath, $thumbFolder, $videoFrame, $thumbSize );
			}

			if (! $thumbFilename) {
				IJReq::setResponse ( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		} else {
			CFactory::load ( 'helpers', 'remote' );
			if (! CRemoteHelper::curlExists ()) {
				IJReq::setResponse ( 404,JText::_ ( 'COM_COMMUNITY_CURL_NOT_EXISTS' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$videoLib = new CVideoLibrary ( );
			$videoObj = $videoLib->getProvider ( $table->path );
			if ($videoObj == false) {
				IJReq::setResponse( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if (! $videoObj->isValid ()) {
				IJReq::setResponse ( 500 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$remoteThumb = $videoObj->getThumbnail ();
			$thumbData = CRemoteHelper::getContent ( $remoteThumb, true );

			if (empty ( $thumbData )) {
				IJReq::setResponse ( 404,JText::_ ( 'COM_COMMUNITY_INVALID_FILE_REQUEST' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// split the header and body
			list ( $headers, $body ) = explode ( "\r\n\r\n", $thumbData, 2 );
			preg_match ( '/Content-Type: image\/(.*)/i', $headers, $matches );

			if (! empty ( $matches )) {
				CFactory::load ( 'helpers', 'file' );
				CFactory::load ( 'helpers', 'image' );

				$thumbPath = CVideoLibrary::getPath ( $table->creator, 'thumb' );
				$thumbFileName = CFileHelper::getRandomFilename ( $thumbPath );
				$tmpThumbPath = $thumbPath .'/'. $thumbFileName;
				if (! JFile::write ( $tmpThumbPath, $body )) {
					IJReq::setResponse ( 404,JText::_ ( 'COM_COMMUNITY_INVALID_FILE_REQUEST' ) );
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}

				// We'll remove the old or none working thumbnail after this
				$oldThumb = $table->thumb;

				// Get the image type first so we can determine what extensions to use
				$info = getimagesize ( $tmpThumbPath );
				$mime = image_type_to_mime_type ( $info [2] );
				$thumbExtension = CImageHelper::getExtension ( $mime );

				$thumbFilename = $thumbFileName . $thumbExtension;
				$thumbPath = $thumbPath .'/'. $thumbFilename;
				if (! JFile::move ( $tmpThumbPath, $thumbPath )) {
					IJReq::setResponse ( 500 );
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}

				list ( $width, $height ) = explode ( 'x', $this->config->get ( 'videosThumbSize' ) );
				CImageHelper::resizeAspectRatio ( $thumbPath, $thumbPath, 112, 84 );
			} else {
				IJReq::setResponse ( 400,JText::_ ( 'COM_COMMUNITY_PHOTOS_IMAGE_NOT_PROVIDED_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		// Update the DB with new thumbnail
		$thumb = $this->config->get ( 'videofolder' ) . '/' . VIDEO_FOLDER_NAME . '/' . $table->creator . '/' . VIDEO_THUMB_FOLDER_NAME . '/' . $thumbFilename;

		$table->set ( 'thumb', $thumb );
		$table->store ();

		// If this video storage is not on local, we move it to remote storage
		// and remove the old thumb if existed
		if (($table->storage != 'file')) {
			$storageType = $config->getString ( 'videostorage' );
			CFactory::load ( 'libraries', 'storage' );
			$storage = CStorage::getStorage ( $storageType );
			$storage->delete ( $oldThumb );

			$localThumb = JPATH::clean ( JPATH_ROOT .'/'. $table->thumb );
			$tempThumbname = JPATH::clean ( JPATH_ROOT .'/'. md5 ( $table->thumb ) );

			if (JFile::exists ( $localThumb )) {
				JFile::copy ( $localThumb, $tempThumbname );
			}

			if (JFile::exists ( $tempThumbname )) {
				$storage->put ( $table->thumb, $tempThumbname );
				JFile::delete ( $localThumb );
				JFile::delete ( $tempThumbname );
			}
		} else {
			if (JFile::exists ( JPATH_ROOT .'/'. $oldThumb )) {
				JFile::delete ( JPATH_ROOT .'/'. $oldThumb );
			}
		}

		if ($returnThumb) {
			return $table->getThumbnail ();
		}
		return true;
	}

	/**
	 *	Generates a resized image of the photo
	 **/
	private function showimage($showPhoto = true) {
		jimport ( 'joomla.filesystem.file' );
		$imgid = JRequest::getVar ( 'imgid', '', 'GET' );
		$maxWidth = JRequest::getVar ( 'maxW', '', 'GET' );
		$maxHeight = JRequest::getVar ( 'maxH', '', 'GET' );

		// round up the w/h to the nearest 10
		$maxWidth = round ( $maxWidth, - 1 );
		$maxHeight = round ( $maxHeight, - 1 );

		$photoModel = CFactory::getModel ( 'photos' );
		$photo = JTable::getInstance ( 'Photo', 'CTable' );
		$photo->loadFromImgPath ( $imgid );

		CFactory::load ( 'helpers', 'image' );

		$photoPath = JPATH_ROOT .'/'. $photo->image;

		if (! JFile::exists ( $photoPath )) {
			$displayWidth = $this->config->getInt ( 'photodisplaysize' );
			$info = getimagesize ( JPATH_ROOT .'/'. $photo->original );
			$imgType = image_type_to_mime_type ( $info [2] );
			$displayWidth = ($info [0] < $displayWidth) ? $info [0] : $displayWidth;

			CImageHelper::resizeProportional ( JPATH_ROOT .'/'. $photo->original, $photoPath, $imgType, $displayWidth );

			if ($this->config->get ( 'deleteoriginalphotos' )) {
				$originalPath = JPATH_ROOT .'/'. $photo->original;
				if (JFile::exists ( $originalPath )) {
					JFile::delete ( $originalPath );
				}
			}
		}

		// Show photo if required
		if ($showPhoto) {
			$info = getimagesize ( JPATH_ROOT .'/'. $photo->image );
			// @rule: Clean whitespaces as this might cause errors when header is used.
			$ob_active = ob_get_length () !== FALSE;

			if ($ob_active) {
				while ( @ ob_end_clean () )
					;
				if (function_exists ( 'ob_clean' )) {
					@ob_clean ();
				}
			}

			header ( 'Content-type: ' . $info ['mime'] );
			//echo JFile::read( $photoPath );
		//exit;
		}
	}

	// called by uploadPhotos
	private function _checkUploadedFile($imageFile, $album, $handler) {
		require_once JPATH_ROOT . '/components/com_community/controllers/photos.php';

		if (! $this->_validImage ( $imageFile )) {
			IJReq::setResponse(415);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if ($this->_imageLimitExceeded ( filesize ( $imageFile ['tmp_name'] ) )) {
			IJReq::setResponse(416);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// We need to read the filetype as uploaded always return application/octet-stream
		// regardless of the actual file type
		$info = getimagesize ( $imageFile ['tmp_name'] );
		$isDefaultPhoto = IJReq::getTaskData ( 'isDefault', false, 'bool' );

		if ($album->id == 0 || (($this->my->id != $album->creator) && $album->type != PHOTOS_GROUP_TYPE)) {
			IJReq::getTaskData ( 400 );
			IJReq::getTaskData ( JText::_ ( 'COM_COMMUNITY_PHOTOS_INVALID_ALBUM' ) );
			return false;
		}

		if (! $album->hasAccess ( $this->my->id, 'upload' )) {
			IJReq::getTaskData ( 400 );
			IJReq::getTaskData ( JText::_ ( 'COM_COMMUNITY_PHOTOS_INVALID_ALBUM' ) );
			return false;
		}

		// Hash the image file name so that it gets as unique possible
		$fileName = JApplication::getHash ( $imageFile ['tmp_name'] . time () );
		$hashFilename = JString::substr ( $fileName, 0, 24 );
		$imgType = image_type_to_mime_type ( $info [2] );

		// Load the tables
		$photoTable = JTable::getInstance ( 'Photo', 'CTable' );

		// @todo: configurable paths?
		$storage = JPATH_ROOT .'/'. $this->config->getString ( 'photofolder' );
		$albumPath = (empty ( $album->path )) ? '' : $album->id . DS;

		// Test if the photos path really exists.
		jimport ( 'joomla.filesystem.file' );
		jimport ( 'joomla.filesystem.folder' );
		CFactory::load ( 'helpers', 'limits' );

		$originalPath = $handler->getOriginalPath ( $storage, $albumPath, $album->id );

		CFactory::load ( 'helpers', 'owner' );
		// @rule: Just in case user tries to exploit the system, we should prevent this from even happening.
		if ($handler->isExceedUploadLimit ( false ) && ! COwnerHelper::isCommunityAdmin ()) {
			$groupID = IJReq::getTaskData ( 'groupID', $album->groupid, 'int' );

			if (intval ( $groupID ) > 0) {
				// group photo
				$photoLimit = $this->config->get ( 'groupphotouploadlimit' );
				IJReq::setResponse ( 416,JText::sprintf ( 'COM_COMMUNITY_GROUPS_PHOTO_LIMIT', $photoLimit ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			} else {
				// user photo
				$photoLimit = $this->config->get ( 'photouploadlimit' );
				IJReq::setResponse ( 416,JText::sprintf ( 'COM_COMMUNITY_PHOTOS_UPLOAD_LIMIT_REACHED', $photoLimit ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			return false;
		}

		if (! JFolder::exists ( $originalPath )) {
			if (! JFolder::create ( $originalPath, ( int ) octdec ( $this->config->get ( 'folderpermissionsphoto' ) ) )) {
				IJReq::setResponse ( 500,JText::_ ( 'COM_COMMUNITY_VIDEOS_CREATING_USERS_PHOTO_FOLDER_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			JFile::copy ( JPATH_ROOT . '/components/com_community/index.html', $originalPath .'/'. 'index.html' );
		}

		$locationPath = $handler->getLocationPath ( $storage, $albumPath, $album->id );

		if (! JFolder::exists ( $locationPath )) {
			if (! JFolder::create ( $locationPath, ( int ) octdec ( $this->config->get ( 'folderpermissionsphoto' ) ) )) {
				IJReq::setResponse ( 500,JText::_ ( 'COM_COMMUNITY_VIDEOS_CREATING_USERS_PHOTO_FOLDER_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			JFile::copy ( JPATH_ROOT . '/components/com_community/index.html', $locationPath .'/'. 'index.html' );
		}

		$thumbPath = $handler->getThumbPath ( $storage, $album->id );
		$thumbPath = $thumbPath .'/'. $albumPath . 'thumb_' . $hashFilename . CImageHelper::getExtension ( $imageFile ['type'] );
		CPhotos::generateThumbnail ( $imageFile ['tmp_name'], $thumbPath, $imgType );

		// Original photo need to be kept to make sure that, the gallery works
		$useAlbumId = (empty ( $album->path )) ? 0 : $album->id;
		$originalFile = $originalPath . $hashFilename . CImageHelper::getExtension ( $imgType );

		if (! $this->_storeOriginal ( $imageFile ['tmp_name'], $originalFile, $useAlbumId, $this->IJUserID )) {
			return false;
		}

		$photoTable->original = CString::str_ireplace ( JPATH_ROOT . '/', '', $originalFile );

		// In joomla 1.6, CString::str_ireplace is not replacing the path properly. Need to do a check here
		if ($photoTable->original == $originalFile)
			$photoTable->original = str_ireplace ( JPATH_ROOT . '/', '', $originalFile );

		// Set photos properties
		$caption = IJReq::getTaskData('caption','');
		$imagename = $imageFile ['name'];
		if (JString::strlen ( $imagename ) > 4) {
			$imagename = JString::substr ( $imagename, 0, JString::strlen ( $imagename ) - 4 );
		}

		$photoTable->albumid = $album->id;
		$photoTable->caption = ($caption) ? $caption : $imagename;
		$photoTable->creator = $this->my->id;
		$photoTable->created = gmdate ( 'Y-m-d H:i:s' );

		$result = array ('photoTable' => $photoTable, 'storage' => $storage, 'albumPath' => $albumPath, 'hashFilename' => $hashFilename, 'thumbPath' => $thumbPath, 'originalPath' => $originalPath, 'imgType' => $imgType, 'isDefaultPhoto' => $isDefaultPhoto );

		return $result;
	}

	// called by _checkUploadedFile
	private function _validImage($image) {
		CFactory::load ( 'helpers', 'image' );

		if ($image ['error'] > 0 && $image ['error'] !== 'UPLOAD_ERR_OK') {
			return false;
		}

		if (empty ( $image ['tmp_name'] )) {
			return false;
		}

		// This is only applicable for html uploader because flash uploader uploads all 'files' as application/octet-stream
		if (! $this->config->get ( 'flashuploader' ) && ! CImageHelper::isValidType ( $image ['type'] )) {
			return false;
		}

		if (! CImageHelper::isMemoryNeededExceed ( $image ['tmp_name'] )) {
			return false;
		}

		if (! CImageHelper::isValid ( $image ['tmp_name'] )) {
			return false;
		}

		return true;
	}

	// called by _checkUploadedFile
	private function _imageLimitExceeded($size) {
		$uploadLimit = ( double ) $this->config->get ( 'maxuploadsize' );
		if ($uploadLimit == 0) {
			IJReq::setResponse ( 416,JText::_ ( 'COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$uploadLimit = ($uploadLimit * 1024 * 1024);
		return $size > $uploadLimit;
	}

	//
	private function _rotatePhoto($imageFile, $photoTable, $storedPath, $thumbPath) {
		require_once JPATH_ROOT . '/components/com_community/controllers/photos.php';

		// Read orientation data from original file
		$orientation = CImageHelper::getOrientation ( $imageFile ['tmp_name'] );

		// A newly uplaoded image might not be resized yet, do it now
		$displayWidth = $this->config->getInt ( 'photodisplaysize' );
		JRequest::setVar ( 'imgid', $photoTable->id, 'GET' );
		JRequest::setVar ( 'maxW', $displayWidth, 'GET' );
		JRequest::setVar ( 'maxH', $displayWidth, 'GET' );

		$this->showimage ( false );

		// Rotata resized files ince it is smaller
		switch ($orientation) {
			case 1 : // nothing
				break;

			case 2 : // horizontal flip
				break;

			case 3 : // 180 rotate left
				CImageHelper::rotate ( $storedPath, $storedPath, 180 );
				CImageHelper::rotate ( $thumbPath, $thumbPath, 180 );
				break;

			case 4 : // vertical flip
				break;

			case 5 : // vertical flip + 90 rotate right
				break;

			case 6 : // 90 rotate right
				CImageHelper::rotate ( $storedPath, $storedPath, - 90 );
				CImageHelper::rotate ( $thumbPath, $thumbPath, - 90 );
				break;

			case 7 : // horizontal flip + 90 rotate right
				break;

			case 8 : // 90 rotate left
				CImageHelper::rotate ( $storedPath, $storedPath, 90 );
				CImageHelper::rotate ( $thumbPath, $thumbPath, 90 );
				break;
		}
	}

	//
	private function _storeOriginal($tmpPath, $destPath, $albumId = 0, $userid) {
		jimport ( 'joomla.filesystem.file' );
		jimport ( 'joomla.utilities.utility' );

		// @todo: We assume now that the config is using the relative path to the
		// default images folder in Joomla.
		// @todo:  this folder creation should really be in its own function
		$albumPath = ($albumId == 0) ? '' :'/'. $albumId;
		$originalPathFolder = JPATH_ROOT .'/'. $this->config->getString ( 'photofolder' ) .'/'. JPath::clean ( $this->config->get ( 'originalphotopath' ) );
		$originalPathFolder = $originalPathFolder .'/'. $this->my->id . $albumPath;

		if (! JFile::exists ( $originalPathFolder )) {
			JFolder::create ( $originalPathFolder, ( int ) octdec ( $this->config->get ( 'folderpermissionsphoto' ) ) );
			JFile::copy ( JPATH_ROOT . '/components/com_community/index.html', $originalPathFolder .'/'. 'index.html' );
		}

		if (! JFile::copy ( $tmpPath, $destPath )) {
			IJReq::setResponse ( 500,JText::sprintf ( 'COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $destPath ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return true;
	}

	//
	private function _getHandler(CTableAlbum $album) {
		require_once JPATH_ROOT .'/'. "components" .'/'. "com_community" .'/'. "controllers" .'/'. "photos.php";
		$handler = null;

		// During AJAX calls, we might not be able to determine the groupid
		$groupId = JRequest::getInt ( 'groupid', $album->groupid, 'REQUEST' );
		$type = PHOTOS_USER_TYPE;

		if (! empty ( $groupId )) {
			// group photo
			$handler = new CommunityControllerPhotoGroupHandler ( $this );
		} else {
			// user photo
			$handler = new CommunityControllerPhotoUserHandler ( $this );
		}

		return $handler;
	}

	// called by uploadVideo
	private function checkVideoAccess() {
		if (! $this->config->get ( 'enablevideos' )) {
			IJReq::setResponse ( 706,JText::_ ( 'COM_COMMUNITY_VIDEOS_DISABLED' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return true;
	}

	/**
	 * @uses report photo/video
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"report",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // photo id, or video id depending on type.
	 * 			"albumID":"albumID", // only if type is photos
	 * 			"userID":"userID",
	 * 			"message":"message",
	 * 			"type":"type" // 'photos', 'videos'
	 * 		}
	 * 	}
	 *
	 */
	function report() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$albumID = IJReq::getTaskData ( 'albumID', 0, 'int' );
		$userID = IJReq::getTaskData ( 'userID', NULL, 'int' );
		if ($userID == 0) {
			$userID = $this->IJUserID;
		}
		$message = IJReq::getTaskData ( 'message' );
		$type = IJReq::getTaskData ( 'type' );

		switch ($type) {
			case 'photos' :
				if (! $uniqueID or ! $albumID or ! $userID or ! $message) {
					IJReq::setResponse ( 400 );
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
				$this->jsonarray = $this->reportPhoto ( $uniqueID, $albumID, $userID, $message );
				if (! $this->jsonarray) {
					return false;
				}
				break;

			case 'videos' :
				if (! $uniqueID or ! $userID or ! $message) {
					IJReq::setResponse ( 400 );
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
				$this->jsonarray = $this->reportVideo ( $uniqueID, $userID, $message );
				if (! $this->jsonarray) {
					return false;
				}
				break;

			default :
				IJReq::setResponse ( 400 );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}
		return $this->jsonarray;
	}

	// called from report()
	private function reportPhoto($uniqueID, $albumID, $userID, $message) {
		CFactory::load ( 'libraries', 'reporting' );
		$report = new CReportingLibrary ( );
		$link = JURI::base () . "/index.php?option=com_community&view=photos&task=photo&userid={$userID}&albumid={$albumID}#photoid={$uniqueID}";
		if (! $this->config->get ( 'enablereporting' ) || (($this->my->id == 0) && (! $this->config->get ( 'enableguestreporting' )))) {
			IJReq::setResponse ( 706 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Pass the link and the reported message
		$report->createReport ( JText::_ ( 'COM_COMMUNITY_BAD_PHOTO' ), $link, $message );

		// Add the action that needs to be called.
		$action = new stdClass ( );
		$action->label = 'Delete photo';
		$action->method = 'photos,unpublishPhoto';
		$action->parameters = $uniqueID;
		$action->defaultAction = true;

		$report->addActions ( array ($action ) );

		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	// called from report()
	private function reportVideo($uniqueID, $userID, $message) {
		CFactory::load ( 'libraries', 'reporting' );
		$report = new CReportingLibrary ( );

		if (! $this->config->get ( 'enablereporting' ) || (($this->my->id == 0) && (! $this->config->get ( 'enableguestreporting' )))) {
			IJReq::setResponse ( 706 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Pass the link and the reported message
		$report->createReport ( JText::_ ( 'COM_COMMUNITY_VIDEOS_ERROR' ), $link, $message );

		// Add the action that needs to be called.
		$action = new stdClass ( );
		$action->label = 'Delete video';
		$action->method = 'videos,deleteVideo';
		$action->parameters = array ($uniqueID, 0 );
		$action->defaultAction = false;

		$report->addActions ( array ($action ) );

		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	/**
	 * @uses to set photo as a coverpage
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setCover",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // photo id
	 * 			"albumID":"albumID",
	 * 		}
	 * 	}
	 *
	 */
	function setCover() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$albumID = IJReq::getTaskData ( 'albumID', 0, 'int' );
		if (! COwnerHelper::isRegisteredUser ()) {
			IJReq::setResponse ( 401 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'models', 'photos' );
		CFactory::load ( 'helpers', 'owner' );
		$album = JTable::getInstance ( 'Album', 'CTable' );
		$album->load ( $albumID );
		$model = CFactory::getModel ( 'Photos' );
		$photo = $model->getPhoto ( $uniqueID );
		$handler = $this->_getHandler ( $album );

		if (! $handler->hasPermission ( $albumID )) {
			IJReq::setResponse ( 706,JText::_ ( 'COM_COMMUNITY_PERMISSION_DENIED_WARNING' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$model->setDefaultImage ( $albumID, $uniqueID );
		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	/**
	 * @uses to set photo as a coverpage
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setAvatar",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID" // photo id
	 * 		}
	 * 	}
	 *
	 */
	function setAvatar() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );
		$photoModel = CFactory::getModel ( 'Photos' );

		if ($this->my->id == 0) {
			IJReq::setResponse ( 401,JText::_ ( 'COM_COMMUNITY_PERMISSION_DENIED_WARNING' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if ($uniqueID == 0) {
			IJReq::setResponse ( 400,JText::_ ( 'COM_COMMUNITY_PHOTOS_INVALID_PHOTO_ID' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$photo = JTable::getInstance ( 'Photo', 'CTable' );
		$photo->load ( $uniqueID );

		if ($this->my->id != $photo->creator) {
			IJReq::setResponse ( 706,JText::_ ( 'COM_COMMUNITY_ACCESS_DENIED' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		jimport ( 'joomla.filesystem.file' );
		jimport ( 'joomla.utilities.utility' );

		CFactory::load ( 'helpers', 'image' );

		// @todo: configurable width?
		$imageMaxWidth = 160;

		// Get a hash for the file name.
		$fileName = JApplication::getHash ( $photo->id . time () );
		$hashFileName = JString::substr ( $fileName, 0, 24 );
		$photoPath = JPATH_ROOT .'/'. $photo->image; //$photo->original;


		if ($photo->storage == 'file') {
			// @rule: If photo original file still exists, we will use the original file.
			if (! JFile::exists ( $photoPath )) {
				$photoPath = JPATH_ROOT .'/'. $photo->image;
			}

			// @rule: If photo still doesn't exists, we should not allow the photo to be changed.
			if (! JFile::exists ( $photoPath )) {
				IJReq::setResponse ( 500,JText::_ ( 'COM_COMMUNITY_PHOTOS_SET_AVATAR_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		} else {
			CFactory::load ( 'helpers', 'remote' );
			$content = cRemoteGetContent ( $photo->getImageURI () );

			if (! $content) {
				IJReq::setResponse ( 500,JText::_ ( 'COM_COMMUNITY_PHOTOS_SET_AVATAR_ERROR' ) );
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$jConfig = JFactory::getConfig ();
			$photoPath = $jConfig->getValue ( 'tmp_path' ) .'/'. md5 ( $photo->image );

			// Store image on temporary location
			JFile::write ( $photoPath, $content );
		}

		$info = getimagesize ( $photoPath );
		$extension = CImageHelper::getExtension ( $info ['mime'] );

		$storage = JPATH_ROOT .'/'. $this->config->getString ( 'imagefolder' ) .'/'. 'avatar';
		$storageImage = $storage .'/'. $hashFileName . $extension;
		$storageThumbnail = $storage .'/'. 'thumb_' . $hashFileName . $extension;
		$image = $this->config->getString ( 'imagefolder' ) . '/avatar/' . $hashFileName . $extension;
		$thumbnail = $this->config->getString ( 'imagefolder' ) . '/avatar/' . 'thumb_' . $hashFileName . $extension;
		$userModel = CFactory::getModel ( 'user' );

		// Only resize when the width exceeds the max.
		if (! CImageHelper::resizeProportional ( $photoPath, $storageImage, $info ['mime'], $imageMaxWidth )) {
			IJReq::setResponse ( 500,JText::sprintf ( 'COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Generate thumbnail
		if (! CImageHelper::createThumb ( $photoPath, $storageThumbnail, $info ['mime'] )) {
			IJReq::setResponse ( 500,JText::sprintf ( 'COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if ($photo->storage != 'file') {
			//@rule: For non local storage, we need to remove the temporary photo
			JFile::delete ( $photoPath );
		}

		$userModel->setImage ( $this->my->id, $image, 'avatar' );
		$userModel->setImage ( $this->my->id, $thumbnail, 'thumb' );

		// Update the user object so that the profile picture gets updated.
		$this->my->set ( '_avatar', $image );
		$this->my->set ( '_thumb', $thumbnail );

		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}

	/**
	 * @uses to set photo as a coverpage
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setAvatar",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID" // photo id
	 * 		}
	 * 	}
	 *
	 */
	function setProfileVideo() {
		$uniqueID = IJReq::getTaskData ( 'uniqueID', 0, 'int' );

		if ($this->my->id == 0) {
			IJReq::setResponse ( 401,JText::_ ( 'COM_COMMUNITY_PERMISSION_DENIED_WARNING' ) );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$params = $this->my->getParams ();
		$params->set ( 'profileVideo', $uniqueID );
		$this->my->save ( 'params' );

		$this->jsonarray ['code'] = 200;
		return $this->jsonarray;
	}


	/**
	 * @uses to set photo as a coverpage
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setPhotoCaption",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // photo id
	 * 			"caption":"caption"
	 * 		}
	 * 	}
	 *
	 */
	function setPhotoCaption(){
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$caption	= IJReq::getTaskData('caption', '');

		$audiofileupload = $this->jomHelper->uploadAudioFile();
		if($audiofileupload){
			$caption = $caption.$audiofileupload['voicetext'];
		}

		$filter = JFilterInput::getInstance();
		$uniqueID = $filter->clean($uniqueID, 'int');
		$caption = $filter->clean($caption, 'string');

		if (!COwnerHelper::isRegisteredUser()){
			IJReq::setResponse(401);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load( 'models' , 'photos' );
		$photo			=& JTable::getInstance( 'Photo' , 'CTable' );
		$photo->load( $uniqueID );
		$album			=& JTable::getInstance( 'Album' , 'CTable' );
		$album->load( $photo->albumid );

		$handler		= $this->_getHandler( $album );

		if( $photo->id == '0' ){
			// user shouldnt call this at all or reach here at all
			IJReq::setResponse(416,JText::_('COM_COMMUNITY_PHOTOS_INVALID_PHOTO_ID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load( 'helpers' , 'owner' );
		if( !$handler->hasPermission( $album->id ) ){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_PHOTOS_NOT_ALLOWED_EDIT_CAPTION_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$photo->caption	= $caption;
		$photo->store();
		$this->jsonarray['code']=200;
		if($audiofileupload){
			$this->jsonarray['voice']=$audiofileupload['voice3gppath'];
		}
		return $this->jsonarray;
	}

	/**
	 * @uses to search videos
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"searchVideo",
	 * 		"taskData":{
	 * 			"query":"query",
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 *
	 */
	function searchVideo() {
		$qString = IJReq::getTaskData ( 'query', NULL );
		$pageNO = IJReq::getTaskData ( 'pageNO', 0, 'int' );
		if ($pageNO == 0 || $pageNO == 1) {
			$startFrom = 0;
		} else {
			$startFrom = (PAGE_VIDEO_LIMIT * ($pageNO - 1));
		}

		$searchModel = CFactory::getModel ( 'Search' );
		$searchModel->setState ( 'limit', PAGE_VIDEO_LIMIT );
		$searchModel->setState ( 'limitstart', $startFrom );
		$result = $searchModel->searchVideo ( $qString );
		//$pagination	 = $searchModel->getPagination();
		$total = $searchModel->getTotal ();
		if ($total > 0) {
			$this->jsonarray ['code'] = 200;
			$this->jsonarray ['total'] = $total;
			$this->jsonarray ['pageLimit'] = PAGE_VIDEO_LIMIT;
		} else {
			IJReq::setResponse ( 204 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		foreach ( $result as $key => $video ) {
			$video_file = $video->path;
			$p_url = JURI::root ();
			if ($video->type == 'file') {
				$ext = JFile::getExt ( $video->path );

				if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
					$video_file = JURI::root () . $video->path;
				} else {
					$lastpos = strrpos ( $video->path, '.' );

					$vname = substr ( $video->path, 0, $lastpos );

					if ($video->storage == 's3') {
						$s3BucketPath = $config->get ( 'storages3bucket' );
						if (! empty ( $s3BucketPath ))
							$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
					}
					$video_file = $p_url . $vname . ".mp4";
				}
			}

			$this->jsonarray ['videos'] [$key] ['id'] = $video->id;
			$this->jsonarray ['videos'] [$key] ['caption'] = $video->title;
			$this->jsonarray ['videos'] [$key] ['thumb'] = ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
			$this->jsonarray ['videos'] [$key] ['url'] = $video_file;
			$this->jsonarray ['videos'] [$key] ['description'] = $video->description;
			$this->jsonarray ['videos'] [$key] ['date'] = $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
			$this->jsonarray ['videos'] [$key] ['location'] = $video->location;
			$this->jsonarray ['videos'] [$key] ['permissions'] = $video->permissions;
			$this->jsonarray ['videos'] [$key] ['categoryId'] = $video->category_id;

			$usr = $this->jomHelper->getUserDetail ( $video->creator );
			$this->jsonarray ['videos'] [$key] ['user_id'] = $usr->id;
			$this->jsonarray ['videos'] [$key] ['user_name'] = $usr->name;
			$this->jsonarray ['videos'] [$key] ['user_avatar'] = $usr->avatar;
			$this->jsonarray ['videos'] [$key] ['user_profile'] = $usr->profile;

			//likes
			$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
			$this->jsonarray ['videos'] [$key] ['likes'] = $likes->likes;
			$this->jsonarray ['videos'] [$key] ['dislikes'] = $likes->dislikes;
			$this->jsonarray ['videos'] [$key] ['liked'] = $likes->liked;
			$this->jsonarray ['videos'] [$key] ['disliked'] = $likes->disliked;

			//comments
			$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
			$this->jsonarray ['videos'] [$key] ['commentCount'] = $count;
			$this->jsonarray ['videos'] [$key] ['deleteAllowed'] = intval ( ($this->IJUserID == $video->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
			if (SHARE_VIDEOS) {
				$this->jsonarray ['videos'] [$key] ['shareLink'] = JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
			}

			$query = "SELECT count(id)
					FROM #__community_videos_tag
					WHERE `videoid`={$video->id}";
			$this->db->setQuery ( $query );
			$count = $this->db->loadResult ();
			$this->jsonarray ['videos'] [$key] ['tags'] = $count;
		}
		return $this->jsonarray;
	}


	/**
	 * @uses to set user cover pic
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setUserCover",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // photoid
	 * 		}
	 * 	}
	 *
	 */
	function setUserCover(){
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');

		if(!$uniqueID){
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$query="SELECT count(1)
				FROM #__ijoomeradv_users
				WHERE `userid`={$this->IJUserID}";
		$this->db->setQuery($query);
		$count=$this->db->loadResult();

		if($count>0){
			$query="UPDATE #__ijoomeradv_users
					SET `coverpic`={$uniqueID}
					WHERE `userid`={$this->IJUserID}";
			$this->db->setQuery($query);
			$this->db->Query();
		}else{
			$query="INSERT INTO #__ijoomeradv_users (`userid`,`coverpic`)
					VALUES ({$this->IJUserID},{$uniqueID})";
			$this->db->setQuery($query);
			$this->db->Query();
		}

		if($this->db->getErrorNum()){
			IJReq::setResponse(500);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}else{
			$this->jsonarray['code']=200;
			return $this->jsonarray;
		}

	}


	/**
	 * @uses to set user cover pic
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setPhotoCover",
	 * 		"taskData":{
	 * 			"type"		:"", 	//Profile/Group/Event
	 * 			"photoId"	:"",	//photo id
	 * 			"uniqueID"	:""		//Profile/Group/Event id
	 * 		}
	 * 	}
	 *
	 */
	public function setPhotoCover() {

		$type		= IJReq::getTaskData('type', 'profile');
		$photoid	= IJReq::getTaskData('photoID', 0);
		$uniqueID	= IJReq::getTaskData('uniqueID', 0);
		$user		= JFactory::getUser();

		//get current user id for profile type
		if($type=='profile'){
			$uniqueID = $this->IJUserID;
		}

		if(empty($photoid) || empty($uniqueID)){

			$this->jsonarray['code']=400;
			return $this->jsonarray;
		}

		$album 		= JTable::getInstance('Album', 'CTable');
        if (!$albumId = $album->isCoverExist($type, $uniqueID)) {
            $albumId = $album->addCoverAlbum($type, $uniqueID);
        }

        if ($photoid) {
            $photo = JTable::getInstance('Photo', 'CTable');
            $photo->load($photoid);

            if (!JFolder::exists(JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/')) {
                JFolder::create(JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/');
            }

            $ext = JFile::getExt($photo->image);
            $dest = ($photo->albumid == $albumId) ? $photo->image : JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/' . md5($type . '_cover' . time()) . CImageHelper::getExtension($photo->image);

            $cTable = JTable::getInstance(ucfirst($type), 'CTable');
            $cTable->load($uniqueID);

            if ($cTable->setCover(str_replace(JPATH_ROOT . '/', '', $dest))) {
                $storage = CStorage::getStorage($photo->storage);
                $storage->get($photo->image, $dest);

                if ($photo->albumid != $albumId) {
                    $photo->id = '';
                    $photo->albumid = $albumId;
                    $photo->image = str_replace(JPATH_ROOT . '/', '', $dest);
                    if ($photo->store()) {
                        $album->load($albumId);
                        $album->photoid = $photo->id;

                        $album->store();
                    }
                }
                $my = CFactory::getUser();
                // Generate activity stream.
                $act = new stdClass();
                $act->cmd = 'cover.upload';
                $act->actor = $my->id;
                $act->target = 0;
                $act->title = '';
                $act->content = '';
                $act->app = 'cover.upload';
                $act->cid = 0;
                $act->comment_id = CActivities::COMMENT_SELF;
                $act->comment_type = 'cover.upload';
                $act->groupid = ($type == 'group') ? $uniqueID : 0;
                $act->eventid = ($type == 'event') ? $uniqueID : 0;
                $act->group_access = ($type == 'group') ? $cTable->approvals : 0;
                $act->event_access = ($type == 'event') ? $cTable->permission : 0;
                $act->like_id = CActivities::LIKE_SELF;
                //;
                $act->like_type = 'cover.upload';

                $params = new JRegistry();
                $params->set('attachment', str_replace(JPATH_ROOT . '/', '', $dest));
                $params->set('type', $type);

                // Add activity logging
                CActivityStream::add($act, $params->toString());

                $this->jsonarray['code']=200;
				return $this->jsonarray;
            }
        }
    }

    /**
	 * @uses to set user cover pic
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"media",
	 *		"extTask":"setCoverUpload",
	 * 		"taskData":{
	 * 			"type"		:"", 	//Profile/Group/Event
	 * 			"uniqueID"	:""		//Profile/Group/Event id
	 * 		}
	 * 	}
	 *
	 * 	FILES : uploadCover
	 *
	 */
	public function setCoverUpload() {
        $uniqueID 	= IJReq::getTaskData('uniqueID', 0);
        $type 		= IJReq::getTaskData('type', 'Profile');
        $type 		= strtolower($type);
        $file 		= JRequest::getVar ( 'uploadCover', '', 'FILES', 'array' );
        $config 	= CFactory::getConfig();
        $my 		= JFactory::getUser();
        $now 		= new JDate();

        // Load up required models and properties
		CFactory::load ( 'libraries', 'photos' );
		CFactory::load ( 'models', 'photos' );
		CFactory::load ( 'helpers', 'image' );
		require_once JPATH_ROOT . '/components/com_community/controllers/photos.php';
		//get current user id for profile type
		if($type=='profile'){
			$uniqueID = $this->IJUserID;
		}

		if(empty($uniqueID)){
			$this->jsonarray['code']=400;
			return $this->jsonarray;
		}

        //check if file is allwoed
        if (!CImageHelper::isValidType($file['type'])) {
            $msg = JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED');
            $this->jsonarray['code']=500;
            $this->jsonarray['message']=$msg;
			return $this->jsonarray;
        }

        //check upload file size
        $uploadlimit = (double) $config->get('maxuploadsize');
        $uploadlimit = ($uploadlimit * 1024 * 1024);

        if (filesize($file['tmp_name']) > $uploadlimit && $uploadlimit != 0) {
            $msg = JText::_('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED');
            $this->jsonarray['code']=500;
            $this->jsonarray['message']=$msg;
			return $this->jsonarray;
        }

        $album = JTable::getInstance('Album', 'CTable');

        if (!$albumId = $album->isCoverExist($type, $uniqueID)) {
            $albumId = $album->addCoverAlbum($type, $uniqueID);
        }

        $imgMaxWidht = 1140;

        // Get a hash for the file name.
        $fileName = JApplication::getHash($file['tmp_name'] . time());
        $hashFileName = JString::substr($fileName, 0, 24);

        if (!JFolder::exists(JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/')) {
            JFolder::create(JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/');
        }

        $dest = JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/' . md5($type . '_cover' . time()) . CImageHelper::getExtension($file['type']);
        $thumbPath = JPATH_ROOT . '/images/cover/' . $type . '/' . $uniqueID . '/thumb_' . md5($type . '_cover' . time()) . CImageHelper::getExtension($file['type']);
        // Generate full image
        if (!CImageHelper::resizeProportional($file['tmp_name'], $dest, $file['type'], $imgMaxWidht)) {
            $msg = JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE', $storageImage);
            $this->jsonarray['code']=500;
            $this->jsonarray['message']=$msg;
			return $this->jsonarray;
        }

        CPhotos::generateThumbnail($file['tmp_name'], $thumbPath, $file['type']);

        $cTable = JTable::getInstance(ucfirst($type), 'CTable');
        $cTable->load($uniqueID);

        if ($cTable->setCover(str_replace(JPATH_ROOT . '/', '', $dest))) {
            $photo = JTable::getInstance('Photo', 'CTable');

            $photo->albumid = $albumId;
            $photo->image = str_replace(JPATH_ROOT . '/', '', $dest);
            $photo->caption = $file['name'];
            $photo->filesize = $file['size'];
            $photo->creator = $my->id;
            $photo->created = $now->toSql();
            $photo->published = 1;
            $photo->thumbnail = str_replace(JPATH_ROOT . '/', '', $thumbPath);

            if ($photo->store()) {
                $album->load($albumId);
                $album->photoid = $photo->id;
                $album->store();
            }

            $msg['success'] = true;
            $msg['path'] = JURI::root() . str_replace(JPATH_ROOT . '/', '', $dest);

            // Generate activity stream.
            $act = new stdClass();
            $act->cmd = 'cover.upload';
            $act->actor = $my->id;
            $act->target = 0;
            $act->title = '';
            $act->content = '';
            $act->app = 'cover.upload';
            $act->cid = 0;
            $act->comment_id = CActivities::COMMENT_SELF;
            $act->comment_type = 'cover.upload';
            $act->groupid = ($type == 'group') ? $uniqueID : 0;
            $act->eventid = ($type == 'event') ? $uniqueID : 0;
            $act->group_access = ($type == 'group') ? $cTable->approvals : 0;
            $act->event_access = ($type == 'event') ? $cTable->permission : 0;
            $act->like_id = CActivities::LIKE_SELF;
            //;
            $act->like_type = 'cover.upload';

            $params = new JRegistry();
            $params->set('attachment', $msg['path']);
            $params->set('type', $type);

            // Add activity logging
            CActivityStream::add($act, $params->toString());

            $this->jsonarray['code']=200;
			return $this->jsonarray;
        }
    }
}