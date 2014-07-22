<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : Jomsocial_1.5 (compatible with k2 2.6.5)
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

class items {
	
	private $IJUserID;
	private $mainframe; 
	private $db;
	private $my;
	private $jsonarray=array();
	static $catcount;
	
	function __construct(){
		$this->mainframe	=	& JFactory::getApplication();
		$this->db			=	& JFactory::getDBO(); // set database object
		$this->IJUserID		=	$this->mainframe->getUserState('com_ijoomer.IJUserID', 0); //get login user id
		$this->my			=	JFactory::getUser($this->IJUserID); // set the login user object
    }
        
    /**
     * @uses This function is used to get all items,subcategories and all details of selected fieldvalue in category view.
     * @example the json string will be like, : 
     *	{
	 *		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"items",
	 * 		"taskData":{
	 * 			"menuId":"menuId",
	 * 			"pageNO":"pageNO"
	 * 			}
	 * 	}
	 */
    function items(){
			$categoryIDs 	  		= IJReq::getTaskData('catId');
			$childCatItems    		= IJReq::getTaskData('itemsChildCat');
			$pageNO           		= IJReq::getTaskData('pageNO');
			$itemsPerCategoryLimit  = IJReq::getTaskData('itemsPerCategoryLimit');
			$pageLimit        		= IJReq::getTaskData('pageLimit');
			$leadLimit        		= IJReq::getTaskData('leadLimit');
			$pageLayout       		= IJReq::getTaskData('pageLayout');
			$ordering    	  		= IJReq::getTaskData('ordering');

			$orders = array(
				'id'=>'i.id DESC',
				'date'=>'i.created ASC',
				'rdate'=>'i.created DESC',
				'alpha'=>'i.title',
				'ralpha'=>'i.title DESC',
				'order'=>'i.ordering',
				'rorder'=>'i.ordering DESC',
				'featured'=>'i.featured DESC, i.created DESC',
				'hits'=>'i.hits DESC',
				'rand'=>'RAND()',
				'best'=>'rating DESC',
				'modified'=>'lastChanged DESC',
				'publishUp'=>'i.publish_up DESC'
			);
			
			$orderby = (array_key_exists($ordering,$orders)) ? $orders[$ordering] : 'i.id DESC';
    		
	    	$itemArray = array();
	    	$countselectedcategories = count($categoryIDs);
	    	if(!$pageLimit){
	    		$pageLimit=10;
	    	}
	    	$itemLimit = ($countselectedcategories==1) ? $pageLimit : $itemsPerCategoryLimit;
	    	if(!$itemLimit){
	    		$itemLimit=10;
	    	}
	    	$startFrom = ($pageNO==0 || $pageNO==1) ? 0 : $itemLimit*($pageNO-1);
    		$startLimit= ($pageNO==0 || $pageNO==1) ? 0 : $pageLimit*($pageNO-1);

    		foreach($categoryIDs as $catID){
	    		//start-To show subcategories if in menu-items,only one category selected
				if($countselectedcategories==1){
					$catdata 	= $this->getCategorySubcat($catID,$clear = false);
				}
				if(isset($catdata)){
					foreach($catdata as $keyy=>$valuee){
						$categoryArray['categories'][$keyy]['id'] 	         	 = $valuee->id;
						$categoryArray['categories'][$keyy]['name'] 		 	 = $valuee->name;
						$categoryArray['categories'][$keyy]['description']       = strip_tags($valuee->description);
						$categoryArray['categories'][$keyy]['parent']       	 = $valuee->parent;
					}
				}
				
	    		if($childCatItems) {
	    			$categories 	= $this->getCategoryChildren($catID, '');
					$array_merge 	= array_merge($categoryIDs,$categories);
					$array_unique 	= array_unique($array_merge);
					$implode 		= implode(",",$array_unique);
	    		}else if(!$childCatItems && $countselectedcategories==1){
	    			$categories 	= $this->getCategoryChildren($catID, '');
					$array_merge 	= array_merge($categoryIDs,$categories);
					$array_unique 	= array_unique($array_merge);
					$implode 		= implode(",",$array_unique);
	    		}else {
	    			$array_unique = $categoryIDs;
	    			$implode = implode(",",$array_unique);
	    		}
    		
    		}
			
  			if ($ordering == 'best'){
          		$query="SELECT i.* , (r.rating_sum/r.rating_count) AS rating 
          				FROM #__k2_items as i 
          				LEFT JOIN #__k2_rating r ON r.itemID = i.id 
          				WHERE i.id>0 
          				AND i.trash=0 
          				AND i.published=1"; 
			}else{
				$query="SELECT i.*, CASE WHEN i.modified = 0 
									THEN i.created 
									ELSE i.modified END as lastChanged 
			    		FROM #__k2_items as i 
			    		WHERE i.id>0 
			    		AND i.published=1"; 
			    			
			}
			
			$query .= " AND (";
			$counter = 0;
			foreach($array_unique as $catUnique){
				if($counter <> 0) {
					$query .= " OR ";
				}
				$query.=" i.id IN 
						(SELECT * FROM (SELECT i.id 
										FROM #__k2_items as i 
										WHERE i.catid =$catUnique 
										ORDER BY $orderby";
				if($countselectedcategories>1 && $itemLimit){
		 			$query .= " LIMIT 0, $itemLimit";
				}
				$query .= " ) AS alias) ";
				$counter++;
			}
			$query .= ")";
			$query .= " ORDER BY $orderby";
			$queryLimit=$query;
			$query .= ($countselectedcategories==1) ? " LIMIT $startFrom, $itemLimit" : " LIMIT $startLimit, $pageLimit";
			$this->db->setQuery($query);
			$rows = $this->db->loadObjectList();
			$this->db->setQuery($queryLimit);
	        $countitems = count($this->db->loadObjectList());
			
			foreach($rows as $key=>$value){
				$itemArray['items'][$key]['id'] 	         = $value->id;
				$itemArray['items'][$key]['title'] 		 	 = $value->title;
				$itemArray['items'][$key]['alias']     	 	 = $value->alias;
				$itemArray['items'][$key]['catId']     	 	 = $value->catid;
				$itemArray['items'][$key]['introText']       = strip_tags($value->introtext);
				$itemArray['items'][$key]['fullText'] 	     = strip_tags($value->fulltext);
				$video=$value->video;
				$ex_video=explode("}",$video);
				$finalvideo= str_replace("{",".",$ex_video[0]);
				
				if(isset($ex_video[1])){
			 		$final_video= explode("{",$ex_video[1]);
				}
				
				$itemArray['items'][$key]['video']=($finalvideo!="" && $final_video!="") ? JURI::base().'/media/k2/videos/'.$final_video[0].$finalvideo :"";	
			 	$gallery=$value->gallery;
			 	$ex_gallery=explode("}",$gallery);
				$finalgallery= str_replace("{",".",$ex_gallery[0]);
			 	$final_gallery= explode("{",$ex_gallery[1]);
			 	$path = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0];
			 	$folders = JFolder::folders($path, $filter = '.');
			 	
			 	foreach($folders as $key2=>$value2){
			 		$fullpath = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				  	$jpg_files = JFolder::files($fullpath, '');
				 	$fullpath1 = JURI::base().'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				 	if($jpg_files){
						foreach ($jpg_files as $key1=>$file1) {
							$itemArray['items'][$key]['imageGalleries'][$key1]['imageGallery'] = $fullpath1.'/'.$file1;
						}
		 			}else{
		 				$itemArray['items'][$key]['imageGalleries'] = "";	
		 			}
	 			}
			 	
	 			$filename = 'media/k2/items/cache/'.md5("Image".$value->id).'_Generic.jpg';
				$filename1 = 'media/k2/items/cache/'.md5("Image".$value->id).'_L.jpg';
				$filename2 = 'media/k2/items/cache/'.md5("Image".$value->id).'_M.jpg';
				$filename3 = 'media/k2/items/cache/'.md5("Image".$value->id).'_S.jpg';
				$filename4 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XL.jpg';
				$filename5 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XS.jpg';
				
				if (file_exists($filename)) {
					$itemArray['items'][$key]['imageGeneric'] = JURI::base().$filename;
					$itemArray['items'][$key]['imageLarge'] = JURI::base().$filename1;
					$itemArray['items'][$key]['imageMedium'] = JURI::base().$filename2;
					$itemArray['items'][$key]['imageSmall'] = JURI::base().$filename3;
					$itemArray['items'][$key]['imageExtraLarge'] = JURI::base().$filename4;
					$itemArray['items'][$key]['imageExtraSmall'] = JURI::base().$filename5;
				}else{
					$itemArray['items'][$key]['imageGeneric'] = '';
					$itemArray['items'][$key]['imageLarge'] = '';
					$itemArray['items'][$key]['imageMedium'] = '';
					$itemArray['items'][$key]['imageSmall'] = '';
					$itemArray['items'][$key]['imageExtraLarge'] = '';
					$itemArray['items'][$key]['imageExtraSmall'] = '';
				}
			 	$extra_fields = json_decode($value->extra_fields,true);
			 	
				if($extra_fields!=""){
					foreach ($extra_fields as $exkey=>$exvalue){
						$ext_query ="SELECT ext.type  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$exvalue['id']}";
						$this->db->setQuery($ext_query);
				        $exttype = $this->db->loadResult();
				        $extra_fields[$exkey]['type']=$exttype;
					}
					foreach ($extra_fields as $k1=>$v1){
						if(is_array($v1['value'])){
							$ext_query ="SELECT ext.value  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$v1['id']}";
							$this->db->setQuery($ext_query);
				        	$extVal = $this->db->loadResult();
				        	$jsonextVal = json_decode($extVal,true);
				        	unset($extra_fields[$k1]['value']);
				        	foreach($jsonextVal as $jsK=>$jsV){
				        		if(in_array($jsV['value'],$v1['value'])){
				        			$extra_fields[$k1]['value'][]=$jsV['name'];
				        		}
				        	}
						}else if($v1['type']=='image'){ 
							if($ext= pathinfo($v1['value'], PATHINFO_EXTENSION)){
							$extra_fields[$k1]['value']=JURI::root().$v1['value'];
							}
						}
						$ext_query = "SELECT ext.name  
					    			  FROM #__k2_extra_fields as ext 
					    			  WHERE ext.id={$v1['id']}";
						$this->db->setQuery($ext_query);
			        	$extname = $this->db->loadResult();
			        	$extra_fields[$k1]['name']=$extname;
					}
			 	}
			 	$itemArray['items'][$key]['extraFields'] = (isset($extra_fields) && !empty($extra_fields)) ? $extra_fields : "" ;
				$itemArray['items'][$key]['publishUp']    = $value->publish_up;
				$itemArray['items'][$key]['createdBy']    = $value->created_by;
				$query="SELECT name 
						FROM #__users 
						WHERE id={$value->created_by}";
        		$this->db->setQuery($query);
        		$created_by_name = $this->db->loadResult();
        		$itemArray['items'][$key]['createdByName']    = $created_by_name;
				require_once JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php';
				$K2ModelItem = new K2ModelItem();

				$itemArray['items'][$key]['tags'] = (count($K2ModelItem->getItemTags($value->id))>0) ? $K2ModelItem->getItemTags($value->id) : '';
				$itemArray['items'][$key]['ratings'] = (count($K2ModelItem->getRating($value->id))>0) ? $K2ModelItem->getRating($value->id) : '';
				$itemArray['items'][$key]['comments'] = (count($K2ModelItem->getItemComments($value->id,"","",true))>0) ? $K2ModelItem->getItemComments($value->id,"","",true) : '';
				$itemArray['items'][$key]['attachments'] = (count($K2ModelItem->getItemAttachments($value->id))>0) ? $K2ModelItem->getItemAttachments($value->id) : '';
				$itemArray['items'][$key]['fields'] = (count($this->getExtraFieldGroup($value->id))>0) ? $this->getExtraFieldGroup($value->id) : '';
				$share_link = JURI::root()."index.php?option=com_k2&view=item&id={$value->id}:{$value->alias}&Itemid=487";
				$itemArray['items'][$key]['shareLink'] = $share_link;
			}
		
	    	$jsonarray = array();
	    	$jsonarray['code'] = ($countitems>0) ? 200 : 204;
	    	$jsonarray['total']	= $countitems;
    		if($countselectedcategories==1){
	    		$jsonarray['pageLimit'] = $itemLimit;
	    	}else{
	    		$jsonarray['pageLimit'] = ($itemLimit) ? $pageLimit : $countitems;
	    	}
			$jsonarray['pageLayout']	= $pageLayout ? $pageLayout : '';
			$jsonarray['leadLimit']		= $leadLimit;
	   	    $jsonarray['mainCategories']=(($pageNO==0 || $pageNO==1) && $categoryIDs!="") ? $this->getMainCategories($categoryIDs) : "";
			$jsonarray['categories'] 	= (($pageNO==0 || $pageNO==1) && $countselectedcategories==1) ? $categoryArray['categories'] : "";
			$jsonarray['items']			= $itemArray['items'];
			return $jsonarray;
    }
    
