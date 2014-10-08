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
class wall{
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
     * @uses fetch user wall
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"wall",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID",
	 * 			"userID":"userID",
	 * 			"pageNO":"pageNO",
	 * 			"type":"type" // wall, activity
	 * 		}
	 * 	}
     *
     */
	function wall(){
		CFactory::load( 'libraries' , 'activities' );
		CFactory::load( 'libraries' , 'comment' );
		CFactory::load('helpers','owner');

		$uniqueID	= IJReq::getTaskData('uniqueID', 0, 'int');
		$userID = IJReq::getTaskData('userID',$this->IJUserID,'int');
		$pageNO = IJReq::getTaskData('pageNO',0,'int');
		$type 	= IJReq::getTaskData('type');
		$limit 	= PAGE_ACTIVITIES_LIMIT;

		if($pageNO==1 || $pageNO == 0){
		  	$pageNO = 0;
		}else{
			$pageNO = ($limit*($pageNO-1));
		}

		$act			= new CActivities();

		switch($type){
			case 'activity';
				$actconfig 		= $this->config->get('frontpageactivitydefault');
				$friendsModel 	= & CFactory::getModel('friends');
				$frids			= $friendsModel->getFriendIds($this->IJUserID);

				if($actconfig=='all'){
					$htmldata = $act->getFEED('', '', null, MAXIMUM_ACTIVITY+1 , '');
				}else{
					$htmldata = $act->getFEED($userID, $frids, null, MAXIMUM_ACTIVITY+1 , '');
				}
				break;

			case 'event':
				$options = array(	'actor' => '0',
									'target' => '0',
									'date' => '',
									'maxList' => MAXIMUM_WALL+1,
									'app' => array(	'events.wall',
													'cover.upload',
													'event.attend'),
									'cid' => '',
									'groupid' => '',
									'eventid' => $uniqueID,
		    						'exclusions' => '',
		    						'displayArchived' => '1');
				$htmldata = $this->_getData( $options );
				break;

			case 'group':
				$options = array(
							'actor' => '0',
							'target' => '0',
							'date' => '',
							'maxList' => MAXIMUM_WALL+1,
							'app' => array('groups.wall','groups.attend','events.wall','videos','groups.discussion','groups.discussion.reply','groups.bulletin','photos','events','cover.upload'),
							'cid' => '',
							'groupid' => $uniqueID,
							'eventid' => '',
		    				'exclusions' => '',
		    				'displayArchived' => '1');

				$htmldata = $this->_getData( $options );
				break;

			case 'wall':
			default:
				$userIDs=array($userID);
				$htmldata = $act->getFEED($userID, $userIDs, null, MAXIMUM_WALL+1);
				break;
		}

		if (IJ_JOOMLA_VERSION>1.5){
 		 	$htmldata = $htmldata->data;
		}

		$inc = 0;
		foreach($htmldata as $data){
			$data->title = $this->jomHelper->addAudioFile($data->title);
			$titletag = isset($data->title) ? $data->title : '';
			if(isset($data->type) && $data->type == 'title'){
				continue;
			}else{
				$temp_htmldata[]=$data;
			}
		}
		$htmldata = $temp_htmldata;
		$cout = ($pageNO+$limit>= count($htmldata)) ? count($htmldata) : $pageNO+$limit;

		if(count($htmldata)>0){
			$this->jsonarray['code']		= 200;
			$this->jsonarray['pageLimit']	= $limit;
			$this->jsonarray['total'] 		= count($htmldata);
			$htmldata = array_slice($htmldata,$pageNO,$cout);
		}else{
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//echo '<pre>';print_r($htmldata);exit;
		foreach ($htmldata as $key=>$html){
			$titletag 		= isset($html->title) ? $html->title : "";
			//change titletag for all activities for version > 2.8
			$titletag 		= $this->jomHelper->getTitleTag($html);
			$likeAllowed 	= $html->likeAllowed=="" ? 0 : 1;
			$commentAllowed = $html->commentAllowed=="" ? 0 : 1;
			$isCommunityAdmin	= intval(COwnerHelper::isCommunityAdmin($this->IJUserID));

			if($type=='activity' or $type=='wall'){
				$createdate = JFactory::getDate($html->created);
				$createdTime = CTimeHelper::timeLapse($createdate);
			}else{
				$createdTime = $html->created;
			}
			$createdTime = (!empty($createdTime))?$createdTime:'';
			if($html->type=='title'){
				$this->jsonarray['title'][$inc] = strip_tags($titletag);
			}else{
				$this->jsonarray['update'][$inc]['id'] = $html->id;

				// add user detail
				$usr = $this->jomHelper->getUserDetail($html->actor);
				$this->jsonarray['update'][$inc]['user_detail']['user_id'] 		= $usr->id;
				$this->jsonarray['update'][$inc]['user_detail']['user_name'] 	= $usr->name;
				$this->jsonarray['update'][$inc]['user_detail']['user_avatar'] 	= $usr->avatar;
				$this->jsonarray['update'][$inc]['user_detail']['user_profile'] = $usr->profile;

				// add content data
				$html->content = $this->jomHelper->addAudioFile($html->content);
				$this->jsonarray['update'][$inc]['content'] = strip_tags($html->content);
				//add video detail
				if($html->app=='videos'){
					$this->jsonarray['update'][$inc]['content_data'] = $videotag;
				}
			    //set title as a content
				if($html->app=='photos' && $html->content == ''){
					$this->jsonarray['update'][$inc]['content'] = strip_tags($html->title);
				}


				$this->jsonarray['update'][$inc]['date'] 			= $createdTime;
				$this->jsonarray['update'][$inc]['likeAllowed'] 	= $likeAllowed;
				$this->jsonarray['update'][$inc]['likeCount'] 		= intval($html->likeCount);
				$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked==1) ? 1 : 0 ;
				$this->jsonarray['update'][$inc]['commentAllowed']	= $commentAllowed;
				$this->jsonarray['update'][$inc]['commentCount'] 	= intval($html->commentCount);

				$query="SELECT comment_type,like_type
						FROM #__community_activities
						WHERE id={$html->id}";
				$this->db->setQuery($query);
				$extra=$this->db->loadObject();

				$this->jsonarray['update'][$inc]['liketype'] 		= $extra->like_type;
				$this->jsonarray['update'][$inc]['commenttype'] 	= $extra->comment_type;

				switch($html->app){
					case 'friends':
						$this->jsonarray['update'][$inc]['type'] = 'friends';
						$actor = CFactory::getUser($html->actor);
						$srch = array('{actor}',"&#9658;","&quot;");
						$rplc = array($actor->getDisplayName(),"►","\"");
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));

						$usrtar = $this->jomHelper->getUserDetail($html->target);
						$this->jsonarray['update'][$inc]['content_data']['user_id'] 		= $usrtar->id;
						$this->jsonarray['update'][$inc]['content_data']['user_name'] 		= $usrtar->name;
						$this->jsonarray['update'][$inc]['content_data']['user_avatar'] 	= $usrtar->avatar;
						$this->jsonarray['update'][$inc]['content_data']['user_profile']	= $usrtar->profile;
						$this->jsonarray['update'][$inc]['deleteAllowed']=( ($html->actor == $this->my->id) || $isCommunityAdmin) && ($this->my->id != 0);//intval($this->my->authorise('community.delete','activities.'.$html->id));
						break;

					case 'videos':
					case 'system.videos.popular':
						if($html->app == 'videos'){
							$this->jsonarray['update'][$inc]['type'] = 'videos';

							$content_id = $this->getActivityContentID($html->id);
							$video	=& JTable::getInstance( 'Video' , 'CTable' );
							$video->load($content_id);
							$videos = array();
							$videos[] = $video;
						}else{
							$model		= CFactory::getModel( 'videos');
							$videos		= $model->getPopularVideos( 3 );
						}

						if($video->id){
							if ($video->storage == 's3') {
								$s3BucketPath = $this->config->get ( 'storages3bucket' );
								if (! empty ( $s3BucketPath ))
									$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
							}else{
								$p_url = JURI::base();
							}

							if ($video->type == 'file') {
								$ext = JFile::getExt ( $video->path );

								if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
									$video_file = JURI::root () . $video->path;
								} else {
									$lastpos = strrpos ( $video->path, '.' );
									$vname = substr ( $video->path, 0, $lastpos );
									$video_file = $p_url . $vname . ".mp4";
								}
							}else{
								$video_file = $video->path;
							}

							$this->jsonarray['update'][$inc]['content_data']['id']				= $video->id;
							$this->jsonarray['update'][$inc]['content_data']['caption']			= $video->title;
							$this->jsonarray['update'][$inc]['content_data']['thumb']			= ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
							$this->jsonarray['update'][$inc]['content_data']['url'] 			= $video_file;
							$this->jsonarray['update'][$inc]['content_data']['description'] 	= $video->description;
							$this->jsonarray['update'][$inc]['content_data']['date'] 			= $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
							$this->jsonarray['update'][$inc]['content_data']['location'] 		= $video->location;
							$this->jsonarray['update'][$inc]['content_data']['permissions'] 	= $video->permissions;
							$this->jsonarray['update'][$inc]['content_data']['categoryId']		= $video->category_id;

							if($type=='group'){
								$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}

							//likes
							$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
							$this->jsonarray['update'][$inc]['content_data']['likes']			= $likes->likes;
							$this->jsonarray['update'][$inc]['content_data']['dislikes']		= $likes->dislikes;
							$this->jsonarray['update'][$inc]['content_data']['liked']			= $likes->liked;
							$this->jsonarray['update'][$inc]['content_data']['disliked'] 		= $likes->disliked;

							//comments
							$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
							$this->jsonarray['update'][$inc]['content_data']['commentCount']	= $count;
							$this->jsonarray['update'][$inc]['content_data']['deleteAllowed']	= intval ( ($this->IJUserID == $video->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );

							if (SHARE_VIDEOS) {
								$this->jsonarray['update'][$inc]['content_data']['shareLink'] 	= JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
							}

							$query="SELECT count(id)
									FROM #__community_videos_tag
									WHERE `videoid`={$video->id}";
							$this->db->setQuery($query);
							$this->jsonarray['update'][$inc]['content_data']['tags'] 			= $this->db->loadResult();

							if($video->groupid){
								$this->getGroupData($video->groupid,$this->jsonarray['update'][$inc]['group_data']);

								$srch = array("&#9658;","&quot;","► ".$usr->name);
								$rplc = array("►","\"","► ".$this->jsonarray['update'][$inc]['group_data']['title']);
								$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}else{
								$srch = array("&#9658;","&quot;");
								$rplc = array("►","\"");
								$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}
							$this->jsonarray['update'][$inc]['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						}

						/*$vinc = 0;
						foreach ($videos as $video){
							if($video->id){
								if ($video->storage == 's3') {
									$s3BucketPath = $this->config->get ( 'storages3bucket' );
									if (! empty ( $s3BucketPath ))
										$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
								}else{
									$p_url = JURI::base();
								}

								if ($video->type == 'file') {
									$ext = JFile::getExt ( $video->path );

									if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
										$video_file = JURI::root () . $video->path;
									} else {
										$lastpos = strrpos ( $video->path, '.' );
										$vname = substr ( $video->path, 0, $lastpos );
										$video_file = $p_url . $vname . ".mp4";
									}
								}else{
									$video_file = $video->path;
								}

								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['id']				= $video->id;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['caption']			= $video->title;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['thumb']			= ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['url'] 			= $video_file;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['description'] 	= $video->description;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['date'] 			= $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['location'] 		= $video->location;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['permissions'] 	= $video->permissions;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['categoryId']		= $video->category_id;

								if($type=='group'){
									$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
								}

								//likes
								$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['likes']			= $likes->likes;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['dislikes']		= $likes->dislikes;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['liked']			= $likes->liked;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['disliked'] 		= $likes->disliked;

								//comments
								$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['commentCount']	= $count;
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['deleteAllowed']	= intval ( ($this->IJUserID == $video->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );

								if (SHARE_VIDEOS) {
									$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['shareLink'] 	= JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
								}

								$query="SELECT count(id)
										FROM #__community_videos_tag
										WHERE `videoid`={$video->id}";
								$this->db->setQuery($query);
								$this->jsonarray['update'][$inc]['content_data']['video'][$vinc]['tags'] 			= $this->db->loadResult();

								if($video->groupid){
									$this->getGroupData($video->groupid,$this->jsonarray['update'][$inc]['group_data']);

									$srch = array("&#9658;","&quot;","► ".$usr->name);
									$rplc = array("►","\"","► ".$this->jsonarray['update'][$inc]['group_data']['title']);
									$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
								}else{
									$srch = array("&#9658;","&quot;");
									$rplc = array("►","\"");
									$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
								}
								$this->jsonarray['update'][$inc]['deleteAllowed'] = intval(($this->IJUserID == $html->actor || (isset($gadmin) || isset($gsadmin))));
							}
							$vinc++;
						}*/
						break;

					case 'photos':
						$this->jsonarray['update'][$inc]['type'] = 'photos';
						$content_id = $this->getActivityContentID($html->id);

						$param = new CParameter($html->params);
						$photoid = $param->get('photoid', false);

						$photos	=& JTable::getInstance( 'photo' , 'CTable' );
						$photos->load($html->cid);

						$album	=& JTable::getInstance( 'Album' , 'CTable' );
						$album->load($photos->albumid);
						if($album->id){
							$photoModel = CFactory::getModel('photos');
							$photo=$photoModel->getPhoto($album->photoid);

							$this->jsonarray['update'][$inc]['content_data']['id']			= $album->id;
							$this->jsonarray['update'][$inc]['content_data']['name'] 		= $album->name;
							$this->jsonarray['update'][$inc]['content_data']['description']	= $album->description;
							$this->jsonarray['update'][$inc]['content_data']['permission'] 	= $album->permissions;
							$this->jsonarray['update'][$inc]['content_data']['thumb'] 		= JURI::base().$photo->thumbnail;
							$this->jsonarray['update'][$inc]['content_data']['date'] 		= $this->jomHelper->timeLapse($this->jomHelper->getDate($album->lastupdated));

							$this->jsonarray['update'][$inc]['content_data']['count'] 		= $photoModel->getTotalPhotos($album->id);
							$this->jsonarray['update'][$inc]['content_data']['location'] 	= $album->location;

							if($type=='group'){
								$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}

							//likes
							$likes = $this->jomHelper->getLikes ( 'album', $album->id, $this->IJUserID );
							$this->jsonarray['update'][$inc]['content_data']['likes'] 		= $likes->likes;
							$this->jsonarray['update'][$inc]['content_data']['dislikes'] 	= $likes->dislikes;
							$this->jsonarray['update'][$inc]['content_data']['liked'] 		= $likes->liked;
							$this->jsonarray['update'][$inc]['content_data']['disliked'] 	= $likes->disliked;

							//comments
							$count = $this->jomHelper->getCommentCount ( $album->id, 'albums' );
							$this->jsonarray['update'][$inc]['content_data']['commentCount']	= $count;
							$this->jsonarray['update'][$inc]['content_data']['shareLink'] 		= JURI::base () . "index.php?option=com_community&view=photos&task=album&albumid={$value->id}&userid={$value->creator}";

							$photos 		= $this->jomHelper->getAlbumContent($html);
							foreach($photos as $key=>$photo){
								$p_url = JURI::base();
								if ($photo->storage == 's3') {
									$s3BucketPath = $this->config->get ( 'storages3bucket' );
									if (! empty ( $s3BucketPath ))
										$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
								} else {
									if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
										$photo->image = $photo->original;
								}

								$this->jsonarray['update'][$inc]['image_data'][$key]['id']				= $photo->id;
								$this->jsonarray['update'][$inc]['image_data'][$key]['caption'] 		= $photo->caption;
								$this->jsonarray['update'][$inc]['image_data'][$key]['thumb'] 			= $p_url . $photo->thumbnail;
								$this->jsonarray['update'][$inc]['image_data'][$key]['url'] 			= $p_url . $photo->image;
								if (SHARE_PHOTOS == 1) {
									$this->jsonarray['update'][$inc]['image_data'][$key]['shareLink']	= JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$photo->creator}&albumid={$photo->albumid}#photoid={$photo->id}";
								}

								//likes
								$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
								$this->jsonarray['update'][$inc]['image_data'][$key]['likes'] 		= $likes->likes;
								$this->jsonarray['update'][$inc]['image_data'][$key]['dislikes'] 	= $likes->dislikes;
								$this->jsonarray['update'][$inc]['image_data'][$key]['liked'] 		= $likes->liked;
								$this->jsonarray['update'][$inc]['image_data'][$key]['disliked'] 	= $likes->disliked;

								//comments
								$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
								$this->jsonarray['update'][$inc]['image_data'][$key]['commentCount'] = $count;

								$query="SELECT count(id)
										FROM #__community_photos_tag
										WHERE `photoid`={$photo->id}";
								$this->db->setQuery($query);
								$count=$this->db->loadResult();
								$this->jsonarray['update'][$inc]['image_data'][$key]['tags'] = $count;
							}

							/*$str		= preg_match_all('|(#\w+=)(\d+)+|',$html->content,$match);
							if($str){
								foreach($match[2] as $key=>$value){
									$photo = $photoModel->getPhoto($value);
									$p_url = JURI::base ();
									if ($photo->storage == 's3') {
										$s3BucketPath = $this->config->get ( 'storages3bucket' );
										if (! empty ( $s3BucketPath ))
											$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
									} else {
										if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
											$photo->image = $photo->original;
									}
									$this->jsonarray['update'][$inc]['image_data'][$key]['id']				= $photo->id;
									$this->jsonarray['update'][$inc]['image_data'][$key]['caption'] 		= $photo->caption;
									$this->jsonarray['update'][$inc]['image_data'][$key]['thumb'] 			= $p_url . $photo->thumbnail;
									$this->jsonarray['update'][$inc]['image_data'][$key]['url'] 			= $p_url . $photo->image;
									if (SHARE_PHOTOS == 1) {
										$this->jsonarray['update'][$inc]['image_data'][$key]['shareLink']	= JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$photo->creator}&albumid={$photo->albumid}#photoid={$photo->id}";
									}

									//likes
									$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
									$this->jsonarray['update'][$inc]['image_data'][$key]['likes'] 		= $likes->likes;
									$this->jsonarray['update'][$inc]['image_data'][$key]['dislikes'] 	= $likes->dislikes;
									$this->jsonarray['update'][$inc]['image_data'][$key]['liked'] 		= $likes->liked;
									$this->jsonarray['update'][$inc]['image_data'][$key]['disliked'] 	= $likes->disliked;

									//comments
									$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
									$this->jsonarray['update'][$inc]['image_data'][$key]['commentCount'] = $count;

									$query="SELECT count(id)
											FROM #__community_photos_tag
											WHERE `photoid`={$photo->id}";
									$this->db->setQuery($query);
									$count=$this->db->loadResult();
									$this->jsonarray['update'][$inc]['image_data'][$key]['tags'] = $count;
								}
							}*/

							if($album->groupid){
								$groupModel = CFactory::getModel('groups');
								$isAdmin	= $groupModel->isAdmin( $this->IJUserID , $album->groupid);
								$this->jsonarray['update'][$inc]['content_data']['editAlbum'] 		= intval($isAdmin);
								$this->jsonarray['update'][$inc]['content_data']['deleteAllowed'] 	= intval ( ($this->IJUserID == $album->creator OR COwnerHelper::isCommunityAdmin ( $this->IJUserID ) OR $isAdmin) );
								CFactory::load('helpers', 'group');
								$albums				= $photoModel->getGroupAlbums($album->groupid);
								$allowManagePhotos	= CGroupHelper::allowManagePhoto($album->groupid);

								if( $allowManagePhotos  && $this->config->get('groupphotos') && $this->config->get('enablephotos') ) {
									$this->jsonarray['update'][$inc]['content_data']['uploadPhoto'] = ( $albums ) ? 1:  0;
								}else{
									$this->jsonarray['update'][$inc]['content_data']['uploadPhoto'] = 0;
								}

								$this->getGroupData($album->groupid,$this->jsonarray['update'][$inc]['group_data']);
								$srch = array("&#9658;","&quot;",$usr->name);
								$rplc = array("►","\"",$usr->name." ► ".$this->jsonarray['update'][$inc]['group_data']['title']);
								$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}else{
								$this->jsonarray['update'][$inc]['content_data']['deleteAllowed'] 	= intval ( ($this->IJUserID == $album->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
								$this->jsonarray['update'][$inc]['content_data']['editAlbum'] 	= intval($this->IJUserID == $album->creator);
								$srch = array("&#9658;","&quot;");
								$rplc = array("►","\"");
								$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}
							$this->jsonarray['update'][$inc]['deleteAllowed']=intval((($act->actor == $this->IJUserID) || COwnerHelper::isCommunityAdmin ( $this->IJUserID )) && ($this->IJUserID != 0));//intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($this->jsonarray['update'][$inc]);
							$inc--;
						}
						break;

					case 'system.photos.popular':
						$model		= CFactory::getModel( 'photos');
						$photos		= $model->getPopularPhotos( 9 , 0 );

						foreach($photos as $key=>$photo){
							$p_url = JURI::base();
							if ($photo->storage == 's3') {
								$s3BucketPath = $this->config->get ( 'storages3bucket' );
								if (! empty ( $s3BucketPath ))
									$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
							} else {
								if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
									$photo->image = $photo->original;
							}

							$this->jsonarray['update'][$inc]['image_data'][$key]['id']				= $photo->id;
							$this->jsonarray['update'][$inc]['image_data'][$key]['caption'] 		= $photo->caption;
							$this->jsonarray['update'][$inc]['image_data'][$key]['thumb'] 			= $p_url . $photo->thumbnail;
							$this->jsonarray['update'][$inc]['image_data'][$key]['url'] 			= $p_url . $photo->image;
							if (SHARE_PHOTOS == 1) {
								$this->jsonarray['update'][$inc]['image_data'][$key]['shareLink']	= JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$photo->creator}&albumid={$photo->albumid}#photoid={$photo->id}";
							}

							//likes
							$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
							$this->jsonarray['update'][$inc]['image_data'][$key]['likes'] 		= $likes->likes;
							$this->jsonarray['update'][$inc]['image_data'][$key]['dislikes'] 	= $likes->dislikes;
							$this->jsonarray['update'][$inc]['image_data'][$key]['liked'] 		= $likes->liked;
							$this->jsonarray['update'][$inc]['image_data'][$key]['disliked'] 	= $likes->disliked;

							//comments
							$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
							$this->jsonarray['update'][$inc]['image_data'][$key]['commentCount'] = $count;

							$query="SELECT count(id)
									FROM #__community_photos_tag
									WHERE `photoid`={$photo->id}";
							$this->db->setQuery($query);
							$count=$this->db->loadResult();
							$this->jsonarray['update'][$inc]['image_data'][$key]['tags'] = $count;
						}
						$this->jsonarray['update'][$inc]['titletag'] = $titletag;
						$this->jsonarray['update'][$inc]['type'] = '';
						break;

					case 'system.members.popular':
						$model		= CFactory::getModel( 'user' );
						$members	= $model->getPopularMember( 10 );
						$minc=0;
						foreach( $members as $user ){
							$this->jsonarray['update'][$inc]['member_data'][$minc]['username'] 	= $user->getDisplayName();
							$this->jsonarray['update'][$inc]['member_data'][$minc]['friendscount'] = JText::sprintf( (CStringHelper::isPlural($user->getFriendCount())) ? 'COM_COMMUNITY_FRIENDS_COUNT_MANY' : 'COM_COMMUNITY_FRIENDS_COUNT', $user->getFriendCount());
							$minc++;
						}
						$this->jsonarray['update'][$inc]['titletag'] = $titletag;
						$this->jsonarray['update'][$inc]['type'] = '';
						break;

					case 'system.groups.popular':
						$groupsModel = CFactory::getModel('groups');
						$activeGroup = $groupsModel->getMostActiveGroup();
						if( !is_null($activeGroup)) {
							$this->getGroupData($activeGroup->id,$this->jsonarray['update'][$inc]['group_data']);
//							$this->jsonarray['update'][$inc]['group_data']['groupname'] 	= $activeGroup->name;
//							$this->jsonarray['update'][$inc]['group_data']['groupmembercount'] = JText::sprintf( (CStringHelper::isPlural( $activeGroup->getMembersCount())) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY' : 'COM_COMMUNITY_GROUPS_MEMBER_COUNT' , $activeGroup->getMembersCount() );
						}
						$this->jsonarray['update'][$inc]['titletag'] = $titletag;
						$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id));
						break;
					case 'groups':
						/*$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));*/
						$this->jsonarray['update'][$inc]['titletag'] = $titletag;
						$content_id = $this->getActivityContentID($html->id);
						$this->jsonarray['update'][$inc]['type'] = 'group';
						$this->getGroupData($content_id,$this->jsonarray['update'][$inc]['content_data']);
						$param = new JRegistry($html->params);
						$action = $param->get('action');
						if($action == 'group.create'){
							$this->jsonarray['update'][$inc]['deleteAllowed'] = 0;
						}else{
							$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id));
						}
						break;

					case 'groups.bulletin':
						$this->jsonarray['update'][$inc]['type'] = 'announcement';
						$content_id = $this->getActivityContentID($html->id);

						$bulletin =& JTable::getInstance( 'Bulletin' , 'CTable' );
						$bulletin->load($content_id);
						if($bulletin->id){
							$this->jsonarray['update'][$inc]['content_data']['id']				= $bulletin->id;
							$this->jsonarray['update'][$inc]['content_data']['title']			= $bulletin->title;
							$this->jsonarray['update'][$inc]['content_data']['message']			= strip_tags($bulletin->message);
							$usr = $this->jomHelper->getUserDetail($bulletin->created_by);
							$this->jsonarray['update'][$inc]['content_data']['user_id']			= $usr->id;
							$this->jsonarray['update'][$inc]['content_data']['user_name']		= $usr->name;
							$this->jsonarray['update'][$inc]['content_data']['user_avatar']		= $usr->avatar;
							$this->jsonarray['update'][$inc]['content_data']['user_profile']	= $usr->profile;
							$format = "%A, %d %B %Y";
							$this->jsonarray['update'][$inc]['content_data']['date']			= CTimeHelper::getFormattedTime($bulletin->date, $format);
							$params = new CParameter($bulletin->params);
							$this->jsonarray['update'][$inc]['content_data']['filePermission']	= $params->get('filepermission-member');
							if(SHARE_GROUP_BULLETIN==1){
								$this->jsonarray['update'][$inc]['content_data']['shareLink']	= JURI::base()."index.php?option=com_community&view=groups&task=viewbulletin&groupid={$result->groupid}&bulletinid={$result->id}";
							}
							if($type=='group'){
								$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}
							$query="SELECT count(id)
									FROM #__community_files
									WHERE `groupid`={$bulletin->groupid}
									AND `bulletinid`={$bulletin->id}";
							$this->db->setQuery($query);
							$this->jsonarray['update'][$inc]['content_data']['files']			= $this->db->loadResult();

							// group data.
							$this->getGroupData($bulletin->groupid,$this->jsonarray['update'][$inc]['group_data']);
							$srch = array("&#9658;","&quot;");
							$rplc = array("►","\"");
							$this->jsonarray['update'][$inc]['titletag'] = $titletag;//$usr->name." ► ".$this->jsonarray['update'][$inc]['group_data']['title']."\n".str_replace($srch,$rplc,strip_tags($titletag));
							$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($this->jsonarray['update'][$inc]);
							$inc--;
						}
						break;

					case 'groups.discussion.reply':
					case 'groups.discussion':
						$content_id = $this->getActivityContentID($html->id);

						$discussion =& JTable::getInstance( 'Discussion' , 'CTable' );
						$discussion->load($content_id);

						if($discussion->id){
							$this->jsonarray['update'][$inc]['type'] = 'discussion';
							$this->jsonarray['update'][$inc]['content_data']['id']				= $discussion->id;
							$this->jsonarray['update'][$inc]['content_data']['title']			= $discussion->title;
							$this->jsonarray['update'][$inc]['content_data']['message'] 		= strip_tags($discussion->message);
							$usr = $this->jomHelper->getUserDetail($discussion->creator);
							$this->jsonarray['update'][$inc]['content_data']['user_id'] 		= $usr->id;
							$this->jsonarray['update'][$inc]['content_data']['user_name'] 		= $usr->name;
							$this->jsonarray['update'][$inc]['content_data']['user_avatar'] 	= $usr->avatar;
							$this->jsonarray['update'][$inc]['content_data']['user_profile'] 	= $usr->profile;

							$format = "%A, %d %B %Y";
							$this->jsonarray['update'][$inc]['content_data']['date'] 			= CTimeHelper::getFormattedTime($discussion->lastreplied, $format);
							$this->jsonarray['update'][$inc]['content_data']['isLocked']		= $discussion->lock;

							if($type=='group'){//echo '<pre>';print_r($html);exit;
								$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}

							$wallModel   =& CFactory::getModel( 'wall' );
							$wallContents = $wallModel ->getPost('discussions' ,$discussion->id,9999999,0);
							$this->jsonarray['update'][$inc]['content_data']['topics']=count($wallContents);
							$params = new CParameter($discussion->params);
							$this->jsonarray['update'][$inc]['content_data']['filePermission']	= $params->get('filepermission-member');
							if(SHARE_GROUP_DISCUSSION==1){
								$this->jsonarray['update'][$inc]['content_data']['shareLink']	= JURI::base()."index.php?option=com_community&view=groups&task=viewdiscussion&groupid={$discussion->groupid}2&topicid={$group->id}";
							}
							$query="SELECT count(id)
									FROM #__community_files
									WHERE `groupid`={$discussion->groupid}
									AND `discussionid`={$discussion->id}";
							$this->db->setQuery($query);
							$this->jsonarray['update'][$inc]['content_data']['files']=$this->db->loadResult();

							// group data.
							$this->getGroupData($discussion->groupid,$this->jsonarray['update'][$inc]['group_data']);
							$srch = array("&#9658;","&quot;");
							$rplc = array("►","\"");
							$this->jsonarray['update'][$inc]['titletag'] = $titletag;//$usr->name." ► ".$this->jsonarray['update'][$inc]['group_data']['title']."\n".str_replace($srch,$rplc,strip_tags($titletag));
							$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($this->jsonarray['update'][$inc]);
							$inc--;
						}
						break;

					case 'groups.wall':
						$this->jsonarray['update'][$inc]['type']			= 'groups.wall';
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$this->jsonarray['update'][$inc]['titletag'] 		= str_replace($srch,$rplc,strip_tags($titletag));
						$this->jsonarray['update'][$inc]['id'] 				= $html->id;
						$this->jsonarray['update'][$inc]['date'] 			= $createdTime;
						$this->jsonarray['update'][$inc]['likeAllowed'] 	= $likeAllowed;
						$this->jsonarray['update'][$inc]['commentAllowed'] 	= $commentAllowed;
						$this->jsonarray['update'][$inc]['likeCount'] 		= intval($html->likeCount);
						$this->jsonarray['update'][$inc]['commentCount'] 	= intval($html->commentCount);
						if($type=='group'){
							$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
						}else{
							$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked==1) ? 1 : 0 ;
						}
						$group =& JTable::getInstance( 'Group' , 'CTable' );
						$group->load($html->groupid);
						$this->jsonarray['update'][$inc]['deleteAllowed'] 	= intval($this->IJUserID==$html->actor OR COwnerHelper::isCommunityAdmin($this->IJUserID ) OR $group->isAdmin($this->IJUserID	));
						$this->jsonarray['update'][$inc]['liketype'] 		= 'groups.wall';
						$this->jsonarray['update'][$inc]['commenttype'] 	= 'groups.wall';

						// event data
						$this->getGroupData($group->id,$this->jsonarray['update'][$inc]['group_data']);
						$this->jsonarray['update'][$inc]['titletag'] = $usr->name." ► ".$this->jsonarray['update'][$inc]['group_data']['title']."\n".str_replace("&#9658;","►",str_replace("&quot;","\"",(strip_tags($titletag))));
						$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id, $group));
						break;

					case 'events':
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$this->jsonarray['update'][$inc]['likeAllowed'] 	= 0;
						$this->jsonarray['update'][$inc]['commentAllowed'] 	= 0;
						$this->jsonarray['update'][$inc]['content'] 		= '';
						$this->jsonarray['update'][$inc]['type'] = 'event';
						$content_id = $this->getActivityContentID($html->id);

						// event data
						$this->getEventData($content_id,$this->jsonarray['update'][$inc]['content_data']);
						$param = new JRegistry($html->params);
						$action = $param->get('action');
						if($action == 'events.create'){
							$this->jsonarray['update'][$inc]['deleteAllowed'] = 0;
						}else{
							$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id));
						}

						break;

					case 'events.wall':
						$this->jsonarray['update'][$inc]['type']			= 'events.wall';
						$this->jsonarray['update'][$inc]['id'] 				= $html->id;
						$this->jsonarray['update'][$inc]['titletag'] 		= str_replace("&#9658;","►",str_replace("&quot;","\"",(strip_tags($titletag))));
						$this->jsonarray['update'][$inc]['date'] 			= $createdTime;
						$this->jsonarray['update'][$inc]['likeAllowed'] 	= $likeAllowed;
						$this->jsonarray['update'][$inc]['commentAllowed'] 	= $commentAllowed;
						$this->jsonarray['update'][$inc]['likeCount'] 		= intval($html->likeCount);
						$this->jsonarray['update'][$inc]['commentCount'] 	= intval($html->commentCount);
						if($type=='event'){
							$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
						}else{
							$this->jsonarray['update'][$inc]['liked'] 			= ($html->userLiked==1) ? 1 : 0 ;
						}
						$event =& JTable::getInstance( 'Event' , 'CTable' );
						$event->load($html->eventid);
						$this->jsonarray['update'][$inc]['deleteAllowed'] 	= intval($this->IJUserID==$html->actor OR COwnerHelper::isCommunityAdmin($this->IJUserID ) OR $event->isAdmin($this->IJUserID	));
						$this->jsonarray['update'][$inc]['liketype'] 		= 'events.wall';
						$this->jsonarray['update'][$inc]['commenttype'] 	= 'events.wall';

						// event data
						$this->getEventData($event->id,$this->jsonarray['update'][$inc]['event_data']);
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$this->jsonarray['update'][$inc]['titletag'] = $usr->name." ► ".$this->jsonarray['update'][$inc]['event_data']['title']."\n".str_replace($srch,$rplc,strip_tags($titletag));
						$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id, $event));
						break;

					case 'profile':
						$this->jsonarray['update'][$inc]['type'] = 'profile';
						$this->jsonarray['update'][$inc]['deleteAllowed'] = intval($this->my->authorise('community.delete','activities.'.$html->id));
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						break;


					case 'profile.avatar.upload':
						$params = new CParameter($html->params);
						$this->jsonarray['update'][$inc]['image_data']['url'] = JURI::base().$params->get('attachment');
						$actor = CFactory::getUser($html->actor);
						$srch = array('{actor}',"&#9658;","&quot;");
						$rplc = array($actor->getDisplayName(),"►","\"");
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$this->jsonarray['update'][$inc]['type'] = 'profile.avatar.upload';
						$this->jsonarray['update'][$inc]['deleteAllowed']=intval((($html->actor == $this->my->id) || $isCommunityAdmin) && ($this->my->id != 0));//intval($this->my->authorise('community.delete','activities.'.$html->id));

						break;

					case 'cover.upload':
						$params = new CParameter($html->params);
						$this->jsonarray['update'][$inc]['image_data']['url'] = JURI::base().$params->get('attachment');
						$actor = CFactory::getUser($html->actor);
						$srch = array('{actor}',"&#9658;","&quot;");
						$rplc = array($actor->getDisplayName(),"►","\"");

						$str1 = "COM_COMMUNITY_PHOTOS_COVER_TYPE_LINK";
						$str2 = ", ".$html->appTitle;
						$titletag =  str_replace($str1,$str2,$titletag);
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$this->jsonarray['update'][$inc]['type'] = 'cover.upload';
						$this->jsonarray['update'][$inc]['deleteAllowed']=intval((($html->actor == $this->my->id) || $isCommunityAdmin) && ($this->my->id != 0));//intval($this->my->authorise('community.delete','activities.'.$html->id));

						if($html->eventid)
						{
							// event data
							$this->getEventData($html->eventid,$this->jsonarray['update'][$inc]['event_data']);
						}
						if($html->groupid)
						{
							// group data
							$this->getGroupData($html->groupid,$this->jsonarray['update'][$inc]['group_data']);
						}
						break;

					default:
