<?php
/**
 * @package     IJoomer.Frontend
 * @subpackage  com_ijoomeradv.extensions
 *
 * @copyright   Copyright (C) 2010 - 2015 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For The Categories
 *
 * @since  1.0
 */
class Categories
{
	private $db;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();
	}

	/**
	 * Category list
	 *
	 * @example the json string will be like, :
	 *    {
	 *        "extName":"icms",
	 *        "extView":"categories",
	 *        "extTask":"allCategories",
	 *        "taskData":{}
	 *    }
	 *
	 * @return it will return some value
	 */

	public function category()
	{
		$id = IJReq::getTaskData('id', 0, 'int');
		$categories = $this->getCategories($id);

		if ($id <= 0)
		{
			$articles = null;
		}
		else
		{
			$articles = $this->getArticles($id);
		}

		return $this->prepareObject($articles, $categories);
	}

	/**
	 * The Get Categories Function
	 *
	 * @param   [type]  $id  it contains the id
	 *
	 * @return  it will return $categories
	 */
	private function getCategories($id)
	{
		JRequest::setVar('id', $id);
		include_once JPATH_SITE . '/libraries/joomla/application/categories.php';
		include_once JPATH_SITE . '/components/com_content/models/categories.php';

		if ($id == 0)
		{
			$ContentModelCategories = new ContentModelCategories;
			$categories = $ContentModelCategories->getItems();
		}
		else
		{
			$ContentModelCategory = new ContentModelCategory;
			$articles = $ContentModelCategory->getItems();
			$categories = $ContentModelCategory->getChildren();
		}

		return (json_decode(json_encode($categories)));
	}

	/**
	 * The GetArticles Function
	 *
	 * @param   [type]  $id  it contains the value of id
	 *
	 * @return  it will returns the value of $articles
	 */
	private function getArticles($id)
	{
		JRequest::setVar('id', $id);
		include_once JPATH_SITE . '/libraries/joomla/application/categories.php';
		include_once JPATH_SITE . '/components/com_content/models/categories.php';

		if ($id == 0)
		{
			$articles = '';
		}
		else
		{
			$ContentModelCategory = new ContentModelCategory;
			$articles = $ContentModelCategory->getItems();
			$articles = json_decode(json_encode($articles));
		}

		return (json_decode(json_encode($articles)));
	}

	/**
	 * Function for prepare object with list of articles and categories
	 *
	 * @param   [type]  $articles    contains the value of $articles
	 * @param   [type]  $categories  contains the value of $categories
	 *
	 * @return  array   $jssonarray
	 */
	private function prepareObject($articles, $categories)
	{
		$totalarticles = count($articles);
		$totalcategories = count($categories);

		if ($totalarticles <= 0 && $totalcategories <= 0)
		{
			$jsonarray['code'] = 204;

			return $jsonarray;
		}

		if ($totalarticles <= 0)
		{
			$articleArray['articles'] = '';
		}
		else
		{
			require_once JPATH_COMPONENT . '/extensions/icms/articles.php';
			$articlesObj = new articles;

			$articleArray = $articlesObj->getArticleList($articles, $totalarticles, true);
		}

		if ($totalcategories <= 0)
		{
			$categoryArray['categories'] = '';
		}
		else
		{
			require_once JPATH_SITE . '/components/com_content/models/category.php';
			$categoryObj = new ContentModelCategory;
			$inc = 0;
			$categoryArray = null;

			foreach ($categories as $key => $value)
			{
				$subcategory = $this->getCategories($value->id);
				$subcategorycount = count($subcategory);
				$ischild = false;

				if ($subcategorycount > 0 or $value->numitems > 0)
				{
					$ischild = true;
				}
				else
				{
					$ischild = $this->getChildCount($value->id);
				}

				if ($ischild)
				{
					$categoryArray['categories'][$inc]['categoryid'] = $value->id;
					$categoryArray['categories'][$inc]['title'] = $value->title;
					$categoryArray['categories'][$inc]['description'] = strip_tags($value->description);

					$images = array();
					preg_match_all('/(src)=("[^"]*")/i', $value->description, $images);
					$imgpath = str_replace(array('src="', '"'), "", $images[0]);

					if (!empty($imgpath[0]))
					{
						$image_properties = parse_url($imgpath[0]);

						if (empty($image_properties['host']))
						{
							$imgpath[0] = JUri::base() . $imgpath[0];
						}
					}

					$categoryArray['categories'][$inc]['image'] = ($imgpath) ? $imgpath[0] : '';
					$categoryArray['categories'][$inc]['parent_id'] = $value->parent_id;
					$categoryArray['categories'][$inc]['hits'] = $value->hits;
					$categoryArray['categories'][$inc]['totalarticles'] = ($value->numitems) ? $value->numitems : 0;

					$query = $this->db->getQuery(true);

					// Create the base select statement.
					$query->select('count(id)')
						->from($this->db->qn('#__categories'))
						->where($this->db->qn('parent_id') . ' = ' . $this->db->q($value->id))
						->where($this->db->qn('published') . ' = ' . $this->db->q('1'));

					$this->db->setQuery($query);
					$categoryArray['categories'][$inc]['totalcategories'] = $this->db->loadResult();

					$inc++;
				}
			}

			if (!$categoryArray)
			{
				$categoryArray['categories'] = '';
			}
		}

		$jsonarray['code'] = 200;
		$jsonarray['total'] = $totalarticles;
		$jsonarray['pageLimit'] = ICMS_ARTICLE_LIMIT;
		$jsonarray['articles'] = $articleArray['articles'];
		$jsonarray['categories'] = $categoryArray['categories'];

		return $jsonarray;
	}

	/**
	 * The Get Child Count Function
	 *
	 * @param   [type]  $id  it will contains the value of id
	 *
	 * @return  boolean it will return the value in true or false
	 */
	private function getChildCount($id)
	{
		$childcategory = $this->getCategories($id);

		foreach ($childcategory as $key => $value)
		{
			if ($value->numitems > 0)
			{
				return true;
			}
			else
			{
				$this->getChildCount($value->id);
			}
		}

		return false;
	}
}