    private function getMainCategories($catID){ 
    	$implode  = implode(",",$catID);
    	$db     = JFactory::getDBO();
   		$query="SELECT id,name,description,parent,image 
   				FROM #__k2_categories 
   				WHERE id IN (".$implode.") 
   				AND published=1 
   				AND trash=0";
        $db->setQuery($query);
        $Allcats = $db->loadObjectList();	
		foreach($Allcats as $key=>$value){
			$jsonarray[$key]['id'] 	         = $value->id;
			$jsonarray[$key]['name'] 		 = $value->name;
			$jsonarray[$key]['description']  = $value->description;
			$jsonarray[$key]['parent']       = $value->parent;
			$jsonarray[$key]['image'] = ($value->image) ? JURI::base().'media/k2/categories/'.$value->image : "";
		}
		return $jsonarray;
    }
    
	
    /**
     * @uses This function is used to get itemdetail based on item selected for itemView.
     * @example the json string will be like, :
     * { 
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"ItemDetail",
	 * 		"taskData":{
	 * 				"menuId":"menuId",
	 * 				}
	 * 	}
	 */
    function ItemDetail(){
    	$itemID=IJReq::getTaskData('itemID',1,'int');
    	$query = "SELECT i.*  
    			  FROM #__k2_items as i 
    			  WHERE i.id>0 
    			  AND i.trash=0 
    			  AND i.published=1 
    			  AND i.id={$itemID}";
		$this->db->setQuery($query);
        $rows = $this->db->loadObjectList();
        $count=count($rows);
        if($count<=0){
			IJReq::setResponseCode(204);
			return false;
		}
	
		foreach($rows as $key=>$value){
			$itemArray['items'][$key]['id'] 	         = $value->id;
			$itemArray['items'][$key]['title'] 		 	 = $value->title;
			$itemArray['items'][$key]['alias']     	 	 = $value->alias;
			$itemArray['items'][$key]['catId']     	 	 = $value->catid;
			$itemArray['items'][$key]['introText']       = strip_tags($value->introtext);
			$itemArray['items'][$key]['fullText'] 	     = strip_tags($value->fulltext);
			$video=$value->video;
			$ex_video=explode("}",$video);
			$finalvideo= str_replace("{",".",$ex_video[0]);
			if(isset($ex_video[1])){
		 		$final_video= explode("{",$ex_video[1]);
			}
			$itemArray['items'][$key]['video']=($finalvideo!="" && $final_video!="") ? JURI::base().'/media/k2/videos/'.$final_video[0].$finalvideo :"";	
		 	$gallery=$value->gallery;
		 	$ex_gallery=explode("}",$gallery);
			$finalgallery= str_replace("{",".",$ex_gallery[0]);
		 	$final_gallery= explode("{",$ex_gallery[1]);
		 	$path = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0];
		 	$folders = JFolder::folders($path, $filter = '.');
		 	foreach($folders as $key2=>$value2){
		 		$fullpath = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
			  	$jpg_files = JFolder::files($fullpath, '');
			 	$fullpath1 = JURI::base().'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
			 	if($jpg_files){
					foreach ($jpg_files as $key1=>$file1) {
						$itemArray['items'][$key]['imageGalleries'][$key1]['imageGallery'] 	     = $fullpath1.'/'.$file1;
					}
	 			}else{
	 				$itemArray['items'][$key]['imageGalleries']        = "";	
	 			}
 			}

 			if (file_exists('media/k2/items/cache/'.md5("Image".$value->id).'_Generic.jpg')) {
				$itemArray['items'][$key]['imageGeneric'] = JURI::base().'media/k2/items/cache/'.md5("Image".$value->id).'_Generic.jpg';
				$itemArray['items'][$key]['imageLarge'] = JURI::base().'media/k2/items/cache/'.md5("Image".$value->id).'_L.jpg';
				$itemArray['items'][$key]['imageMedium'] = JURI::base().'media/k2/items/cache/'.md5("Image".$value->id).'_M.jpg';
				$itemArray['items'][$key]['imageSmall'] = JURI::base().'media/k2/items/cache/'.md5("Image".$value->id).'_S.jpg';
				$itemArray['items'][$key]['imageExtraLarge'] = JURI::base().'media/k2/items/cache/'.md5("Image".$value->id).'_XL.jpg';
				$itemArray['items'][$key]['imageExtraSmall'] = JURI::base().'media/k2/items/cache/'.md5("Image".$value->id).'_XS.jpg';
			}else{
				$itemArray['items'][$key]['imageGeneric'] = '';
				$itemArray['items'][$key]['imageLarge'] = '';
				$itemArray['items'][$key]['imageMedium'] = '';
				$itemArray['items'][$key]['imageSmall'] = '';
				$itemArray['items'][$key]['imageExtraLarge'] = '';
				$itemArray['items'][$key]['imageExtraSmall'] = '';
			}
			
			$extra_fields = json_decode($value->extra_fields,true);
			 	
			if($extra_fields!=""){
					foreach ($extra_fields as $exkey=>$exvalue){
						$ext_query ="SELECT ext.type  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$exvalue['id']}";
						$this->db->setQuery($ext_query);
				        $exttype = $this->db->loadResult();
				        $extra_fields[$exkey]['type']=$exttype;
					}
					foreach ($extra_fields as $k1=>$v1){
						if(is_array($v1['value'])){
							$ext_query ="SELECT ext.value  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$v1['id']}";
							$this->db->setQuery($ext_query);
				        	$extVal = $this->db->loadResult();
				        	$jsonextVal = json_decode($extVal,true);
				        	unset($extra_fields[$k1]['value']);
				        	foreach($jsonextVal as $jsK=>$jsV){
				        		if(in_array($jsV['value'],$v1['value'])){
				        			$extra_fields[$k1]['value'][]=$jsV['name'];
				        		}
				        	}
						}else if($v1['type']=='image'){ 
							if($ext= pathinfo($v1['value'], PATHINFO_EXTENSION)){
							$extra_fields[$k1]['value']=JURI::root().$v1['value'];
							}
						}
						$ext_query = "SELECT ext.name  
					    			  FROM #__k2_extra_fields as ext 
					    			  WHERE ext.id={$v1['id']}";
						$this->db->setQuery($ext_query);
			        	$extname = $this->db->loadResult();
			        	$extra_fields[$k1]['name']=$extname;
					}
			 	}
			 	
		 	$itemArray['items'][$key]['extraFields'] = (isset($extra_fields) && !empty($extra_fields)) ? $extra_fields : "";
			$itemArray['items'][$key]['publishUp']    = $value->publish_up;
			$itemArray['items'][$key]['createdBy']    = $value->created_by;
			$query="SELECT name 
					FROM #__users 
					WHERE id={$value->created_by}";
        	$this->db->setQuery($query);
        	$created_by_name = $this->db->loadResult();
        	$itemArray['items'][$key]['createdByName']    = $created_by_name;
			require_once JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php';
			$K2ModelItem = new K2ModelItem();
			$itemArray['items'][$key]['tags'] = (count($K2ModelItem->getItemTags($value->id))>0) ? $K2ModelItem->getItemTags($value->id) : '';
			$itemArray['items'][$key]['ratings'] = (count($K2ModelItem->getRating($value->id))>0) ? $K2ModelItem->getRating($value->id) : '';
			$itemArray['items'][$key]['comments'] = (count($K2ModelItem->getItemComments($value->id,"","",true))>0) ? $K2ModelItem->getItemComments($value->id,"","",true) : '';
			$itemArray['items'][$key]['attachments'] = (count($K2ModelItem->getItemAttachments($value->id))>0) ? $K2ModelItem->getItemAttachments($value->id) : '';
			$itemArray['items'][$key]['fields'] = (count($this->getExtraFieldGroup($value->id))>0) ? $this->getExtraFieldGroup($value->id) : '';
			$share_link = JURI::root()."index.php?option=com_k2&view=item&id={$value->id}:{$value->alias}&Itemid=487";
			$itemArray['items'][$key]['shareLink'] 	   = $share_link;
		}
		$jsonarray['code'] = 200;
		$jsonarray['items']	= $itemArray['items'];
		return $jsonarray;
    }
    
    
     /**
     * @uses This function is used to get  items based on selected tag and selected categories.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"TagRelatedItems",
	 * 		"taskData":{
	 * 				"menuId":"menuId",
	 * 				 }
	 *             
	 * 	}
	 */
    function TagRelatedItems(){
    	$tagname 		=IJReq::getTaskData('tag');
    	$catid 			=IJReq::getTaskData('catId');
    	$pageNO         = IJReq::getTaskData('pageNO');
		$pageLimit      = IJReq::getTaskData('pageLimit');
    	$itemordering 	=IJReq::getTaskData('itemorder');
    	if($pageNO==0 || $pageNO==1){
		  		$startFrom=0;		
		}else{
				$startFrom = $pageLimit*($pageNO-1);
		}
    	$query 	= "SELECT tr.itemID 
   	  				FROM #__k2_tags AS t 
		 			LEFT JOIN #__k2_tags_xref AS tr 
		 			ON t.id = tr.tagID 
		 			WHERE t.name ='".$tagname."'"; 
		$this->db->setQuery($query);
        $TagitemID = $this->db->loadResultArray();
    	$implodeCAT = implode(",",$catid);
    	$implodeTagitem = implode(",",$TagitemID);
    	
    	$orders = array(
				'id'=>'i.id DESC',
				'date'=>'i.created ASC',
				'rdate'=>'i.created DESC',
				'alpha'=>'i.title',
				'ralpha'=>'i.title DESC',
				'order'=>'i.ordering',
				'rorder'=>'i.ordering DESC',
				'featured'=>'i.featured DESC, i.created DESC',
				'hits'=>'i.hits DESC',
				'rand'=>'RAND()',
				'best'=>'rating DESC',
				'modified'=>'lastChanged DESC',
				'publishUp'=>'i.publish_up DESC'
			);
			
		$orderby = (array_key_exists($ordering,$orders)) ? $orders[$ordering] : 'i.id DESC';
    	
		$query = "SELECT count(i.id), CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged 
		   			FROM #__k2_items as i 
		   			WHERE i.id>0 
		   			AND i.trash=0 
		   			AND i.published=1 
		   			AND i.catid IN (".$implodeCAT.")
		   			AND i.id IN (".$implodeTagitem.")";
		$query .= " ORDER BY ".$orderby;
		$this->db->setQuery($query);
        $countitems = $this->db->loadResult();
		if ($itemordering == 'best'){
        	$query  = "SELECT i.* , (r.rating_sum/r.rating_count) AS rating FROM #__k2_items as i ";
			$query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id WHERE i.id>0 AND i.trash=0 AND i.published=1 
   					AND i.catid IN (".$implodeCAT.") AND i.id IN (".$implodeTagitem.")";
		}else {
			$query = "SELECT i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged 
		   			FROM #__k2_items as i 
		   			WHERE i.id>0 
		   			AND i.trash=0 
		   			AND i.published=1 
		   			AND i.catid IN (".$implodeCAT.")
		   			AND i.id IN (".$implodeTagitem.")";
			
		}
		$query .= " ORDER BY ".$orderby;
		$query .= " LIMIT $startFrom, ".$pageLimit."";
		$this->db->setQuery($query);
        $rowss = $this->db->loadObjectList();
        $countRows = count($rowss);
        if($countRows<=0){
			IJReq::setResponseCode(204);
			return false;
		}
		
        $itemArray = array();
        foreach($rowss as $key=>$value){
				$itemArray['items'][$key]['id'] 	         = $value->id;
				$itemArray['items'][$key]['title'] 		 	 = $value->title;
				$itemArray['items'][$key]['alias']     	 	 = $value->alias;
				$itemArray['items'][$key]['catId']     	 	 = $value->catid;
				$itemArray['items'][$key]['introText']       = strip_tags($value->introtext);
				$itemArray['items'][$key]['fullText'] 	     = strip_tags($value->fulltext);
				$video=$value->video;
				$ex_video=explode("}",$video);
				$finalvideo= str_replace("{",".",$ex_video[0]);
				if(isset($ex_video[1])){
			 		$final_video= explode("{",$ex_video[1]);
				}
				$itemArray['items'][$key]['video']=($finalvideo!="" && $final_video!="") ? JURI::base().'/media/k2/videos/'.$final_video[0].$finalvideo :"";	
			 	$gallery=$value->gallery;
			 	$ex_gallery=explode("}",$gallery);
				$finalgallery= str_replace("{",".",$ex_gallery[0]);
			 	$final_gallery= explode("{",$ex_gallery[1]);
			 	$path = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0];
			 	$folders = JFolder::folders($path, $filter = '.');
			 	
			 	foreach($folders as $key2=>$value2){
			 		$fullpath = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				  	$jpg_files = JFolder::files($fullpath, '');
				 	$fullpath1 = JURI::base().'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				 	if($jpg_files){
						foreach ($jpg_files as $key1=>$file1) {
							$itemArray['items'][$key]['imageGalleries'][$key1]['imageGallery'] = $fullpath1.'/'.$file1;
						}
		 			}else{
		 				$itemArray['items'][$key]['imageGalleries'] = "";	
		 			}
	 			}
			 	
	 			 $filename = 'media/k2/items/cache/'.md5("Image".$value->id).'_Generic.jpg';
				 $filename1 = 'media/k2/items/cache/'.md5("Image".$value->id).'_L.jpg';
				 $filename2 = 'media/k2/items/cache/'.md5("Image".$value->id).'_M.jpg';
				 $filename3 = 'media/k2/items/cache/'.md5("Image".$value->id).'_S.jpg';
				 $filename4 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XL.jpg';
				 $filename5 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XS.jpg';
				
				if (file_exists($filename)) {
					$itemArray['items'][$key]['imageGeneric'] = JURI::base().$filename;
					$itemArray['items'][$key]['imageLarge'] = JURI::base().$filename1;
					$itemArray['items'][$key]['imageMedium'] = JURI::base().$filename2;
					$itemArray['items'][$key]['imageSmall'] = JURI::base().$filename3;
					$itemArray['items'][$key]['imageExtraLarge'] = JURI::base().$filename4;
					$itemArray['items'][$key]['imageExtraSmall'] = JURI::base().$filename5;
				}else{
					$itemArray['items'][$key]['imageGeneric'] = '';
					$itemArray['items'][$key]['imageLarge'] = '';
					$itemArray['items'][$key]['imageMedium'] = '';
					$itemArray['items'][$key]['imageSmall'] = '';
					$itemArray['items'][$key]['imageExtraLarge'] = '';
					$itemArray['items'][$key]['imageExtraSmall'] = '';
				}
				
			 	$extra_fields = json_decode($value->extra_fields,true);
        		if($extra_fields!=""){
					foreach ($extra_fields as $exkey=>$exvalue){
						$ext_query ="SELECT ext.type  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$exvalue['id']}";
						$this->db->setQuery($ext_query);
				        $exttype = $this->db->loadResult();
				        $extra_fields[$exkey]['type']=$exttype;
					}
					foreach ($extra_fields as $k1=>$v1){
						if(is_array($v1['value'])){
							$ext_query ="SELECT ext.value  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$v1['id']}";
							$this->db->setQuery($ext_query);
				        	$extVal = $this->db->loadResult();
				        	$jsonextVal = json_decode($extVal,true);
				        	unset($extra_fields[$k1]['value']);
				        	foreach($jsonextVal as $jsK=>$jsV){
				        		if(in_array($jsV['value'],$v1['value'])){
				        			$extra_fields[$k1]['value'][]=$jsV['name'];
				        		}
				        	}
						}else if($v1['type']=='image'){ 
							if($ext= pathinfo($v1['value'], PATHINFO_EXTENSION)){
							$extra_fields[$k1]['value']=JURI::root().$v1['value'];
							}
						}
						$ext_query = "SELECT ext.name  
					    			  FROM #__k2_extra_fields as ext 
					    			  WHERE ext.id={$v1['id']}";
						$this->db->setQuery($ext_query);
			        	$extname = $this->db->loadResult();
			        	$extra_fields[$k1]['name']=$extname;
					}
			 	}
			 	$itemArray['items'][$key]['extraFields'] = (isset($extra_fields) && !empty($extra_fields)) ? $extra_fields : "" ;
				$itemArray['items'][$key]['publishUp']    = $value->publish_up;
				$itemArray['items'][$key]['createdBy']    = $value->created_by;
				$query="SELECT name 
						FROM #__users 
						WHERE id={$value->created_by}";
        		$this->db->setQuery($query);
        		$created_by_name = $this->db->loadResult();
        		$itemArray['items'][$key]['createdByName']    = $created_by_name;
				require_once JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php';
				$K2ModelItem = new K2ModelItem();
				$itemArray['items'][$key]['tags'] = (count($K2ModelItem->getItemTags($value->id))>0) ? $K2ModelItem->getItemTags($value->id) : '';
				$itemArray['items'][$key]['ratings'] = (count($K2ModelItem->getRating($value->id))>0) ? $K2ModelItem->getRating($value->id) : '';
				$itemArray['items'][$key]['comments'] = (count($K2ModelItem->getItemComments($value->id,"","",true))>0) ? $K2ModelItem->getItemComments($value->id,"","",true) : '';
				$itemArray['items'][$key]['attachments'] = (count($K2ModelItem->getItemAttachments($value->id))>0) ? $K2ModelItem->getItemAttachments($value->id) : '';
				$itemArray['items'][$key]['fields'] = (count($this->getExtraFieldGroup($value->id))>0) ? $this->getExtraFieldGroup($value->id) : '';
				$share_link = JURI::root()."index.php?option=com_k2&view=item&id={$value->id}:{$value->alias}&Itemid=487";
				$itemArray['items'][$key]['shareLink'] = $share_link;
		}
    	$jsonarray = array();
    	$jsonarray['code']		 		= 200;
    	$jsonarray['pageLimit']			= $pageLimit;
    	$jsonarray['total']				= $countitems;
		$jsonarray['items']		 		= $itemArray['items'];
		
    	return $jsonarray;
    	
    }
    /**
     * @uses This function is used to get  items based on selected user and selected categories.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"Userpage",
	 * 		"taskData":{
	 * 				"menuId":"menuId",
	 * 				 }
	 *             
	 * 	}
	 */
	function Userpage(){
    	$userID 		=IJReq::getTaskData('userID');
    	$catID 			=IJReq::getTaskData('catId');
    	$ordering 	    =IJReq::getTaskData('ordering');
    	$pageNO         = IJReq::getTaskData('pageNO');
		$pageLimit      = IJReq::getTaskData('pageLimit');
    	if($pageNO==0 || $pageNO==1){
		  		$startFrom=0;		
		}else{
				$startFrom = $pageLimit*($pageNO-1);
		}
    	$implodeCAT     = implode(",",$catID);  
    	
    	$orders = array(
				'id'=>'i.id DESC',
				'date'=>'i.created ASC',
				'rdate'=>'i.created DESC',
				'alpha'=>'i.title',
				'ralpha'=>'i.title DESC',
				'order'=>'i.ordering',
				'rorder'=>'i.ordering DESC',
				'featured'=>'i.featured DESC, i.created DESC',
				'hits'=>'i.hits DESC',
				'rand'=>'RAND()',
				'best'=>'rating DESC',
				'modified'=>'lastChanged DESC',
				'publishUp'=>'i.publish_up DESC'
			);
		$orderby = (array_key_exists($ordering,$orders)) ? $orders[$ordering] : 'i.id DESC';
        
		$query = "SELECT count(i.id), CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged 
		   			FROM #__k2_items as i 
		   			WHERE i.id>0 
		   			AND i.trash=0 
		   			AND i.published=1 
		   			AND i.catid IN (".$implodeCAT.")
		   			AND i.created_by='".$userID."'
		   			AND i.created_by_alias=''";
        $this->db->setQuery($query);
        $countRows = $this->db->loadResult();
    
		if ($ordering == 'best'){
        	$query  = "SELECT i.* , (r.rating_sum/r.rating_count) AS rating FROM #__k2_items as i ";
			$query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id WHERE i.id>0 AND i.trash=0 AND i.published=1 
   					AND i.catid IN (".$implodeCAT.") AND i.created_by='".$userID."' AND i.created_by_alias=''";
		}else {
			$query = "SELECT i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged 
		   			FROM #__k2_items as i 
		   			WHERE i.id>0 
		   			AND i.trash=0 
		   			AND i.published=1 
		   			AND i.catid IN (".$implodeCAT.")
		   			AND i.created_by='".$userID."'
		   			AND i.created_by_alias=''";
		   			//AND i.id IN (".$implodeTagitem.")";
		}
		$query .= " ORDER BY ".$orderby;
		$query .= " LIMIT $startFrom, ".$pageLimit."";
		$this->db->setQuery($query);
        $rowss = $this->db->loadObjectList();
        if($countRows<=0){
			IJReq::setResponseCode(204);
			return false;
		}
        $itemArray = array();
        foreach($rowss as $key=>$value){
				$itemArray['items'][$key]['id'] 	         = $value->id;
				$itemArray['items'][$key]['title'] 		 	 = $value->title;
				$itemArray['items'][$key]['alias']     	 	 = $value->alias;
				$itemArray['items'][$key]['catId']     	 	 = $value->catid;
				$itemArray['items'][$key]['introText']       = strip_tags($value->introtext);
				$itemArray['items'][$key]['fullText'] 	     = strip_tags($value->fulltext);
				$video=$value->video;
				$ex_video=explode("}",$video);
				$finalvideo= str_replace("{",".",$ex_video[0]);
				if(isset($ex_video[1])){
			 		$final_video= explode("{",$ex_video[1]);
				}
				$itemArray['items'][$key]['video']=($finalvideo!="" && $final_video!="") ? JURI::base().'/media/k2/videos/'.$final_video[0].$finalvideo :"";	
			 	$gallery=$value->gallery;
			 	$ex_gallery=explode("}",$gallery);
				$finalgallery= str_replace("{",".",$ex_gallery[0]);
			 	$final_gallery= explode("{",$ex_gallery[1]);
			 	$path = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0];
			 	$folders = JFolder::folders($path, $filter = '.');
			 	
			 	foreach($folders as $key2=>$value2){
			 		$fullpath = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				  	$jpg_files = JFolder::files($fullpath, '');
				 	$fullpath1 = JURI::base().'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				 	if($jpg_files){
						foreach ($jpg_files as $key1=>$file1) {
							$itemArray['items'][$key]['imageGalleries'][$key1]['imageGallery'] = $fullpath1.'/'.$file1;
						}
		 			}else{
		 				$itemArray['items'][$key]['imageGalleries'] = "";	
		 			}
	 			}
			 	
	 			 $filename = 'media/k2/items/cache/'.md5("Image".$value->id).'_Generic.jpg';
				 $filename1 = 'media/k2/items/cache/'.md5("Image".$value->id).'_L.jpg';
				 $filename2 = 'media/k2/items/cache/'.md5("Image".$value->id).'_M.jpg';
				 $filename3 = 'media/k2/items/cache/'.md5("Image".$value->id).'_S.jpg';
				 $filename4 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XL.jpg';
				 $filename5 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XS.jpg';
				
				if (file_exists($filename)) {
					$itemArray['items'][$key]['imageGeneric'] = JURI::base().$filename;
					$itemArray['items'][$key]['imageLarge'] = JURI::base().$filename1;
					$itemArray['items'][$key]['imageMedium'] = JURI::base().$filename2;
					$itemArray['items'][$key]['imageSmall'] = JURI::base().$filename3;
					$itemArray['items'][$key]['imageExtraLarge'] = JURI::base().$filename4;
					$itemArray['items'][$key]['imageExtraSmall'] = JURI::base().$filename5;
				}else{
					$itemArray['items'][$key]['imageGeneric'] = '';
					$itemArray['items'][$key]['imageLarge'] = '';
					$itemArray['items'][$key]['imageMedium'] = '';
					$itemArray['items'][$key]['imageSmall'] = '';
					$itemArray['items'][$key]['imageExtraLarge'] = '';
					$itemArray['items'][$key]['imageExtraSmall'] = '';
				}
				
			 	$extra_fields = json_decode($value->extra_fields,true);
        		if($extra_fields!=""){
					foreach ($extra_fields as $exkey=>$exvalue){
						$ext_query ="SELECT ext.type  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$exvalue['id']}";
						$this->db->setQuery($ext_query);
				        $exttype = $this->db->loadResult();
				        $extra_fields[$exkey]['type']=$exttype;
					}
					foreach ($extra_fields as $k1=>$v1){
						if(is_array($v1['value'])){
							$ext_query ="SELECT ext.value  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$v1['id']}";
							$this->db->setQuery($ext_query);
				        	$extVal = $this->db->loadResult();
				        	$jsonextVal = json_decode($extVal,true);
				        	unset($extra_fields[$k1]['value']);
				        	foreach($jsonextVal as $jsK=>$jsV){
				        		if(in_array($jsV['value'],$v1['value'])){
				        			$extra_fields[$k1]['value'][]=$jsV['name'];
				        		}
				        	}
						}else if($v1['type']=='image'){ 
							if($ext= pathinfo($v1['value'], PATHINFO_EXTENSION)){
							$extra_fields[$k1]['value']=JURI::root().$v1['value'];
							}
						}
						$ext_query = "SELECT ext.name  
					    			  FROM #__k2_extra_fields as ext 
					    			  WHERE ext.id={$v1['id']}";
						$this->db->setQuery($ext_query);
			        	$extname = $this->db->loadResult();
			        	$extra_fields[$k1]['name']=$extname;
					}
			 	}
			 	$itemArray['items'][$key]['extraFields'] = (isset($extra_fields) && !empty($extra_fields)) ? $extra_fields : "" ;
				$itemArray['items'][$key]['publishUp']    = $value->publish_up;
				$itemArray['items'][$key]['createdBy']    = $value->created_by;
				$query="SELECT name 
						FROM #__users 
						WHERE id={$value->created_by}";
        		$this->db->setQuery($query);
        		$created_by_name = $this->db->loadResult();
        		$itemArray['items'][$key]['createdByName']    = $created_by_name;
				require_once JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php';
				$K2ModelItem = new K2ModelItem();
				$itemArray['items'][$key]['tags'] = (count($K2ModelItem->getItemTags($value->id))>0) ? $K2ModelItem->getItemTags($value->id) : '';
				$itemArray['items'][$key]['ratings'] = (count($K2ModelItem->getRating($value->id))>0) ? $K2ModelItem->getRating($value->id) : '';
				$itemArray['items'][$key]['comments'] = (count($K2ModelItem->getItemComments($value->id,"","",true))>0) ? $K2ModelItem->getItemComments($value->id,"","",true) : '';
				$itemArray['items'][$key]['attachments'] = (count($K2ModelItem->getItemAttachments($value->id))>0) ? $K2ModelItem->getItemAttachments($value->id) : '';
				$itemArray['items'][$key]['fields'] = (count($this->getExtraFieldGroup($value->id))>0) ? $this->getExtraFieldGroup($value->id) : '';
				$share_link = JURI::root()."index.php?option=com_k2&view=item&id={$value->id}:{$value->alias}&Itemid=487";
				$itemArray['items'][$key]['shareLink'] = $share_link;
		}
    	$jsonarray = array();
    	$jsonarray['code']		 		= 200;
    	$jsonarray['total'] 			= $countRows;
    	$jsonarray['pageLimit']			= $pageLimit;
		$jsonarray['items']		 		= $itemArray['items'];
		
    	return $jsonarray; 
	}
	/**
     * @uses This function is used to get latest items based on categories and users.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"LatestItems",
	 * 		"taskData":{
	 * 				"menuId":"menuId",
	 * 				 }
	 *             
	 * 	}
	 * */
	
	function LatestItems(){
		$source 		  =IJReq::getTaskData('source');
    	$userIDs 	      =IJReq::getTaskData('userIDs');
    	$categoryIDs 	  =IJReq::getTaskData('catId');
		$pageNO         = IJReq::getTaskData('pageNO');
		$pageLimit      = IJReq::getTaskData('pageLimit');
    	if($pageNO==0 || $pageNO==1){
		  		$startFrom=0;		
		}else{
				$startFrom = $pageLimit*($pageNO-1);
		}
    	$nullDate = $this->db->getNullDate();
    	$jnow = JFactory::getDate();
        $now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		if($source=='Categories'){
			
			$query= " SELECT i.*, CASE WHEN i.modified = 0 THEN i.created ELSE i.modified END as lastChanged, 
					    	c.name as categoryname,c.id as categoryid, c.alias as categoryalias, c.params as categoryparams 
					    	FROM #__k2_items as i RIGHT JOIN #__k2_categories AS c ON c.id = i.catid 
					    	WHERE i.published = 1 AND i.access IN(1,1) AND i.trash = 0 
					    	AND c.published = 1 AND c.access IN(1,1) AND c.trash = 0 
					    	AND ( i.publish_up =  ".$this->db->Quote($nullDate)." OR i.publish_up <= ".$this->db->Quote($now)." ) 
					    	AND ( i.publish_down = ".$this->db->Quote($nullDate)." OR i.publish_down >= ".$this->db->Quote($now)." ) ";
			$cnt = 1;
	    	foreach($categoryIDs as $categoryids)
	    	{
	    		if($cnt == 1) {
	    			$query .= " AND ( ";
	    		}
	    		if(count($categoryIDs) == $cnt) {
	    			$categories = $this->getCategoryTree($categoryids);
	            	$sql = @implode(',', $categories);
		    		$query .= " c.id IN (".$sql.") ";
	    			$query .= " ) ";
	    		} else {
	    			$categories = $this->getCategoryTree($categoryids);
	            	$sql = @implode(',', $categories);
	            	$query .= " c.id IN (".$sql.") OR ";
	    		}
	    		$cnt++;
	    	}
	    	$query .= " ORDER BY i.created DESC ";
	    	$queryLimit=$query;
	    	$query .= " LIMIT $startFrom, ".$pageLimit."";
			$this->db->setQuery($query);
	        $rows = $this->db->loadObjectList();
	        $this->db->setQuery($queryLimit);
	        $count = count($this->db->loadObjectList());
	       
		}else{
			$query= " SELECT i.*, c.alias as categoryalias FROM #__k2_items as i 
	    			LEFT JOIN #__k2_categories c ON c.id = i.catid 
	    			WHERE i.id != 0 AND i.published = 1 
	    			AND ( i.publish_up = ".$this->db->Quote($nullDate)." OR i.publish_up <= ".$this->db->Quote($now)." ) 
	    			AND ( i.publish_down = ".$this->db->Quote($nullDate)." OR i.publish_down >= ".$this->db->Quote($now)." ) 
	    			AND i.access IN(1,1) AND i.trash = 0 AND i.created_by IN (".implode(',', $userIDs).")  
	    			AND i.created_by_alias='' AND c.published = 1 
	    			AND c.access IN(1,1) AND c.trash = 0 
	    			ORDER BY i.created DESC"; 
			$queryLimit=$query;
	    	$query .= " LIMIT $startFrom, ".$pageLimit."";
	    	$this->db->setQuery($query);
	        $rows = $this->db->loadObjectList();
	        $this->db->setQuery($queryLimit);
        	$count = count($this->db->loadObjectList());
		}
	 	if($count<=0){
			IJReq::setResponseCode(204);
			return false;
		}
		foreach($rows as $key=>$value){
				$itemArray['items'][$key]['id'] 	         = $value->id;
				$itemArray['items'][$key]['title'] 		 	 = $value->title;
				$itemArray['items'][$key]['alias']     	 	 = $value->alias;
				$itemArray['items'][$key]['catId']     	 	 = $value->catid;
				$itemArray['items'][$key]['introText']       = strip_tags($value->introtext);
				$itemArray['items'][$key]['fullText'] 	     = strip_tags($value->fulltext);
				$video=$value->video;
				$ex_video=explode("}",$video);
				$finalvideo= str_replace("{",".",$ex_video[0]);
				if(isset($ex_video[1])){
			 		$final_video= explode("{",$ex_video[1]);
				}
				$itemArray['items'][$key]['video']=($finalvideo!="" && $final_video!="") ? JURI::base().'/media/k2/videos/'.$final_video[0].$finalvideo :"";	
			 	$gallery=$value->gallery;
			 	$ex_gallery=explode("}",$gallery);
				$finalgallery= str_replace("{",".",$ex_gallery[0]);
			 	$final_gallery= explode("{",$ex_gallery[1]);
			 	$path = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0];
			 	$folders = JFolder::folders($path, $filter = '.');
			 	
			 	foreach($folders as $key2=>$value2){
			 		$fullpath = JPATH_SITE.'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				  	$jpg_files = JFolder::files($fullpath, '');
				 	$fullpath1 = JURI::base().'/media/k2/galleries/'.$final_gallery[0].'/'.$value2;
				 	if($jpg_files){
						foreach ($jpg_files as $key1=>$file1) {
							$itemArray['items'][$key]['imageGalleries'][$key1]['imageGallery'] = $fullpath1.'/'.$file1;
						}
		 			}else{
		 				$itemArray['items'][$key]['imageGalleries'] = "";	
		 			}
	 			}
			 	
	 			 $filename = 'media/k2/items/cache/'.md5("Image".$value->id).'_Generic.jpg';
				 $filename1 = 'media/k2/items/cache/'.md5("Image".$value->id).'_L.jpg';
				 $filename2 = 'media/k2/items/cache/'.md5("Image".$value->id).'_M.jpg';
				 $filename3 = 'media/k2/items/cache/'.md5("Image".$value->id).'_S.jpg';
				 $filename4 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XL.jpg';
				 $filename5 = 'media/k2/items/cache/'.md5("Image".$value->id).'_XS.jpg';
				
				if (file_exists($filename)) {
					$itemArray['items'][$key]['imageGeneric'] = JURI::base().$filename;
					$itemArray['items'][$key]['imageLarge'] = JURI::base().$filename1;
					$itemArray['items'][$key]['imageMedium'] = JURI::base().$filename2;
					$itemArray['items'][$key]['imageSmall'] = JURI::base().$filename3;
					$itemArray['items'][$key]['imageExtraLarge'] = JURI::base().$filename4;
					$itemArray['items'][$key]['imageExtraSmall'] = JURI::base().$filename5;
				}else{
					$itemArray['items'][$key]['imageGeneric'] = '';
					$itemArray['items'][$key]['imageLarge'] = '';
					$itemArray['items'][$key]['imageMedium'] = '';
					$itemArray['items'][$key]['imageSmall'] = '';
					$itemArray['items'][$key]['imageExtraLarge'] = '';
					$itemArray['items'][$key]['imageExtraSmall'] = '';
				}
				
			 	$extra_fields = json_decode($value->extra_fields,true);
				if($extra_fields!=""){
					foreach ($extra_fields as $exkey=>$exvalue){
						$ext_query ="SELECT ext.type  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$exvalue['id']}";
						$this->db->setQuery($ext_query);
				        $exttype = $this->db->loadResult();
				        $extra_fields[$exkey]['type']=$exttype;
					}
					foreach ($extra_fields as $k1=>$v1){
						if(is_array($v1['value'])){
							$ext_query ="SELECT ext.value  
					    			 	FROM #__k2_extra_fields as ext 
					    			  	WHERE ext.id={$v1['id']}";
							$this->db->setQuery($ext_query);
				        	$extVal = $this->db->loadResult();
				        	$jsonextVal = json_decode($extVal,true);
				        	unset($extra_fields[$k1]['value']);
				        	foreach($jsonextVal as $jsK=>$jsV){
				        		if(in_array($jsV['value'],$v1['value'])){
				        			$extra_fields[$k1]['value'][]=$jsV['name'];
				        		}
				        	}
						}else if($v1['type']=='image'){ 
							if($ext= pathinfo($v1['value'], PATHINFO_EXTENSION)){
							$extra_fields[$k1]['value']=JURI::root().$v1['value'];
							}
						}
						$ext_query = "SELECT ext.name  
					    			  FROM #__k2_extra_fields as ext 
					    			  WHERE ext.id={$v1['id']}";
						$this->db->setQuery($ext_query);
			        	$extname = $this->db->loadResult();
			        	$extra_fields[$k1]['name']=$extname;
					}
			 	}
			 	$itemArray['items'][$key]['extraFields'] = (isset($extra_fields) && !empty($extra_fields)) ? $extra_fields : "" ;
				$itemArray['items'][$key]['publishUp']    = $value->publish_up;
				$itemArray['items'][$key]['createdBy']    = $value->created_by;
				$query="SELECT name 
						FROM #__users 
						WHERE id={$value->created_by}";
        		$this->db->setQuery($query);
        		$created_by_name = $this->db->loadResult();
        		$itemArray['items'][$key]['createdByName']    = $created_by_name;
				require_once JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php';
				$K2ModelItem = new K2ModelItem();
				$itemArray['items'][$key]['tags'] = (count($K2ModelItem->getItemTags($value->id))>0) ? $K2ModelItem->getItemTags($value->id) : '';
				$itemArray['items'][$key]['ratings'] = (count($K2ModelItem->getRating($value->id))>0) ? $K2ModelItem->getRating($value->id) : '';
				$itemArray['items'][$key]['comments'] = (count($K2ModelItem->getItemComments($value->id,"","",true))>0) ? $K2ModelItem->getItemComments($value->id,"","",true) : '';
				$itemArray['items'][$key]['attachments'] = (count($K2ModelItem->getItemAttachments($value->id))>0) ? $K2ModelItem->getItemAttachments($value->id) : '';
				$itemArray['items'][$key]['fields'] = (count($this->getExtraFieldGroup($value->id))>0) ? $this->getExtraFieldGroup($value->id) : '';
				$share_link = JURI::root()."index.php?option=com_k2&view=item&id={$value->id}:{$value->alias}&Itemid=487";
				$itemArray['items'][$key]['shareLink'] = $share_link;
		}
		$jsonarray = array();
    	$jsonarray['code']		 		= 200;
    	$jsonarray['total'] 			= $count;
    	$jsonarray['pageLimit'] 		= $pageLimit;
		$jsonarray['items']		 		= $itemArray['items'];
        return $jsonarray; 
	}
   
    
    /**
     * @uses This function is used to get rating based on particular item.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"rating",
	 * 		"taskData":{
	 * 				"itemID":"itemID",
	 * 				 }
	 *             
	 * 	}
	 * */
    
	function rating($itemID){
    	
   	  	$query = "SELECT r.*  
   	  			FROM #__k2_rating AS r 
		 		WHERE r.itemID ={$itemID}";
		$this->db->setQuery($query);
        $rows = $this->db->loadObjectList();
        $jsonarray = array();
		foreach($rows as $row){
			$jsonarray['rating']['rating_sum'] 	     	 = $row->rating_sum;
			$jsonarray['rating']['rating_count'] 		 = $row->rating_count;
			$jsonarray['rating']['lastip'] 		         = $row->lastip;
		}
		return $jsonarray;
    }
    
    /**
     * @uses This function is used to get comments based on particular item.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"comments",
	 * 		"taskData":{
	 * 				"itemID":"itemID",
	 * 				 }
	 *             
	 * 	}
	 * */
    
    function comments($itemID){
    	$query = "SELECT c.*  
    			FROM #__k2_comments AS c 
		 		WHERE c.itemID ={$itemID}";
    	
		$this->db->setQuery($query);
        $rows = $this->db->loadObjectList();
        $jsonarray = array();
		foreach($rows as $key=>$row){
			$jsonarray[$key]['comment']['userID'] 	     = $row->userID;
			$jsonarray[$key]['comment']['userName'] 		 = $row->userName;
			$jsonarray[$key]['comment']['commentDate'] 	 = $row->commentDate;
			$jsonarray[$key]['comment']['commentText'] 	 = $row->commentText;
			$jsonarray[$key]['comment']['commentEmail'] 	 = $row->commentEmail;
			$jsonarray[$key]['comment']['commentURL'] 	 = $row->commentURL;
		}
		return $jsonarray;
    }
    
    /**
     * @uses This function is used to get attachments based on particular item.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"attachments",
	 * 		"taskData":{
	 * 				"itemID":"itemID",
	 * 				 }
	 *             
	 * }
	 * */
    
	function attachments($itemID){
    	$query = "SELECT at.*  
    			FROM #__k2_attachments AS at 
		 		WHERE at.itemID ={$itemID}";
    	
		$this->db->setQuery($query);
        $rows = $this->db->loadObjectList();
        $jsonarray = array();
		foreach($rows as $key=>$row){
			$jsonarray[$key]['attachment']['filename'] 	     = JURI::base().'/media/k2/attachments/'.$row->filename;
			$jsonarray[$key]['attachment']['title'] 		     = $row->title;
			$jsonarray[$key]['attachment']['titleAttribute'] 	 = $row->titleAttribute;
		}
		return $jsonarray;
    	
    }
    
     /**
     * @uses This function is used to insert rating in database of particular item.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"addrating",
	 * 		"taskData":{
	 * 				"user_rating":"3",
	 * 				"itemID":"68"
	 * 				}
	 * }
	 * */
    
    function addrating(){
    	
    	$rate   = IJReq::getTaskData('userRating', 0, '', 'int');
		$itemID = IJReq::getTaskData('itemId',1,'int');
		$userIP = $_SERVER["REMOTE_ADDR"];
				
    	if ($rate >= 1 && $rate <= 5)
		{
			$db     = JFactory::getDBO();
			//$userIP = $_SERVER['REMOTE_ADDR'];
			$query  = "SELECT * FROM #__k2_rating WHERE itemID ={$itemID}";
			$db->setQuery($query);
			$rating = $db->loadObject();
			
			if (!$rating)
			{
				$query = "INSERT INTO #__k2_rating 
						( itemID, lastip, rating_sum, rating_count ) 
						VALUES ( ".$itemID.", ".$db->Quote($userIP).", {$rate}, 1 )";
				$db->setQuery($query);
				$db->query();
				$GetRatings = $this->rating($itemID);
				$rating = new stdClass;
				$rating->code = '200';
				$rating->message = 'Thanks For Rating';
				$rating->itemID = $itemID;
				$rating->rating_sum = $GetRatings['rating']['rating_sum'];
				$rating->rating_count = $GetRatings['rating']['rating_count'];
				$rating->lastip = $GetRatings['rating']['lastip'];
				return $rating;
			}
			else
			{
				if ($userIP != ($rating->lastip))
				{
					$query = "UPDATE #__k2_rating"." 
							SET rating_count = rating_count + 1, 
							rating_sum = rating_sum + {$rate}, 
							lastip = ".$db->Quote($userIP)." 
							WHERE itemID = $itemID";
					$db->setQuery($query);
					$db->query();
					$GetRatings = $this->rating($itemID);
					$rating = new stdClass;
					$rating->code = '200';
					$rating->message = 'Thanks For Rating';
					$rating->itemID = $itemID;
					$rating->rating_sum = $GetRatings['rating']['rating_sum'];
					$rating->rating_count = $GetRatings['rating']['rating_count'];
					$rating->lastip = $GetRatings['rating']['lastip'];
					return $rating;
				}
				else
				{
					IJReq::setResponseCode(416);
					IJReq::setResponseMessage("You Have Already Rated This Item");
				}
			}

		}
		
    }
    
     /**
     * @uses This function is used to make a form for comment.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"commentform",
	 * 		"taskData":{
	 * 				}
	 * }
	 * */
    
    function commentform(){
    	 
    	 $jsonarray  = array();
		 $jsonarray['code'] = "200";
    	
    	 $jsonarray['field'][0]['fieldid'] = 1;
    	 $jsonarray['field'][0]['fieldtype'] = "textarea";
         $jsonarray['field'][0]['fieldrequired'] = 1;
         $jsonarray['field'][0]['fieldname'] = "commentText";
         $jsonarray['field'][0]['fieldcaption'] = "commentText";
      
         $jsonarray['field'][1]['fieldid'] = 2;
    	 $jsonarray['field'][1]['fieldtype'] = "text";
         $jsonarray['field'][1]['fieldrequired'] = 1;
         $jsonarray['field'][1]['fieldname'] = "userName";
         $jsonarray['field'][1]['fieldcaption'] = "userName";
        
         $jsonarray['field'][2]['fieldid'] = 3;
    	 $jsonarray['field'][2]['fieldtype'] = "text";
         $jsonarray['field'][2]['fieldrequired'] = 1;
         $jsonarray['field'][2]['fieldname'] = "commentEmail";
         $jsonarray['field'][2]['fieldcaption'] = "commentEmail";
             
         $jsonarray['field'][3]['fieldid'] = 4;
    	 $jsonarray['field'][3]['fieldtype'] = "text";
         $jsonarray['field'][3]['fieldrequired'] = 1;
         $jsonarray['field'][3]['fieldname'] = "commentURL";
         $jsonarray['field'][3]['fieldcaption'] = "commentURL";
          
		 return $jsonarray;  
          
    }
    
   /**
     * @uses This function is used to insert comment-detail of particular item in database.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"addcomment",
	 * 		"taskData":{
	 * 				"itemID":"68",
	 * 				"userName":"nisha",
	 * 				"commentText":"see you",
	 * 				"commentEmail":"xyzZ@gmail.com",
	 * 				"commentURL":"www.yahoo.com"
	 * 				}
	 * }
	 * */
    
    function addcomment(){
    	
		$itemID       = IJReq::getTaskData('itemId',1,'int');
		$uname        = IJReq::getTaskData('userName',1,'char');
		$commentText  = IJReq::getTaskData('commentText',1,'char');
		$commentEmail = IJReq::getTaskData('commentEmail',1,'char');
		$commentURL   = IJReq::getTaskData('commentURL',1,'char');
		
		//jimport( 'joomla.application.component.helper' );
       	$parameters = JComponentHelper::getParams('com_k2');
       	$published = $parameters->get ('commentsPublishing');
		
    	$db     = JFactory::getDBO();
    	if($published == 1){ 
	    	$query = "INSERT INTO #__k2_comments 
	    				( itemID, userName, commentDate, commentText, commentEmail, commentURL, published ) 
	    				VALUES ( '".$itemID."','".$uname."',NOW(),'".$commentText."','".$commentEmail."','".$commentURL."','".$published."'  )";
	    	$db->setQuery($query);
			$db->query();
			$lastID=$db->insertid();
    	}else{
			$query = "INSERT INTO #__k2_comments 
						( itemID, userName, commentDate, commentText, commentEmail, commentURL, published ) 
	    				VALUES ( '".$itemID."','".$uname."',NOW(),'".$commentText."','".$commentEmail."','".$commentURL."','".$published."'  )";
			$db->setQuery($query);
			$db->query();
			$lastID=$db->insertid();
    	}	
		$params = K2HelperUtilities::getParams('com_k2');
		$order = $params->get('commentsOrdering', 'DESC');
		$ordering = ($order == 'DESC') ? 'DESC' : 'ASC';
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__k2_comments WHERE id=".(int)$lastID;
		if ($published)
		{
			$query .= " AND published=1 ";
		}
		$query .= " ORDER BY commentDate {$ordering}";
		$db->setQuery($query, $limitstart, $limit);
		$comments = $db->loadObject();
		$book = new stdClass;
		$book->code = '200';
		if($published){
			$book->message = 'Successfull Comment Submitted and Published';
		}else{
			$book->message = 'Successfull Comment Submitted but UnPublished';
		}
		$book->id = $comments->id;
		$book->itemID = $comments->itemID;
		$book->userID = $comments->userID;
		$book->userName = $comments->userName;
		$book->commentDate = $comments->commentDate;
		$book->commentText = $comments->commentText;
		$book->commentEmail = $comments->commentEmail;
		$book->commentURL = $comments->commentURL;
		$book->published = $comments->published;
		return $book;
   
    }
    
    /**
     * @uses This function is used to get extrafield group of particular item.
     * @example the json string will be like, :
     * {
	 * 		"extName":"k2",
	 *		"extView":"items",
 	 *		"extTask":"getExtraField",
	 * 		"taskData":{
	 * 				"catID":"67"
	 * 				}
	 * }
	 * */
    
    function getExtraFieldGroup($itemID){
    	
    	$query1 = "SELECT catid
				FROM `#__k2_items`
				WHERE `id` ={$itemID}";
    	$this->db->setQuery($query1);
        $catID = $this->db->loadResult();
    	$query = "SELECT  ex.*
    			FROM #__k2_categories AS c, #__k2_extra_fields_groups as ex
    			WHERE ex.id = c.extraFieldsGroup 
    			AND c.id={$catID}"; 
		$this->db->setQuery($query);
        $rows = $this->db->loadObjectList();
    	$total=count($rows);
        if($total<=0){
			IJReq::setResponseCode(204);
			return false;
		}
		foreach($rows as $value){
			$jsonarray['extrafieldgroup']['group_id']   	= $value->id;
			$jsonarray['extrafieldgroup']['group_name']   	= $value->name;
			$jsonarray['extrafieldgroup']['field']  		= $this->getExtraField($value->id);
			
		}
		return $jsonarray;
    }
    
    function getExtraField($group){
    	$query = "SELECT  ex.* 
    			FROM  #__k2_extra_fields as ex
    			WHERE ex.group ={$group}"; 
		$this->db->setQuery($query);
        $rows = $this->db->loadObjectList();
       	$total=count($rows);
        if($total<=0){
			IJReq::setResponseCode(204);
			return false;
		}
    	
		foreach($rows as $key=>$value){
			$jsonarray[$key]['id'] 	        = $value->id;
			$jsonarray[$key]['name'] 		= $value->name;
			$jsonarray[$key]['value']     	= $value->value;
			$jsonarray[$key]['type']        = $value->type;
			$jsonarray[$key]['published']   = $value->published;
			$jsonarray[$key]['ordering']    = $value->ordering;
		}
		return $jsonarray;
    }
    
	function getCategoryTree($categories)
    {
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        if (!is_array($categories))
        {
            $categories = (array)$categories;
        }
        JArrayHelper::toInteger($categories);
        $categories = array_unique($categories);
        sort($categories);
        $key = implode('|', $categories);
        $clientID = $mainframe->getClientId();
        static $K2CategoryTreeInstances = array();
        if (isset($K2CategoryTreeInstances[$clientID]) && array_key_exists($key, $K2CategoryTreeInstances[$clientID]))
        {
            return $K2CategoryTreeInstances[$clientID][$key];
        }
        $array = $categories;
        while (count($array))
        {
            $query = "SELECT id
						FROM #__k2_categories 
						WHERE parent IN (".implode(',', $array).") 
						AND id NOT IN (".implode(',', $array).") ";
            if ($mainframe->isSite())
            {
                $query .= "
								AND published=1 
								AND trash=0";
                if (K2_JVERSION != '15')
                {
                    $query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).")";
                    if ($mainframe->getLanguageFilter())
                    {
                        $query .= " AND language IN(".$db->Quote(JFactory::getLanguage()->getTag()).", ".$db->Quote('*').")";
                    }
                }
                else
                {
                    $query .= " AND access<={$aid}";
                }
            }
            $db->setQuery($query);
            $array = K2_JVERSION == '30' ? $db->loadColumn() : $db->loadResultArray();
            $categories = array_merge($categories, $array);
        }
        JArrayHelper::toInteger($categories);
        $categories = array_unique($categories);
        $K2CategoryTreeInstances[$clientID][$key] = $categories;
        return $categories;
    }
    
 	function categoriesTree($row = NULL, $hideTrashed = false, $hideUnpublished = true)
    {
        $db = JFactory::getDBO();
        if (isset($row->id))
        {
            $idCheck = ' AND id != '.( int )$row->id;
        }
        else
        {
            $idCheck = null;
        }
        if (!isset($row->parent))
        {
            if (is_null($row))
            {
                $row = new stdClass;
            }
            $row->parent = 0;
        }
        $query = "SELECT m.* FROM #__k2_categories m WHERE id > 0 {$idCheck}";

        if ($hideUnpublished)
        {
            $query .= " AND published=1 ";
        }

        if ($hideTrashed)
        {
            $query .= " AND trash=0 ";
        }

        $query .= " ORDER BY parent, ordering";
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                if (K2_JVERSION != '15')
                {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                }
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();
        foreach ($list as $item)
        {
            $item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);

            if ($item->trash)
                $item->treename .= ' [**'.JText::_('K2_TRASHED_CATEGORY').'**]';
            if (!$item->published)
                $item->treename .= ' [**'.JText::_('K2_UNPUBLISHED_CATEGORY').'**]';

            $mitems[] = JHTML::_('select.option', $item->id, $item->treename);
        }
        return $mitems;
    }
    
	function getCategoryChildren($catid, $clear = false)
    {
		static $array = array();
        if ($clear)
            $array = array();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $catid = (int)$catid;
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 ORDER BY ordering ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        foreach ($rows as $row)
        {
            array_push($array, $row->id);
            if ($this->hasChildren($row->id))
            {
                $this->getCategoryChildren($row->id);
            }
        }
        return $array;
    }
    
	function getCategorySubcat($catid, $clear = false)
    {
        static $array = array();
        if ($clear)
            $array = array();
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $catid = (int)$catid;
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$catid} AND published=1 AND trash=0 ORDER BY ordering ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        foreach ($rows as $row)
        {
            array_push($array, $row);
            if ($this->hasChildren($row->id))
            {
                $this->getCategorySubcat($row->id);
            }
        }
        return $array;
    }

    // Deprecated function, left for compatibility reasons
    function hasChildren($id)
    {
        $user = JFactory::getUser();
        $aid = (int)$user->get('aid');
        $id = (int)$id;
        $db = JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 ";
        $db->setQuery($query);
        $rows = $db->loadObjectList();
		if (count($rows))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
			
}