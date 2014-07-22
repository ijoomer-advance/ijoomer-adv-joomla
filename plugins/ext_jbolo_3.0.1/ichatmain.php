<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : JBOLO_1.4 (compatible with joomla 2.5)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport( 'joomla.application.component.helper' );
jimport( 'joomla.filesystem.folder' );
class ichatmain
{
	private $IJUserID;
	private $mainframe; 
	private $db;
	private $my;
	private $jsonarray=array();
	protected $options;
	
	function __construct($options = null)
    {
		$this->mainframe	=	& JFactory::getApplication();
		$this->db			=	& JFactory::getDBO(); // set database object
		$this->IJUserID		=	$this->mainframe->getUserState('com_ijoomeradv.IJUserID', 0); //get login user id
		$this->my			=	JFactory::getUser($this->IJUserID);
		
    	$this->options = array(
			'script_url' => '',//$this->get_full_url().'/',
			'upload_dir' => JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'uploads'.DS,//dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
			'upload_url' => 'components'.DS.'com_jbolo'.DS.'uploads'.DS,// $this->get_full_url().'/files/',
			'user_dirs' => false,
			'mkdir_mode' => 0755,
			'param_name' => 'files',
			// Set the following option to 'POST', if your server does not support
			// DELETE requests. This is a parameter sent to the client:
			'delete_type' => 'DELETE',
			'access_control_allow_origin' => '*',
			'access_control_allow_credentials' => false,
			'access_control_allow_methods' => array(
				'OPTIONS',
				'HEAD',
				'GET',
				'POST',
				'PUT',
				'PATCH',
				'DELETE'
			),
			'access_control_allow_headers' => array(
				'Content-Type',
				'Content-Range',
				'Content-Disposition'
			),
			// Enable to provide file downloads via GET requests to the PHP script:
			'download_via_php' => false,
			// Defines which files can be displayed inline when downloaded:
			'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
			// Defines which files (based on their names) are accepted for upload:
			'accept_file_types' => '/.+$/i',
			// The php.ini settings upload_max_filesize and post_max_size
			// take precedence over the following max_file_size setting:
			'max_file_size' => null,
			'min_file_size' => 1,
			// The maximum number of files for the upload directory:
			'max_number_of_files' => null,
			// Image resolution restrictions:
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			// Set the following option to false to enable resumable uploads:
			'discard_aborted_uploads' => true,
			// Set to true to rotate images based on EXIF meta data, if available:
			'orient_image' => false,
			'image_versions' => array(
				// Uncomment the following version to restrict the size of
				// uploaded images:
				/*
				'' => array(
					'max_width' => 1920,
					'max_height' => 1200,
					'jpeg_quality' => 95
				),
				*/
				// Uncomment the following to create medium sized images:
				/*
				'medium' => array(
					'max_width' => 800,
					'max_height' => 600,
					'jpeg_quality' => 80
				),
				*/
				'thumbnail' => array(
					'max_width' => 80,
					'max_height' => 80
				)
			)
		);
		if ($options) {
			$this->options = array_merge($this->options, $options);
		}
    }
	 /**
     * @uses to fetch all online users detail,status,status mrssage and their messages
     * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"polling",
	 * 		"taskData":{
	 * 		}
	 * 	} 
     */
	function polling()
	{
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		$uid = $this->IJUserID;
		$user=JFactory::getUser($uid);
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$this->jsonarray['code'] = 200;
		$usersHelper=new usersHelper();
		$data=$usersHelper->getOnlineUsersInfo($uid);
		foreach($data as $dataK=>$dataV){
			if($dataV->uid==$uid){
				unset($data[$dataK]);
			}
		}
		//
		$params=JComponentHelper::getParams('com_jbolo');
		foreach($data as $ukey=>$uval){
			$this->jsonarray['users'][$ukey]['userId'] 	  = $uval->uid;
			if($params->get('chatusertitle')){
				$userName = $uval->username;
			}else{
				$userName = $uval->name;
			}
			$this->jsonarray['users'][$ukey]['userName']  = $userName;
			$this->jsonarray['users'][$ukey]['status']    = $uval->sts;
			$this->jsonarray['users'][$ukey]['statusMsg'] = $uval->stsm;
			$this->jsonarray['users'][$ukey]['avtr']      = $uval->avtr;
		}
		//$this->jsonarray['users']=$data;
		//
		$nodesHelper=new nodesHelper();
		$nodes=$nodesHelper->getActiveChatNodes($uid);
		$messages=array(); //to get msg data

		//for each node get participants and unread messages for this user
		for($nc=0;$nc<count($nodes);$nc++)
		{
			$messages=$this->getUnreadMessages($nodes[$nc]->nid,$uid);
			if(count($messages))
			{
				$participants=$nodesHelper->getNodeParticipants($nodes[$nc]->nid,$uid);
				$nodes[$nc]->wt=$nodesHelper->getNodeTitle($nodes[$nc]->nid,$uid,$nodes[$nc]->ctyp);
				$nodes[$nc]->ns=$nodesHelper->getNodeStatus($nodes[$nc]->nid,$uid,$nodes[$nc]->ctyp);
				for($k=0;$k<count($messages);$k++)
				{
					$this->jsonarray['messages'][$k]['msgID'] = $messages[$k]->mid;
					if($messages[$k]->msgtype == 'file'){
						if(strstr($messages[$k]->msg, "You have sent this file")){
							$this->jsonarray['messages'][$k]['fromID'] = 0;
							$this->jsonarray['messages'][$k]['fromName'] = $user->name;
						}else{
							$this->jsonarray['messages'][$k]['fromID'] = $messages[$k]->fid;
							$this->jsonarray['messages'][$k]['fromName'] = JFactory::getUser($messages[$k]->fid)->name;
						}
					}else if($messages[$k]->fid==0){
						$this->jsonarray['messages'][$k]['fromID'] = -1;
						$this->jsonarray['messages'][$k]['fromName'] = '';
					}else{
						$this->jsonarray['messages'][$k]['fromID'] = $messages[$k]->fid;
						$this->jsonarray['messages'][$k]['fromName'] = JFactory::getUser($messages[$k]->fid)->name;
					}
					$this->jsonarray['messages'][$k]['message'] = $messages[$k]->msg;
					$this->jsonarray['messages'][$k]['timestamp'] = strtotime($messages[$k]->ts);
					$this->jsonarray['messages'][$k]['msgType'] = $messages[$k]->msgtype;
					$this->jsonarray['messages'][$k]['nodeID'] = $nodes[$nc]->nid;
					$nodeType=$nodesHelper->getNodeType($nodes[$nc]->nid);
					$this->jsonarray['messages'][$k]['Type'] = $nodeType;
					foreach($data as $key=>$value){
						if($value->uid==$messages[$k]->fid){
							$this->jsonarray['messages'][$k]['avtr'] = $value->avtr;
						}
					}
					$msg_id=$messages[$k]->mid;
					$messages[$k]->ts=JFactory::getDate($messages[$k]->ts)->format(JText::_('COM_JBOLO_SENT_AT_FORMAT'));
					$db=JFactory::getDBO();
					$query="UPDATE #__jbolo_chat_msgs_xref AS x SET x.read=1
							WHERE x.read=0
							AND x.to_user_id=".$uid.
							" AND x.msg_id=".$msg_id;
					$this->db->setQuery($query);
					if(!$this->db->execute()){
						IJReq::setResponse(500);
						IJReq::setResponseMessage(JText::_('COM_JBOLO_DB_ERROR'));
						return false;
					}
				}

				//add msgs to session for particular node
				if(isset($_SESSION['jbolo']['nodes']))//if nodes array is set
				{
					$node_ids= array();
					$nodecount=count($_SESSION['jbolo']['nodes']);

					//get all node ids for nodes which are present in session
					for($d=0;$d<$nodecount;$d++)
					{
						if(isset($_SESSION['jbolo']['nodes'][$d])){
							$node_ids[$d]=$_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid;
						}
					}

					//if current node is not in session, add nodeinfo & particpants in session
					if(!in_array($nodes[$nc]->nid,$node_ids))
					{
						if($nodecount)
						{
							//if node data not in session, push new nodedata at end	of array
							//push nodeinfo
							$_SESSION['jbolo']['nodes'][$nodecount]['nodeinfo']=$nodes[$nc];
							//push node participants
							$_SESSION['jbolo']['nodes'][$nodecount]['participants']=$participants['participants'];
						}
						else
						{
							//if no node is present in session
							//add a new node in session
							//push nodeinfo
							$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$nodes[$nc];
							//push node participants
							$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
						}
					}

					//loop through all nodes
					for($k=0;$k<count($_SESSION['jbolo']['nodes']);$k++)
					{
						//if node found
						if($_SESSION['jbolo']['nodes'][$k]['nodeinfo']->nid==$nodes[$nc]->nid)
						{
							//this is important
							//update node participants
							$_SESSION['jbolo']['nodes'][$k]['participants']=$participants['participants'];

							$mcnt=0;//initialize mesasge count for node found to 0
							//check if the node found has messages stored in session
							if(isset($_SESSION['jbolo']['nodes'][$k]['messages']))
							{
								//if yes count msgs
								$mcnt=count($_SESSION['jbolo']['nodes'][$k]['messages']);
							}
							for($m=0;$m<count($messages);$m++)
							{
								//add new mesage at the end
								$_SESSION['jbolo']['nodes'][$k]['messages'][$mcnt]=$messages[$m];//changed
								$mcnt++;//increasemesage count for messages in session for current node
							}
						}
					}

				}//end if
				else//if no nodes in session
				{
					//if no node is present in session
					//add a new node in session
					//push nodeinfo
					$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$nodes[$nc];
					//push node participants
					$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
					//push unread messages
					$mcnt=0;
					for($m=0;$m<count($messages);$m++)
					{
						$_SESSION['jbolo']['nodes'][0]['messages'][$mcnt] = $messages[$m]; //changed
						$mcnt++;
					}
				}
			}//if messages
		}//for loop for nodes

		//check if nodes array is present in session
		if(isset($_SESSION['jbolo']['nodes']))
		{
			$nodeStatusArray=$nodesHelper->getNodeStatusArray($_SESSION['jbolo']['nodes'],$uid);
			$this->jsonarray['nsts']=$nodeStatusArray;
		}
		else{
			$this->jsonarray['nsts']=array();
		}
		return $this->jsonarray;
	}
	 /**
     * @uses to get nodeId of whatever user selected(depending upon userId passed in pid).
     * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"initiateNode",
	 * 		"taskData":{
	 * 			"pid":"pid"
	 * 		}
	 * 	} 
     */
	function initiateNode(){
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		$uid = $this->IJUserID;
		$pid = IJReq::getTaskData('pid',0,'int');
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		if(!$pid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_PARTICIPANT'));
			return false;
		}
		$nodesHelper=new nodesHelper();
		$node_id_found=$nodesHelper->checkNodeExists($uid,$pid);
		if(!$node_id_found)
		{
			$myobj=new stdclass;
			$myobj->title=NULL;
			$myobj->type=1;
			$myobj->owner=$uid;
			$myobj->time=date("Y-m-d H:i:s");//note
			$this->db->insertObject('#__jbolo_nodes',$myobj);
			//get last insert id
			$new_node_id=$this->db->insertid();
			if($this->db->insertid())
			{
				for($i=0;$i<2;$i++)
				{
					$myobj=new stdclass;
					$myobj->node_id=$new_node_id;
					$myobj->user_id=($i==0) ? $uid:$pid;//add entry for both users one after other
					$myobj->status=1;
					$this->db->insertObject('#__jbolo_node_users',$myobj);
			   }
			}
		}
		else{//node already exists
			$new_node_id=$node_id_found;
		}

		$query="SELECT node_id AS nid, type AS ctyp
				FROM #__jbolo_nodes
				WHERE node_id=".$new_node_id;
		$this->db->setQuery($query);
		$node_d=$this->db->loadObject();
		
		$this->jsonarray['code'] = 200;
		$this->jsonarray['nodeinfo']=$node_d;
		//get chat window title(wt)
		$this->jsonarray['nodeinfo']->wt=$nodesHelper->getNodeTitle($new_node_id,$uid,$this->jsonarray['nodeinfo']->ctyp);
		//get chatbox status (node status - ns)
		$this->jsonarray['nodeinfo']->ns=$nodesHelper->getNodeStatus($new_node_id,$uid,$this->jsonarray['nodeinfo']->ctyp);
		$participants=$nodesHelper->getNodeParticipants($new_node_id,$uid);

		//update this node info in session
		$d=0;
		if(!isset($_SESSION['jbolo']['nodes']))//check if 'nodes' array is set
		{
			//if nodes array is not set, push new node at the start in nodes array
			$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$this->jsonarray['nodeinfo'];
			$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
		}
		else//if nodes array is set
		{
			$node_ids=array();
			$nodecount=count($_SESSION['jbolo']['nodes']);
			for($d=0;$d<$nodecount;$d++)
			{
				if(isset($_SESSION['jbolo']['nodes'][$d]))
				{
					//get all node ids set in session
					$node_ids[$d]=$_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid;
				}
			}
			//if the current node is not present in session,
			//we add it into session
			if(!in_array($this->jsonarray['nodeinfo']->nid,$node_ids))
			{
				if($nodecount)//if nodecount is >0, push new node data at the end of array
				{
					$_SESSION['jbolo']['nodes'][$nodecount]['nodeinfo']=$this->jsonarray['nodeinfo'];
					$_SESSION['jbolo']['nodes'][$nodecount]['participants']=$participants['participants'];
				}
				else//if nodecount is 0, push this at start i.e. 0th position in array
				{
					$_SESSION['jbolo']['nodes'][0]['nodeinfo']=$this->jsonarray['nodeinfo'];
					$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];
				}
			}
		}
		return $this->jsonarray;
	}
	 /**
     * @uses to send message to nid of particular userid(whom we want to send a message)
     * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"pushChatToNode",
	 * 		"taskData":{
	 * 			"nid":"nid",
	 * 			"message":"message"
	 * 		}
	 * 	} 
     */
	function pushChatToNode()
	{
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		$uid = $this->IJUserID;
		$nid = IJReq::getTaskData('nid',0,'int');
		$msg = IJReq::getTaskData('message','');
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		if(!$nid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_NODE_ID'));
			return false;
		}
		if(!$msg){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_EMPTY_MSG'));
			return false;
		}
		$user =& JFactory::getUser($uid);
		$msg = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($msg)); //2.9.5
		$msg= html_entity_decode($msg,null,'UTF-8');//2.9.5	
		$msg = str_replace("\'","'",$msg);
		$nodesHelper=new nodesHelper();
		$isNodeParticipant=$nodesHelper->isNodeParticipant($uid,$nid);
		if($isNodeParticipant==2)//error handling for inactive user
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG'));
			return false;
		}
		if(!$isNodeParticipant)//error handling for not member/unauthorized access to this group chat
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_NON_MEMBER_MSG'));
			return false;
		}
		$this->jsonarray['code'] = 200;

		//trigger plugins to process message text
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('jbolo','plg_jbolo_textprocessing');
		//process urls
		$processedText=$dispatcher->trigger('processUrls',array($msg));
		$msg=$processedText[0];
		//process smilies
		//$processedText=$dispatcher->trigger('processSmilies',array($msg));
		$processedText=$this->processSmilies(array($msg));
		$msg=$processedText[0];
		//process bad words
		$processedText=$dispatcher->trigger('processBadWords',array($msg));
		$msg=$processedText[0];

		//add msg to database
		$myobj=new stdclass;
		$myobj->from=$uid;
		$myobj->to_node_id=$nid;
		$myobj->msg=$msg;
		$myobj->msg_type='txt';
		$myobj->time=date("Y-m-d H:i:s");//NOTE - date format
		$myobj->sent=1;//@TODO need to chk if this is really used or is it for future proofing
		$this->db->insertObject('#__jbolo_chat_msgs',$myobj);
		//get last insert id
		$new_mid=$this->db->insertid();

		//update msg xref table
		if($new_mid)
		{
			//get participants for this node
			$query="SELECT user_id
					FROM #__jbolo_node_users
					WHERE node_id = ".$nid."
					AND user_id <> ".$uid."
					AND status=1";//status indicates that user is still part of node
			$this->db->setQuery($query);
			$participant=$this->db->loadColumn();
			$count=count($participant);
			//add entry for all users against this msg
			for($i=0;$i<$count;$i++)
			{
				$myobj= new stdclass;
				$myobj->msg_id=$new_mid;
				$myobj->node_id=$nid;
				$myobj->to_user_id=$participant[$i];
				$myobj->delivered=0;
				$myobj->read=0;
				$this->db->insertObject('#__jbolo_chat_msgs_xref',$myobj);
			}
		}
		//prepare json response
	 	$query="SELECT chm.to_node_id AS nid, chm.from AS uid, chm.msg_id AS mid, chm.sent, 
	 			chm.msg, chm.time, chm.msg_type as msgtype
	 			FROM #__jbolo_chat_msgs AS chm
	 			WHERE chm.msg_id=".$new_mid;
		$this->db->setQuery($query);
		$node_d=$this->db->loadObject();
		$usersHelper=new usersHelper();
		$u_data=$usersHelper->getLoggedinUserInfo($uid);
		
		$this->jsonarray['messages']['msgID'] = $node_d->mid;
		$this->jsonarray['messages']['message'] = $node_d->msg;
		$this->jsonarray['messages']['timestamp'] = strtotime($node_d->time);
		$this->jsonarray['messages']['nodeID'] = $node_d->nid;
		$nodeType=$nodesHelper->getNodeType($node_d->nid);
		$this->jsonarray['messages']['Type'] = $nodeType;
		$this->jsonarray['messages']['fromID'] = 0;
		$this->jsonarray['messages']['fromName'] = $user->name;
		$this->jsonarray['messages']['avtr'] = $u_data->avtr;
		$this->jsonarray['messages']['msgType'] = $node_d->msgtype;

		//add this msg to session
	 	$query ="SELECT m.msg_id AS mid, m.from AS fid, m.msg, m.time AS ts
				FROM #__jbolo_chat_msgs AS m
				LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
				WHERE m.msg_id=".$new_mid." AND m.sent=1";
		$this->db->setQuery($query);
		$msg_dt=$this->db->loadObject();
		$msg_dt->ts=JFactory::getDate($msg_dt->ts)->Format(JText::_('COM_JBOLO_SENT_AT_FORMAT'));

		//update session by adding this msg against corresponding node
		if(isset($_SESSION['jbolo']['nodes']))//if jbolo nodes array is set
		{
			//count nodes in session
			$nodecount=count($_SESSION['jbolo']['nodes']);
			for($k=0;$k<$nodecount;$k++)//loop through all nodes
			{
				if(isset($_SESSION['jbolo']['nodes'][$k]))//if k'th node is set
				{
					if(isset($_SESSION['jbolo']['nodes'][$k]['nodeinfo']))//if nodeinfo is set
					{
						//if the required node is found in session
						if($_SESSION['jbolo']['nodes'][$k]['nodeinfo']->nid==$nid)
						{
							$mcnt=0;//initialize mesasge count for node found to 0
							//check if the node found has messages stored in session
							if(isset($_SESSION['jbolo']['nodes'][$k]['messages']))
							{
								//if yes count msgs
								$mcnt=count($_SESSION['jbolo']['nodes'][$k]['messages']);
								//add new mesage at the end
								$_SESSION['jbolo']['nodes'][$k]['messages'][$mcnt]=$msg_dt;
							}else{//add new mesage at the start
								$_SESSION['jbolo']['nodes'][$k]['messages'][0]=$msg_dt;
							}
						}
					}
				}
				else//@TODO remaining...
				{
					//if node is not present in session
					//this situation is not expected ideally
				}
			}//end for
		}//end if
		return $this->jsonarray;
	}
	 /**
     * @uses to fetch chat history between two users.
     * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"chatHistory",
	 * 		"taskData":{
	 * 			"nid":"nid",
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	} 
     */
	function chatHistory(){
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		$uid        = $this->IJUserID;
		$nid        = IJReq::getTaskData('nid',0,'int');
		$pageNO     = IJReq::getTaskData('pageNO');
		$pageLimit  = 10;
		if(!$pageLimit){
	    	$pageLimit=10;
	    }
    	$startFrom= ($pageNO==0 || $pageNO==1) ? 0 : $pageLimit*($pageNO-1);
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		if(!$nid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_NODE_ID'));
			return false;
		}
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}
		$query ="SELECT DISTINCT(m.msg_id) AS mid, m.from AS fid, m.msg, m.time AS ts, m.msg_type as msgtype, m.to_node_id as nid, u.$chattitle AS uname
				FROM #__jbolo_chat_msgs_xref AS mx
				LEFT JOIN #__jbolo_chat_msgs AS m ON m.msg_id=mx.msg_id
				LEFT JOIN #__users AS u ON u.id=m.from
				WHERE m.to_node_id=".$nid."
				AND (mx.to_user_id =".$uid." OR m.from=".$uid.")
				AND (mx.to_user_id =".$uid." OR m.from=".$uid." AND m.msg_type<>'file')
				ORDER BY m.msg_id DESC ";
		$queryLimit=$query;
		$query .= " LIMIT $startFrom, $pageLimit";
		$this->db->setQuery($query);
		$chats = $this->db->loadObjectList();
		$this->db->setQuery($queryLimit);
	    $total = count($this->db->loadObjectList());
		$me = JText::_('me');	
		$usersHelper = new usersHelper();
		$data = $usersHelper->getOnlineUsersInfo($uid);
		$u_data=$usersHelper->getLoggedinUserInfo($uid);
		for($i=0;$i<count($chats);$i++){
			$udetails = JFactory::getUser($chats[$i]->fid);
			if($chats[$i]->fid == $uid)
			{
				$chatfromId = 0;
				$chatfrom = $me;
			}else if($chats[$i]->fid==0){
				$chatfromId = -1;
				$chatfrom = '';
			}else{
				$chatfromId = $chats[$i]->fid;
				$chatfrom = $chats[$i]->uname;
			}
			$chats[$i]->msg = $this->sanitize($chats[$i]->msg);
			$this->jsonarray['messages'][$i]['fromID'] = $chatfromId;
			$this->jsonarray['messages'][$i]['fromName'] = $chatfrom;
			$this->jsonarray['messages'][$i]['msgID'] = $chats[$i]->mid;
			$this->jsonarray['messages'][$i]['message'] = $chats[$i]->msg;
			$this->jsonarray['messages'][$i]['msgType'] = $chats[$i]->msgtype;
			$this->jsonarray['messages'][$i]['nodeID'] = $chats[$i]->nid;
			$this->jsonarray['messages'][$i]['timestamp'] = strtotime($chats[$i]->ts);
			foreach($data as $key=>$value){
				if($value->uid==$chats[$i]->fid){
					$this->jsonarray['messages'][$i]['avtr'] = $value->avtr;
				}else{
					$this->jsonarray['messages'][$i]['avtr'] = $u_data->avtr;
				}
			}
		}	
		$this->jsonarray['code'] = ($total>0) ? 200 : 204;
		$this->jsonarray['total'] = $total;
		$this->jsonarray['pageLimit'] = $pageLimit;
		$this->jsonarray['messages'] = $this->jsonarray['messages'];
		return $this->jsonarray;
	}
	/**
     * @uses to upload file of different extensions which allowed from jbolo config.
     * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"uploadFile",
	 * 		"taskData":{(FILES Array)
	 * 			"nid":"nid"
	 * 			}
	 * 	} 
     */
	function uploadFile($print_response = true)
	{
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			return $this->delete($print_response);
		}
		$upload=isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
		//Parse the Content-Disposition header, if available:
		$file_name=isset($_SERVER['HTTP_CONTENT_DISPOSITION']) ? rawurldecode(preg_replace('/(^[^"]+")|("$)/','',$_SERVER['HTTP_CONTENT_DISPOSITION'])) : null;
		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range=isset($_SERVER['HTTP_CONTENT_RANGE']) ? preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']) : null;
		$size=$content_range ? $content_range[3] : null;
		$files=array();

		if($upload && is_array($upload['tmp_name']))
		{
			// param_name is an array identifier like "files[]",
			// $_FILES is a multi-dimensional array:
			foreach($upload['tmp_name'] as $index => $value)
			{
				$files[]=$this->handle_file_upload($upload['tmp_name'][$index],
					$file_name ? $file_name : $upload['name'][$index],
					$size ? $size : $upload['size'][$index],
					$upload['type'][$index],
					$upload['error'][$index],
					$index,
					$content_range
				);
			}
		}
		else
		{
			// param_name is a single object identifier like "file",
			// $_FILES is a one-dimensional array:
			$files[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				$file_name ? $file_name : (isset($upload['name']) ? $upload['name'] : null),
				$size ? $size : (isset($upload['size']) ? $upload['size'] : $_SERVER['CONTENT_LENGTH']),
				isset($upload['type']) ? $upload['type'] : $_SERVER['CONTENT_TYPE'],
				isset($upload['error']) ? $upload['error'] : null,
				null,
				$content_range
			);
		}
		$this->prepareChatMsgs($files);
		return $this->generate_response(array($this->options['param_name'] => $files),false);
	}
   /** @uses to upload file of different extensions which allowed from jbolo config.
     //called by uploadFile
    */
	function prepareChatMsgs($files)
	{
		foreach($files as $file)
		{
			if( !isset($file->error) )
			{
				$particularUID = $this->IJUserID;
				$nid = IJReq::getTaskData('nid',0,'int');
				$msgType='file';
				//for sender
				$msg=/*JText::_('COM_JBOLO_YOU_SENT_FILE').' '.JURI::base().'components/com_jbolo/uploads/'.*/$file->name;
				$this->pushChat($msgType,$nid,$msg,$particularUID,0);

				//for all receivers
				$msg=/*JText::_('COM_JBOLO_I_SENT_FILE').' '.JURI::base().'components/com_jbolo/uploads/'.*/$file->name;
				$this->pushChat($msgType,$nid,$msg,0,0);
			}
		}
		return true;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by prepareChatMsgs(Function uploadFile)
    */
	function pushChat($msgType,$nid,$msg,$particularUID=0,$sendToActor=0)
	{
		$actorid= $this->IJUserID;
		//process text for urls & download links
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('jbolo','plg_jbolo_textprocessing');
		if($msgType=='file')
		{
			//process download link
			//note - another parameter passed here - particularUID
			$processedText=$dispatcher->trigger('processDownloadLink',array($msg,$particularUID));
		}
		else
		{
			//process urls
			$processedText=$dispatcher->trigger('processUrls',array($msg));
		}
		$msg=$processedText[0];
		//process smilies
		$processedText=$this->processSmilies(array($msg));
		$msg=$processedText[0];
		//process bad words
		$processedText=$dispatcher->trigger('processBadWords',array($msg));
		$msg=$processedText[0];

		//add msg to database
		$myobj=new stdclass;
		if($msgType=='gbc'){
			$myobj->from=0;//set userid to 0 for gbc messages
		}else{
			$myobj->from=$actorid;//set userid to 0 for gbc messages
		}
		$myobj->to_node_id=$nid;
		$myobj->msg=$msg;
		$myobj->msg_type=$msgType;
		$myobj->time=date("Y-m-d H:i:s");
		$myobj->sent=1;
		
		$this->db->insertObject('#__jbolo_chat_msgs',$myobj);
		//get last insert id
		$new_mid=$this->db->insertid();

		//update msg xref table
		if($new_mid)
		{
			if($particularUID)
			{
				$myobj= new stdclass;
				$myobj->msg_id=$new_mid;
				$myobj->node_id=$nid;
				$myobj->to_user_id=$particularUID;
				$myobj->delivered=0;
				$myobj->read=0;
				$this->db->insertObject('#__jbolo_chat_msgs_xref',$myobj);
			}
			else
			{
				$query="SELECT user_id
				FROM #__jbolo_node_users
				WHERE node_id = ".$nid."
				AND status=1";//status indicates of user is still part of node (only active users)
				if(!$sendToActor){
					$query.=" AND user_id <> ".$actorid;
				}
				$this->db->setQuery($query);
				$participant=$this->db->loadColumn();
				$count=count($participant);
				for($i=0;$i<$count;$i++)
				{
					$myobj= new stdclass;
					$myobj->msg_id=$new_mid;
					$myobj->node_id=$nid;
					$myobj->to_user_id=$participant[$i];
					$myobj->delivered=0;
					$myobj->read=0;
					$this->db->insertObject('#__jbolo_chat_msgs_xref',$myobj);
				}
			}
return 1;
			//add this msg to session
			$query ="SELECT m.msg_id AS mid, m.from AS fid, m.msg, m.time AS ts
			FROM #__jbolo_chat_msgs AS m
			LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
			WHERE m.msg_id=".$new_mid." AND m.sent=1";
			$this->db->setQuery($query);
			$msg_dt=$this->db->loadObject();
			//update session by adding this msg against corresponding node
			if(isset($_SESSION['jbolo']['nodes']))//if jbolo nodes array is set
			{
				//count nodes in session
				$nodecount=count($_SESSION['jbolo']['nodes']);
				for($k=0;$k<$nodecount;$k++)//loop through all nodes
				{
					if(isset($_SESSION['jbolo']['nodes'][$k]))//if k'th node is set
					{
						if(isset($_SESSION['jbolo']['nodes'][$k]['nodeinfo']))//if nodeinfo is set
						{
							//if the required node is found in session
							if($_SESSION['jbolo']['nodes'][$k]['nodeinfo']->nid==$nid)
							{
								$mcnt=0;//initialize mesasge count for node found to 0
								//check if the node found has messages stored in session
								if(isset($_SESSION['jbolo']['nodes'][$k]['messages']))
								{
									//if yes count msgs
									$mcnt=count($_SESSION['jbolo']['nodes'][$k]['messages']);
									//add new mesage at the end
									$_SESSION['jbolo']['nodes'][$k]['messages'][$mcnt]=$msg_dt;
								}else{//add new mesage at the start
									$_SESSION['jbolo']['nodes'][$k]['messages'][0]=$msg_dt;
								}
							}
						}
					}
					else//@TODO remaining...
					{
						//if node is not present in session
						//this situation is not expected ideally
					}
				}//end for
			}//end if

		}
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by uploadFile
    */
	function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index=null, $content_range=null)
	{
		$file=new stdClass();
		//$file->name=$this->get_file_name($name, $type, $index, $content_range);
		$file->name=$this->get_file_name($name, $type, $index, $content_range,$uploaded_file);//manoj
		$file->size = $this->fix_integer_overflow(intval($size));
		$file->type = $type;
		if($this->validate($uploaded_file, $file, $error, $index))
		{
			$this->handle_form_data($file, $index);
			$upload_dir = $this->get_upload_path();
			if (!is_dir($upload_dir)) {
				mkdir($upload_dir, $this->options['mkdir_mode'], true);
			}
			$file_path = $this->get_upload_path($file->name);
			$append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
			if ($uploaded_file && is_uploaded_file($uploaded_file))
			{
				// multipart/formdata uploads (POST method uploads)
				if($append_file){
					file_put_contents($file_path,fopen($uploaded_file, 'r'),FILE_APPEND);
				}else{
					move_uploaded_file($uploaded_file, $file_path);
				}
			}
			else
			{
				//Non-multipart uploads (PUT method support)
				file_put_contents($file_path,fopen('php://input', 'r'),$append_file ? FILE_APPEND : 0);
			}

			$file_size = $this->get_file_size($file_path, $append_file);
			if ($file_size === $file->size)
			{
				if ($this->options['orient_image']) {
					$this->orient_image($file_path);
				}
				$file->url = $this->get_download_url($file->name);
				foreach($this->options['image_versions'] as $version => $options)
				{
					if ($this->create_scaled_image($file->name, $version, $options))
					{
						if(!empty($version))
						{
							$file->{$version.'_url'} = $this->get_download_url($file->name,$version);
						}else{
							$file_size = $this->get_file_size($file_path, true);
						}
					}
				}
			}
			else if (!$content_range && $this->options['discard_aborted_uploads'])
			{
				unlink($file_path);
				$file->error = 'abort';
			}
			$file->size = $file_size;
		}
		return $file;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by handle_file_upload(Function uploadFile)
    */
	function get_file_name($name, $type, $index, $content_range,$uploaded_file)//manoj
	{
		return $this->get_unique_filename(
			//$this->trim_file_name($name, $type, $index, $content_range),
			$this->trim_file_name($name, $type, $index, $content_range,$uploaded_file),//manoj
			$type,
			$index,
			//$content_range
			$content_range,//manoj
			$uploaded_file
		);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_file_name(Function uploadFile)
    */
	function trim_file_name($name, $type, $index, $content_range,$uploaded_file) 
	{
		$fileInfo=pathinfo($name);
		$fileExt=$fileInfo['extension'];//file extension
		$fileBase=$fileInfo['filename'];//base name
		//Clean up filename to get rid of strange characters like spaces etc
		$fileBase=JFile::makeSafe($fileBase);
		//lose any special characters in the filename
		$fileBase=preg_replace("/[^A-Za-z0-9]/i", "-", $fileBase);
		//use lowercase
		$fileBase=strtolower($fileBase);
		//add timestamp to file name
		$timestamp=time();
		$fileName=$fileBase.'_'.$timestamp.'.'.$fileExt;
		return $fileName;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_file_name(Function uploadFile)
    */
	function get_unique_filename($name, $type, $index, $content_range,$uploaded_file)//manoj
	{
		while(is_dir($this->get_upload_path($name)))
		{
			$name=$this->upcount_name($name);
		}
		// Keep an existing filename if this is part of a chunked upload:
		$uploaded_bytes = $this->fix_integer_overflow(intval($content_range[1]));
		while(is_file($this->get_upload_path($name))) {
			if ($uploaded_bytes === $this->get_file_size(
					$this->get_upload_path($name))) {
				break;
			}
			$name = $this->upcount_name($name);
		}
		return $name;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_unique_filename(Function uploadFile)
    */
	function get_upload_path($file_name=null, $version=null)
	{
		$file_name=$file_name ? $file_name : '';
		$version_path=empty($version) ? '' : $version.'/';
		return $this->options['upload_dir'].$this->get_user_path().$version_path.$file_name;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by handle_file_upload,get_unique_filename,validate,
				 get_file_size,get_config_bytes,generate_response(Function uploadFile)
    */
	function fix_integer_overflow($size) {
		if ($size < 0) {
			$size += 2.0 * (PHP_INT_MAX + 1);
		}
		return $size;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by handle_file_upload,head(Function uploadFile)
    */
	function validate($uploaded_file, $file, $error, $index)
	{
		if($error)
		{
			$file->error = $this->get_error_message($error);
			return false;
		}
		$content_length = $this->fix_integer_overflow(intval($_SERVER['CONTENT_LENGTH']));
		$post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
		if ($post_max_size && ($content_length > $post_max_size))
		{
			$file->error = $this->get_error_message('post_max_size');
			return false;
		}
		if (!preg_match($this->options['accept_file_types'], $file->name))
		{
			$file->error = $this->get_error_message('accept_file_types');
			return false;
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file)){
			$file_size = $this->get_file_size($uploaded_file);
		} else {
			$file_size = $content_length;
		}
		if ($this->options['max_file_size'] && ($file_size > $this->options['max_file_size'] || $file->size > $this->options['max_file_size']))
		{
			$file->error = $this->get_error_message('max_file_size');
			return false;
		}

		if ($this->options['min_file_size'] &&  $file_size < $this->options['min_file_size'])
		{
			$file->error = $this->get_error_message('min_file_size');
			return false;
		}

		if (is_int($this->options['max_number_of_files']) && ($this->count_file_objects() >= $this->options['max_number_of_files']))
		{
			$file->error = $this->get_error_message('max_number_of_files');
			return false;
		}

		list($img_width, $img_height) = @getimagesize($uploaded_file);

		if (is_int($img_width))
		{
			if ($this->options['max_width'] && $img_width > $this->options['max_width'])
			{
				$file->error = $this->get_error_message('max_width');
				return false;
			}
			if ($this->options['max_height'] && $img_height > $this->options['max_height'])
			{
				$file->error = $this->get_error_message('max_height');
				return false;
			}
			if ($this->options['min_width'] && $img_width < $this->options['min_width'])
			{
				$file->error = $this->get_error_message('min_width');
				return false;
			}
			if ($this->options['min_height'] && $img_height < $this->options['min_height'])
			{
				$file->error = $this->get_error_message('min_height');
				return false;
			}
		}
		return true;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by handle_file_upload(Function uploadFile)
    */
	function handle_form_data($file, $index) {
		// Handle form data, e.g. $_REQUEST['description'][$index]
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_download_url,get_upload_path(Function uploadFile)
    */
	function get_user_path()
	{
		if ($this->options['user_dirs']){
			return $this->get_user_id().'/';
		}
		return '';
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_user_path,get_upload_path(Function uploadFile)
    */
	function get_user_id()
	{
		@session_start();
		return session_id();
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_unique_filename(Function uploadFile)
    */
	function upcount_name($name)
	{
		return preg_replace_callback('/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
		array($this,'upcount_name_callback'),$name,1);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by upcount_name(Function uploadFile)
    */
	function upcount_name_callback($matches)
	{
		$index=isset($matches[1]) ? intval($matches[1]) + 1 : 1;
		$ext=isset($matches[2]) ? $matches[2] : '';
		return ' ('.$index.')'.$ext;
	}

	/*function get_full_url() {
		$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
		return
			($https ? 'https://' : 'http://').
			(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
			(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
			($https && $_SERVER['SERVER_PORT'] === 443 ||
			$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
			substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	}*/
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_download_url,set_file_delete_properties(Function uploadFile)
    */
	function get_query_separator($url) {
		return strpos($url, '?') === false ? '?' : '&';
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by fet_file_object,handle_file_upload(Function uploadFile)
    */
	function get_download_url($file_name, $version = null) {
		if ($this->options['download_via_php']) {
			$url = $this->options['script_url']
				.$this->get_query_separator($this->options['script_url'])
				.'file='.rawurlencode($file_name);
			if ($version) {
				$url .= '&version='.rawurlencode($version);
			}
			return $url.'&download=1';
		}
		$version_path = empty($version) ? '' : rawurlencode($version).'/';
		return $this->options['upload_url'].$this->get_user_path()
			.$version_path.rawurlencode($file_name);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by (Function uploadFile)
    */
	function set_file_delete_properties($file) {
		$file->delete_url = $this->options['script_url']
			.$this->get_query_separator($this->options['script_url'])
			.'file='.rawurlencode($file->name);
		$file->delete_type = $this->options['delete_type'];
		if ($file->delete_type !== 'DELETE') {
			$file->delete_url .= '&_method=DELETE';
		}
		if ($this->options['access_control_allow_credentials']) {
			$file->delete_with_credentials = true;
		}
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_file_object,download,handle_file_upload,
				validate,get_unique_filename(Function uploadFile)
    */
	function get_file_size($file_path, $clear_stat_cache = false) {
		if ($clear_stat_cache) {
			clearstatcache(true, $file_path);
		}
		return $this->fix_integer_overflow(filesize($file_path));
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get_file_object,download,count_file_objects(Function uploadFile)
    */
	function is_valid_file_object($file_name) {
		$file_path = $this->get_upload_path($file_name);
		if (is_file($file_path) && $file_name[0] !== '.') {
			return true;
		}
		return false;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by (Function uploadFile)
    */
	function get_file_object($file_name) {
		if ($this->is_valid_file_object($file_name)) {
			$file = new stdClass();
			$file->name = $file_name;
			$file->size = $this->get_file_size(
				$this->get_upload_path($file_name)
			);
			$file->url = $this->get_download_url($file->name);
			foreach($this->options['image_versions'] as $version => $options) {
				if (!empty($version)) {
					if (is_file($this->get_upload_path($file_name, $version))) {
						$file->{$version.'_url'} = $this->get_download_url(
							$file->name,
							$version
						);
					}
				}
			}
			//$this->set_file_delete_properties($file);//commented by manoj
			return $file;
		}
		return null;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get,count_file_objects(Function uploadFile)
    */
	function get_file_objects($iteration_method = 'get_file_object') {
		$upload_dir = $this->get_upload_path();
		if (!is_dir($upload_dir)) {
			return array();
		}
		return array_values(array_filter(array_map(
			array($this, $iteration_method),
			scandir($upload_dir)
		)));
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by validate(Function uploadFile)
    */
	function count_file_objects() {
		return count($this->get_file_objects('is_valid_file_object'));
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by handle_file_upload(Function uploadFile)
    */
	function create_scaled_image($file_name, $version, $options) {
		$file_path = $this->get_upload_path($file_name);
		if (!empty($version)) {
			$version_dir = $this->get_upload_path(null, $version);
			if (!is_dir($version_dir)) {
				mkdir($version_dir, $this->options['mkdir_mode'], true);
			}
			$new_file_path = $version_dir.'/'.$file_name;
		} else {
			$new_file_path = $file_path;
		}
		list($img_width, $img_height) = @getimagesize($file_path);
		if (!$img_width || !$img_height) {
			return false;
		}
		$scale = min(
			$options['max_width'] / $img_width,
			$options['max_height'] / $img_height
		);
		if ($scale >= 1) {
			if ($file_path !== $new_file_path) {
				return copy($file_path, $new_file_path);
			}
			return true;
		}
		$new_width = $img_width * $scale;
		$new_height = $img_height * $scale;
		$new_img = @imagecreatetruecolor($new_width, $new_height);
		switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
			case 'jpg':
			case 'jpeg':
				$src_img = @imagecreatefromjpeg($file_path);
				$write_image = 'imagejpeg';
				$image_quality = isset($options['jpeg_quality']) ?
					$options['jpeg_quality'] : 75;
				break;
			case 'gif':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				$src_img = @imagecreatefromgif($file_path);
				$write_image = 'imagegif';
				$image_quality = null;
				break;
			case 'png':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$src_img = @imagecreatefrompng($file_path);
				$write_image = 'imagepng';
				$image_quality = isset($options['png_quality']) ?
					$options['png_quality'] : 9;
				break;
			default:
				$src_img = null;
		}
		$success = $src_img && @imagecopyresampled(
			$new_img,
			$src_img,
			0, 0, 0, 0,
			$new_width,
			$new_height,
			$img_width,
			$img_height
		) && $write_image($new_img, $new_file_path, $image_quality);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($src_img);
		@imagedestroy($new_img);
		return $success;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by validate(Function uploadFile)
    */
	function get_error_message($error) {
		return array_key_exists($error, $this->error_messages) ?
			$this->error_messages[$error] : $error;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by validate(Function uploadFile)
    */
	function get_config_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $this->fix_integer_overflow($val);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by handle_file_upload(Function uploadFile)
    */
	function orient_image($file_path) {
		if (!function_exists('exif_read_data')) {
			return false;
		}
		$exif = @exif_read_data($file_path);
		if ($exif === false) {
			return false;
		}
		$orientation = intval(@$exif['Orientation']);
		if (!in_array($orientation, array(3, 6, 8))) {
			return false;
		}
		$image = @imagecreatefromjpeg($file_path);
		switch ($orientation) {
			case 3:
				$image = @imagerotate($image, 180, 0);
				break;
			case 6:
				$image = @imagerotate($image, 270, 0);
				break;
			case 8:
				$image = @imagerotate($image, 90, 0);
				break;
			default:
				return false;
		}
		$success = imagejpeg($image, $file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($image);
		return $success;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by download(Function uploadFile)
    */
	function readfile($file_path) {
		return readfile($file_path);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by generate_response(Function uploadFile)
    */
	function body($str) {
		echo $str;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by generate_response,download,send_content_type_header
				send_access_control_headers,head(Function uploadFile)
    */
	function header($str) {
		header($str);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get,delete,uploadFile(Function uploadFile)
    */
	function generate_response($content, $print_response = true) {
		if ($print_response) 
		{
			$json = json_encode($content);
			$redirect = isset($_REQUEST['redirect']) ?
				stripslashes($_REQUEST['redirect']) : null;
			if ($redirect) {
				$this->header('Location: '.sprintf($redirect, rawurlencode($json)));
				return;
			}
			$this->head();
			if (isset($_SERVER['HTTP_CONTENT_RANGE'])) {
				$files = isset($content[$this->options['param_name']]) ?
					$content[$this->options['param_name']] : null;
				if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
					$this->header('Range: 0-'.($this->fix_integer_overflow(intval($files[0]->size)) - 1));
				}
			}
			$this->body($json);
		}
		$this->jsonarray['code'] = 200;
		$this->jsonarray['files'] = $content['files'];
		return $this->jsonarray;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by download(Function uploadFile)
    */
	function get_version_param() {
		return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by download(Function uploadFile)
    */
	function get_file_name_param() {
		return isset($_GET['file']) ? basename(stripslashes($_GET['file'])) : null;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by download(Function uploadFile)
    */
	function get_file_type($file_path) {
		switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
			case 'jpeg':
			case 'jpg':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'gif':
				return 'image/gif';
			default:
				return '';
		}
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by get(Function uploadFile)
    */
   function download() {
		if (!$this->options['download_via_php']) {
			$this->header('HTTP/1.1 403 Forbidden');
			return;
		}
		$file_name = $this->get_file_name_param();
		if ($this->is_valid_file_object($file_name)) {
			$file_path = $this->get_upload_path($file_name, $this->get_version_param());
			if (is_file($file_path)) {
				if (!preg_match($this->options['inline_file_types'], $file_name)) {
					$this->header('Content-Description: File Transfer');
					$this->header('Content-Type: application/octet-stream');
					$this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
					$this->header('Content-Transfer-Encoding: binary');
				} else {
					// Prevent Internet Explorer from MIME-sniffing the content-type:
					$this->header('X-Content-Type-Options: nosniff');
					$this->header('Content-Type: '.$this->get_file_type($file_path));
					$this->header('Content-Disposition: inline; filename="'.$file_name.'"');
				}
				$this->header('Content-Length: '.$this->get_file_size($file_path));
				$this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
				$this->readfile($file_path);
			}
		}
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by head(Function uploadFile)
    */
	function send_content_type_header() {
		$this->header('Vary: Accept');
		if (isset($_SERVER['HTTP_ACCEPT']) &&
			(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			$this->header('Content-type: application/json');
		} else {
			$this->header('Content-type: text/plain');
		}
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by head(Function uploadFile)
    */
	function send_access_control_headers() {
		$this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
		$this->header('Access-Control-Allow-Credentials: '
			.($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
		$this->header('Access-Control-Allow-Methods: '
			.implode(', ', $this->options['access_control_allow_methods']));
		$this->header('Access-Control-Allow-Headers: '
			.implode(', ', $this->options['access_control_allow_headers']));
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by (Function uploadFile)
    */
	public function head() {
		$this->header('Pragma: no-cache');
		$this->header('Cache-Control: no-store, no-cache, must-revalidate');
		$this->header('Content-Disposition: inline; filename="files.json"');
		// Prevent Internet Explorer from MIME-sniffing the content-type:
		$this->header('X-Content-Type-Options: nosniff');
		if ($this->options['access_control_allow_origin']) {
			$this->send_access_control_headers();
		}
		$this->send_content_type_header();
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by (Function uploadFile)
    */
	public function get($print_response = true) {
		if ($print_response && isset($_GET['download'])) {
			return $this->download();
		}
		$file_name = $this->get_file_name_param();
		if ($file_name) {
			$response = array(
				substr($this->options['param_name'], 0, -1) => $this->get_file_object($file_name)
			);
		} else {
			$response = array(
				$this->options['param_name'] => $this->get_file_objects()
			);
		}
		return $this->generate_response($response, $print_response);
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by uploadFile(Function uploadFile)
    */
	public function delete($print_response = true) {
		$file_name = $this->get_file_name_param();
		$file_path = $this->get_upload_path($file_name);
		$success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
		if ($success) {
			foreach($this->options['image_versions'] as $version => $options) {
				if (!empty($version)) {
					$file = $this->get_upload_path($file_name, $version);
					if (is_file($file)) {
						unlink($file);
					}
				}
			}
		}
		return $this->generate_response(array('success' => $success), $print_response);
	}
	/** @uses to fetch chat history between two users.
     //called by chatHistory
    */
	function sanitize($text) {
		$text = str_replace("\n\r","\n",$text);
		$text = str_replace("\r\n","\n",$text);
		$text = str_replace("\n","<br>",$text);
		$text = addslashes( $text );
		return $text;
	}
	/** @uses to upload file of different extensions which allowed from jbolo config.
     //called by pushChatToNode,pushChat
    */
	function processSmilies($text){
		$params=JComponentHelper::getParams('com_jbolo');
		$template=$params->get('template');
		$smiliesfile=JFile::read(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'jbolo'.DS.'assets'.DS.'smileys.txt');
		$smilies=explode("\n",$smiliesfile);
		foreach($smilies as $smiley)
		{
			if(trim($smiley)==''){
				continue;
			}
			$pcs=explode('=',$smiley);
			$img=JURI::base().'components/com_jbolo/jbolo/view/'.$template.'/images/smileys/default/'.$pcs[1];
			$imgsrc = "<img src=\"{$img}\" border=\"0\" />";
			$text=str_replace($pcs[0],$imgsrc,$text);
		}
		return $text;
	}
	/**
	 * @uses to get users to invite for group chat based on search
	 * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"getAutoCompleteUserList",
	 * 		"taskData":{
	 * 			"filterText":"filterText",
	 * 		}
	 * 	}
	 */
	function getAutoCompleteUserList(){
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		$uid = $this->IJUserID;
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$filterText = IJReq::getTaskData('filterText');
		if(!$filterText){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_EMPTY_SEARCH_STRING'));
			return false;
		}
		//addslashes, user might enter anything to search
		$filterText=addslashes($filterText);
		$usersHelper=new usersHelper();
		$data=$usersHelper->getAutoCompleteUserList($uid,$filterText);
		$total = count($data);
		$this->jsonarray['code'] = ($total>0) ? 200 : 204;
		$this->jsonarray['users']= $data;
		return $this->jsonarray;
	}
	/**
	 * @uses to invite users to join group chat
	 * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"addNodeUser",
	 * 		"taskData":{
	 * 			"nid":"nid",
	 * 			"pid":"pid
	 * 		}
	 * 	}
	 */
	function addNodeUser()
	{
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'chatBroadcast.php');
		$uid = $this->IJUserID;
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$nid = IJReq::getTaskData('nid',0,'int');
		$pid= IJReq::getTaskData('pid',0,'int');
		if(!$nid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_NODE_ID'));
			return false;
		}

		if(!$pid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_PARTICIPANT'));
			return false;
		}

		$params=JComponentHelper::getParams('com_jbolo');
		$maxChatUsers=$params->get('maxChatUsers');
		$nodesHelper=new nodesHelper();

		//validate max allowed users for group chat
		$activeNodeParticipantsCount=$nodesHelper->getActiveNodeParticipantsCount($nid);
		if($activeNodeParticipantsCount>=$maxChatUsers)
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_GC_MAX_USERS_LIMIT'));
			return false;
		}
		//validate if this user is participant of this node
		$isNodeParticipant=$nodesHelper->isNodeParticipant($uid,$nid);
		if($isNodeParticipant==2)//error handling for inactive user
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG'));
			return false;
		}
		if(!$isNodeParticipant)//error handling for not member/unauthorized access to this group chat
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_NON_MEMBER_MSG'));
			return false;
		}
		//get node type
		$nodeType=$nodesHelper->getNodeType($nid);//important
		if($nodeType==1)//if adding a new user to 1to1 chat
		{
			//create a new node for this group chat
			$myobj=new stdclass;
			$myobj->title=NULL;
			$myobj->type=2;
			$myobj->owner=$uid;
			$myobj->time=date("Y-m-d H:i:s");
			$this->db->insertObject('#__jbolo_nodes',$myobj);
			$this->db->stderr();
			$new_node_id=$this->db->insertid();

			if($new_node_id)//when new node is created
			{
				//get old node users
				$query="SELECT user_id
				FROM #__jbolo_node_users AS nu
				WHERE node_id=".$nid."";
				$this->db->setQuery($query);
				$old_node_users=$this->db->loadColumn();
				//print_r($old_node_users);

				//add participants from old node(i.e. current 1to1 node) to newly created group chat node
				for($i=0;$i<count($old_node_users);$i++)
				{
					$myobj=new stdclass;
					$myobj->node_id=$new_node_id;
					$myobj->user_id=$old_node_users[$i];
					$myobj->status=1;
					$this->db->insertObject('#__jbolo_node_users',$myobj);
					if($uid!=$myobj->user_id){
						$first_one2one_chat_user=$myobj->user_id;
					}
				}

				//after adding existing users from 1to1 chat, add new user to new node
				$myobj=new stdclass;
				$myobj->node_id=$new_node_id;
				$myobj->user_id=$pid;
				$myobj->status=1;
				$this->db->insertObject('#__jbolo_node_users',$myobj);

				//push welcome messages for actor and others
				$this->pushWelcomeMsgBroadcast('gbc',$new_node_id,0,1,$uid);
				//push invited messages for actor and others
				//invited first user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$first_one2one_chat_user);
				//the added user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$pid);
				//push who has joined messages to actor and others
				$this->pushJoinedMsgBroadcast('gbc',$new_node_id,0,1,$uid);
			}
		}
		else if($nodeType==2)//called from group chat
		{
			//check if user being added is already participant
			$isNodeParticipant=$nodesHelper->isNodeParticipant($pid,$nid);
			$new_node_id=$nid;//@TODO chk /test
			if(!$isNodeParticipant)
			{
				//after adding existing users from 1to1 chat add new user
				$myobj=new stdclass;
				$myobj->node_id=$nid;
				$myobj->user_id=$pid;
				$myobj->status=1;
				$this->db->insertObject('#__jbolo_node_users',$myobj);

				//push welcome message only to newly added user
				$particularUID=$pid;
				$this->pushWelcomeMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);
				//push invited messages for actor and others
				//the added user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$pid);
				//push who has joined messages to actor and others
				$this->pushJoinedMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);

			}
			elseif($isNodeParticipant==2)//re adding user
			{
				//re-add existing user
				$query="UPDATE #__jbolo_node_users
				SET status=1
				WHERE node_id=".$nid."
				AND user_id=".$pid."
				AND status=0";
				$this->db->setQuery($query);
				$this->db->execute();

				//use broadcast helper
				$chatBroadcastHelper=new chatBroadcastHelper();

				//push welcome message only to newly added user
				$particularUID=$pid;
				$this->pushWelcomeMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);
				//push invited messages for actor and others
				//the added user
				$this->pushInvitedMsgBroadcast('gbc',$new_node_id,0,1,$uid,$pid);
				//push who has joined messages to actor and others
				$this->pushJoinedMsgBroadcast('gbc',$new_node_id,$particularUID,0,$uid);
			}
		}

		$query="SELECT node_id AS nid, type AS ctyp
				FROM #__jbolo_nodes
				WHERE node_id=".$new_node_id;
		$this->db->setQuery($query);
		$node_d=$this->db->loadObject();
		$this->jsonarray['code'] = 200;
		$this->jsonarray['nodeinfo']=$node_d;
		$this->jsonarray['nodeinfo']->wt=$nodesHelper->getNodeTitle($new_node_id,$pid,$this->jsonarray['nodeinfo']->ctyp);
		//get chatbox status
		$this->jsonarray['nodeinfo']->ns=$nodesHelper->getNodeStatus($this->jsonarray['nodeinfo']->nid,$pid,$this->jsonarray['nodeinfo']->ctyp);
		$user=JFactory::getUser($pid);

		//add node data to session
		$d=0;
		$participants=$nodesHelper->getNodeParticipants($new_node_id,$pid);
		if(isset($_SESSION['jbolo']['nodes']))//check if node array is set
		{
			$node_ids=array();
			for($d=0;$d<count($_SESSION['jbolo']['nodes']);$d++)
			{
				$node_info['nodeinfo']=array();
				if(isset($_SESSION['jbolo']['nodes'][$d]))
				{
					$node_ids[$d]=$_SESSION['jbolo']['nodes'][$d]['nodeinfo']->nid;
				}
			}
			//if entry for node found in session, update it
			if(in_array($this->jsonarray['nodeinfo']->nid,$node_ids))
			{
				
			}
			else//if node data not session, push new nodedata at end
			{
				$_SESSION['jbolo']['nodes'][$d]['nodeinfo']=$this->jsonarray['nodeinfo'];
				//push node participants
				$_SESSION['jbolo']['nodes'][$d]['participants']=$participants['participants'];//??//
			}
		}
		else
		{
			$_SESSION['jbolo']['nodes'][0]['nodeinfo'] = $this->jsonarray['nodeinfo'];
			$_SESSION['jbolo']['nodes'][0]['participants']=$participants['participants'];//??//
		}
		return $this->jsonarray;
	}
	/**
	 * @uses to change status of particular user
	 * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"changeStatus",
	 * 		"taskData":{
	 * 			"status":"status",
	 * 			"statusMsg":"statusMsg
	 * 		}
	 * 	}
	 * 
	 */
	function changeStatus(){
		$uid = $this->IJUserID;
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$sts = IJReq::getTaskData('status',0,'int');
		$stsm = IJReq::getTaskData('statusMsg');
		if( $sts>=5 || $sts<0 ){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_STATUS'));
			return false;
		}
		$stsm=addslashes(strip_tags($stsm));
		$query="UPDATE #__jbolo_users SET status_msg='".$stsm."'";
		if($sts){//update chat sts only if it is there in posted data
			$query.=" , chat_status=".$sts;
		}
		$query.=" WHERE user_id=".$uid;
		$this->db->setQuery($query);
		if(!$this->db->execute())//error updating
		{
			IJReq::setResponse(500);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_DB_ERROR'));
			return false;
		}
		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}
	/**
	 * @uses to invite users to join group chat
	 * //called by addNodeUser
	 */
	function pushWelcomeMsgBroadcast($msgType,$nid,$particularUID=0,$sendToActor=0,$uid){
		$chatBroadcastHelper=new chatBroadcastHelper();
		$msg=JText::_('COM_JBOLO_GC_BC_WELCOME_MSG');
		$chatBroadcastHelper->pushChat($msgType,$nid,$msg,$particularUID,$sendToActor);
		return true;
	}
	/**
	 * @uses to invite users to join group chat
	 * //called by addNodeUser
	 */
	function pushInvitedMsgBroadcast($msgType,$nid,$particularUID=0,$sendToActor=0,$uid,$pid){
		$chatBroadcastHelper=new chatBroadcastHelper();
		$params=JComponentHelper::getParams('com_jbolo');
		//show username OR name
		if($params->get('chatusertitle')){
			$msg=$broadcast_msg=JFactory::getUser($uid)->username.' <i>'.JText::_('COM_JBOLO_GC_INVITED').' </i>'.JFactory::getUser($pid)->username;
		}else{
			$msg=$broadcast_msg=JFactory::getUser($uid)->name.' <i>'.JText::_('COM_JBOLO_GC_INVITED').' </i>'.JFactory::getUser($pid)->name;
		}
		$chatBroadcastHelper->pushChat($msgType,$nid,$msg,$particularUID,$sendToActor);
		return true;
	}
	/**
	 * @uses to invite users to join group chat
	 * //called by addNodeUser
	 */
	function pushJoinedMsgBroadcast($msgType,$nid,$particularUID=0,$sendToActor=0,$uid){
		$nodesHelper=new nodesHelper();
		$participants=$nodesHelper->getActiveNodeParticipants($nid);
		//use broadcast helper
		$chatBroadcastHelper=new chatBroadcastHelper();
		$msg="";
		foreach($participants as $p)
		{
			$msg.=$p->name.' <i>'.JText::_('COM_JBOLO_GC_JOINED')."</i><br/>";
		}
		$chatBroadcastHelper->pushChat($msgType,$nid,$msg,$particularUID,$sendToActor);
		return true;
	}
	/**
	 * @uses to get participants online and involved in particular groupchat of particular nodeid
	 * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"getgroupParticipants",
	 * 		"taskData":{
	 * 			"nid":"nid",
	 * 			"pageNO":"pageNO
	 * 		}
	 * 	}
	 */
	function getgroupParticipants(){
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'chatBroadcast.php');
		$uid = $this->IJUserID;
		$nid = IJReq::getTaskData('nid',0,'int');
		if(!$uid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		if(!$nid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_NODE_ID'));
			return false;
		}
		$integrationsHelper=new integrationsHelper();
		$usersHelper=new usersHelper();
		$nodesHelper=new nodesHelper();
		$params=JComponentHelper::getParams('com_jbolo');
		if($params->get('chatusertitle')){
			$chattitle='username';
		}else{
			$chattitle='name';
		}
		$db=JFactory::getDBO();
		//get node participants info
		$query="SELECT DISTINCT u.id AS uid, u.$chattitle AS uname, u.name, u.username,
				ju.chat_status AS sts, ju.status_msg AS stsm
				FROM #__users AS u
				LEFT JOIN #__jbolo_node_users AS nu ON nu.user_id=u.id
				LEFT JOIN #__jbolo_users AS ju ON ju.user_id=nu.user_id
				WHERE nu.node_id=".$nid."
				AND nu.status=1
				ORDER BY u.username";
		$this->db->setQuery($query);
		$participants = $this->db->loadObjectList();
	    $total = count($participants);
		foreach($participants as $pKe=>$pVal){
			$participant['participants'][$pKe]['userId'] 	  	 = $pVal->uid;
			if($params->get('chatusertitle')){
				$userName = $pVal->uname;
			}else{
				$userName = $pVal->name;
			}
			$participant['participants'][$pKe]['userName']  = $userName;
			$participant['participants'][$pKe]['statusMsg'] = $pVal->stsm;
			$onlineStatus=$usersHelper->checkOnlineStatus($pVal->uid);
			if($onlineStatus){
				$participant['participants'][$pKe]['status']    = $pVal->sts;//online
			}else{
				$participant['participants'][$pKe]['status']    = 4;//offline
			}
			$participant['participants'][$pKe]['avtr']  = $integrationsHelper->getUserAvatar($pVal->uid);
		}
		$this->jsonarray['code']  	  = ($total>0) ? 200 : 204;
		$this->jsonarray['total']     = $total;
		$this->jsonarray['users']	  = $participant['participants'];
		return $this->jsonarray;
	}
	/**
	 * @uses If particular login user wants to leave groupchat for particular nodeid
	 * @example the json string will be like, : 
	 * 	{
	 * 		"extName":"jbolo",
	 *		"extView":"ichatmain",
 	 *		"extTask":"leaveChat",
	 * 		"taskData":{
	 * 			"nid":"nid",
	 * 		}
	 * 	}
	 */
	function leaveChat(){
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'integrations.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'users.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'nodes.php');
		require(JPATH_SITE.DS.'components'.DS.'com_jbolo'.DS.'helpers'.DS.'chatBroadcast.php');
		$actorid = $this->IJUserID;
		if(!$actorid){
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$nid = IJReq::getTaskData('nid',0,'int');
		if(!$nid){
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INVALID_NODE_ID'));
			return false;
		}
		$this->jsonarray=$this->validateNodeParticipant($actorid,$nid);
		$nodesHelper=new nodesHelper();
		$nodeType=$nodesHelper->getNodeType($nid);//important Coz only group chat can be left

		if($nodeType==2)//called from group chat
		{
			//mark user as inactive for this group chat
			$query="UPDATE #__jbolo_node_users
					SET status=0
					WHERE node_id=".$nid."
					AND user_id=".$actorid;
			$this->db->setQuery($query);
			if(!$this->db->query($query))
			{
				echo $this->db->stderr();
				return false;
			}
			$params=JComponentHelper::getParams('com_jbolo');
			//show username OR name
			if($params->get('chatusertitle')){
				$broadcast_msg=JFactory::getUser($actorid)->username.' <i>'.JText::_('COM_JBOLO_GC_LEFT_CHAT_MSG').'</i>';
			}else{
				$broadcast_msg=JFactory::getUser($actorid)->name.' <i>'.JText::_('COM_JBOLO_GC_LEFT_CHAT_MSG').'</i>';
			}
			$chatBroadcastHelper=new chatBroadcastHelper();
			//send to one who left chat
			$chatBroadcastHelper->pushChat('gbc',$nid,$broadcast_msg,$actorid,0);
			//send to all
			$chatBroadcastHelper->pushChat('gbc',$nid,$broadcast_msg,0,0);
			$this->jsonarray['code'] = 200;
			//set message to be sent back to ajax request
			$this->jsonarray['lcresponse']->msg=JText::_('COM_JBOLO_YOU').' '.JText::_('COM_JBOLO_GC_LEFT_CHAT_MSG');
			return $this->jsonarray;
		}
	}
	/**
	 * @uses If particular login user wants to leave groupchat for particular nodeid
	 * //called from leavechat
	 */
	function validateNodeParticipant($uid,$nid){
		$this->jsonarray['validate']=new stdclass;
		$nodesHelper=new nodesHelper();
		$isNodeParticipant=$nodesHelper->isNodeParticipant($uid,$nid);

		if($isNodeParticipant==1)//active participant
		{
			return $this->jsonarray;
		}
		else if($isNodeParticipant==2)//inactive participant (who left chat)
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_INACTIVE_MEMBER_MSG'));
			return false;
		}
		else if(!$isNodeParticipant)// 0 - not a valid group chat participant
		{
			IJReq::setResponse(400);
			IJReq::setResponseMessage(JText::_('COM_JBOLO_NON_MEMBER_MSG'));
			return false;
		}
		return $this->jsonarray;
	}
	/**
	 * @uses to get unread messages
	 * //called from polling
	 */
	function getUnreadMessages($nid,$uid)
	{
		//get all unread messages against current node for this user
		$query ="SELECT m.msg_id AS mid,m.from AS fid, m.msg, m.time AS ts, m.msg_type as msgtype
				 FROM #__jbolo_chat_msgs AS m
				 LEFT JOIN #__jbolo_chat_msgs_xref AS mx ON mx.msg_id=m.msg_id
		  		 WHERE m.to_node_id=".$nid."
				 AND mx.to_user_id =".$uid."
				 AND mx.read = 0
				 ORDER BY m.msg_id ";
		$this->db->setQuery($query);
		$messages=$this->db->loadObjectList();
		return $messages;
	}
}
?>