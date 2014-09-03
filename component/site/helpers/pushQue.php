<?php
define( '_JEXEC', 1 );
define('JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
JDEBUG ? $_PROFILER->mark( 'afterLoad' ) : null;
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();
JPluginHelper::importPlugin('system');
$db	= & JFactory::getDBO();

//get que data
$query = $db->getQuery(true)
        ->select('*')
        ->from($db->qn('#__ijoomeradv_push_notification_que'))
        ->where('ispushed IS NULL');
$db->setQuery($query);
$queResults = $db->loadObjectList();

if(count($queResults) > 0)
{
	include_once JPATH_SITE.'/components/com_ijoomeradv/helpers/helper.php';

	//get ijoomer configuration
	$db->setQuery(
	    $db->getQuery(true)
	        ->select('name, value')
	        ->from($db->qn('#__ijoomeradv_config'))
	        ->where('name IN ("IJOOMER_PUSH_ENABLE_IPHONE","IJOOMER_PUSH_DEPLOYMENT_IPHONE","IJOOMER_PUSH_ENABLE_ANDROID","IJOOMER_PUSH_API_KEY_ANDROID", "IJOOMER_PUSH_KEY_PASSWORD_IPHONE")')
	);
	$iJoomerConfig=$db->loadAssocList('name', 'value');

	foreach($queResults as $queResult)
	{
		if($queResult->userids == '') continue;

		//for #__ijoomeradv_push_notification_que table
		$queTable = new stdClass();
		$queTable->id 		= $queResult->id;
		$queTable->ispushed	= 1;

		//get details of userids
		$db->setQuery(
        	$db->getQuery(true)
            	->select('*')
            	->from($db->qn('#__ijoomeradv_users').' AS u')
            	->where('userid IN ('.$queResult->userids.')')
            	->where('device_token != ""')
        );
        $puserlist = $db->loadObjectList();

        if(count($puserlist) == 0) continue;

		$dtoken =array();
		$params = new JRegistry($queResult->params);
		$type 	= $params->get('type', '');
		$objId	= $params->get('obj_id', '');

		$push_response = array();
		foreach($puserlist as $key=>$puser)
		{
		    if($iJoomerConfig['IJOOMER_PUSH_ENABLE_IPHONE'] == 1 && $puser->device_type=='iphone')
		    {
		        $val=array();
		        $val['device_token']	= $puser->device_token;
		        $val['live']			= intval($iJoomerConfig['IJOOMER_PUSH_DEPLOYMENT_IPHONE']);
		        $val['key_pass']        = $iJoomerConfig['IJOOMER_PUSH_KEY_PASSWORD_IPHONE'];//@todo I know this isnt the best solution its here as a temp hack
		        $val['aps']['message']	= strip_tags($queResult->message);
		        $val['aps']['type']		= $type;
		        $val['aps']['id']		= $objId;

		        $response = IJPushNotif::sendIphonePushNotification($val);
		        $push_response[$key]['userid'] 	 = $puser->userid;
		        $push_response[$key]['device'] 	 = 'iphone';
		        $push_response[$key]['response'] = $response['response'];
		        $push_response[$key]['message']  = $response['message'];
		    }

		    if($iJoomerConfig['IJOOMER_PUSH_ENABLE_ANDROID'] == 1 && $puser->device_type=='android')
		    {
		        $dtoken[]=$puser->device_token;//@todo this is a bad hack and should be expanded to include all ids to send all in a single request
		    }
		}

		if($iJoomerConfig['IJOOMER_PUSH_ENABLE_ANDROID'] == 1 && count($dtoken) > 0)
		{
		    $val=array();
		    $val['registration_ids']	= $dtoken;//according to the api this needs to be an array
		    $val['api_key']             = $iJoomerConfig['IJOOMER_PUSH_API_KEY_ANDROID'];//@todo I know this isnt the best solution there seems to be an issue with the API constant int he helper
		    $val['data']['message']		= strip_tags($queResult->message);
		    $val['data']['type']		= $type;
		    $val['data']['id']			= $objId;
		    $response = IJPushNotif::sendAndroidPushNotification($val);
	        $push_response[$key]['userid'] 	 = $puser->userid;
	        $push_response[$key]['device'] 	 = 'android';
	        $push_response[$key]['response'] = $response['response'];
	        $push_response[$key]['message']  = $response['message'];
		}

		$queTable->push_response	= json_encode($push_response);
		$db->updateObject('#__ijoomeradv_push_notification_que',$queTable,'id');
	}
}
