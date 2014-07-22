<?php
/*--------------------------------------------------------------------------------
# Ijoomeradv Extension : EASYBLOG_1.5 (compatible with easybBlog 3.8.14427)
# ------------------------------------------------------------------------
# author Tailored Solutions - ijoomer.com
# copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.ijoomer.com
# Technical Support: Forum - http://www.ijoomer.com/Forum/
----------------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );
class easyblog_helper {
	
	private $db_helper;
	
	function __construct(){
		$this->db_helper =& JFactory::getDBO();
	}
	
	function getAllBlogList() {
		require_once JPATH_ADMINISTRATOR.'/components/com_easyblog/models/blogs.php';
		$class = new EasyBlogModelBlogs();
		$query = $class->getBlogs();

		$this->db_helper->setQuery($query);
		$result = $this->db_helper->loadObjectList();
		return $result; 
	}
	function getAllBlogCategory() {
		require_once JPATH_ADMINISTRATOR.'/components/com_easyblog/models/categories.php';
		$class = new EasyBlogModelCategories();
		$query = $class->_buildQuery();

		$this->db_helper->setQuery($query);
		$result = $this->db_helper->loadObjectList();
		return $result; 
	}
	
	/*function getParseData($results){
		$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
		$resultData = array();
		switch ($results['view']){
			case 'article':
				$results['id'] = $safeHtmlFilter->clean($results['id'], 'int');
				$resultData['itemview']='IcmsSingleArticle';
				$resultData['itemdata']['id']=$results['id'];
			break;
			
			case 'featured':
				$resultData['itemview']='IcmsFeaturedArticles';
			break;
			
			case 'category':
				$resultData['itemview']=($results['layout']=='blog')?'IcmsCategoryBlog':'IcmsAllCategory';
				$resultData['itemdata']['id']=$results['id'];
			break;
		}
		
		if(!empty($resultData)){
			$resultData['type'] = 'icms';
		}
		
		return $resultData;
	}*/
}