//						$srch = array("&#9658;","&quot;");
//						$rplc = array("►","\"");
						$actor = CFactory::getUser($html->actor);
						$srch = array('{actor}',"&#9658;","&quot;");
						$rplc = array($actor->getDisplayName(),"►","\"");
						$this->jsonarray['update'][$inc]['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$this->jsonarray['update'][$inc]['type'] = '';
						break;
				}
			}
			$i++;
			$inc++;
		}
		return $this->jsonarray;
	}

	// called by wall,
	// returns content id of the activity.
	private function  getActivityContentID($id){
		$query="SELECT cid
				FROM #__community_activities
				WHERE `id`='{$id}'";
		$this->db->setQuery($query);
		return $this->db->loadResult();
	}

	// called by wall,
	// get group data
	private function getGroupData($id,&$result){
		CFactory::load( 'helpers' , 'owner' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load($id);

		$result['id']				= $group->id;
		$result['title']			= $group->name;
		$result['description']		= $group->description;

		if($this->config->get('groups_avatar_storage') == 'file'){
			$p_url	= JURI::base();
		}else{
			$s3BucketPath	= $this->config->get('storages3bucket');
			if(!empty($s3BucketPath))
				$p_url	= 'http://'.$s3BucketPath.'.s3.amazonaws.com/';
			else
				$p_url	= JURI::base();
		}
		$result['avatar']			= ($group->avatar=="") ? JURI::base().'components'.'com_community'.'assets'.'group.png' : $p_url.$group->avatar;
		$result['members']			= $group->membercount;
		$result['walls']			= $group->wallcount;
		$result['discussions']		= $group->discusscount;
		$result['isAdmin']			= intval($group->isAdmin($this->IJUserID));
		$result['isCommunityAdmin']	= intval(COwnerHelper::isCommunityAdmin($this->IJUserID));
	}

	// called by wall
	// get event data
	private function getEventData($id,&$result){
		$event			=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load($id);

		$format	=   ($this->config->get('eventshowampm')) ?  JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_12H') : JText::_('COM_COMMUNITY_DATE_FORMAT_LC2_24H');

		$result['id'] 			= $event->id;
		$result['title'] 		= $event->title;
		$result['groupid'] 		= intval($event->contentid);
		$result['location'] 	= $event->location;
		$result['startdate'] 	= CTimeHelper::getFormattedTime($event->startdate, $format);
		$result['enddate'] 		= CTimeHelper::getFormattedTime($event->enddate, $format);
		$result['date'] 		= strtoupper(CEventHelper::formatStartDate($event, $this->config->get('eventdateformat')));
		$result['avatar'] 		= ($event->avatar != '')? JURI::base ().$event->avatar : JURI::base ().'components'.'com_community'.'assets'.'event_thumb.png';
		$result['past'] 		= (strtotime($event->enddate)<time()) ? 1 : 0;
		$result['ongoing']	 	= (strtotime($event->startdate)<=time() and strtotime($event->enddate)>time()) ? 1 : 0;
		$result['confirmed']	= $event->confirmedcount;
	}


	private function _getData( $options ){
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
						if ( JFile::exists(JPATH_ROOT . '/components/com_community/templates' .'/'. $this->config->get('template') . '/images/favicon' .'/'.'groups.png') ){
							$favicon = JURI::root(). 'components/com_community/templates/'.$config->get('template').'/images/favicon/groups.png';
						}

					}

					// Favicon override with event image for known event stream data
					// This would override group favicon
					if( $oRow->eventid ){
						// check if the image icon exist in template folder
						$favicon = JURI::root(). 'components/com_community/assets/favicon/events.png';
						if ( JFile::exists(JPATH_ROOT . '/components/com_community/templates' .'/'. $this->config->get('template') . '/images/favicon' .'/'.'groups.png') ){
							$favicon = JURI::root(). 'components/com_community/templates/'.$this->config->get('template').'/images/favicon/events.png';
						}
					}

					// If it is not group or event stream, use normal favicon search
					if( !($oRow->groupid || $oRow->eventid) ){
						// check if the image icon exist in template folder
						if ( JFile::exists(JPATH_ROOT . '/components/com_community/templates' .'/'. $this->config->get('template') . '/images/favicon' .'/'. $oRow->app.'.png') ){
							$favicon = JURI::root(). 'components/com_community/templates/'.$this->config->get('template').'/images/favicon/'.$oRow->app.'.png';
						}else{
							// check if the image icon exist in asset folder
							if ( JFile::exists(JPATH_ROOT . '/components/com_community/assets/favicon' .'/'. $oRow->app.'.png') ){
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
				$createdTime = (!empty($createdTime))?$createdTime:'';

				$act->created 			= $createdTime;
				$act->createdDate 		= (C_JOOMLA_15==1)?$date->toFormat(JText::_('DATE_FORMAT_LC2')):$date->Format(JText::_('DATE_FORMAT_LC2'));
				$act->app 				= $oRow->app;
				$act->eventid			= $oRow->eventid;
				$act->groupid			= $oRow->groupid;
				$act->group_access		= $oRow->group_access;
				$act->event_access		= $oRow->event_access;
				$act->params			= $oRow->params;
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
	}


	private function _appLink($name, $actor = 0, $userid = 0, $title = ''){
		if(empty($name))
			return '';

		$appModel	= CFactory::getModel('apps');
		$url = '';

		// @todo: check if this app exist
		if(true) {
			// if no target specified, we use actor
			if($userid == 0)
				$userid= $actor;

			if( $userid != 0
				&& $name != 'profile'
				&& $name != 'news_feed'
				&& $name != 'photos'
				&& $name != 'friends')
				{

				$url = CUrlHelper::userLink($userid) . '#app-' . $name;
				if($title == JText::_('COM_COMMUNITY_ACTIVITIES_APPLICATIONS_REMOVED')){
					$url = $appModel->getAppTitle($name);
				}else{
					$url = '<a href="' . $url .'" >'. $appModel->getAppTitle($name) . '</a>';
				}
			}else{
				$url = $appModel->getAppTitle($name);
			}
		}
		return $url;
	}


	private function _targetLink( $id, $onApp=false ){
		static $instances = array();

		if( empty($instances[$id]) ){
			$my			=& JFactory::getUser($this->IJUserID);
			$linkName	= ($id==0)? false : true;
			$user 	= CFactory::getUser($id);
			$name = $user->getDisplayName();

			// Wrap the name with link to his/her profile
			$html = $name;
			if($linkName)
				$html = '<a href="'.CUrlHelper::userLink($id).'">'.$name.'</a>';

			$instances[$id] = $html;
		}
		return $instances[$id];
	}

	private function _actorLink($id){
		static $instances = array();

		if( empty($instances[$id])){
			$my			=& JFactory::getUser($this->IJUserID);
			$format		= JRequest::getVar('format', 'html', 'REQUEST');
			$linkName	= ($id==0)? false : true;
			$user		= CFactory::getUser($id);
			$name = $user->getDisplayName();

			// Wrap the name with link to his/her profile
			$html		= $name;
			if($linkName){
				$html = '<a class="actor-link" href="'.CUrlHelper::userLink($id).'">'.$name.'</a>';
			}
			$instances[$id] = $html;
		}
		return $instances[$id];
	}


	private function _formatTitle($row){
		// We will need to replace _QQQ_ here since
		// CKses will reformat the link
		$row->title = CTemplate::quote($row->title);

		// If the title start with actor's name, and it is a normal status, remove them!
		// Otherwise, leave it as the old style
		if(strpos( $row->title, '<a class="actor-link"' ) !== false && isset($row->actor) && ($row->app == 'profile')){
			$pattern = '/(<a class="actor-link".*?' . '>.*?<\/a>)/';
			$row->title = preg_replace($pattern, '', $row->title);
		}

		return CKses::kses($row->title, CKses::allowed() );
	}


	/**
	 * @uses Add wall
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"addWall",
	 * 		"taskData":{
	 * 			"message":"message",
	 * 			"uniqueID":"uniqueID", // userid: for status and wall entry or wallid: for comment.
	 * 			"privacy":"privacy",
	 * 			"comment":"0/1" // 0: if wall post, 1: if comment.
	 * 		}
	 * 	}
	 *
	 */
	function add(){
		$message	= IJReq::getTaskData('message');

		$audiofileupload = $this->jomHelper->uploadAudioFile();
		if($audiofileupload){
			$message = $message.$audiofileupload['voicetext'];
		}

		$uniqueID 	= IJReq::getTaskData('uniqueID',$this->IJUserID,'int');
		$privacy	= IJReq::getTaskData('privacy',0,'int');
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
			//only update user status if share messgage is on his profile
			if (COwnerHelper::isMine($this->IJUserID,$uniqueID)){
				//save the message
				$status = new CommunityModelStatus();
				$status->update($this->IJUserID, $rawMessage, $privacy );

				//set user status for current session.
				$today		=& JFactory::getDate();
				$message2	= (empty($message)) ? ' ' : $message;
				$this->my->set( '_status' , $rawMessage );
				$this->my->set( '_posted_on' , $today->toSQL());

				// Order of replacement
				$order   = array("\r\n", "\n", "\r");
				$replace = '<br />';

				// Processes \r\n's first so they aren't converted twice.
				$messageDisplay = str_replace($order, $replace, $message);
				$messageDisplay = CKses::kses($messageDisplay, CKses::allowed() );

				if($audiofileupload){
					$this->jsonarray['voice']=$audiofileupload['voice3gppath'];
				}
			}

			//push to activity stream
			$privacyParams	= $this->my->getParams();
			$act = new stdClass();
			$act->cmd			= 'profile.status.update';
			$act->actor			= $this->IJUserID;
			$act->target		= $uniqueID;
			$act->title			= $message;
			$act->content		= '';
			$act->app			= 'profile';
			$act->cid			= $this->IJUserID;
			$act->access		= $privacy;
			$act->comment_id	= CActivities::COMMENT_SELF;
			$act->comment_type	= 'profile.status';
			$act->like_id		= CActivities::LIKE_SELF;
			$act->like_type		= 'profile.status';

			CActivityStream::add($act);
			CUserPoints::assignPoint('profile.status.update');

			$recipient = CFactory::getUser($uniqueID);
			$params			= new CParameter( '' );
			$params->set( 'actorName' , $this->my->getDisplayName() );
			$params->set( 'recipientName', $recipient->getDisplayName());
			$params->set('url',CUrlHelper::userLink($act->target, false));
			$params->set('message',$message);

			CNotificationLibrary::add( 'profile_status_update' , $this->IJUserID , $uniqueID , JText::sprintf('COM_COMMUNITY_FRIEND_WALL_POST', $this->my->getDisplayName() ) , '' , 'wall.post' , $params);
			//Send pushnotification
			// get user push notification params and user device token and device type
			$query="SELECT `jomsocial_params`,`device_token`,`device_type`
					FROM #__ijoomeradv_users
					WHERE `userid`={$recipient->id}";
			$this->db->setQuery($query);
			$puser=$this->db->loadObject();
			$ijparams = new CParameter($puser->jomsocial_params);

			//change for id based push notification
			$pushOptions['detail']=array();
			$pushOptions = gzcompress(json_encode($pushOptions));

			$usr=$this->jomHelper->getUserDetail($this->my->id);
			$match = array('{actor}','{stream}');
			$replace = array($usr->name,JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
			$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'));
			$obj = new stdClass();
			$obj->id 		= null;
			$obj->detail 	= $pushOptions;
			$obj->tocount  	= count($recipient->id);
			$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
			if($obj->id){
				$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
				$this->jsonarray['pushNotificationData']['to'] 		= $recipient->id;
				$this->jsonarray['pushNotificationData']['message'] = $message;
				$this->jsonarray['pushNotificationData']['type'] 	= 'profile';
				$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_profile_status_update';
			}
		}

		$this->jsonarray['code']=200;

		return $this->jsonarray;
	}


	/**
	 * this function is used by add function to add comment to wall list
	 *
	 * this function is copied from com_community/controllers/system.php and edited.
	 *
	 */
	private function addComment($actid, $comment){
		$filter		= JFilterInput::getInstance();
		$actid		= $filter->clean($actid, 'int');
		$comment	= $filter->clean($comment, 'string');

		$wallModel = CFactory::getModel( 'wall' );
		CFactory::load('libraries', 'wall');
		CFactory::load('libraries', 'activities');
		CFactory::load('helpers', 'friends');
		CFactory::load('helper', 'owner');

		// Pull the activity record and find out the actor
		// only allow comment if the actor is a friend of current user
		$act =& JTable::getInstance('Activity', 'CTable');
		$act->load($actid);

		//who can add comment
		$obj = new stdClass();

		if($act->groupid > 0){
			$obj	=& JTable::getInstance( 'Group' , 'CTable' );
			$obj->load( $act->groupid );
		}else if($act->eventid > 0){
			$obj	=& JTable::getInstance( 'Event' , 'CTable' );
			$obj->load( $act->eventid );
		}

		if($this->my->authorise('community.add','activities.comment.'.$act->actor, $obj) ){

			$table =& JTable::getInstance('Wall', 'CTable');
			$table->type 		= $act->comment_type;
			$table->contentid 	= $act->comment_id;
			$table->post_by 	= $this->my->id;
			$table->comment 	= $comment;
			$table->store();

			$comment = CWall::formatComment($table);
			//$objResponse->addScriptCall('joms.miniwall.insert', $actid, $comment);

			//notification for activity comment
			//case 1: user's activity
			if($act->groupid == 0 && $act->eventid == 0){
				CFactory::load( 'libraries' , 'notification' );
				$params		= new CParameter( '' );
				$url		= 'index.php?option=com_community&view=profile&userid=' . $act->actor.'&actid='.$actid;
				$params->set( 'message' , $table->comment );
				$params->set( 'url' , $url );
				$params->set( 'stream' , JText::_('COM_COMMUNITY_SINGULAR_STREAM') );
				$params->set( 'stream_url' , $url );

				if( $this->my->id != $act->actor ){
					CNotificationLibrary::add( 'profile_activity_add_comment' , $this->my->id , $act->actor , JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT' ) , '' , 'profile.activitycomment' , $params );

					// get user push notification params and user device token and device type
					$usr=$this->jomHelper->getUserDetail($this->IJUserID);
					$match = array('{actor}','{stream}');
					$replace = array($usr->name,JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
					$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_EMAIL_SUBJECT'));

					$memberslist = $act->actor;
					$configText = 'pushnotif_profile_activity_add_comment';
				}else{
					//for activity reply action
					//get relevent users in the activity
					$users = $wallModel->getAllPostUsers($act->comment_type,$act->id,$act->actor);
					if(!empty($users)){
						CNotificationLibrary::add( 'profile_activity_reply_comment' , $this->my->id , $users , JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_REPLY_EMAIL_SUBJECT' ) , '' , 'profile.activityreply' , $params );

						$usr=$this->jomHelper->getUserDetail($this->IJUserID);
						$match = array('{stream}');
						$replace = array(JText::_('COM_COMMUNITY_SINGULAR_STREAM'));
						$message = str_replace($match,$replace,JText::sprintf('COM_COMMUNITY_ACITIVY_WALL_REPLY_EMAIL_SUBJECT' ));
						$memberslist = implode(',',$users);
						$configText = 'pushnotif_profile_activity_reply_comment';
					}
				}

			//push notification detail
				$html = $act;

				CFactory::load( 'libraries' , 'activities' );
				$actModel = CFactory::getModel( 'Activities' );
				$html = $actModel->getActivities('', '', null, 1 , true , null , false,$actid);
				$html = $html[0];

				$titletag 		= isset($html->title) ? $html->title : "";
				$likeAllowed 	= intval($html->allowLike());
				$commentAllowed = intval($html->allowComment());
				$cadmin			= COwnerHelper::isCommunityAdmin($this->IJUserID);
				$pushcontentdata['id'] = $html->id;

				// add user detail
				$usr = $this->jomHelper->getUserDetail($html->actor);
				$pushcontentdata['user_detail']['user_id'] 		= $usr->id;
				$pushcontentdata['user_detail']['user_name'] 	= $usr->name;
				$pushcontentdata['user_detail']['user_avatar'] 	= $usr->avatar;
				$pushcontentdata['user_detail']['user_profile'] = $usr->profile;

				// add content data
				$pushcontentdata['content'] = strip_tags($html->content);
				//add video detail
				if($html->app=='videos'){
					$pushcontentdata['content_data'] = $videotag;
				}

				$pushcontentdata['date'] 			= $html->created;
				$pushcontentdata['likeAllowed'] 	= intval($html->allowLike());
				$pushcontentdata['likeCount'] 		= intval($html->getLikeCount());
				$pushcontentdata['liked'] 			= ($html->userLiked==1) ? 1 : 0 ;
				$pushcontentdata['commentAllowed']	= intval($html->allowComment());
				$pushcontentdata['commentCount'] 	= intval($html->getCommentCount());

				$query="SELECT comment_type,like_type
						FROM #__community_activities
						WHERE id={$html->id}";
				$this->db->setQuery($query);
				$extra=$this->db->loadObject();

				$pushcontentdata['liketype'] 		= $extra->like_type;
				$pushcontentdata['commenttype'] 	= $extra->comment_type;

				switch($html->app){
					case 'friends':
						$pushcontentdata['type'] = 'friends';

						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));

						$usrtar = $this->jomHelper->getUserDetail($html->target);
						$pushcontentdata['content_data']['user_id'] 		= $usrtar->id;
						$pushcontentdata['content_data']['user_name'] 		= $usrtar->name;
						$pushcontentdata['content_data']['user_avatar'] 	= $usrtar->avatar;
						$pushcontentdata['content_data']['user_profile']	= $usrtar->profile;
						$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						break;

					case 'videos':
						$pushcontentdata['type'] = 'videos';

						$content_id = $this->getActivityContentID($html->id);
						$video	=& JTable::getInstance( 'Video' , 'CTable' );
						$video->load($content_id);
						if($video->id){
							if ($video->storage == 's3') {
								$s3BucketPath = $this->config->get ( 'storages3bucket' );
								if (! empty ( $s3BucketPath ))
									$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
							}else{
								$p_url = JURI::base();
							}

							if ($video->type == 'file') {
								$ext = JFile::getExt ( $video->path );

								if ($ext == 'mov' && file_exists ( JPATH_SITE .'/'. $video->path )) {
									$video_file = JURI::root () . $video->path;
								} else {
									$lastpos = strrpos ( $video->path, '.' );
									$vname = substr ( $video->path, 0, $lastpos );
									$video_file = $p_url . $vname . ".mp4";
								}
							}else{
								$video_file = $video->path;
							}

							$pushcontentdata['content_data']['id']				= $video->id;
							$pushcontentdata['content_data']['caption']			= $video->title;
							$pushcontentdata['content_data']['thumb']			= ($video->thumb) ? $p_url . $video->thumb : JURI::base () . 'components/com_community/assets/video_thumb.png';
							$pushcontentdata['content_data']['url'] 			= $video_file;
							$pushcontentdata['content_data']['description'] 	= $video->description;
							$pushcontentdata['content_data']['date'] 			= $this->jomHelper->timeLapse ( $this->jomHelper->getDate ( $video->created ) );
							$pushcontentdata['content_data']['location'] 		= $video->location;
							$pushcontentdata['content_data']['permissions'] 	= $video->permissions;
							$pushcontentdata['content_data']['categoryId']		= $video->category_id;

							if($type=='group'){
								$pushcontentdata['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}

							//likes
							$likes = $this->jomHelper->getLikes ( 'videos', $video->id, $this->IJUserID );
							$pushcontentdata['content_data']['likes']			= $likes->likes;
							$pushcontentdata['content_data']['dislikes']		= $likes->dislikes;
							$pushcontentdata['content_data']['liked']			= $likes->liked;
							$pushcontentdata['content_data']['disliked'] 		= $likes->disliked;

							//comments
							$count = $this->jomHelper->getCommentCount ( $video->id, 'videos' );
							$pushcontentdata['content_data']['commentCount']	= $count;
							$pushcontentdata['content_data']['deleteAllowed']	= intval ( ($this->IJUserID == $video->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );

							if (SHARE_VIDEOS) {
								$pushcontentdata['content_data']['shareLink'] 	= JURI::base () . "index.php?option=com_community&view=videos&task=video&userid={$video->creator}&videoid={$video->id}";
							}

							$query="SELECT count(id)
									FROM #__community_videos_tag
									WHERE `videoid`={$video->id}";
							$this->db->setQuery($query);
							$pushcontentdata['content_data']['tags'] 			= $this->db->loadResult();

							if($video->groupid){
								$this->getGroupData($video->groupid,$pushcontentdata['group_data']);

								$srch = array("&#9658;","&quot;","► ".$usr->name);
								$rplc = array("►","\"","► ".$pushcontentdata['group_data']['title']);
								$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}else{
								$srch = array("&#9658;","&quot;");
								$rplc = array("►","\"");
								$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}
							$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($pushcontentdata);
							$inc--;
						}
						break;

					case 'photos':
						$pushcontentdata['type'] = 'photos';
						$content_id = $this->getActivityContentID($html->id);
						$album	=& JTable::getInstance( 'Album' , 'CTable' );
						$album->load($content_id);
						if($album->id){
							$photoModel = CFactory::getModel('photos');
							$photo=$photoModel->getPhoto($album->photoid);

							$pushcontentdata['content_data']['id']			= $album->id;
							$pushcontentdata['content_data']['name'] 		= $album->name;
							$pushcontentdata['content_data']['description']	= $album->description;
							$pushcontentdata['content_data']['permission'] 	= $album->permissions;
							$pushcontentdata['content_data']['thumb'] 		= JURI::base().$photo->thumbnail;
							$pushcontentdata['content_data']['date'] 		= $this->jomHelper->timeLapse($this->jomHelper->getDate($album->lastupdated));

							$pushcontentdata['content_data']['count'] 		= $photoModel->getTotalPhotos($album->id);
							$pushcontentdata['content_data']['location'] 	= $album->location;

							if($type=='group'){
								$pushcontentdata['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}

							//likes
							$likes = $this->jomHelper->getLikes ( 'album', $album->id, $this->IJUserID );
							$pushcontentdata['content_data']['likes'] 		= $likes->likes;
							$pushcontentdata['content_data']['dislikes'] 	= $likes->dislikes;
							$pushcontentdata['content_data']['liked'] 		= $likes->liked;
							$pushcontentdata['content_data']['disliked'] 	= $likes->disliked;

							//comments
							$count = $this->jomHelper->getCommentCount ( $album->id, 'albums' );
							$pushcontentdata['content_data']['commentCount']	= $count;
							$pushcontentdata['content_data']['shareLink'] 		= JURI::base () . "index.php?option=com_community&view=photos&task=album&albumid={$value->id}&userid={$value->creator}";

							$str		= preg_match_all('|(#\w+=)(\d+)+|',$html->content,$match);
							if($str){
								foreach($match[2] as $key=>$value){
									$photo = $photoModel->getPhoto($value);
									$p_url = JURI::base ();
									if ($photo->storage == 's3') {
										$s3BucketPath = $this->config->get ( 'storages3bucket' );
										if (! empty ( $s3BucketPath ))
											$p_url = 'http://' . $s3BucketPath . '.s3.amazonaws.com/';
									} else {
										if (! file_exists ( JPATH_SITE .'/'. $photo->image ))
											$photo->image = $photo->original;
									}
									$pushcontentdata['image_data'][$key]['id']				= $photo->id;
									$pushcontentdata['image_data'][$key]['caption'] 		= $photo->caption;
									$pushcontentdata['image_data'][$key]['thumb'] 			= $p_url . $photo->thumbnail;
									$pushcontentdata['image_data'][$key]['url'] 			= $p_url . $photo->image;
									if (SHARE_PHOTOS == 1) {
										$pushcontentdata['image_data'][$key]['shareLink']	= JURI::base () . "index.php?option=com_community&view=photos&task=photo&userid={$photo->creator}&albumid={$photo->albumid}#photoid={$photo->id}";
									}

									//likes
									$likes = $this->jomHelper->getLikes ( 'photo', $photo->id, $this->IJUserID );
									$pushcontentdata['image_data'][$key]['likes'] 		= $likes->likes;
									$pushcontentdata['image_data'][$key]['dislikes'] 	= $likes->dislikes;
									$pushcontentdata['image_data'][$key]['liked'] 		= $likes->liked;
									$pushcontentdata['image_data'][$key]['disliked'] 	= $likes->disliked;

									//comments
									$count = $this->jomHelper->getCommentCount ( $photo->id, 'photos' );
									$pushcontentdata['image_data'][$key]['commentCount'] = $count;

									$query="SELECT count(id)
											FROM #__community_photos_tag
											WHERE `photoid`={$photo->id}";
									$this->db->setQuery($query);
									$count=$this->db->loadResult();
									$pushcontentdata['image_data'][$key]['tags'] = $count;
								}
							}

							if($album->groupid){
								$groupModel = CFactory::getModel('groups');
								$isAdmin	= $groupModel->isAdmin( $this->IJUserID , $album->groupid);
								$pushcontentdata['content_data']['editAlbum'] 		= intval($isAdmin);
								$pushcontentdata['content_data']['deleteAllowed'] 	= intval ( ($this->IJUserID == $album->creator OR COwnerHelper::isCommunityAdmin ( $this->IJUserID ) OR $isAdmin) );
								CFactory::load('helpers', 'group');
								$albums				= $photoModel->getGroupAlbums($album->groupid);
								$allowManagePhotos	= CGroupHelper::allowManagePhoto($album->groupid);

								if( $allowManagePhotos  && $this->config->get('groupphotos') && $this->config->get('enablephotos') ) {
									$pushcontentdata['content_data']['uploadPhoto'] = ( $albums ) ? 1:  0;
								}else{
									$pushcontentdata['content_data']['uploadPhoto'] = 0;
								}

								$this->getGroupData($album->groupid,$pushcontentdata['group_data']);
								$srch = array("&#9658;","&quot;",$usr->name);
								$rplc = array("►","\"",$usr->name." ► ".$pushcontentdata['group_data']['title']);
								$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}else{
								$pushcontentdata['content_data']['deleteAllowed'] 	= intval ( ($this->IJUserID == $album->creator or COwnerHelper::isCommunityAdmin ( $this->IJUserID )) );
								$pushcontentdata['content_data']['editAlbum'] 	= intval($this->IJUserID == $album->creator);
								$srch = array("&#9658;","&quot;");
								$rplc = array("►","\"");
								$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
							}
							$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($pushcontentdata);
							$inc--;
						}
						break;

					case 'groups':
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$content_id = $this->getActivityContentID($html->id);
						$pushcontentdata['type'] = 'group';
						$this->getGroupData($content_id,$pushcontentdata['content_data']);
						$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						break;

					case 'groups.bulletin':
						$pushcontentdata['type'] = 'announcement';
						$content_id = $this->getActivityContentID($html->id);

						$bulletin =& JTable::getInstance( 'Bulletin' , 'CTable' );
						$bulletin->load($content_id);
						if($bulletin->id){
							$pushcontentdata['content_data']['id']				= $bulletin->id;
							$pushcontentdata['content_data']['title']			= $bulletin->title;
							$pushcontentdata['content_data']['message']			= strip_tags($bulletin->message);
							$usr = $this->jomHelper->getUserDetail($bulletin->created_by);
							$pushcontentdata['content_data']['user_id']			= $usr->id;
							$pushcontentdata['content_data']['user_name']		= $usr->name;
							$pushcontentdata['content_data']['user_avatar']		= $usr->avatar;
							$pushcontentdata['content_data']['user_profile']	= $usr->profile;
							$format = "%A, %d %B %Y";
							$pushcontentdata['content_data']['date']			= CTimeHelper::getFormattedTime($bulletin->date, $format);
							$params = new CParameter($bulletin->params);
							$pushcontentdata['content_data']['filePermission']	= $params->get('filepermission-member');
							if(SHARE_GROUP_BULLETIN==1){
								$pushcontentdata['content_data']['shareLink']	= JURI::base()."index.php?option=com_community&view=groups&task=viewbulletin&groupid={$result->groupid}&bulletinid={$result->id}";
							}
							if($type=='group'){
								$pushcontentdata['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}
							$query="SELECT count(id)
									FROM #__community_files
									WHERE `groupid`={$bulletin->groupid}
									AND `bulletinid`={$bulletin->id}";
							$this->db->setQuery($query);
							$pushcontentdata['content_data']['files']			= $this->db->loadResult();

							// group data.
							$this->getGroupData($bulletin->groupid,$pushcontentdata['group_data']);
							$srch = array("&#9658;","&quot;");
							$rplc = array("►","\"");
							$pushcontentdata['titletag'] = $usr->name." ► ".$pushcontentdata['group_data']['title']."\n".str_replace($srch,$rplc,strip_tags($titletag));
							$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($pushcontentdata);
							$inc--;
						}
						break;

					case 'groups.discussion.reply':
					case 'groups.discussion':
						$content_id = $this->getActivityContentID($html->id);

						$discussion =& JTable::getInstance( 'Discussion' , 'CTable' );
						$discussion->load($content_id);

						if($discussion->id){
							$pushcontentdata['type'] = 'discussion';
							$pushcontentdata['content_data']['id']				= $discussion->id;
							$pushcontentdata['content_data']['title']			= $discussion->title;
							$pushcontentdata['content_data']['message'] 		= strip_tags($discussion->message);
							$usr = $this->jomHelper->getUserDetail($discussion->creator);
							$pushcontentdata['content_data']['user_id'] 		= $usr->id;
							$pushcontentdata['content_data']['user_name'] 		= $usr->name;
							$pushcontentdata['content_data']['user_avatar'] 	= $usr->avatar;
							$pushcontentdata['content_data']['user_profile'] 	= $usr->profile;

							$format = "%A, %d %B %Y";
							$pushcontentdata['content_data']['date'] 			= CTimeHelper::getFormattedTime($discussion->lastreplied, $format);
							$pushcontentdata['content_data']['isLocked']		= $discussion->lock;

							if($type=='group'){
								$pushcontentdata['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
							}

							$wallModel   =& CFactory::getModel( 'wall' );
							$wallContents = $wallModel ->getPost('discussions' ,$discussion->id,9999999,0);
							$pushcontentdata['content_data']['topics']=count($wallContents);
							$params = new CParameter($discussion->params);
							$pushcontentdata['content_data']['filePermission']	= $params->get('filepermission-member');
							if(SHARE_GROUP_DISCUSSION==1){
								$pushcontentdata['content_data']['shareLink']	= JURI::base()."index.php?option=com_community&view=groups&task=viewdiscussion&groupid={$discussion->groupid}2&topicid={$group->id}";
							}
							$query="SELECT count(id)
									FROM #__community_files
									WHERE `groupid`={$discussion->groupid}
									AND `discussionid`={$discussion->id}";
							$this->db->setQuery($query);
							$pushcontentdata['content_data']['files']=$this->db->loadResult();

							// group data.
							$this->getGroupData($discussion->groupid,$pushcontentdata['group_data']);
							$srch = array("&#9658;","&quot;");
							$rplc = array("►","\"");
							$pushcontentdata['titletag'] = $usr->name." ► ".$pushcontentdata['group_data']['title']."\n".str_replace($srch,$rplc,strip_tags($titletag));
							$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						}else{
							unset($pushcontentdata);
							$inc--;
						}
						break;

					case 'groups.wall':
						$pushcontentdata['type']			= 'groups.wall';
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$pushcontentdata['titletag'] 		= str_replace($srch,$rplc,strip_tags($titletag));
						$pushcontentdata['id'] 				= $html->id;
						$pushcontentdata['date'] 			= $html->created;
						$pushcontentdata['likeAllowed'] 	= $likeAllowed;
						$pushcontentdata['commentAllowed'] 	= $commentAllowed;
						$pushcontentdata['likeCount'] 		= intval($html->likeCount);
						$pushcontentdata['commentCount'] 	= intval($html->commentCount);
						if($type=='group'){
							$pushcontentdata['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
						}else{
							$pushcontentdata['liked'] 			= ($html->userLiked==1) ? 1 : 0 ;
						}
						$group =& JTable::getInstance( 'Group' , 'CTable' );
						$group->load($html->groupid);
						$pushcontentdata['deleteAllowed'] 	= intval($this->IJUserID==$html->actor OR COwnerHelper::isCommunityAdmin($this->IJUserID ) OR $group->isAdmin($this->IJUserID	));
						$pushcontentdata['liketype'] 		= 'groups.wall';
						$pushcontentdata['commenttype'] 	= 'groups.wall';

						// event data
						$this->getGroupData($group->id,$pushcontentdata['group_data']);
						$pushcontentdata['titletag'] = $usr->name." ► ".$pushcontentdata['group_data']['title']."\n".str_replace("&#9658;","►",str_replace("&quot;","\"",(strip_tags($titletag))));
						$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id, $group));
						break;

					case 'events':
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$pushcontentdata['likeAllowed'] 	= 0;
						$pushcontentdata['commentAllowed'] 	= 0;
						$pushcontentdata['content'] 		= '';
						$pushcontentdata['type'] = 'event';
						$content_id = $this->getActivityContentID($html->id);

						// event data
						$this->getEventData($content_id,$pushcontentdata['content_data']);
						$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						break;

					case 'events.wall':
						$pushcontentdata['type']			= 'events.wall';
						$pushcontentdata['id'] 				= $html->id;
						$pushcontentdata['titletag'] 		= str_replace("&#9658;","►",str_replace("&quot;","\"",(strip_tags($titletag))));
						$pushcontentdata['date'] 			= $html->created;
						$pushcontentdata['likeAllowed'] 	= $likeAllowed;
						$pushcontentdata['commentAllowed'] 	= $commentAllowed;
						$pushcontentdata['likeCount'] 		= intval($html->likeCount);
						$pushcontentdata['commentCount'] 	= intval($html->commentCount);
						if($type=='event'){
							$pushcontentdata['liked'] 			= ($html->userLiked>=0) ? 0 : 1 ;
						}else{
							$pushcontentdata['liked'] 			= ($html->userLiked==1) ? 1 : 0 ;
						}
						$event =& JTable::getInstance( 'Event' , 'CTable' );
						$event->load($html->eventid);
						$pushcontentdata['deleteAllowed'] 	= intval($this->IJUserID==$html->actor OR COwnerHelper::isCommunityAdmin($this->IJUserID ) OR $event->isAdmin($this->IJUserID	));
						$pushcontentdata['liketype'] 		= 'events.wall';
						$pushcontentdata['commenttype'] 	= 'events.wall';

						// event data
						$this->getEventData($event->id,$pushcontentdata['event_data']);
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$pushcontentdata['titletag'] = $usr->name." ► ".$pushcontentdata['event_data']['title']."\n".str_replace($srch,$rplc,strip_tags($titletag));
						$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id, $event));
						break;

					case 'profile':
						$pushcontentdata['type'] = 'profile';
						$pushcontentdata['deleteAllowed']=intval($this->my->authorise('community.delete','activities.'.$html->id));
						$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						break;

					default:
						$srch = array("&#9658;","&quot;");
						$rplc = array("►","\"");
						$pushcontentdata['titletag'] = str_replace($srch,$rplc,strip_tags($titletag));
						$pushcontentdata['type'] = '';
						break;
				}

				// get user push notification params and user device token and device type
				$query="SELECT userid,`jomsocial_params`,`device_token`,`device_type`
						FROM #__ijoomeradv_users
						WHERE `userid` IN ('{$memberslist}')";
				$this->db->setQuery($query);
				$puserlist=$this->db->loadObjectList();
				//change for id based push notification
				$pushOptions = array();
				$pushOptions['detail']['content_data']=$pushcontentdata;
				$pushOptions = gzcompress(json_encode($pushOptions));

				$obj = new stdClass();
				$obj->id 		= null;
				$obj->detail 	= $pushOptions;
				$obj->tocount  	= count($puserlist);
				$this->db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
				if($obj->id){
					$this->jsonarray['pushNotificationData']['id'] 		= $obj->id;
					$this->jsonarray['pushNotificationData']['to'] 		= $memberslist;
					$this->jsonarray['pushNotificationData']['message'] = $message;
					$this->jsonarray['pushNotificationData']['type'] 	= 'replaycomment';
					$this->jsonarray['pushNotificationData']['configtype'] 	= 'pushnotif_profile_activity_reply_comment';
				}
			}
		}else{
			// Cannot comment on non-friend stream.
			//IJReq::setResponse(706,JText::_('Permission denied'));
			IJReq::setResponse(704,JText::_('Session expire'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		return true;
	}


	/**
	 * @uses remove wall
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"remove",
	 * 		"taskData":{
	 * 			"uniqueID":"uniqueID", // wall id or comment id
	 * 			"comment":"0/1" 0: if posting wall, 1: if postin comment.
	 * 		}
	 * 	}
	 */
	function remove(){
		$uniqueID  = IJReq::getTaskData('uniqueID',0,'int');
		$comment  = IJReq::getTaskData('comment',0,'bool');
		$type = IJReq::getTaskData('type');
		$userID	= IJReq::getTaskData('userID',0,'int');

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
		$type	    =	$filter->clean( $type, 'string' );
		$uniqueID 	=	$filter->clean( $uniqueID, 'int' );

		CFactory::load( 'helpers' , 'owner' );

		if(!$this->IJUserID){
			IJReq::setResponse(401);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$id=$this->IJUserID;

		if( COwnerHelper::isCommunityAdmin() ){
			$id	= $userID;
		}

		// to do user premission checking
		$user = CFactory::getUser($this->IJUserID);

		$activity	=& JTable::getInstance( 'Activity' , 'CTable' );
		$activity->load($uniqueID);
		if($activity->allowDelete()){
			$activity->delete($type);
		}

		/*switch($type){
			case 'groups.wall':
				$act	=& JTable::getInstance( 'Activity' , 'CTable' );
				$act->load($uniqueID);
				$group_id = $act->groupid;

				$group		  =& JTable::getInstance( 'Group' , 'CTable' );
				$group->load( $group_id );

				//superadmin, group creator can delete all the activity while normal user can delete thier own post only
				if($user->authorise('community.delete','activities.'.$uniqueID, $group)){
					$model->deleteActivity( $type, $uniqueID, $group );
				}
				break;
			case 'events.wall':
				//to retrieve the event id
				$act	=& JTable::getInstance( 'Activity' , 'CTable' );
				$act->load($uniqueID);
				$event_id = $act->eventid;

				$event		  =& JTable::getInstance( 'Event' , 'CTable' );
				$event->load( $event_id );

				if($user->authorise('community.delete','activities.'.$uniqueID, $event)){
					$model->deleteActivity( $type, $uniqueID, $event);
					$wall =CFactory::getModel('wall');
					$wall->deleteAllChildPosts($uniqueID, $type);
				}
				break;
			default:
				//delete if this activity belongs to the current user
				if($user->authorise('community.delete','activities.'.$uniqueID)){
					$model->deleteActivity( $type, $uniqueID );
				}else{
					$model->hide( $id, $uniqueID );
				}
			}*/
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


	/**
	 * @uses like wall
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"like",
	 * 		"taskData":{
	 * 			"wallID":"wallID",
	 * 			"type":"type"
	 * 		}
	 * 	}
	 */
	function like(){
		$this->jsonarray = array();
		$wallID  = IJReq::getTaskData( 'wallID',0,'int');
		$type  = IJReq::getTaskData('type');
		if($this->jomHelper->Like($type,$wallID)){
			$this->jsonarray['code']=200;
    		return $this->jsonarray;
		}else{
			IJReq::setResponse(500);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}


	/**
	 * @uses unlike wall
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"unlike",
	 * 		"taskData":{
	 * 			"wallID":"wallID",
	 * 			"type":"type"
	 * 		}
	 * 	}
	 */
	function unlike(){
		$wallID  = IJReq::getTaskData('wallID',0,'int');
		$type  = IJReq::getTaskData('type');
		if($this->jomHelper->Unlike($type,$wallID)){
			$this->jsonarray['code']=200;
    		return $this->jsonarray;
		}else{
			IJReq::setResponse(500);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
	}


	/**
	 * @uses get like user list
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"getLikes",
	 * 		"taskData":{
	 * 			"wallID":"wallID",
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 */
	function getLikes(){
		$wallID=IJReq::getTaskData('wallID',0,'int');
		$pageNO=IJReq::getTaskData('pageNo',0,'int');
		$limit=PAGE_MEMBER_LIMIT;

		if($pageNO==1 || $pageNO == 0){
		  	$startFrom = 0;
		}else{
			$startFrom = ($limit*($pageNO-1));
		}

		$query="SELECT `like_id`,`like_type`
				FROM #__community_activities
				WHERE `id`='{$wallID}'";
		$this->db->setQuery($query);
		$like_fields = $this->db->loadObject();

		if($like_fields->like_id){
			$query="SELECT *
					FROM #__community_likes
					WHERE (uid='{$like_fields->like_id}' OR uid= '-1')
					AND `element`='{$like_fields->like_type}'
					LIMIT {$startFrom},{$limit}";
			$this->db->setQuery($query);
			$rows = $this->db->loadObjectList();

			foreach($rows as $row){
				if(empty($row->like)){
					$likes=array();
				}else{
					$likes = explode(',',$row->like);
				}
				if(!empty($likes)>0){
					$this->jsonarray['code']=200;
					$this->jsonarray['pageLimit']=$limit;
					$this->jsonarray['total']=count($likes);
				}else{
					IJReq::setResponse(204);
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
				$likes=array_slice($likes,$startFrom,$limit);
				if($row->like){
					foreach($likes as $key=>$userlike){
						$usr = $this->jomHelper->getUserDetail($userlike);
						$this->jsonarray['likes'][$key]['user_id'] 		= $usr->id;
						$this->jsonarray['likes'][$key]['user_name'] 	= $usr->name;
						$this->jsonarray['likes'][$key]['user_profile']	= $usr->profile;
					}
				}
			}
		}
		return $this->jsonarray;
	}


	/**
	 * @uses get comment user list
     * @example the json string will be like, :
	 * 	{
	 * 		"extName":"jomsocial",
	 *		"extView":"wall",
 	 *		"extTask":"getComments",
	 * 		"taskData":{
	 * 			"wallID":"wallID",
	 * 			"pageNO":"pageNO"
	 * 		}
	 * 	}
	 */
	function getComments(){
		$wallID=IJReq::getTaskData('wallID',0,'int');
		$pageNO=IJReq::getTaskData('pageNo',0,'int');
		$limit=PAGE_COMMENT_LIMIT;

		if($pageNO==1 || $pageNO == 0){
		  	$startFrom = 0;
		}else{
			$startFrom = ($limit*($pageNO-1));
		}

		$query="SELECT cid,comment_id,comment_type
				FROM #__community_activities
				WHERE id = '{$wallID}'";
		$this->db->setQuery($query);
		$comment_act = $this->db->loadObject();

		if($comment_act->comment_id>0){
			$query="SELECT count(1)
					FROM #__community_wall as wa
					WHERE `contentid`='{$comment_act->comment_id}'
					AND type='{$comment_act->comment_type}'";
			$this->db->setQuery($query);
			$total = $this->db->loadResult();

			$query="SELECT wa.date as dt, wa.*
					FROM #__community_wall as wa
					WHERE `contentid`='{$comment_act->comment_id}'
					AND type='{$comment_act->comment_type}'
					ORDER BY id ASC
					LIMIT {$startFrom},{$limit}";
			$this->db->setQuery($query);
			$comments = $this->db->loadObjectList();

			if(count($comments)>0){
				$this->jsonarray['code']=200;
				$this->jsonarray['pageLimit']=$limit;
				$this->jsonarray['total']=$total;
			}else{
				IJReq::setResponse(204);
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			//$isAdmin			= intval($group->isAdmin($this->IJUserID));
			$isCommunityAdmin	= intval(COwnerHelper::isCommunityAdmin($this->IJUserID));
			foreach($comments as $key=>$comment){
				$usr = $this->jomHelper->getUserDetail($comment->post_by);
				CFactory::load('libraries','comment');
				$com_obj = new CComment();
				$wall = $com_obj->stripCommentData($comment->comment);
				$comm = htmlspecialchars( $wall, ENT_QUOTES , 'UTF-8' );
				$dates = $this->jomHelper->getDate($comment->dt);
				$createdTime = $this->jomHelper->timeLapse($dates);

				$createdTime = (!empty($createdTime))?$createdTime:'';

				$this->jsonarray['comments'][$key]['comment_id']	= $comment->id;
				$comm = $this->jomHelper->addAudioFile($comm);
				$this->jsonarray['comments'][$key]['comment_text'] 	= $comm;
				$this->jsonarray['comments'][$key]['date'] 			= $createdTime;
				$this->jsonarray['comments'][$key]['user_id']		= $usr->id;
				$this->jsonarray['comments'][$key]['user_name']		= $usr->name;
				$this->jsonarray['comments'][$key]['user_avatar']	= $usr->avatar;
				$this->jsonarray['comments'][$key]['user_profile']	= $usr->profile;
				$this->jsonarray['comments'][$key]['deleteAllowed']	= intval($this->IJUserID==$comment->post_by || $isCommunityAdmin);
			}
		}
		return $this->jsonarray;
	}
}