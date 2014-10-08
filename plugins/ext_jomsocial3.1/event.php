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
jimport('joomla.version');

class event {
	private $jomHelper;
	private $date_now;
	private $IJUserID;
	private $mainframe;
	private $db;
	private $my;
	private $config;
	private $jsonarray=array();

	function __construct(){
		$this->jomHelper	=	new jomHelper();
        $this->date_now		=	JFactory::getDate();
		$this->mainframe = JFactory::getApplication();
		$this->db		 = JFactory::getDBO(); // set database object
		$this->IJUserID		=	$this->mainframe->getUserState('com_ijoomeradv.IJUserID', 0); //get login user id
		$this->my			=	CFactory::getUser($this->IJUserID); // set the login user object
		$this->config		=	CFactory::getConfig();
		$notification		=	$this->jomHelper->getNotificationCount();
		if(isset($notification['notification'])){
			$this->jsonarray['notification']=$notification['notification'];
		}
    }

	/**
     * @uses to fetch all categories
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"categories"
	 * 	}
     *
     */
	function categories(){
		$now	=   new JDate();
		//Display Category List
		$query="SELECT *
				FROM #__community_events_category
				WHERE parent=0";
		$this->db->setQuery($query);
		$categories = $this->db->loadObjectList();

		if (count($categories)>0){
			$this->jsonarray['code']=200;
			foreach ( $categories as $key=>$value ){
				$query='SELECT count(*)
						FROM #__community_events_category
						WHERE parent='.$value->id;
				$this->db->setQuery ($query);
				$subcategories = $this->db->loadResult ();

				$query="SELECT count(ce.id) as count
						FROM #__community_events as ce
						WHERE ce.catid = {$value->id}
						AND ce.published = 1
						AND ce.enddate >= '{$now->toSql()}' ";
				$this->db->setQuery ($query);
				$events = $this->db->loadResult ();

				$this->jsonarray['categories'][$key]['id'] = $value->id;
				$this->jsonarray['categories'][$key]['name'] = $value->name;
				$this->jsonarray['categories'][$key]['description'] = $value->description;
				$this->jsonarray['categories'][$key]['categories']=$subcategories;
				$this->jsonarray['categories'][$key]['events'] = $events;
				if ($value->parent == 0){
					$res = $this->subCategories( $value->id );
					if($res)
						$this->jsonarray['categories'][$key]['subcategory'] = $res;

				}
			}
		}else{
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return $this->jsonarray;
	}


	// called from categories
	private function subCategories($pid){
		$now = new JDate();
		$jsonarray= array();
		$query='SELECT *
				FROM #__community_events_category
				WHERE parent='.$pid;
		$this->db->setQuery ($query);
		$categories = $this->db->loadObjectList();

		foreach ( $categories as $key=>$value ){
			$query='SELECT count(*)
					FROM #__community_events_category
					WHERE parent='.$id;
			$this->db->setQuery ( $query );
			$subcategories = $this->db->loadResult ();

			$query="SELECT count(ce.id) as count
					FROM #__community_events as ce
					WHERE ce.catid={$id}
					AND ce.published=1
					AND ce.enddate>='{$now->toSql()}'";
			$this->db->setQuery ($query);
			$events = $this->db->loadResult ();

			$jsonarray[$key]['id'] = $value->id;
			$jsonarray[$key]['parent'] = $value->parent;
			$jsonarray[$key]['name'] = $value->name;
			$jsonarray[$key]['description'] = $value->description;
			$jsonarray[$key]['categories'] = $subcategories;
			$jsonarray[$key]['events'] = $events;
			if ($parent != 0){
				$res = $this->subCategories ($value->id);
				$jsonarray[$key]['subcategory'] = $res;
			}
		}
		return $jsonarray;
	}


	/**
     * @uses to fetch all categories
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"events",
 	 * 		"taskData":{
 	 * 			"type":"type", // all, my, group, pending, past, search
 	 * 			"categoryID":"categoryID", // optional: if type is all & search
 	 * 			"groupID":"groupID", // optional: if type is group
 	 * 			"query":"query", // optional: if type is search
 	 * 			"startDate":"startDate", // optional: if type is search
 	 * 			"endDate":"endDate", // optional: if type is search
 	 * 			"radius":"radius", // optional: if type is search
 	 * 			"location":"location", // optional: if type is search
 	 * 			"sorting":"sorting", // latest(default), startdate
 	 * 			"pageNO":"pageNO"
 	 * 		}
	 * 	}
     *
     */
	function events(){
		$type = IJReq::getTaskData('type', 'all');
		$sorting = IJReq::getTaskData('sorting', 'latest');
		$pageNO = IJReq::getTaskData('pageNO', 0, 'int');
		$limit = PAGE_EVENT_LIMIT;
		$categoryID = $userID = $sorting = $search = $pending = $advance = $startFrom = null;
		$hideOldEvent = true;
		$showOnlyOldEvent = false;
		$contentID = 0;

		switch ($type){
			case 'all' :
				$type='all';
				$categoryID = IJReq::getTaskData('categoryID', NULL, 'int');
				break;

			case 'group' :
				$contentID = IJReq::getTaskData('groupID', 0, 'int');
				if(!$contentID){
					IJReq::setResponse(400);
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
				break;

			case 'my' : //myevents view
				$type='all';
				$userID = $this->my->id;
				if(!$userID){
					IJReq::setResponse(400);
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
				break;

			case 'pending' : //pending invitations view
				$type='all';
				$userID = $this->my->id;
				$pending = 0;
				break;

			case 'past' : //Past events view
				$contentID = IJReq::getTaskData('groupID', 0, 'int');
				$type = ($contentID) ? 'group':'all';
				$hideOldEvent = false;
				$showOnlyOldEvent = true;
				break;

			case 'search' : //search
				$type='all';
				$categoryID = IJReq::getTaskData('categoryID', NULL, 'int');
				$search = IJReq::getTaskData('query', NULL);
				$hideOldEvent = false;
				$advance ['startdate'] = IJReq::getTaskData('startDate', '' );
				$advance ['enddate'] = IJReq::getTaskData('endDate', '' );
				$advance ['radius'] = IJReq::getTaskData('radius', '' );
				$advance ['fromlocation'] = IJReq::getTaskData('location', '' );
				break;

			default :
				IJReq::setResponse(400);
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
		}

		if ($pageNO == 0 || $pageNO == '' || $pageNO == 1) {
			$startFrom = 0;
		} else {
			$startFrom = ($limit * ($pageNO - 1));
		}

		if($contentID){
			$this->jsonarray["createEvent"]=$this->config->get("group_events");
		}else{
			$this->jsonarray["createEvent"]=$this->config->get("createevents");
		}

		$eventsModel	=& CFactory::getModel('events');
		$eventsModel = new CommunityModelEvents();
		$eventsModel->setState('limit', $limit);
		$eventsModel->setState('limitstart', $startFrom);

		$results = $eventsModel->getEvents( $categoryID, $userID, $sorting, $search, $hideOldEvent, $showOnlyOldEvent, $pending, $advance, $type, $contentID, $limit );

		if (count($results)>0) {
			$this->jsonarray['code'] = 200;
			$this->jsonarray['total']=$eventsModel->_pagination->total;
			$this->jsonarray['pageLimit']=$limit;
		} else {
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		foreach($results as $key=>$result){
			$this->jsonarray['events'][$key]['id'] = $result->id;
			$this->jsonarray['events'][$key]['title'] = $result->title;
			$this->jsonarray['events'][$key]['location'] = $result->location;
			$this->jsonarray['events'][$key]['groupid'] = $result->contentid;
			$format	= ($this->config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
			$startdateHTML   = CTimeHelper::getFormattedTime($result->startdate, $format);
			$enddateHTML     = CTimeHelper::getFormattedTime($result->enddate, $format);
			$this->jsonarray['events'][$key]['startdate'] = CTimeHelper::getFormattedTime($result->startdate, $format);
			$this->jsonarray['events'][$key]['enddate'] = CTimeHelper::getFormattedTime($result->enddate, $format);
			$this->jsonarray['events'][$key]['date'] = strtoupper(CEventHelper::formatStartDate($result, $this->config->get('eventdateformat')));

			if($this->config->get('user_avatar_storage') == 'file'){
					$p_url	= JURI::base();
			}else{
				$s3BucketPath	= $this->config->get('storages3bucket');
				if(!empty($s3BucketPath))
					$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
				else
					$p_url	= JURI::base();
			}

			$this->jsonarray['events'][$key]['avatar'] = ($result->avatar != '') ? $p_url. $result->avatar : JURI::base ().'components/com_community/assets/event_thumb.png';
			$this->jsonarray['events'][$key]['past'] = (strtotime($result->enddate)<time()) ? 1 : 0;
			$this->jsonarray['events'][$key]['ongoing'] = (strtotime($result->startdate)<=time() and strtotime($result->enddate)>time()) ? 1 : 0;
			$this->jsonarray['events'][$key]['confirmed']=$result->confirmedcount;
		}
		return $this->jsonarray;
	}


	/**
     * @uses to get event details
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"detail",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID"
 	 * 		}
	 * 	}
     *
     */
	function search_field(){
		require_once JPATH_ROOT . '/components/com_community/helpers/category.php';
		$halper_category_obj=new CCategoryHelper();

		require_once JPATH_ROOT . '/components/com_community/helpers/time.php';
		$halper_time_obj=new CTimeHelper();

		$eventsModel	=& CFactory::getModel('events');


		$sql = "SELECT * FROM #__community_events_category";
		$this->db->setQuery ( $sql );
		$cats = $this->db->loadObjectList ();

		$catlist=$halper_category_obj->getCategories($cats);

		$typelist = array (	"search" => array ("text","Search"),
							"catid" => array ("select","Category"),
							"startdate" => array ("datetime","Start time"),
							"enddate" => array ("datetime","End time"),
							"location" => array ("text","Location"),
							"radius" => array ("select","Radius")
						);

		$i=0;
		foreach ($typelist as $key=>$value){
			if($key == "catid"){
				$this->jsonarray["fields"][$i]["field"]["id"] = $i;
				$this->jsonarray["fields"][$i]["field"]["name"] = $key;
				$this->jsonarray["fields"][$i]["field"]["type"] = $value[0];
				$this->jsonarray["fields"][$i]["field"]["caption"] = $value[1];
				foreach ($catlist as $kt=>$vt){
					$this->jsonarray["fields"][$i]["field"]["options"][$kt]["value"]= $vt["id"];
					$this->jsonarray["fields"][$i]["field"]["options"][$kt]["name"]= $vt["name"];
				}
			}else if($key=='radius'){
				$this->jsonarray["fields"][$i]["field"]["id"] = $i;
				$this->jsonarray["fields"][$i]["field"]["name"] = $key;
				$this->jsonarray["fields"][$i]["field"]["type"] = $value[0];
				$this->jsonarray["fields"][$i]["field"]["caption"] = $value[1];
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["value"]= 5;
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["name"]= '5';
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["value"]= 10;
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["name"]= '10';
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["value"]= 20;
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["name"]= '20';
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["value"]= 50;
				$this->jsonarray["fields"][$i]["field"]["options"][$kt]["name"]= '50';
			}else{
				$this->jsonarray["fields"][$i]["field"]["id"] = $i;
				$this->jsonarray["fields"][$i]["field"]["name"] = $key;
				$this->jsonarray["fields"][$i]["field"]["type"] = $value[0];
				$this->jsonarray["fields"][$i]["field"]["caption"] = $value[1];
			}
			$i++;
		}
		return $this->jsonarray;
	}


	/**
     * @uses to get event details
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"detail",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID"
 	 * 		}
	 * 	}
     *
     */
	function detail() {
		$uniqueID = IJReq::getTaskData('uniqueID', null, 'int');
		$event		=&	JTable::getInstance( 'Event' , 'CTable' );
		$event->load($uniqueID);
		$event->hit();
		$isCommunityAdmin	= COwnerHelper::isCommunityAdmin($this->my->id);
		$this->jsonarray['code']=200;
		CFactory::load('helpers', 'owner');


		$query="SELECT `status`
				FROM `#__community_events_members`
				WHERE `eventid`={$uniqueID}
				AND `memberid`={$this->IJUserID}";
		$this->db->setQuery ( $query );
		$userStatus = $this->db->loadResult ();

		$category	=& JTable::getInstance( 'EventCategory' , 'CTable' );
		$category->load( $event->catid ); // load categories from categoryid

		$this->jsonarray['event']['category'] = $category->name;
		$this->jsonarray['event']['summary'] = strip_tags($event->summary);
		$this->jsonarray['event']['description'] = strip_tags($event->description);
		$user = $this->jomHelper->getUserDetail($event->creator); // get user detail
		$this->jsonarray['event']['user_id']=$user->id;
		$this->jsonarray['event']['user_name'] = $user->name;
		$this->jsonarray['event']['lat'] = $event->latitude;
		$this->jsonarray['event']['long'] = $event->longitude;
		$this->jsonarray['event']['isOpen'] = intval(!$event->permission); // 0-private, 1-open
		$this->jsonarray['event']['allowInvite'] = intval($event->allowinvite && $userStatus); // 0- guest can not invite their friends, 1- guest can invite their friends
		$this->jsonarray['event']['isCommunityAdmin'] = intval($isCommunityAdmin);
		$this->jsonarray['event']['isMap'] = intval($this->config->get('eventshowmap'));
		$query="SELECT *
				FROM #__community_events_members
				WHERE eventid={$uniqueID}
				AND memberid={$this->IJUserID}
				AND status=0";
		$this->db->setQuery($query);
		$isInvited = $this->db->loadObject();

		$this->jsonarray['event']['isInvitation']	= intval(!empty($isInvited)); // if user is invited to join event.
		if(!empty($isInvited)){
			$usr=$this->jomHelper->getUserDetail($isInvited->invited_by);
			$invitemessage=$usr->name." invited you to join this event.";

			// check how many friends are the member of this group
			$friendsModel =& CFactory::getModel('friends');
			$frids=$friendsModel->getFriendIds($this->IJUserID);

			$frdcount=0;
			foreach ($frids as $member){
				if($event->isMember($member)){
					$frdcount++;
				}
			}

			if($frdcount){
				$invitemessage.=" \n".$frdcount." of your friends are the members of this event.";
			}
			$this->jsonarray['event']['invitationMessage']	= $invitemessage;
			$this->jsonarray['event']['invitationicon']		= JURI::root().'components/com_community/templates/default/images/action/icon-invite-32.png';
		}
		$query="SELECT count(id)
				FROM #__community_activities
				WHERE eventid = '{$uniqueID}'
				AND app='events.wall'";
		$this->db->setQuery($query);
		$wallcount = $this->db->loadResult();
		$this->jsonarray['event']['comments'] = intval($wallcount);
		if($event->ticket>0){
			$avail = $event->ticket - $event->confirmedcount;//echo $avail;exit;
			$this->jsonarray['event']["total_seats"] = intval($event->ticket);
			$this->jsonarray['event']["available_seats"] = intval($avail);
		}else{
			$this->jsonarray['event']['total_seats'] = "";
			$this->jsonarray['event']['available_seats'] = "";
		}

		$this->jsonarray['event']['myStatus'] = intval($userStatus); // [Join / Invite]: 0 - [pending approval/pending invite], 1 - [approved/confirmed], 2 - [rejected/declined], 3 - [maybe/maybe], 4 - [blocked/blocked]

		//likes
		$likes = $this->jomHelper->getLikes ( 'events', $uniqueID, $this->IJUserID );
		$this->jsonarray['event']['likes'] = $likes->likes;
		$this->jsonarray['event']['dislikes'] = $likes->dislikes;
		$this->jsonarray['event']['liked'] = $likes->liked;
		$this->jsonarray['event']['disliked'] = $likes->disliked;

		// cover images
		if($event->cover)
		{
			$this->jsonarray ['event'] ['cover'] = JURI::base().$event->cover;
		}
		else
		{
			//set default event coverpic.
			$this->jsonarray ['event'] ['cover'] = JURI::base()."components/com_community/templates/default/images/cover/event-default.png";
		}

		if($this->config->get('user_avatar_storage') == 'file'){
			$p_url	= JURI::base();
		}else{
			$s3BucketPath	= $this->config->get('storages3bucket');
			if(!empty($s3BucketPath))
				$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
			else
				$p_url	= JURI::base();
		}

		$this->jsonarray['event']['avatar'] = ($event->avatar != '') ? $p_url.$event->avatar : JURI::base ().'components/com_community/assets/event_thumb.png';

		$grp=($event->type=='group') ? "&groupid={$event->contentid}" : '' ;

		if(SHARE_EVENT){
			$this->jsonarray['event']['shareLink']=JURI::base()."index.php?option=com_community&view=events&task=viewevent&eventid={$event->id}".$grp;
		}

		/*
		 * coding from views/events/view.html.php
		 * this coding is to fetch nesessory data for the options
		 */

		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );

		// Permissions and privacies
		CFactory::load('helpers' , 'owner');
		$isEventGuest		= $event->isMember( $this->my->id ); // is user member/guest?
		$isMine				= ($this->my->id == $event->creator); // is user event creator?
		$isAdmin			= $event->isAdmin( $this->my->id ); // is user event admin?

		// Get Event Admins
		$eventAdmins		= $event->getAdmins();

		// Attach avatar of the admin
		for( $i = 0; ($i < count($eventAdmins)); $i++){
			$row				=&	$eventAdmins[$i];
			$eventAdmins[$i]	=	CFactory::getUser( $row->id );
		}

		$waitingApproval	    = $event->isPendingApproval( $this->my->id ); // is pending approved for user?
		$this->jsonarray['event']['isWaitingApproval'] = intval($waitingApproval); // is member already requested to join and waiting for aproval?

		if($isMine || $isCommunityAdmin || $isAdmin){
			$query="SELECT COUNT(1)
					FROM #__community_events_members
					WHERE status=6
					AND eventid={$event->id}";
			$this->db->setQuery($query);
			$memberWaiting=$this->db->loadResult();
			$this->jsonarray['event']['memberWaiting']=intval($memberWaiting); // waiting member counts who requested to join private events and is required admin approval.
		}

		$waitingRespond	        = false;

		// Is this event is a past event?
		$now		=   new JDate();
		//if joomla 1.5 enable this
		$isPastEvent	=   ($event->getEndDate(false)->toSQL() < $now->toSql( true ) ) ? true : false;

		$myStatus = $event->getUserStatus($this->my->id);

		if($myStatus != COMMUNITY_EVENT_STATUS_BLOCKED){

			if( $isMine || $isCommunityAdmin || $isAdmin){
				$this->jsonarray['event']['menu']['editAvatar'] = 1;
				$this->jsonarray['event']['menu']['sendMail'] = 1;
				$this->jsonarray['event']['menu']['editEvent'] = 1;
			}else{
				$this->jsonarray['event']['menu']['editAvatar'] = 0;
				$this->jsonarray['event']['menu']['sendMail'] = 0;
				$this->jsonarray['event']['menu']['editEvent'] = 0;
			}

			if((($isEventGuest && ($event->allowinvite)) || $isAdmin) && $handler->hasInvitation() && !$isPastEvent){
				$this->jsonarray['event']['menu']['inviteFriend'] = 1;
			}else{
				$this->jsonarray['event']['menu']['inviteFriend'] = 0;
			}

			if( (!$isMine) && !($waitingRespond) && (COwnerHelper::isRegisteredUser()) ) {
				$this->jsonarray['event']['menu']['ignoreEvent'] = 1;
			}else{
				$this->jsonarray['event']['menu']['ignoreEvent'] = 0;
			}

			if( $handler->manageable() ) {
				$this->jsonarray['event']['menu']['deleteEvent'] = 1;
			}else{
				$this->jsonarray['event']['menu']['deleteEvent'] = 0;
			}

			if(!$isPastEvent && ($event->permission)){
				$this->jsonarray['event']['menu']['yourResponse'] = 1;
			}else{
				$this->jsonarray['event']['menu']['yourResponse'] = 0;
			}
		}
		return $this->jsonarray;
	}


	/**
     * @uses to fetch event users
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"members",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID",
 	 * 			"type":"type", // admin, gueast, waiting, blocked
 	 * 			"pageNO":"pageNO"
 	 * 		}
	 * 	}
     *
     */
	function members(){
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$pageNO		= IJReq::getTaskData('pageNO', 0, 'int');
		$type 		= IJReq::getTaskData('type');
		$limit		= PAGE_MEMBER_LIMIT;

		$event = JTable::getInstance ( 'Event', 'CTable' );
		$event->load ($uniqueID);

		if($pageNO == 0 || $pageNO == 1){
		  	$startFrom = 0;
		}else{
			$startFrom = ($limit*($pageNO-1));
		}

		switch ($type){
			case 'admin':
				$where="WHERE em.eventid ={$uniqueID}
						AND (em.permission = 1 OR em.permission = 2)";
				break;

			case 'guest':
				$where="WHERE em.eventid ={$uniqueID}
						AND em.status = 1";
				break;

			case 'waiting':
				$where="WHERE em.eventid ={$uniqueID}
						AND em.status = 6";
				break;

			case 'blocked':
				$where="WHERE em.eventid ={$uniqueID}
						AND em.status = 4";
				break;
		}

		$query="SELECT em.*, e.permission as user, e.allowinvite, e.creator
				FROM #__community_events_members as em
				LEFT JOIN #__community_events as e on e.id = em.eventid
				{$where}
				LIMIT {$startFrom},{$limit}";
		$this->db->setQuery($query);
		$results = $this->db->loadObjectList();

		$query="SELECT COUNT(em.id)
				FROM #__community_events_members as em
				LEFT JOIN #__community_events as e on e.id = em.eventid
				{$where}";
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if(count($results)>0){
			$this->jsonarray['code'] = 200;
			$this->jsonarray['total'] = intval($total);
			$this->jsonarray['pageLimit'] = intval($limit);
		}else{
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$friendsModel =& CFactory::getModel('friends');
		$frids=$friendsModel->getFriendIds($this->my->id);

		$cAdmin = $event->isAdmin($this->IJUserID);
		$cCommunityAdmin = COwnerHelper::isCommunityAdmin($this->IJUserID);

		foreach($results as $key=>$value){
			$user = $this->jomHelper->getUserDetail($value->memberid); // get user detail

			$this->jsonarray['members'][$key]['user_id']		= $user->id ;
			$this->jsonarray['members'][$key]['user_name']		= $user->name;
			$this->jsonarray['members'][$key]['user_avatar']	= $user->avatar;
			$this->jsonarray['members'][$key]['user_lat']		= $user->latitude;
			$this->jsonarray['members'][$key]['user_long']		= $user->longitude;
			$this->jsonarray['members'][$key]['user_online']	= $user->online;
			$this->jsonarray['members'][$key]['user_profile']	= $user->profile;

			$isAdmin=0;
			if($value->status == 1){
				$isAdmin = intval($event->isAdmin($user->id) or COwnerHelper::isCommunityAdmin($user->id));
			}

			if($value->status == 4){
				$isAdmin = $event->isAdmin($user->id);
			}

			$this->jsonarray['members'][$key]['isAdmin']=$isAdmin;
			$this->jsonarray['members'][$key]['canRemove']	= intval(($cAdmin OR $cCommunityAdmin) AND $this->IJUserID!=$user->id);
			$this->jsonarray['members'][$key]['canAdmin']	= intval(($cAdmin or $cCommunityAdmin) AND !$isAdmin AND $this->IJUserID!=$user->id);
			$this->jsonarray['members'][$key]['canMember']	= intval(($event->isAdmin($this->IJUserID) or COwnerHelper::isCommunityAdmin($this->IJUserID)) AND $isAdmin AND $this->IJUserID!=$user->id);
		}
		return $this->jsonarray;
	}


	/**
     * @uses to send mail to all participent
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"removeMember",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID", // event id
 	 * 			"userID":"userID", // user id
 	 * 			"block":"block" // boolean 0/1
 	 * 		}
	 * 	}
     *
     */
	function removeMember(){
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');
		$userID = IJReq::getTaskData('userID', 0, 'int');
		$block = IJReq::getTaskData('block', 0, 'bool');

		if($userID == 0)
		{
			$userID = $this->my->id;
		}
		if($userID == 0)
		{
			IJReq::setResponse(704,JText::_('Login Required'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($block==1){
			$this->jsonarray=$this->blockMember();
			if(!$this->jsonarray){
				return false;
			}
			return $this->jsonarray;
		}

		CFactory::load('helpers', 'owner');
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load($uniqueID);

		// Site admin can remove guest
		// Event creator can remove guest
		// The guest himself can remove himself
		if($event->isAdmin($this->my->id) || COwnerHelper::isCommunityAdmin($this->my->id) || $this->my->id==$userID){
			// Delete guest from event
			$event->removeGuest($userID, $uniqueID);

			// Update event stats count
			$event->updateGuestStats();
			$event->store();

			$this->jsonarray['code']=200;
			return $this->jsonarray;
		}else{
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}


	// called from remove admin
	private function blockMember(){
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');
		$userID = IJReq::getTaskData('userID', 0, 'int');

		CFactory::load('helpers', 'owner');
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		// Make sure I am the group admin
		if($event->isAdmin($userID) || COwnerHelper::isCommunityAdmin($this->my->id)){
			$guest	=& JTable::getInstance( 'EventMembers' , 'CTable' );
	        $guest->load($userID, $uniqueID);

			// Set status to "BLOCKED"
	        $guest->status = COMMUNITY_EVENT_STATUS_BLOCKED;
	        $guest->store();

	        // Update event stats count
	        $event->updateGuestStats();
	        $event->store();
	        $this->jsonarray['code']=200;
	        return $this->jsonarray;
        }else{
        	IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
        	IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
        	return false;
		}
	}


	/**
     * @uses to send mail to all participent
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"unblockMember",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID", // event id
 	 * 			"userID":"userID" // user id
 	 * 		}
	 * 	}
     *
     */
	function unblockMember(){
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');
		$userID = IJReq::getTaskData('userID', 0, 'int');

		CFactory::load('helpers', 'owner');
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		// Make sure I am the group admin
		if($event->isAdmin($this->my->id)){
			if(COwnerHelper::isCommunityAdmin($userID)){
				IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}else{
				// Make sure the user is not an admin
				$guest	=& JTable::getInstance( 'EventMembers' , 'CTable' );
				$guest->load($userID, $uniqueID);

				$guest->status = COMMUNITY_EVENT_STATUS_MAYBE;
				$guest->store();

				// Update event stats count
				$event->updateGuestStats();
				$event->store();
				$this->jsonarray['code']=200;
	       		return $this->jsonarray;
			}
		}else{
	       	IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
	       	IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
	       	return false;
		}
	}



	/**
     * @uses to send mail to all participent
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"sendmail",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID",
 	 * 			"title":"title",
 	 * 			"message":"message"
 	 * 		}
	 * 	}
     *
     */
	function sendmail() {
		$uniqueID 	= IJReq::getTaskData('uniqueID', 0, 'int');
		$title		= IJReq::getTaskData('title');
		$message	= IJReq::getTaskData('message');

		if(!$uniqueID){
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (empty ( $message )) {
			IJReq::setResponse(204,JText::_('COM_COMMUNITY_INBOX_MESSAGE_REQUIRED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if (empty ( $title )) {
			IJReq::setResponse(204,JText::_('COM_COMMUNITY_TITLE_REQUIRED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$this->my){
			IJReq::setResponse(401);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'libraries', 'notification' );
		CFactory::load ( 'helpers', 'owner' );
		CFactory::load ( 'models', 'events' );
		$event = JTable::getInstance ( 'Event', 'CTable' );
		$event->load ($uniqueID);

		CFactory::load('helpers','event');
		$handler = CEventHelper::getHandler($event);

		if (empty ( $uniqueID ) || ! $handler->manageable ()) {
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$members = $event->getMembers ( COMMUNITY_EVENT_STATUS_ATTEND, null );
		$emails = array ();
		$total = 0;

		foreach ( $members as $member ) {
			$user = CFactory::getUser ( $member->id );
			// Do not sent email notification to self
			if ($this->my->id != $user->id) {
				$total += 1;
				$emails [] = $user->id;
			}
		}

		$params		= new CParameter( '' );
		$params->set ( 'url', $handler->getFormattedLink ( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false, true ) );
		$params->set ( 'title', $title );
		$params->set ( 'message', $message );
		CNotificationLibrary::add( 'etype_events_sendmail' , $this->my->id , $emails , JText::sprintf( 'COM_COMMUNITY_EVENT_SENDMAIL_SUBJECT' , $event->title ) , '' , 'events.sendmail' , $params );
		//Send push notification
		// get user push notification params and user device token and device type
		$memberslist = implode(',',$emails);

		$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
				FROM #__ijoomeradv_users
				WHERE `userid` IN ('{$memberslist}')";
		$this->db->setQuery($query);
		$puserlist=$this->db->loadObjectList();

		//change for id based push notification
		$pushOptions['detail']=array();
		$pushOptions = gzcompress(json_encode($pushOptions));

		$match = array('{event}','{title}');
		$replace = array($event->title,$title);
		$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_EVENT_SENDMAIL_SUBJECT'));
		$obj = new stdClass();
		$obj->id 		= null;
		$obj->detail 	= $pushOptions;
		$obj->tocount  	= count($puserlist);
		$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
		if($obj->id){
			$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
			$this->jsonarray['pushNotificationData']['to'] 		= $memberslist;
			$this->jsonarray['pushNotificationData']['message'] = $message;
			$this->jsonarray['pushNotificationData']['type'] 	= 'eventmail';
			$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_events_sendmail';
		}
		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}


	/**
     * @uses to request invitation for private event
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"requestInvite",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID",
 	 * 		}
	 * 	}
     *
     */
	function requestInvite(){
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');

		if(!$this->IJUserID){
			IJReq::setResponse(401);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Load necessary tables
		$model	= CFactory::getModel('events');

		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load($uniqueID);

		$eventMembers	=& JTable::getInstance( 'EventMembers' , 'CTable' );
		$eventMembers->load($this->my->id, $uniqueID);
		$isMember		= $eventMembers->exists();

		if( $isMember ){
			IJReq::setResponse(707,JText::_( 'COM_COMMUNITY_EVENTS_ALREADY_MEMBER' ));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}else{
			// Set the properties for the members table
			$eventMembers->eventid	= $event->id;
			$eventMembers->memberid	= $this->my->id;

			CFactory::load( 'helpers' , 'owner' );

	 		//@todo: need to set the privileges
	 		$date   =& JFactory::getDate();
	 		$eventMembers->status			= COMMUNITY_EVENT_STATUS_REQUESTINVITE; // for now just set it to approve for the demo purpose
	 		$eventMembers->permission		= '3'; //always a member
	 		$eventMembers->created			= $date->toSql();

			// Get the owner data
			$owner	= CFactory::getUser( $event->creator );

			$store	= $eventMembers->store();

			// Build the URL.
			//$url	= CUrl::build( 'groups' , 'viewgroup' , array( 'groupid' => $group->id ) , true );
			$url 	= CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, false);

			// Add notification
			CFactory::load( 'libraries' , 'notification' );
			CFactory::load( 'helpers' , 'event' );

			$emails		= array();
			$emails[] 	= $owner->id;

			$params		= new CParameter( '' );
			$params->set( 'url'		, 'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id );
			$params->set( 'event'	, $event->title );
			$params->set( 'event_url'	, 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id  );
			CNotificationLibrary::add( 'event_join_request' , $this->my->id , $emails , JText::sprintf( 'COM_COMMUNITY_EVENT_JOIN_REQUEST_SUBJECT' ) , '' , 'events.joinrequest' , $params );

			//Send push notification
			// get user push notification params and user device token and device type
			$memberslist = implode(',',$emails);
			$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid` IN ('{$memberslist}')";
			$this->db->setQuery($query);
			$puserlist=$this->db->loadObjectList();

			$eventdata['id'] = $event->id;
			$eventdata['title'] = $event->title;
			$eventdata['location'] = $event->location;
			$eventdata['groupid'] = $event->contentid;
			$format	= ($this->config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
			$eventdata['startdate'] = CTimeHelper::getFormattedTime($event->startdate, $format);
			$eventdata['enddate'] = CTimeHelper::getFormattedTime($event->enddate, $format);
			$eventdata['date'] = strtoupper(CEventHelper::formatStartDate($event, $this->config->get('eventdateformat')));

			if($this->config->get('user_avatar_storage') == 'file'){
					$p_url	= JURI::base();
			}else{
				$s3BucketPath	= $this->config->get('storages3bucket');
				if(!empty($s3BucketPath))
					$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
				else
					$p_url	= JURI::base();
			}

			$eventdata['avatar'] = ($result->avatar != '') ? $p_url. $event->avatar : JURI::base ().'components/com_community/assets/event_thumb.png';
			$eventdata['past'] = (strtotime($event->enddate)<time()) ? 1 : 0;
			$eventdata['ongoing'] = (strtotime($event->startdate)<=time() and strtotime($event->enddate)>time()) ? 1 : 0;
			$eventdata['confirmed']=$event->confirmedcount;

			//change for id based push notification
			$pushOptions['detail']['content_data']			= $eventdata;
			$pushOptions['detail']['content_data']['type'] 	= 'event';
			$pushOptions = gzcompress(json_encode($pushOptions));

			$usr		= $this->jomHelper->getUserDetail($this->IJUserID);
			$match 		= array('{actor}','{event}');
			$replace 	= array($usr->name,$event->title);
			$message 	= str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_EVENT_JOIN_REQUEST_SUBJECT'));
			$obj = new stdClass();
			$obj->id 		= null;
			$obj->detail 	= $pushOptions;
			$obj->tocount  	= count($puserlist);
			$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
			if($obj->id){
				$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
				$this->jsonarray['pushNotificationData']['to'] 		= $memberslist;
				$this->jsonarray['pushNotificationData']['message'] = $message;
				$this->jsonarray['pushNotificationData']['type'] 	= 'event';
				$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_event_join_request';
			}

			//trigger for on event request invite
			CFactory::load('controllers','events');
			$event_controller_obj = new CommunityEventsController ( );
			$event_controller_obj->triggerEvents( 'onEventRequestInvite' , $event , $this->my->id);
			IJReq::setResponse(708);
			return false;
		}
	}


	/**
     * @uses to approve user invitation
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"approveMember",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID",
 	 * 			"memberID":"memberID"
 	 * 		}
	 * 	}
     *
     */
	function approveMember(){
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');
		$memberID = IJReq::getTaskData('memberID', 0, 'int');

		if(!$uniqueID || !$memberID){
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$filter	    =	JFilterInput::getInstance();
		$uniqueID = $filter->clean( $uniqueID, 'int' );
		$memberID = $filter->clean( $memberID, 'int' );

		$model		= CFactory::getModel( 'events' );

		$event  	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );

		if( !$handler->manageable() ){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}else{
			// Load required tables
			$member		=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$memberkeys['eventId']=$uniqueID;
			$memberkeys['memberId']=$memberID;
			$member->load($memberkeys);

			$member->attend();
			$member->store();

			// Build the URL.
			$user	= CFactory::getUser( $memberID );

			// Send email to evnt member once their invitation is approved
			CFactory::load( 'libraries' , 'notification' );

			$params			= new CParameter( '' );
			$params->set('url' , CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, false));
			$params->set('eventTitle' , $event->title );
			$params->set('event' , $event->title );
			$params->set('event_url' , 'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id );
			CNotificationLibrary::add( 'events_invitation_approved' , $event->creator , $user->id , JText::sprintf( 'COM_COMMUNITY_EVENTS_EMAIL_SUBJECT' , $event->title ) , '' , 'events.invitation.approved' , $params );

			// get user push notification params
			$eventdata['id'] = $event->id;
			$eventdata['title'] = $event->title;
			$eventdata['location'] = $event->location;
			$eventdata['groupid'] = $event->contentid;
			$format	= ($this->config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
			$eventdata['startdate'] = CTimeHelper::getFormattedTime($event->startdate, $format);
			$eventdata['enddate'] = CTimeHelper::getFormattedTime($event->enddate, $format);
			$eventdata['date'] = strtoupper(CEventHelper::formatStartDate($event, $this->config->get('eventdateformat')));

			if($this->config->get('user_avatar_storage') == 'file'){
					$p_url	= JURI::base();
			}else{
				$s3BucketPath	= $this->config->get('storages3bucket');
				if(!empty($s3BucketPath))
					$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
				else
					$p_url	= JURI::base();
			}

			$eventdata['avatar'] = ($result->avatar != '') ? $p_url. $event->avatar : JURI::base ().'components/com_community/assets/event_thumb.png';
			$eventdata['past'] = (strtotime($event->enddate)<time()) ? 1 : 0;
			$eventdata['ongoing'] = (strtotime($event->startdate)<=time() and strtotime($event->enddate)>time()) ? 1 : 0;
			$eventdata['confirmed']=$event->confirmedcount;

			$query="SELECT `jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid`={$user->id}";
			$this->db->setQuery($query);
			$puser=$this->db->loadObject();
			$ijparams = new CParameter($puser->jomsocial_params);

			//change for id based push notification
			$pushOptions['detail']['content_data']			= $eventdata;
			$pushOptions['detail']['content_data']['type'] 	= 'event';
			$pushOptions = gzcompress(json_encode($pushOptions));

			$message = str_replace('{event}',$event->title,JText::sprintf('COM_COMMUNITY_EVENTS_EMAIL_SUBJECT'));
			$obj = new stdClass();
			$obj->id 		= null;
			$obj->detail 	= $pushOptions;
			$obj->tocount  	= 1;
			$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
			if($obj->id){
				$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
				$this->jsonarray['pushNotificationData']['to'] 		= $user->id;
				$this->jsonarray['pushNotificationData']['message'] = $message;
				$this->jsonarray['pushNotificationData']['type'] 	= 'event';
				$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_events_invitation_approved';
			}
		}
		$this->jsonarray['code']=200;
		return $this->jsonarray;
	}


	/**
     * @uses to send mail to all participent
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"response",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID",
 	 * 			"status":"status" // 1: attend, 2: not Attend
 	 * 		}
	 * 	}
     *
     */
	function response() {
		CFactory::load ( 'helpers', 'friends' );

		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$status		= IJReq::getTaskData('status', 0, 'int');

		if ($this->my->id == 0){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$model = CFactory::getModel ( 'events' );
		$event = JTable::getInstance ( 'Event', 'CTable' );
		$event->load($uniqueID);

		CFactory::load ( 'helpers', 'event' );
		$handler = CEventHelper::getHandler ( $event );

		if (! $handler->isAllowed()) {
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(($event->ticket) && (($status == COMMUNITY_EVENT_STATUS_ATTEND ) && ($event->confirmedcount + 1) > $event->ticket) ){
			IJReq::setResponse(416,JText::_('COM_COMMUNITY_EVENTS_TICKET_FULL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$eventMember = JTable::getInstance ( 'EventMembers', 'CTable' );
		$eventkey['eventId'] =$uniqueID;
		$eventkey['memberId'] =$this->my->id;
		$eventMember->load ( $eventkey );

		if ($eventMember->permission != 1 && $eventMember->permission != 2) {
			$eventMember->permission = 3; //always a member
		}

		$date = JFactory::getDate ();
		$eventMember->created = $date->toSql();
		$eventMember->status = $status;
		$eventMember->store();

		$event->updateGuestStats ();
		$event->store();

		//activities stream goes here.
		$url = $handler->getFormattedLink ( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false );
		$statustxt = JText::_ ( 'COM_COMMUNITY_EVENTS_NO' );

		if ($status == COMMUNITY_EVENT_STATUS_ATTEND) {
			$statustxt = JText::_ ( 'COM_COMMUNITY_EVENTS_YES' );
		}

		if ($status == COMMUNITY_EVENT_STATUS_MAYBE) {
			$statustxt = JText::_ ( 'COM_COMMUNITY_EVENTS_MAYBE' );
		}

		CFactory::load ( 'helpers', 'event' );
		$handler = CEventHelper::getHandler ( $event );

		// We update the activity only if a user attend an event and the event was set to public event
		if ($status == COMMUNITY_EVENT_STATUS_ATTEND && $handler->isPublic ()) {
			$command = 'events.attendence.attend';
			$actor = $this->my->id;
			$target = 0;
			$content = '';
			$cid = $event->id;
			$app = 'events';
			$act = $handler->getActivity ( $command, $actor, $target, $content, $cid, $app );
			$act->eventid	= $event->id;

			$params 		= new CParameter('');
			$action_str = 'events.attendence.attend';
			$params->set ( 'eventid', $event->id );
			$params->set ( 'action', $action_str );
			$params->set ( 'event_url', $handler->getFormattedLink ( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false, true, false ) );

			// Add activity logging
			CFactory::load ( 'libraries', 'activities' );
			CActivityStream::add ( $act, $params->toString () );
		}

		//trigger goes here.
		CFactory::load ( 'libraries', 'apps' );
		$appsLib = & CAppPlugins::getInstance ();
		$appsLib->loadApplications ();

		$params = array ();
		$params [] = &$event;
		$params [] = $this->my->id;
		$params [] = $status;

		if (! is_null ( $target ))
			$params [] = $target;

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}


	/**
     * @uses to edit avatar
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"editAvatar",
 	 * 		"taskData":{
 	 * 			"uniqueID":"uniqueID"
 	 * 		}
	 * 	}
	 *
	 * // avatar image will be post with the name 'image'
     *
     */
	function editAvatar() {
		$uniqueID = IJReq::getTaskData('uniqueID', 0, 'int');
		if(!$uniqueID){
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$event = JTable::getInstance ( 'Event', 'CTable' );
		$event->load($uniqueID);

		CFactory::load ( 'helpers', 'event' );
		$handler = CEventHelper::getHandler ( $event );

		if (! $handler->manageable ()) {
			IJReq::setResponse(706,JText::_ ( 'COM_COMMUNITY_NOT_ALLOWED_TO_ACCESS_SECTION' ));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load ( 'libraries', 'apps' );
		$appsLib = & CAppPlugins::getInstance ();
		$saveSuccess = $appsLib->triggerEvent ( 'onFormSave', array ('jsform-events-uploadavatar' ) );

		if (empty ( $saveSuccess ) || ! in_array ( false, $saveSuccess )) {
			CFactory::load ( 'helpers', 'image' );
			$file = JRequest::getVar ( 'image', '', 'FILES', 'array' );

			if (empty ( $file )) {
				IJReq::setResponse(204);
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if (!CImageHelper::isValidType ( $file ['type'] ) or !CImageHelper::isValid ( $file ['tmp_name'] )) {
				IJReq::setResponse(415,JText::_('COM_COMMUNITY_IMAGE_FILE_NOT_SUPPORTED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$uploadLimit = ( double ) $this->config->get ( 'maxuploadsize' );
			$uploadLimit = ($uploadLimit * 1024 * 1024);

			// @rule: Limit image size based on the maximum upload allowed.
			if (filesize ( $file ['tmp_name'] ) > $uploadLimit && $uploadLimit != 0) {
				IJReq::setResponse(416,JText::_('COM_COMMUNITY_VIDEOS_IMAGE_FILE_SIZE_EXCEEDED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// @todo: configurable width?
			$imageMaxWidth = 160;

			// Get a hash for the file name.
			$fileName = JApplication::getHash ( $file ['tmp_name'] . time () );
			$hashFileName = JString::substr ( $fileName, 0, 24 );

			// @todo: configurable path for avatar storage?
			$storage = JPATH_ROOT .'/'. $this->config->getString ( 'imagefolder' ) . '/avatar/events';
			$storageImage = $storage .'/'. $hashFileName . CImageHelper::getExtension ( $file ['type'] );
			$image = $this->config->getString ( 'imagefolder' ) . '/avatar/events/' . $hashFileName . CImageHelper::getExtension ( $file ['type'] );

			$storageThumbnail = $storage . '/thumb_' . $hashFileName . CImageHelper::getExtension ( $file ['type'] );
			$thumbnail = $this->config->getString ( 'imagefolder' ) . '/avatar/events/' . 'thumb_' . $hashFileName . CImageHelper::getExtension ( $file ['type'] );

			// Generate full image
			if (! CImageHelper::resizeProportional ( $file ['tmp_name'], $storageImage, $file ['type'], $imageMaxWidth )) {
				IJReq::setResponse(500,JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE' , $storageImage));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// Generate thumbnail
			if (! CImageHelper::createThumb ( $file ['tmp_name'], $storageThumbnail, $file ['type'] )) {
				IJReq::setResponse(500,JText::sprintf('COM_COMMUNITY_ERROR_MOVING_UPLOADED_FILE' , $storageImage));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			// Update the event with the new image
			$event->setImage ( $image, 'avatar' );
			$event->setImage ( $thumbnail, 'thumb' );

			CFactory::load ( 'helpers', 'event' );
			$handler = CEventHelper::getHandler ( $event );

			if ($handler->isPublic ()) {
				$actor = $this->my->id;
				$target = 0;
				$content = '<img class="event-thumb" src="' . rtrim ( JURI::root (), '/' ) . '/' . $image . '" style="border: 1px solid #eee;margin-right: 3px;" />';
				$cid = $event->id;
				$app = 'events';
				$act = $handler->getActivity ( 'events.avatar.upload', $actor, $target, $content, $cid, $app );
				$act->eventid	= $event->id;

				$params = new CParameter('');
				$params->set ( 'event_url', $handler->getFormattedLink ( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id, false, true, false ) );

				CFactory::load ( 'libraries', 'activities' );
				CActivityStream::add ( $act, $params->toString () );
			}

			//add user points
			CFactory::load ( 'libraries', 'userpoints' );
			CUserPoints::assignPoint ( 'event.avatar.upload' );
			$query='SELECT MAX(id)
					FROM #__community_events';
			$this->db->setQuery($query);
			$res = $this->db->loadResult();

			$query="SELECT avatar,thumb
					FROM #__community_events
					WHERE id = {$res}";
			$this->db->setQuery($query);
			$res = $this->db->loadObject();
			$this->jsonarray['code']	= 200;
			$this->jsonarray['avatar']	= JURI::base().$res->avatar;
			return $this->jsonarray;
		}
	}


	/**
	 * @uses to add like to the event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"like",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID" // optional, if not passed then logged in user id will be used
	 * 		}
	 * 	}
	 *
	 */
    function like(){
    	$uniqueID=IJReq::getTaskData('uniqueID',0,'int');
    	if(!$uniqueID){
    		IJReq::setResponse(400);
    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
    		return false;
    	}
    	if($this->jomHelper->Like('events',$uniqueID)){
    		$this->jsonarray['code']=200;
    		return $this->jsonarray;
    	}else{
    		IJReq::setResponse(500);
    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
    		return false;
    	}
    }

 	/**
	 * @uses to add dislike to the event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"dislike",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID" // optional, if not passed then logged in user id will be used
	 * 		}
	 * 	}
	 *
	 */
    function dislike(){
    $uniqueID=IJReq::getTaskData('uniqueID',0,'int');
    	if(!$uniqueID){
    		IJReq::setResponse(400);
    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
    		return false;
    	}
    	if($this->jomHelper->Dislike('events',$uniqueID)){
    		$this->jsonarray['code']=200;
    		return $this->jsonarray;
    	}else{
    		IJReq::setResponse(500);
    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
    		return false;
    	}
    }


	/**
	 * @uses to unlike like/dislike value to the event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"unlike",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID"
	 * 		}
	 * 	}
	 *
	 */
    function unlike(){
    $uniqueID=IJReq::getTaskData('uniqueID',0,'int');
    	if(!$uniqueID){
    		IJReq::setResponse(400);
    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
    		return false;
    	}
    	if($this->jomHelper->Unlike('events',$uniqueID)){
    		$this->jsonarray['code']=200;
    		return $this->jsonarray;
    	}else{
    		IJReq::setResponse(500);
    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
    		return false;
    	}
    }


    /**
	 * @uses to ignore event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"ignore",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID"
	 * 		}
	 * 	}
	 */
	function ignore(){
		$uniqueID	= IJReq::getTaskData('uniqueID' , 0, 'int');

		if( $this->my->id == 0 ){
			IJReq::setResponse(401,JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

        $eventMembers	=& JTable::getInstance( 'EventMembers' , 'CTable' );
        $eventMembers->load($this->my->id, $uniqueID);

        $event	=& JTable::getInstance( 'Event' , 'CTable' );
        $event->load($uniqueID);

        if($eventMembers->id != 0){
            $eventMembers->status = COMMUNITY_EVENT_STATUS_IGNORE;
            $eventMembers->store();

            //now we need to update the events various count.
			$event->updateGuestStats();
            $event->store();
        }
    	$this->jsonarray['code']=200;
		return $this->jsonarray;
    }


    /**
	 * @uses to delete event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"delete",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID"
	 * 		}
	 * 	}
	 */
	function delete(){
    	$uniqueID= IJReq::getTaskData('uniqueID', 0, 'int');
		if( empty($uniqueID) ){
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_EVENTS_INVALID_ID_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		CFactory::load( 'libraries' , 'activities' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events' );

		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );
		$membersCount	= $event->getMembersCount('accepted');
		$isMine			= ($this->my->id == $event->creator);

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );

		if( !$handler->manageable() ){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Delete all event members
		if(!$event->deleteAllMembers()){
			IJReq::setResponse(500);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Delete all event wall
		if(!$event->deleteWalls()){
			IJReq::setResponse(500);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Delete event master record
		$eventData = $event;

		if( $event->delete() ){
			// Delete featured event.
			CFactory::load( 'libraries' , 'featured' );
    		$featured	= new CFeatured(FEATURED_EVENTS);
    		$featured->delete($uniqueID);

			jimport( 'joomla.filesystem.file' );

			if($eventData->avatar != 'components/com_community/assets/eventAvatar.png' && !empty( $eventData->avatar ) ){
				$path = explode('/', $eventData->avatar);

				$file = JPATH_ROOT .'/'. $path[0] .'/'. $path[1] .'/'. $path[2] . '/'. $path[3];
				if(JFile::exists($file)){
					JFile::delete($file);
				}
			}

			if($eventData->thumb != 'components/com_community/assets/event_thumb.png' && !empty( $eventData->avatar ) ){
				$file	= JPATH_ROOT .'/'. JString::str_ireplace('/', DS, $eventData->thumb);
				if(JFile::exists($file)){
					JFile::delete($file);
				}
			}

			//trigger for onGroupDelete
			CFactory::load('controllers','events');
			$event_controller_obj = new CommunityEventsController ( );
			$event_controller_obj->triggerEvents( 'onAfterEventDelete' , $eventData);

			// Remove from activity stream
			CActivityStream::remove('events', $uniqueID);

			$this->jsonarray['code']=200;
		}else{
			IJReq::setResponse(500);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return $this->jsonarray;
    }

    /**
	 * @uses to report event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"report",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"message":"message"
	 * 		}
	 * 	}
	 */
	function report(){
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$message	= IJReq::getTaskData("message");

		if($uniqueID==0){
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$link=JURI::base()."index.php?option=com_community&view=events&task=viewevent&eventid=".$uniqueID;

		CFactory::load( 'libraries' , 'reporting' );

		$report = new CReportingLibrary();
		$report->createReport( JText::_('COM_COMMUNITY_EVENTS_BAD') , $link , $message );

		$action					= new stdClass();
		$action->label			= 'Unpublish event';
		$action->method			= 'events,unpublishEvent';
		$action->parameters		= $uniqueID;
		$action->defaultAction	= true;

		$report->addActions( array( $action ) );

		$this->jsonarray['code']=200;
		return $this->jsonarray;
	}


	/**
	 * @uses to set user as admin
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"setAdmin",
	 * 		"taskData":{
	 * 			"userID":"userID",
	 * 			"uniqueID":"uniqueID"
	 * 		}
	 * 	}
	 */
	function setAdmin(){
		$userID		= IJReq::getTaskData('userID', 0, 'int');
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');

		if($userID==0 or $uniqueID==0){
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );

		if( !$handler->manageable() ){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}else{
			$member		=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$member->load( $userID , $event->id );
			$member->permission	= 2;
			$member->store();
		}

		$this->jsonarray['code']=200;
		return $this->jsonarray;
	}


	/**
	 * @uses to revert Admin
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"setUser",
	 * 		"taskData":{
	 * 			"userID":"userID",
	 * 			"uniqueID":"uniqueID"
	 * 		}
	 * 	}
	 */
	function setUser(){
		$userID		= IJReq::getTaskData('userID', 0, 'int');
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');

		if($userID==0 or $uniqueID==0){
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );

		if( !$handler->manageable() ){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}else{
			$member		=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$member->load( $userID , $event->id );
			$member->permission	= 3;
			$member->store();
		}

		$this->jsonarray['code']=200;
		return $this->jsonarray;
	}


	/**
	 * @uses to add/edit event
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"addEvent",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // optional, if id passed then edit event
	 * 			"groupID":"groupID" // optional
	 * 			"fields":"fields" // optional: if 0: add/edit event, 1: field list.
	 * 		}
	 * 	}
	 */
	function addEvent(){
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$fields	= IJReq::getTaskData('fields', 0, 'bool');

		if($fields){ // for getting fields to add/edit event.
			$this->jsonarray=$this->addEventFields($uniqueID);
			if(!$this->jsonarray){
				return false;
			}
			return $this->jsonarray;
		}

    	require_once JPATH_ROOT . '/components/com_community/controllers/events.php';
		$event_controller_obj = new CommunityEventsController ();
		$event		= JTable::getInstance( 'Event' , 'CTable' );

		if($uniqueID){
			$event->load($uniqueID);
			CFactory::load( 'helpers' , 'event' );
			$handler	= CEventHelper::getHandler( $event );

			if( ! $handler->manageable() ){
				IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$eid = $this->save($event);

		if($eid !== FALSE ){
			if($uniqueID == '0'){
				$event->load($eid);
			}else{
				$event->load($uniqueID);
			}

			//trigger for onGroupCreate
			$event_controller_obj->triggerEvents( 'onEventUpdate' , $event);

			$this->jsonarray['code']=200;
			return $this->jsonarray;
		}else{
			return false;
		}

		CFactory::load( 'libraries' , 'userpoints' );
		CUserPoints::assignPoint('events.update');

		return $this->jsonarray;
    }


	// to get field list to add/edit event.
	private function addEventFields($uniqueID=0) {
		CFactory::load( 'helpers', 'event');
		CFactory::load( 'helpers', 'category');
		CFactory::load( 'helpers' , 'time');

		$halper_category_obj=new CCategoryHelper();
		$halper_time_obj=new CTimeHelper();

		$fieldList = array ('title'			=> array ('text',		1, JText::_('COM_COMMUNITY_EVENTS_TITLE_LABEL')),
							'summary'		=> array ('textarea',	0, JText::_('COM_COMMUNITY_EVENTS_SUMMARY')),
							'description'	=> array ('textarea',	0, JText::_('COM_COMMUNITY_EVENTS_DESCRIPTION')),
							'catid'			=> array ('select',		1, JText::_('COM_COMMUNITY_EVENTS_CATEGORY')),
							'location'		=> array ('map',		1, JText::_('COM_COMMUNITY_EVENTS_LOCATION')),
							'startdate'		=> array ('datetime',	1, JText::_('COM_COMMUNITY_EVENTS_START_TIME')),
							'enddate'		=> array ('datetime',	1, JText::_('COM_COMMUNITY_EVENTS_END_TIME')),
							'allday'		=> array ('checkbox',	0, JText::_('COM_COMMUNITY_EVENTS_ALL_DAY')),
							'repeat'		=> array ('select',		1, JText::_('COM_COMMUNITY_EVENTS_REPEAT')),
							'repeatend'		=> array ('dataetime',	1, JText::_('COM_COMMUNITY_EVENTS_REPEAT_END')),
							'offset'		=> array ('select',		1, JText::_('COM_COMMUNITY_TIMEZONE')),
							'permission'	=> array ('checkbox',	0, JText::_('COM_COMMUNITY_EVENTS_PRIVATE_EVENT')),
							'ticket'		=> array ('text',		1, JText::_('COM_COMMUNITY_EVENTS_NO_SEAT')),
							'allowinvite'	=> array ('checkbox',	0, JText::_('COM_COMMUNITY_EVENTS_GUEST_INVITE'))
						);

		$query="SELECT *
				FROM #__community_events_category";
		$this->db->setQuery($query);
		$cats = $this->db->loadObjectList ();
		$catlist=$halper_category_obj->getCategories($cats);

		$timezone=$halper_time_obj->getTimezoneList();
		$event=false;
		$this->jsonarray['code']=200;
		if ($uniqueID != '' || $uniqueID != 0) {
			$event		= JTable::getInstance( 'Event' , 'CTable' );
			$event->load($uniqueID);
		}
		$i=0;
		foreach ($fieldList as $key=>$field){
			$this->jsonarray['fields'][$i]['name']		= $key;
			$this->jsonarray['fields'][$i]['type']		= $field[0];
			$this->jsonarray['fields'][$i]['required']	= $field[1];
			$this->jsonarray['fields'][$i]['caption']	= $field[2];
			if ($event) { // if edit event.. value should be passed to adit.
				$this->jsonarray['fields'][$i]['value']	= trim($event->{$key});
			}else{
				$this->jsonarray['fields'][$i]['value']	= '';
			}

			if($key=='catid'){
				foreach ($catlist as $catk=>$catv){
					$this->jsonarray['fields'][$i]['options'][$catk]['name']	= $catv['nodeText'];
					$this->jsonarray['fields'][$i]['options'][$catk]['value']	= $catv['id'];
				}
			}

			if($key=='repeat'){
				$this->jsonarray['fields'][$i]['options'][0]['name']	= 'None';
				$this->jsonarray['fields'][$i]['options'][0]['value']	= '';
				$this->jsonarray['fields'][$i]['options'][1]['name']	= 'Daily';
				$this->jsonarray['fields'][$i]['options'][1]['value']	= 'daily';
				$this->jsonarray['fields'][$i]['options'][2]['name']	= 'Weekly';
				$this->jsonarray['fields'][$i]['options'][2]['value']	= 'weekly';
				$this->jsonarray['fields'][$i]['options'][3]['name']	= 'Monthly';
				$this->jsonarray['fields'][$i]['options'][3]['value']	= 'monthly';
			}

			if($key=='offset'){
				$is=0;
				foreach($timezone as $timek=>$timev){
					$this->jsonarray['fields'][$i]['options'][$is]['name']	= $timev;
					$this->jsonarray['fields'][$i]['options'][$is]['value']	= $timek;
					$is++;
				}
			}
			$i++;
		}
		return $this->jsonarray;
	}


	// call from addEvent
	private function save(&$event){
 		// Get my current data.
		$model		= CFactory::getModel( 'events' );

		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$groupId    = IJReq::getTaskData('groupID', 0, 'int');
		$isNew		= ($uniqueID == 0) ? true : false;

		$postData['title']			= IJReq::getTaskData('title');
		$postData['summary']		= IJReq::getTaskData('summary');
		$postData['description']	= IJReq::getTaskData('description');
		$postData['catid']			= IJReq::getTaskData('catid', 0, 'int');
		$postData['location']		= IJReq::getTaskData('location');
		$startdate	= IJReq::getTaskData('startdate');
		$enddate	= IJReq::getTaskData('enddate');
		$startdate	= explode(' ', $startdate);
		$enddate	= explode(' ', $enddate);
		$postData['startdate']		= $startdate[0];
		$postData['enddate']		= $enddate[0];
		if(isset($startdate[1]) && isset($enddate[1])){
			$startdate	= explode(':',$startdate[1]);
			$enddate	= explode(':',$enddate[1]);
			$postData['starttime-hour']	= $startdate[0];
			$postData['starttime-min']	= $startdate[1];
			$postData['starttime-ampm']	= ($startdate[0]<12) ? 'AM' : 'PM';
			$postData['endtime-hour']	= $enddate[0];
			$postData['endtime-min']	= $enddate[1];
			$postData['endtime-ampm']	= ($enddate[0]<12) ? 'AM' : 'PM';
		}else{
			$postData['starttime-hour']	= 12;
			$postData['starttime-min']	= 0;
			$postData['starttime-ampm']	= 'AM';
			$postData['endtime-hour']	= 12;
			$postData['endtime-min']	= 0;
			$postData['endtime-ampm']	= 'AM';
		}
		$postData['allday']			= 1;
		$postData['repeat']			= IJReq::getTaskData('repeat');
		$postData['repeatend']		= IJReq::getTaskData('repeatend');
		$postData['offset']			= IJReq::getTaskData('offset');
		$postData['ticket']			= IJReq::getTaskData('ticket', 0, 'int');
		$postData['permission']		= IJReq::getTaskData('permission', 0, 'int');
		$postData['allowinvite']	= IJReq::getTaskData('allowinvite', 0, 'int');

		//format startdate and eendate with time before we bind into event object
		$this->_formatStartEndDate($postData);

		$event->load($uniqueID);

		// record event original start and end date
		$postData['oldstartdate'] = $event->startdate;
		$postData['oldenddate']   = $event->enddate;

		$event->bind($postData);

		if(!array_key_exists('permission', $postData) ) {
			$event->permission  =   0;
		}

		if( !array_key_exists('allowinvite', $postData) ) {
			$event->allowinvite =   0;
		}else if( isset( $postData['endtime-ampm'] ) && $postData['endtime-ampm'] == 'AM' && $postData['endtime-hour'] == 12 ){
			$postData['endtime-hour'] = 00;
		}

		$inputFilter = CFactory::getInputFilter(true);

		// Despite the bind, we would still need to capture RAW description
		//$event->description = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$event->description = $inputFilter->clean($event->description);

		// @rule: Test for emptyness
		if( empty( $event->title ) ){
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_EVENTS_TITLE_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if( empty( $event->location ) ){
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_EVENTS_LOCATION_ERR0R'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// @rule: Test if the event is exists
		if( $model->isEventExist( $event->title, $event->location , $event->startdate, $event->enddate, $uniqueID) ){
			IJReq::setResponse(707,JText::_('COM_COMMUNITY_EVENTS_TAKEN_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// @rule: Start date cannot be empty
		if( empty( $event->startdate ) ){
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_EVENTS_STARTDATE_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// @rule: End date cannot be empty
		if( empty( $event->enddate ) ){
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_EVENTS_ENDDATE_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// @rule: Number of ticket must at least be 0
		if( Jstring::strlen( $event->ticket ) <= 0 ){
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_EVENTS_TICKET_EMPTY_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$now = CTimeHelper::getLocaleDate();
		CFactory::load('helpers', 'time');

		if(CTimeHelper::timeIntervalDifference($event->startdate, $event->enddate) > 0){
			IJReq::setResponse(416,JText::_('COM_COMMUNITY_EVENTS_STARTDATE_GREATER_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// if all day event.
		$isToday = false;
		if ($postData['allday'] == '1') {
			$isToday = date("Y-m-d", strtotime($event->enddate)) == date("Y-m-d", strtotime($now->toSql( true ))) ? true : $isToday ;
		}

		// @rule: Event must not end in the past
		/*$now = new JDate();
		$jConfig	= JFactory::getConfig();
		$now->setOffset( $jConfig->getValue('offset') + (- COMMUNITY_DAY_HOURS ) );*/

		if( CTimeHelper::timeIntervalDifference( $now->toSql( true ), $event->enddate) > 0  && !$isToday){
			IJReq::setResponse(416,JText::_('COM_COMMUNITY_EVENTS_ENDDATE_GREATER_ERROR'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$eventChild = array();
		// check event recurrence limit.

		if ( !empty($event->repeat) && ($isNew || $postData['repeataction'] == 'future')) {
			$repeatLimit = 'COMMUNITY_EVENT_RECURRING_LIMIT_' . strtoupper($event->repeat);
			if (defined($repeatLimit)) {
				$eventChild = $this->_generateRepeatList($event);
				if (count($eventChild) > constant($repeatLimit)){
					IJReq::setResponse(416,sprintf(JText::_('COM_COMMUNITY_EVENTS_REPEAT_LIMIT_ERROR')));
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
			}
		}

		if( !$this->config->get('eventshowtimezone') ){
			$event->offset	= 0;
		}

		// Set the default thumbnail and avatar for the event just in case
		// the user decides to skip this
		if($isNew){
			$event->creator		= $this->my->id;
			//@rule: If event moderation is enabled, event should be unpublished by default
			$event->published	= $this->config->get('event_moderation') ? 0 : 1;
			$event->created		= JFactory::getDate()->toSql();

			$event->contentid	= $groupId;
			$event->type		= ($groupId) ? 'group':'profile';
		}

		$event->store();

		// Save event members
		if ($isNew && !$event->isRecurring())
		{
			$this->_saveMember($event);

			// Increment the member count
			$event->updateGuestStats();
			$event->store();
		}

		if ($isNew) {
			$event->parent = !empty($event->repeat) ? $event->id : 0;
		}

		// Save recurring event's child.
		$this->_saveRepeatChild($event, $eventChild, $isNew, $postData);

		if($isNew){
			// add activity stream
			$this->_addActivityStream($event);

			//add user points
			$action_str = 'events.create';
			CFactory::load( 'libraries' , 'userpoints' );
			CUserPoints::assignPoint($action_str);

			//add notification: New group event is added
            $this->_addGroupNotification($event);

			//Send notification
            $modelGroup			=& CFactory::getModel( 'groups' );
			$groupMembers		= array();
			$groupMembers 		= $modelGroup->getMembersId($event->contentid, true );

			$memberlist = implode(',',$groupMembers);
			$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid` IN ('{$memberlist}')";
			$this->db->setQuery($query);
			$puserlist=$this->db->loadObjectList();

			$eventdata['id'] = $event->id;
			$eventdata['title'] = $event->title;
			$eventdata['location'] = $event->location;
			$eventdata['groupid'] = $event->contentid;
			$format	= ($this->config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
			$eventdata['startdate'] = CTimeHelper::getFormattedTime($event->startdate, $format);
			$eventdata['enddate'] = CTimeHelper::getFormattedTime($event->enddate, $format);
			$eventdata['date'] = strtoupper(CEventHelper::formatStartDate($event, $this->config->get('eventdateformat')));

			if($this->config->get('user_avatar_storage') == 'file'){
					$p_url	= JURI::base();
			}else{
				$s3BucketPath	= $this->config->get('storages3bucket');
				if(!empty($s3BucketPath))
					$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
				else
					$p_url	= JURI::base();
			}

			$eventdata['avatar'] = ($event->avatar != '') ? $p_url. $event->avatar : JURI::base ().'components/com_community/assets/event_thumb.png';
			$eventdata['past'] = (strtotime($event->enddate)<time()) ? 1 : 0;
			$eventdata['ongoing'] = (strtotime($event->startdate)<=time() and strtotime($event->enddate)>time()) ? 1 : 0;
			$eventdata['confirmed']=$event->confirmedcount;

			/*$pushcontentdata['id']	= $group->id;
			$allowCreateEvent	= CGroupHelper::allowCreateEvent( $this->my->id , $group->id );
			if( $allowCreateEvent && $this->config->get('group_events') && $this->config->get('enableevents') && ($this->config->get('createevents') || COwnerHelper::isCommunityAdmin()) ){
				$pushcontentdata['createEvent']=1;
			}else{
				$pushcontentdata['createEvent']=0;
			}*/

			//change for id based push notification
			$pushOptions['detail']['content_data']			= $eventdata;
			$pushOptions['detail']['content_data']['type']		= 'event';
			$pushOptions = gzcompress(json_encode($pushOptions));

			$search 	= array('{event}','{group}');
			$replace 	= array($event->title,$group->name);
			$message 	= str_replace($search,$replace,JText::sprintf('COM_COMMUNITY_GROUP_NEW_EVENT_NOTIFICATION'));
			$obj = new stdClass();
			$obj->id 		= null;
			$obj->detail 	= $pushOptions;
			$obj->tocount  	= count($puserlist);
			$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
			if($obj->id){
				$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
				$this->jsonarray['pushNotificationData']['to'] 		= $memberlist;
				$this->jsonarray['pushNotificationData']['message'] = $message;
				$this->jsonarray['pushNotificationData']['type'] 	= 'event';
				$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_groups_create_event';
			}
		}

		/*if($isNew){
			CFactory::load( 'helpers' , 'event' );
			$handler			= CEventHelper::getHandler( $event );
			$event->contentid	= $handler->getContentId();
			$event->type		= $handler->getType();

			CFactory::load( 'helpers' , 'event' );
			$handler	= CEventHelper::getHandler( $event );

			// Activity stream purpose if the event is a public event
			// if( $handler->isPublic() )
			$actor			= $this->my->id;
			$target			= 0;
			$content		= '';
			$cid			= $event->id;
			$app			= 'events';
			$act			= $handler->getActivity( 'events.create' , $actor, $target , $content , $cid , $app );
			$url			= $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false , true , false );

			// Set activity group id if the event is in group
			$act->groupid	= ($event->type == 'group') ? $groupId : null;
			$act->eventid	= $event->id;
			$act->location	= $event->location;
			$act->comment_id	= $event->id;
			$act->comment_type	= 'events';
			$act->like_id	= $event->id;
			$act->like_type	= 'events';

			$params		=   new CParameter('');
			$cat_url	=   $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=display&categoryid=' . $event->catid , false , true , false );
			$params->set( 'action', 'events.create' );
			$params->set( 'event_url', $url );
			$params->set( 'event_category_url', $cat_url );

			// Add activity logging
			CFactory::load ( 'libraries', 'activities' );
			CActivityStream::add( $act, $params->toString() );

			//add user points
			CFactory::load( 'libraries' , 'userpoints' );
			CUserPoints::assignPoint('events.create');

			//add notification: New group event is added
			CFactory::load('helpers','event');
			if($event->type == CEventHelper::GROUP_TYPE && $event->contentid != 0){
				CFactory::load('libraries','notification');
				$group			=& JTable::getInstance( 'Group' , 'CTable' );
				$group->load( $event->contentid );

				$modelGroup			=& CFactory::getModel( 'groups' );
				$groupMembers		= array();
				$groupMembers 		= $modelGroup->getMembersId($event->contentid, true );
				$subject			= JText::sprintf('COM_COMMUNITY_GROUP_NEW_EVENT_NOTIFICATION', $this->my->getDisplayName(), $group->name );

				$params			= new CParameter( '' );
				$params->set( 'title' , $event->title );
				$params->set('group' , $group->name );
				$params->set('subject' , $subject );
				$params->set( 'url', 'index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id);

				CNotificationLibrary::add( 'etype_groups_create_event' , $this->my->id , $groupMembers ,$subject , '' , 'groups.event' , $params);
			}
		}*/
		return $event->id;
	}


	// call from save()
	private function _formatStartEndDate(&$postData){
		if( isset( $postData['starttime-ampm'] ) && $postData['starttime-ampm'] == 'PM' && $postData['starttime-hour'] != 12 ){
			$postData['starttime-hour'] = $postData['starttime-hour']+12;
		}

		if( isset( $postData['endtime-ampm'] ) && $postData['endtime-ampm'] == 'PM' && $postData['endtime-hour'] != 12 ){
			$postData['endtime-hour'] = $postData['endtime-hour']+12;
		}

		if( isset( $postData['starttime-ampm'] ) && $postData['starttime-ampm'] == 'AM' && $postData['starttime-hour'] == 12 ){
			$postData['starttime-hour'] = 0;
		}

		if( isset( $postData['endtime-ampm'] ) && $postData['endtime-ampm'] == 'AM' && $postData['endtime-hour'] == 12 ){
			$postData['endtime-hour'] = 0;
		}

		// When the All-day is selected, means the startdate & enddate should be same.
		// The time should have to start from 00:00:00 until 23:59:59
		if( array_key_exists('allday', $postData) && $postData['allday']== '1' ) {
			$postData['startdate']  =	$postData['startdate'] . ' 00:00:00';
			$postData['enddate']    =	$postData['enddate'] . ' 23:59:59';

		}else{
			$postData['startdate']  = $postData['startdate'] . ' ' . $postData['starttime-hour'].':'.$postData['starttime-min'].':00';
			$postData['enddate']  	= $postData['enddate'] . ' ' . $postData['endtime-hour'].':'.$postData['endtime-min'] . ':00';
		}

		unset($postData['startdatetime']);
		unset($postData['enddatetime']);
		unset($postData['starttime-hour']);
		unset($postData['starttime-min']);
		unset($postData['starttime-ampm']);
		unset($postData['endtime-hour']);
		unset($postData['endtime-min']);
		unset($postData['endtime-ampm']);
		unset($postData['privacy']);
	}


	private function _generateRepeatList($event, $postData = ''){
		$day	   = 0;
		$month     = 0;

		$eventList    = array();
		$limit	      = isset($postData['limit']) ? (int)$postData['limit'] : 0;
		$defaultLimit = 0;
		$count	      = 0;

		// Repeat option.
		switch ($event->repeat) {

			case 'daily':
				$day   = 1;
				$defaultLimit = COMMUNITY_EVENT_RECURRING_LIMIT_DAILY;
				break;

			case 'weekly':
				$day   = 7;
				$defaultLimit = COMMUNITY_EVENT_RECURRING_LIMIT_WEEKLY;
				break;

			case 'monthly':
				$month = 1;
				$defaultLimit = COMMUNITY_EVENT_RECURRING_LIMIT_MONTHLY;
				break;

			default :
				break;
		}

		$strstartdate = strtotime($event->startdate);
		$starttime	  = date('H', $strstartdate) .  ':' . date('i', $strstartdate) . ':' . date('s', $strstartdate);
		$strenddate	  = strtotime($event->enddate);
		$endtime	  = date('H', $strenddate) .  ':' . date('i', $strenddate) . ':' . date('s', $strenddate);

		$startdate    = date('Y-m-d', $strstartdate);
		$enddate      = date('Y-m-d', $strenddate);

		CFactory::load('helpers', 'time');

		$start  = strtotime($event->startdate);
		$end    = strtotime($event->enddate);

		// if repeatend is empty, generate dummy date to make it valid.
		if ($event->repeatend == '')
		{
			$repeatend = $event->enddate;
			// if both repeat end and limit never been set, use default limit.
			$limit = $limit == 0 ? $defaultLimit : $limit;
		} else {
			$repeatend = $event->repeatend;
		}

		$addDay   = 0;
		$addMonth = 0;
		// Generate list of event childs in given date.
		while ((CTimeHelper::timeIntervalDifference( $repeatend , $enddate) >=0 ) || ($count < $limit)) {

			// Add event child as new array item.
			$eventList[] = array('startdate'=> $startdate . ' ' . $starttime, 'enddate' => $enddate . ' ' . $endtime);

			// Compute the next event child.
			$addDay   += $day;
			$addMonth += $month;

			$startdate = date('Y-m-d', mktime(0, 0, 0, date('m',$start)+$addMonth, date('d',$start)+$addDay, date('Y',$start)));
			$enddate   = date('Y-m-d', mktime(0, 0, 0, date('m',$end)+$addMonth, date('d',$end)+$addDay, date('Y',$end)));

			$count++;

			// To avoid unnecessary loop.
			if ($count > $defaultLimit) {
				break;
			}
		}
                // SET repeat end date for empty data from import page
                if ($event->repeatend == '') {
                    $event->repeatend = $enddate;
                }

		return $eventList;
	}


	private function _saveMember($event){

		// Since this is storing event, we also need to store the creator / admin
		// into the events members table
		$member				= JTable::getInstance( 'EventMembers' , 'CTable' );
		$member->eventid	= $event->id;
		$member->memberid	= $event->creator;
		$member->created	= JFactory::getDate()->toSql();

		// Creator should always be 1 as approved as they are the creator.
		$member->status	= COMMUNITY_EVENT_STATUS_ATTEND;

		// @todo: Setup required permissions in the future
		$member->permission	= '1';

		$member->store();
	}



	private function _saveRepeatChild($event, $eventChild, $isNew = true, $postData = ''){
		$insertList = array();
		$updateList = array();
		$id			= 0;

		if ($isNew) {
			$insertList = $eventChild;
		} else {
			// event edit
			$id = $event->id;
			if (isset($postData['repeataction']) && $postData['repeataction'] == 'future'){

				$newList = $eventChild;
				array_shift($newList);

				$model	 = CFactory::getModel( 'Events' );
				$oldList = $model->getEventChilds($event->parent, array('id'=>$event->id));

				// start update old records.
				$this->db->setQuery('START TRANSACTION');
				$this->db->query();

				// Update existing event child.
				$published = $event->published;
				foreach ($oldList as $key => $value) {
					if (isset($newList[$key])) {
						$event->id		  = $value['id'];
						$event->startdate = $newList[$key]['startdate'];
						$event->enddate   = $newList[$key]['enddate'];
						$event->published = $value['published'] == 3 ? $value['published'] : $published;
						$event->store();
					} else {
						break;
					}
				}

				if (count($newList) > count($oldList)) {
					// insert new event child
					$insertList = array_slice($newList, count($oldList));
				} else if(count($oldList) > count($newList)) {
					// delete
					$deleteList = array_slice($oldList, count($newList));
					$id = array();
					foreach($deleteList as $value){
						$id[] = $value['id'];
					}
					$model->deleteExpiredEvent($id);
				}

				$this->db->setQuery('COMMIT');
				$this->db->query();
			}
		}

		// Insert new records.
		if (count($insertList) > 0){
			$this->db->setQuery('START TRANSACTION');
			$this->db->query();

			foreach ($insertList as $key => $value) {

				$event->id		  = 0;
				$event->startdate = $value['startdate'];
				$event->enddate   = $value['enddate'];
				$event->store();

				$id = $key == 0 && ($id == 0) ? $event->id : $id;

				// Update event member.
				$this->_saveMember($event);

				// Increment the member count
				$event->updateGuestStats();
				$event->store();

			}
			$event->id = $id;

			$this->db->setQuery('COMMIT');
			$this->db->query();
		}

	}


	private function _addActivityStream($event){
		CFactory::load ( 'libraries', 'events' );
        CEvents::addEventStream($event);
	}


	private function _addGroupNotification($event){
		CFactory::load ( 'libraries', 'events' );
		CEvents::addGroupNotification($event);
	}


	/**
	 * @uses to invite friends
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"invite",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"userID":"userID", // one or more userid
	 * 			"message":"message" // optional
	 * 		}
	 * 	}
	 */
	function invite(){
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$userID		= IJReq::getTaskData('userID');
		$message	= IJReq::getTaskData('message');

		CFactory::load('controllers','events');
		CFactory::load('helpers','owner');
		$event_controller_obj = new CommunityEventsController ( );

		$userID		=explode(',',$userID);

		$model  =& $event_controller_obj->getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		if( $this->my->id == 0 ){
			IJReq::setResponse(401,JText::_('COM_COMMUNITY_PERMISSION_DENIED_WARNING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$status		=   $event->getUserStatus($this->my->id);
		$allowed	=   array( COMMUNITY_EVENT_STATUS_INVITED , COMMUNITY_EVENT_STATUS_ATTEND , COMMUNITY_EVENT_STATUS_WONTATTEND , COMMUNITY_EVENT_STATUS_MAYBE );

		$accessAllowed	=   ( ( in_array( $status , $allowed ) ) && $status != COMMUNITY_EVENT_STATUS_BLOCKED ) ? true : false;
		$accessAllowed	=   COwnerHelper::isCommunityAdmin() ? true : $accessAllowed;

		if( !($accessAllowed && $event->allowinvite) && !$event->isAdmin( $this->my->id ) ){
			IJReq::setResponse(706,JText::_('COM_COMMUNITY_ACCESS_FORBIDDEN'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if( !empty($userID ) ){
			$invitedCount   =   0;
			$invited		=	array();
			foreach( $userID as $invitedUserId ){
				$date			    		=&  JFactory::getDate();
				$eventMember		    	=&  JTable::getInstance( 'EventMembers' , 'CTable' );
				$eventMember->eventid	    =   $event->id;
				$eventMember->memberid	    =   $invitedUserId;
				$eventMember->status	    =   COMMUNITY_EVENT_STATUS_INVITED;
				$eventMember->invited_by    =	$this->my->id;
				$eventMember->created       =	$date->toSql();
				$invited[]					=	$invitedUserId;

				$eventMember->store();
				$invitedCount++;
			}

			//now update the invited count in event
			$event->invitedcount = $event->invitedcount + $invitedCount;
			$event->store();

			// Send notification to the invited user.
			CFactory::load( 'libraries' , 'notification' );

			$params	    =	new CParameter( '' );
			$params->set('url' , CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ) );
			$params->set('eventTitle' , $event->title );
			$params->set('message' , $inviteMessage );
			CNotificationLibrary::add( 'etype_events_invite' , $this->my->id , $invited ,JText::sprintf('COM_COMMUNITY_EVENTS_JOIN_INVITE' , $event->title ) , '' , 'events.invite' , $params );

			//Send push notification
			// get user push notification params and user device token and device type
			$userIDS	= IJReq::getTaskData('userID');
			$memberslist = implode(',',$invited);
			$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid` IN ('{$memberslist}')";
			$this->db->setQuery($query);
			$puserlist=$this->db->loadObjectList();

			$eventdata['id'] = $event->id;
			$eventdata['title'] = $event->title;
			$eventdata['location'] = $event->location;
			$eventdata['groupid'] = $event->contentid;
			$format	= ($this->config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');
			$eventdata['startdate'] = CTimeHelper::getFormattedTime($event->startdate, $format);
			$eventdata['enddate'] = CTimeHelper::getFormattedTime($event->enddate, $format);
			$eventdata['date'] = strtoupper(CEventHelper::formatStartDate($event, $this->config->get('eventdateformat')));

			if($this->config->get('user_avatar_storage') == 'file'){
					$p_url	= JURI::base();
			}else{
				$s3BucketPath	= $this->config->get('storages3bucket');
				if(!empty($s3BucketPath))
					$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
				else
					$p_url	= JURI::base();
			}

			$eventdata['avatar'] = ($result->avatar != '') ? $p_url. $event->avatar : JURI::base ().'components/com_community/assets/event_thumb.png';
			$eventdata['past'] = (strtotime($event->enddate)<time()) ? 1 : 0;
			$eventdata['ongoing'] = (strtotime($event->startdate)<=time() and strtotime($event->enddate)>time()) ? 1 : 0;
			$eventdata['confirmed']=$event->confirmedcount;

			//change for id based push notification
			$pushOptions['detail']['content_data']=$eventdata;
			$pushOptions['detail']['content_data']['type']='event';
			$pushOptions = gzcompress(json_encode($pushOptions));

			$usr=$this->jomHelper->getUserDetail($this->IJUserID);
			$match = array('{actor}','{event}');
			$replace = array($usr->name,$event->title);
			$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_EVENTS_JOIN_INVITE'));
			$obj = new stdClass();
			$obj->id 		= null;
			$obj->detail 	= $pushOptions;
			$obj->tocount  	= count($puserlist);
			$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
			if($obj->id){
				$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
				$this->jsonarray['pushNotificationData']['to'] 		= $memberslist;
				$this->jsonarray['pushNotificationData']['message'] = $message;
				$this->jsonarray['pushNotificationData']['type'] 	= 'event';
				$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_events_invite';
			}

			$this->jsonarray['code']=200;
			return $this->jsonarray;
		}else{
			IJReq::setResponse(400,JText::_('COM_COMMUNITY_INVITE_NEED_AT_LEAST_1_FRIEND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
    }


    /**
	 * @uses to invite friends
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"friendList",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 */
	function friendList(){
		//$userId = JRequest::getInt( 'userid',0);
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$pageNO		= IJReq::getTaskData('pageNO', 0, 'int');
		$limit		= PAGE_MEMBER_LIMIT;

		if($this->IJUserID==0 or $uniqueID==0){
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($pageNO == 0 || $pageNO == 1){
		  	$startFrom = 0;
		}else{
			$startFrom = ($limit*($pageNO-1));
		}

		$query="SELECT COUNT(DISTINCT(a.{$this->db->quoteName('connect_to')})) AS id
				FROM {$this->db->quoteName('#__community_connection')} AS a
				INNER JOIN {$this->db->quoteName( '#__users' )} AS b ON a.{$this->db->quoteName('connect_from')} = {$this->db->Quote($this->IJUserID)}
			    	AND a.{$this->db->quoteName('connect_to')} = b.{$this->db->quoteName('id')}
			    	AND b.{$this->db->quoteName('id')} != {$this->db->Quote($this->IJUserID)}
			    	AND a.{$this->db->quoteName('status')} = {$this->db->Quote( '1' )}
					AND b.{$this->db->quoteName('block')} = {$this->db->Quote('0')}
				WHERE NOT EXISTS (	SELECT d.{$this->db->quoteName('blocked_userid')} as id
									FROM {$this->db->quoteName('#__community_blocklist')} AS d
									WHERE d.{$this->db->quoteName('userid')} = {$this->db->Quote($this->IJUserID)}
									AND d.{$this->db->quoteName('blocked_userid')} = a.{$this->db->quoteName('connect_to')})
				AND NOT EXISTS (	SELECT e.{$this->db->quoteName('memberid')} as id
									FROM {$this->db->quoteName('#__community_events_members')} AS e
									WHERE e.{$this->db->quoteName('eventid')} = {$this->db->Quote($uniqueID)}
									AND e.{$this->db->quoteName('memberid')} = a.{$this->db->quoteName('connect_to')}
									AND e.{$this->db->quoteName('invited_by')} = {$this->db->Quote($this->IJUserID)})
			    ORDER BY b.name ASC";
		$this->db->setQuery($query);
		$totalMembers = $this->db->loadResult();

		$query="SELECT DISTINCT(a.{$this->db->quoteName('connect_to')}) AS id
				FROM {$this->db->quoteName('#__community_connection')} AS a
				INNER JOIN {$this->db->quoteName( '#__users' )} AS b ON a.{$this->db->quoteName('connect_from')} = {$this->db->Quote($this->IJUserID)}
				    AND a.{$this->db->quoteName('connect_to')} = b.{$this->db->quoteName('id')}
				    AND b.{$this->db->quoteName('id')} != {$this->db->Quote($this->IJUserID)}
				    AND a.{$this->db->quoteName('status')} = {$this->db->Quote('1')}
					AND b.{$this->db->quoteName('block')} = {$this->db->Quote('0')}
				WHERE NOT EXISTS (	SELECT d.{$this->db->quoteName('blocked_userid')} as id
									FROM {$this->db->quoteName('#__community_blocklist')} AS d
									WHERE d.{$this->db->quoteName('userid')} = {$this->db->Quote($this->IJUserID)}
									AND d.{$this->db->quoteName('blocked_userid')} = a.{$this->db->quoteName('connect_to')})
				AND NOT EXISTS (	SELECT e.{$this->db->quoteName('memberid')} as id
									FROM {$this->db->quoteName('#__community_events_members')} AS e
									WHERE e.{$this->db->quoteName('eventid')} = {$this->db->Quote($uniqueID)}
									AND e.{$this->db->quoteName('memberid')} = a.{$this->db->quoteName('connect_to')}
									AND e.{$this->db->quoteName('invited_by')} = {$this->db->Quote( $this->IJUserID )})
			    ORDER BY b.name ASC
			    LIMIT {$startFrom},{$limit}";
		$this->db->setQuery( $query );
		$latestMembers = $this->db->loadObjectList();
		$friendsModel =& CFactory::getModel('friends');
		$frids=$friendsModel->getFriendIds($this->IJUserID);

		$query="SELECT a.memberid
				FROM {$this->db->quoteName( '#__community_events_members' )} AS a
				INNER JOIN {$this->db->quoteName( '#__community_events' )} AS b ON b.{$this->db->quoteName('id')} = a.{$this->db->quoteName('eventid')}
					AND b.{$this->db->quoteName('published')} = {$this->db->Quote(1)}
				WHERE a.{$this->db->quoteName('eventid')} = {$this->db->Quote( $uniqueID )}
				AND a.{$this->db->quoteName('status')} = {$this->db->Quote( COMMUNITY_EVENT_STATUS_INVITED )}
				AND b.{$this->db->quoteName('enddate')} >= NOW()";
       	$this->db->setQuery($query);
       	$connection=$this->db->loadColumn();

		$inc = 0;

		$sqlquery="	SELECT memberid
					FROM #__community_events_members
					WHERE eventid=".$uniqueID;
		$this->db->setQuery($sqlquery);
		$user_list = $this->db->loadColumn();

		foreach($latestMembers as $key=>$member){
			if(in_array($member->id,$user_list)){
				$totalMembers--;
				continue;
			}

			CFactory::setActiveProfile();
			$usr=$this->jomHelper->getUserDetail($member->id);

			$this->jsonarray['member'][$key]['user_id'] = $usr->id;
			$this->jsonarray['member'][$key]['user_name'] = $usr->name;
			$this->jsonarray['member'][$key]['user_avatar'] = $usr->avatar;
		}

		if($totalMembers>0){
			$this->jsonarray['code']		= 200;
			$this->jsonarray['total'] = $totalMembers;
			$this->jsonarray['pageLimit']	= PAGE_MEMBER_LIMIT;
    	}else{
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
    	}
		return $this->jsonarray;
	}


	/**
	 * @uses to fetch wall list
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"wall",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 */
	/*function wall(){
		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$pageNO		= IJReq::getTaskData('pageNO', 0, 'int');
		$limit		= PAGE_ACTIVITIES_LIMIT;

		if($pageNO == '1'  || $pageNO == '0'){
		  	$startFrom = 0;
		}else{
			$startFrom = ($limit*($pageNO-1));
		}

		$event		=&	JTable::getInstance( 'Event' , 'CTable' );
		$event->load($uniqueID);
		CFactory::load( 'libraries' , 'activities' );
		$act	= new CActivities();

		$options = array(	'actor' => '0',
							'target' => '0',
							'date' => '',
							'maxList' => MAXIMUM_WALL+1,
							'app' => array(	'events.wall',
											'event.attend'),
							'cid' => '',
							'groupid' => '',
							'eventid' => $uniqueID,
    						'exclusions' => '',
    						'displayArchived' => '1');

		$htmldata = $this->_getData( $options );
		if (preg_match('[^2.]', IJ_JOMSOCIAL_VERSION, $matches)){
 		 	$htmldata = $htmldata->data;
		}

		$inc = 0;
		foreach ($htmldata as $key=>$value){
			$titletag = isset($value->title) ? $value->title : "";
			if(isset($value->type) && $value->type == 'title'){
				continue;
			}else{
				$temp_htmldata[]=$value;
			}
		}
		$htmldata = $temp_htmldata;

		if($startFrom+$limit>= count($htmldata)){
			$cout = count($htmldata);
		}else{
			$cout = $startFrom+$limit;
		}

		if(count($htmldata)>0){
			$this->jsonarray['code']		= 200;
			$this->jsonarray['total']		= count($htmldata);
			$this->jsonarray['pageLimit']	= $limit;
		}else{
			IJReq::setResponseCode(204);
			return false;
		}

		for($m=$startFrom;$m<$cout;$m++){
			$html =$htmldata[$m];
		  	$titletag = isset($html->title) ? $html->title : "";
			if(isset($html->type) && $html->type == 'title'){
				$date  = '';
				$time = '';
				$id = '';
				$content = '';
				$actor = '';
				$likeAllowed = $commentAllowed = 0;
				continue;
			}else{
				$date  = $html->createdDate;
				$time  = $html->created;
				$id	   = $html->id;
		  		$content = $html->content;
				$actor = $html->actor;
				$likeAllowed = $html->likeAllowed=="" ? 0 : 1;
				$commentAllowed = $html->commentAllowed=="" ? 0 : 1;
			}

			$str=strip_tags($content,"<img>");

		    //For jreview comment display
			if($html->app == "wall"){
				$text = explode("`", $str);
			}

			if($html->type=="title"){
				$this->jsonarray['title'][$inc] = strip_tags($titletag);
			}else{
				$this->jsonarray['update'][$inc]['id'] = $id;

				$usr = $this->jomHelper->getUserDetail($value->memberid); // get user detail
				$this->jsonarray['update'][$inc]['user_detail']['user_id'] = $usr->id;
				$this->jsonarray['update'][$inc]['user_detail']['user_name'] = $usr->name;
				$this->jsonarray['update'][$inc]['user_detail']['user_avatar'] = $usr->avatar;
				$this->jsonarray['update'][$inc]['user_detail']['user_profile'] = $usr->profile;

				$this->jsonarray['update'][$inc]['titletag'] =  str_replace("&#9658;","►",str_replace("&quot;","\"",(strip_tags($titletag))));
				$this->jsonarray['update'][$inc]['date'] = $time;
				$this->jsonarray['update'][$inc]['likeAllowed'] = $likeAllowed;
				$this->jsonarray['update'][$inc]['commentAllowed'] = $commentAllowed;
				$this->jsonarray['update'][$inc]['likeCount'] = intval($html->likeCount);
				$this->jsonarray['update'][$inc]['commentCount'] = intval($html->commentCount);
				$this->jsonarray['update'][$inc]['liked'] = ($html->userLiked==1) ? 1 : 0 ;
				$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->IJUserID==$actor OR COwnerHelper::isCommunityAdmin($this->IJUserID ) OR $event->isAdmin($this->IJUserID	));

				$query="SELECT comment_type,like_type
						FROM #__community_activities
						WHERE id={$id}";
				$this->db->setQuery($query);
				$extra=$this->db->loadObject();

				$this->jsonarray['update'][$inc]['liketype'] = $extra->like_type;
				$this->jsonarray['update'][$inc]['commenttype'] = $extra->comment_type;
			}
			$inc++;
		}
		return $this->jsonarray;
	}*/


	/*private function _getData( $options ){
		$dispatcher =& CDispatcher::getInstanceStatic();
		$observers =& $dispatcher->getObservers();
		$plgObj = false;
		for ($i = 0; $i < count($observers); $i++)
		{
			if ($observers[$i] instanceof plgCommunityWordfilter)
			{
				$plgObj = $observers[$i];
			}
		}

		// Default params
		$default = array(
			'actor' =>0,
			'target' => 0,
			'date' => null,
			'app' => null,
			'cid' => null, // don't filter with cid
			'groupid' => null,
			'eventid' => null,
			'maxList' => 20 ,
			'type' => '' ,
			'exclusions' => null ,
			'displayArchived' => false
		);
		$options = array_merge($default, $options);
		extract($options);

		CFactory::load('libraries', 'mapping');
		CFactory::load('libraries', 'wall');
		CFactory::load('libraries', 'groups');
		CFactory::load('libraries', 'events');
		CFactory::load('helpers', 'friends');

		$activities = CFactory::getModel('activities');
		$appModel	= CFactory::getModel('apps');
		$html 		= '';
		$numLines 	= 0;
		$actorId	= 0;
		$htmlData 	= array();

		//Get blocked list
		$model		   = CFactory::getModel('block');
		$blockLists    = $model->getBanList($this->my->id);
		$blockedUserId = array();

		foreach($blockLists as $blocklist){
		    $blockedUserId[] = $blocklist->blocked_userid;
        }

        // Exclude banned userid
        if( !empty($target) && !empty($blockedUserId) ){
            $target = array_diff($target,$blockedUserId);
		}

		if( !empty($app)){
			$rows = $activities->getAppActivities( $options );
		}else{
			$rows = $activities->getActivities( $actor, $target, $date, $maxList , $this->config->get('respectactivityprivacy') , $exclusions , $displayArchived );
		}
		$day = -1;


		// If exclusion is set, we need to remove activities that arrives
		// after the exclusion list is set.
		// Inject additional properties for processing
		for($i = 0; $i < count($rows); $i++){
			$row			=& $rows[$i];

			// A 'used' activities = activities that has been aggregated
			$row->used 		= false;

			// If the id is larger than any of the exclusion list,
			// we simply hide it
			if(isset($exclusion) && $exclusion > 0 && $row->id > $exclusions){
				$row->used 		= true;
			}
		}

		unset($row);


		$dayinterval 	= ACTIVITY_INTERVAL_DAY;
		$lastTitle 		= '';

		// Experimental Viewer Sensitive Profile Status
		$viewer	= CFactory::getUser()->id;
		$view	= JRequest::getCmd('view');

		for($i = 0; $i < count($rows) && (count($htmlData) <= $maxList ); $i++){
			$row		= $rows[$i];
			$oRow		=& $rows[$i];	// The original object

			// store aggregated activities
			$oRow->activities = array();

			if(!$row->used && count($htmlData) <= $maxList ){
				$oRow	=& $rows[$i];

				if(!isset($row->used)){
					$row->used = false;
				}

				if($day != $row->getDayDiff() ){
					$act		= new stdClass();
					$act->type	= 'content';
					$day		= $row->getDayDiff();

					if($day == 0){
						$act->title = JText::_('TODAY');
					}else if($day == 1){
						$act->title = JText::_('COM_COMMUNITY_ACTIVITIES_YESTERDAY');
					}else if($day < 7){
						$act->title = JText::sprintf('COM_COMMUNITY_ACTIVITIES_DAYS_AGO', $day);
					}else if(($day >= 7) && ($day < 30)){
						$dayinterval = ACTIVITY_INTERVAL_WEEK;
						$act->title = (intval($day/$dayinterval) == 1 ? JText::_('COM_COMMUNITY_ACTIVITIES_WEEK_AGO') : JText::sprintf('COM_COMMUNITY_ACTIVITIES_WEEK_AGO_MANY', intval($day/$dayinterval)));
					}else if(($day >= 30)){
						$dayinterval = ACTIVITY_INTERVAL_MONTH;
						$act->title = (intval($day/$dayinterval) == 1 ? JText::_('COM_COMMUNITY_ACTIVITIES_MONTH_AGO') : JText::sprintf('COM_COMMUNITY_ACTIVITIES_MONTH_AGO_MANY', intval($day/$dayinterval)));
					}

					// set to a new 'title' type if this new one has a new title
					// only add if this is a new title
					if($act->title != $lastTitle){
						$lastTitle 	= $act->title;
						$act->type 	= 'title';
						$htmlData[] = $act;
					}
				}

				$act = new stdClass();
				$act->type = 'content';

				// Set to compact view if necessary
				// This method is a bit crude, but we have no other reliable data
				// to choose which will go to compact view

				// Attend an event
				$act->compactView		= !( strpos( $oRow->params , 'action=events.attendence.attend') === FALSE );
				$act->compactView		=  $act->compactView || !( strpos( $oRow->params , '"action":"events.attendence.attend"') === FALSE );

				// Create an event
				$act->compactView		= $act->compactView || !(strpos( $oRow->params , 'action=events.create') === FALSE);
				$act->compactView		= $act->compactView || !(strpos( $oRow->params , '"action":"events.create"') === FALSE);

				// Update/join group
				$act->compactView		= $act->compactView || ($oRow->app == 'groups' && empty($oRow->content));

				// Add as friend
				$act->compactView		= $act->compactView || ($oRow->app == 'friends');

				// Add/Remove app. This is tricky since string is hard-coded
				// and no other info is available
				$act->compactView		= $act->compactView || ($oRow->title == JText::_('COM_COMMUNITY_ACTIVITIES_APPLICATIONS_ADDED') );

				// Feature a user
				$act->compactView		= $act->compactView || ($oRow->app == 'users');

				$title 	= $row->title;
				$app 	= $row->app;
				$cid 	= $row->cid;
				$actor 	= $row->actor;

				//Check for event or group title if exists
				if($row->eventid){
					$eventModel	= CFactory::getModel('events');
					$act->appTitle  = $eventModel->getTitle($row->eventid);
				}else if($row->groupid){
					$groupModel	= CFactory::getModel('groups');
					$act->appTitle  = $groupModel->getGroupName($row->groupid);
				}

				for($j = $i; ($j < count($rows)) && ($row->getDayDiff() == $day); $j++){
					$row = $rows[$j];
					// we aggregate stream that has the same content on the same day.
					// we should not however aggregate content that does not support
					// multiple content. How do we detect? easy, they don't have
					// {multiple} in the title string

					// However, if the activity is from the same user, we only want
					// to show the laste acitivity
					if( ($row->getDayDiff() == $day)
						&& ($row->title  == $title)
						&& ($app == $row->app)
						&& ($cid == $row->cid )
						&& (( JString::strpos($row->title, '{/multiple}') !== FALSE ) || ($row->actor == $actor ))
					){
						// @rule: If an exclusion is added, we need to fetch activities without these items.
						// Aggregated activities should also be excluded.
						$row->used 			= true;
						$oRow->activities[] = $row;
					}
				}

				$app	= !empty($oRow->app) ? $this->_appLink($oRow->app, $oRow->actor, $oRow->target,$oRow->title) : '';
				$oRow->title	= CString::str_ireplace('{app}', $app, $oRow->title);
				$favicon = '';

				// this should not really be empty
				if(!empty($oRow->app)){
					// Favicon override with group image for known group stream data
					//if(in_array($oRow->app, CGroups::getStreamAppCode())){
					if( $oRow->groupid ){
						// check if the image icon exist in template folder
						$favicon = JURI::root(). 'components/com_community/assets/favicon/groups.png';
						if ( JFile::exists(JPATH_ROOT . '/components/com_community/templates/'. $config->get('template') . '/images/favicon/groups.png') ){
							$favicon = JURI::root(). 'components/com_community/templates/'.$config->get('template').'/images/favicon/groups.png';
						}

					}

					// Favicon override with event image for known event stream data
					// This would override group favicon
					if( $oRow->eventid ){
						// check if the image icon exist in template folder
						$favicon = JURI::root(). 'components/com_community/assets/favicon/events.png';
						if ( JFile::exists(JPATH_ROOT . '/components/com_community/templates/'. $this->config->get('template') . '/images/favicon/groups.png') ){
							$favicon = JURI::root(). 'components/com_community/templates/'.$this->config->get('template').'/images/favicon/events.png';
						}
					}

					// If it is not group or event stream, use normal favicon search
					if( !($oRow->groupid || $oRow->eventid) ){
						// check if the image icon exist in template folder
						if ( JFile::exists(JPATH_ROOT . '/components/com_community/templates/'. $this->config->get('template') . '/images/favicon/'. $oRow->app.'.png') ){
							$favicon = JURI::root(). 'components/com_community/templates/'.$this->config->get('template').'/images/favicon/'.$oRow->app.'.png';
						}else{
							// check if the image icon exist in asset folder
							if ( JFile::exists(JPATH_ROOT . '/components/com_community/assets/favicon/'. $oRow->app.'.png') ){
								$favicon = JURI::root(). 'components/com_community/assets/favicon/'.$oRow->app.'.png';
							}elseif ( JFile::exists(CPluginHelper::getPluginPath('community',$oRow->app) .'/'. $oRow->app . '/favicon.png') ){
								$favicon = JURI::root(). CPluginHelper::getPluginURI('community',$oRow->app) . '/' .$oRow->app.'/favicon.png';
							}else{
								$favicon = JURI::root(). 'components/com_community/assets/favicon/default.png';
							}
						}
					}
				}else{
				    $favicon = JURI::root(). 'components/com_community/assets/favicon/default.png';
				}

				$act->favicon = $favicon;
				$target = $this->_targetLink($oRow->target, true );
				$oRow->title	= CString::str_ireplace('{target}', $target, $oRow->title);

				if(count($oRow->activities) > 1){
					// multiple
					$actorsLink = '';
					foreach( $oRow->activities as $actor ){
						if(empty($actorsLink))
							$actorsLink = $this->_actorLink(intval($actor->actor));
						else {
							// only add if this actor is NOT already linked
							$alink = $this->_actorLink(intval($actor->actor));
							$pos = strpos($actorsLink, $alink);
							if ($pos === false) {
								$actorsLink .= ', '.$alink;
							}
						}
					}
					$actorLink = $this->_actorLink(intval($oRow->actor));

					$count = count($oRow->activities);

					$oRow->title 	= preg_replace('/\{single\}(.*?)\{\/single\}/i', '', $oRow->title);
					$search  		= array('{multiple}','{/multiple}');

					$oRow->title	= CString::str_ireplace($search, '', $oRow->title);

					//Joomla 1.6 CString::str_ireplace issue of not replacing correctly strings with backslashes
					$oRow->title = str_ireplace($search, '', $oRow->title);

					$oRow->title	= CString::str_ireplace('{actors}'	, $actorsLink, $oRow->title);
					$oRow->title	= CString::str_ireplace('{actor}'	, $actorLink, $oRow->title);
					$oRow->title	= CString::str_ireplace('{count}'	, $count, $oRow->title);
				} else {
					// single
					$actorLink = $this->_actorLink(intval($oRow->actor));

					$oRow->title = preg_replace('/\{multiple\}(.*)\{\/multiple\}/i', '', $oRow->title);
					$search  = array('{single}','{/single}');
					$oRow->title	= CString::str_ireplace($search, '', $oRow->title);
					$oRow->title	= CString::str_ireplace('{actor}', $actorLink, $oRow->title);
				}

				// If the param contains any data, replace it with the content
				preg_match_all("/{(.*?)}/", $oRow->title, $matches, PREG_SET_ORDER);
				if(!empty( $matches )){
					$params = new CParameter( $oRow->params );
					foreach ($matches as $val){
						$replaceWith = $params->get($val[1], null);

						//if the replacement start with 'index.php', we can CRoute it
						if( strpos($replaceWith, 'index.php') === 0){
							$replaceWith = CRoute::_($replaceWith);
						}

						if( !is_null( $replaceWith ) ){
							$oRow->title	= CString::str_ireplace($val[0], $replaceWith, $oRow->title);
						}
					}
				}

				$act1	= new CActivities();

				// Format the title
				$oRow->title = ($plgObj) ? $plgObj->_censor($oRow->title) : $oRow->title;
				$oRow->title = $this->_formatTitle($oRow);
				$act->id 		= $oRow->id;
				$act->title 	= $oRow->title;
				$act->actor 	= $oRow->actor;
				$act->target 	= $oRow->target;
				$act->content	= $act1->getActivityContent( $oRow );

				$timeFormat		= $this->config->get( 'activitiestimeformat' );
				$dayFormat		= $this->config->get( 'activitiesdayformat' );
				$date			= CTimeHelper::getDate($oRow->created);

				$createdTime = '';
				if($this->config->get('activitydateformat') == COMMUNITY_DATE_FIXED){
					$createdTime 	= $date->toFormat($dayinterval == ACTIVITY_INTERVAL_DAY ? $timeFormat : $dayFormat ,true);
				}else{
					$createdTime	= CTimeHelper::timeLapse($date);
				}
				$act->created 			= $createdTime;
				$act->createdDate 		= (C_JOOMLA_15==1)?$date->toFormat(JText::_('DATE_FORMAT_LC2')):$date->Format(JText::_('DATE_FORMAT_LC2'));
				$act->app 				= $oRow->app;
				$act->eventid			= $oRow->eventid;
				$act->groupid			= $oRow->groupid;
				$act->group_access		= $oRow->group_access;
				$act->event_access		= $oRow->event_access;
				$act->location			= $oRow->getLocation();
				$act->commentCount		= $oRow->getCommentCount();
				$act->commentAllowed	= $oRow->allowComment();
				$act->commentLast		= $oRow->getLastComment();
				$act->likeCount			= $oRow->getLikeCount();
				$act->likeAllowed		= $oRow->allowLike();
				$act->isFriend			= $this->my->isFriendWith( $act->actor );
				$act->isMyGroup			= $this->my->isInGroup($oRow->groupid);
				$act->isMyEvent			= $this->my->isInEvent($oRow->eventid);
				$act->userLiked			= $oRow->userLiked($my->id);

				$htmlData[] = $act;
			}
		}

		$objActivity				= new stdClass();
		$objActivity->data			= $htmlData;

		return $objActivity;
	}*/



	/**
	 * @uses to invite friends
	 * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"addWall",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"message":"message",
	 * 			"comment":"comment" // boolean 0/1, if 1 comment will be add.
	 * 		}
	 * 	}
	 */
	function addWall(){
		$message	= IJReq::getTaskData('message');

		$audiofileupload = $this->jomHelper->uploadAudioFile();
		if($audiofileupload){
			$message = $message.$audiofileupload['voicetext'];
		}
		$uniqueID 	= IJReq::getTaskData('uniqueID', 0,'int');
		$comment	= IJReq::getTaskData('comment',0,'bool');

		if($comment===1){
			if($this->addComment($uniqueID,$message)){
				$this->jsonarray['code']=200;
				return $this->jsonarray;
			}else{
				return false;
			}

		}

		if (!COwnerHelper::isRegisteredUser()){
			IJReq::setResponse(401);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueID );

		CFactory::load('libraries', 'activities');
		CFactory::load('libraries', 'userpoints');
		CFactory::load('helpers', 'linkgenerator');
		CFactory::load( 'libraries' , 'notification' );

		//@rule: In case someone bypasses the status in the html, we enforce the character limit.
		if( JString::strlen( $message ) > $this->config->get('statusmaxchar') ){
			$message	= JString::substr( $message , 0 , $this->config->get('statusmaxchar') );
		}

		$message	= JString::trim($message);
		$rawMessage	= $message;
		// @rule: Autolink hyperlinks
		$message	= CLinkGeneratorHelper::replaceURL( $message );
		// @rule: Autolink to users profile when message contains @username
		$message	= CLinkGeneratorHelper::replaceAliasURL( $message );
		$emailMessage	= CLinkGeneratorHelper::replaceAliasURL( $rawMessage, true );

		// @rule: Spam checks
		if($this->config->get( 'antispam_akismet_status')){
			CFactory::load( 'libraries' , 'spamfilter' );

			$filter	= CSpamFilter::getFilter();
			$filter->setAuthor( $this->my->getDisplayName() );
			$filter->setMessage( $message );
			$filter->setEmail( $this->my->email );
			$filter->setURL( CRoute::_('index.php?option=com_community&view=profile&userid=' . $this->my->id ) );
			$filter->setType( 'message' );
			$filter->setIP( $_SERVER['REMOTE_ADDR'] );

			if( $filter->isSpam() ){
				IJReq::setResponse(705,JText::_('COM_COMMUNITY_STATUS_MARKED_SPAM'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		//respect wall setting before adding activity
		CFactory::load('helpers' , 'friends' );
		CFactory::load('helpers', 'owner');
		CFactory::load('models', 'status');

		if(!empty($message)){
			//push to activity stream
			$privacyParams	= $this->my->getParams();
			$act = new stdClass();
			$act->cmd			= 'events.wall';
			$act->actor			= $this->IJUserID;
			$act->target		= 0;
			$act->title			= $message;
			$act->content		= '';
			$act->app			= 'events.wall';
			$act->cid			= $event->catid;
			$act->groupid		= 0;
			$act->eventid		= $uniqueID;
			$act->access		= $privacy;
			$act->comment_id	= CActivities::COMMENT_SELF;
			$act->comment_type	= 'events.wall';
			$act->like_id		= CActivities::LIKE_SELF;
			$act->like_type		= 'events.wall';

			CActivityStream::add($act);
			CUserPoints::assignPoint('events.wall');
		}
		$this->jsonarray['code']=200;
		return $this->jsonarray;
	}


	/**
	 * @uses remove wall
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"event",
 	 *		"extTask":"removeWall",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // wall id or comment id
	 * 			"comment":"0/1" 0: if posting wall, 1: if postin comment.
	 * 		}
	 * 	}
	 */
	function removeWall(){
		$uniqueID  = IJReq::getTaskData('uniqueID',0,'int');
		$comment  = IJReq::getTaskData('comment',0,'bool');
		$app='events.wall';

		if($comment==1){
			if($this->removeComment($uniqueID)){
				$this->jsonarray['code']=200;
				return $this->jsonarray;
			}else{
				return false;
			}
		}

		CFactory::load ( 'models', 'activities' );
		$model = new CommunityModelActivities();
		$filter	    =	JFilterInput::getInstance();
		$app	    =	$filter->clean( $app, 'string' );
		$activityId =	$filter->clean( $uniqueID, 'int' );

		CFactory::load( 'helpers' , 'owner' );

		$model->deleteActivity( $app, $uniqueID );
		$this->jsonarray['code']=200;
		return $this->jsonarray;
	}


	// this function is used to delete wall comment. Call by remove function
	private function removeComment($wallid){
		$filter = JFilterInput::getInstance();
		$wallid = $filter->clean($wallid, 'int');

		//CFactory::load('helper', 'owner');
		$table =& JTable::getInstance('Wall', 'CTable');
		$table->load($wallid);
		if($table->delete()){
			return true;
		}else{
			IJReq::setResponse(400);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}
}