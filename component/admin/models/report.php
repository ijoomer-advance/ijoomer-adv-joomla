<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * The Class For IJoomeradvModelReport which will Extends The JModelList
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class IjoomeradvModelReport extends JModelList
{
	var $db;

/**
 * Constructor
 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();

		$config = null;

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'created', 'a.created',
				'status', 'a.status'
			);
		}

		parent::__construct($config);
	}

	/**
	 * The Function For Getting The Report
	 *
	 * @return  it will returns the loadobjectList
	 */
	public function getReports()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->qn('#__ijoomeradv_report'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			return $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * The Function For Getting The Extension
	 *
	 * @return  it will returns the loadobjectList
	 */
	public function getExtensions()
	{
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('name,classname')
			->from($this->db->qn('#__ijoomeradv_extensions'))
			->where($this->db->qn('published') . ' = ' . $this->db->q('1'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			return $this->db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * The Function For PopulateState
	 *
	 * @param   [type]  $ordering   contains the value of ordering
	 * @param   [type]  $direction  contains the value of directions
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		$extension = JRequest::getVar('extensiontype', null);

		if (!empty($extension))
		{
			$app->setUserState($this->context . '.extensiontype', $extension);
		}

		$extensiontype = $app->getUserState($this->context . '.extensiontype');
		$this->setState('filter.extensiontype', $extensiontype);

		// List state information.
		parent::populateState('id', 'asc');
	}

	/**
	 * The Function For The Delete
	 *
	 * @return  boolean it will returns the value in true or false
	 */
	public function delete()
	{
		$cids = JRequest::getVar('cid', array());
		$table = $this->getTable('report', 'IjoomeradvTable');

		foreach ($cids as $cid)
		{
			if (!$table->delete($cid))
			{
				$this->setError($table->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * The Function For The Ignore
	 *
	 * @return  boolean it will returns the value in true or false
	 */
	public function ignore()
	{
		$cid = JRequest::getInt('cid', 0);

		if ($cid)
		{
			$table = $this->getTable('report', 'IjoomeradvTable');
			$table->load($cid);

			if ($table->status != 1)
			{
				$table->status = 2;

				if ($table->store())
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * The Function Deletereport is used for Deleting The Report
	 *
	 * @return  void
	 */
	public function deletereport()
	{
		$cid = JRequest::getInt('cid', 0);
		$result = false;

		if ($cid)
		{
			$table = $this->getTable('report', 'IjoomeradvTable');
			$table->load($cid);

			$extension = $table->extension;
			$params = json_decode($table->params);

			if ($extension == 'jomsocial')
			{
				require_once JPATH_ROOT . '/components/com_community/libraries/' . 'core.php';

				switch ($params->type)
				{
					case 'activity':
						CFactory::load('libraries', 'activities');
						$activity = JTable::getInstance('Activity', 'CTable');
						$activity->load($params->content->id);
						$jomparams = json_decode($activity->params);

						switch ($activity->app)
						{
							case 'profile':
								$profile = JTable::getInstance('Profile', 'CTable');
								$profile->load($activity->actor);
								$profile->status = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');

								$activity->title = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');

								$result = ($activity->store()) ? true : false;
								$result = ($profile->store()) ? true : false;
								break;

							case 'albums.comment':
							case 'albums':
								$activity->content = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
								$result = ($activity->store()) ? true : false;

								$wall = JTable::getInstance('Wall', 'CTable');
								$wall->load($jomparams->wallid);
								$wall->comment = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
								break;

							case 'photos':
								if ($jomparams->action == 'upload')
								{
									$activity->title = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
									$photo = JTable::getInstance('Photo', 'CTable');
									$photo->load($jomparams->photoid);
									$photo->caption = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
									$photo->store();
								}
								else
								{
									$activity->content = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
									$wall = JTable::getInstance('Wall', 'CTable');
									$wall->load($jomparams->wallid);
									$wall->comment = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
									$wall->store();
								}

								$result = ($activity->store()) ? true : false;
								break;

							case 'events.wall':
							case 'groups.wall':
								$activity->title = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
								$result = ($activity->store()) ? true : false;
								break;

							default:
								// @TODO : Default
								break;
						}

						break;
					case 'wall':
						switch ($params->content->app)
						{
							case 'videos':
							case 'profile.status':
							case 'albums':
							case 'groups.wall':
							case 'events.wall':
								$wall = JTable::getInstance('Wall', 'CTable');
								$wall->load($params->content->id);
								$wall->comment = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
								$result = ($wall->store()) ? true : false;
								break;
						}
						break;

					case 'photos':
						$photo = JTable::getInstance('Photo', 'CTable');
						$photo->load($params->content->id);
						$photo->caption = JText::_('COM_IJOOMERADV_REPORT_REMOVED_TEXT');
						$result = ($photo->store()) ? true : false;
						break;

					default:
						// @TODO : Default
						break;
				}
			}

			// Set status in report table
			if ($result)
			{
				$table = $this->getTable('report', 'IjoomeradvTable');
				$table->load($cid);
				$table->status = 1;

				if ($table->store())
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * The Function GetListQuery
	 *
	 * @return  it will returns the query
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$query = $this->db->getQuery(true);

		$query->select('a.*');

		//filter by extension type
		$extensiontype = $this->getState('filter.extensiontype');

		if (!empty($extensiontype) && $extensiontype!='default')
		{
			$query->where($this->db->qn('extension') . ' = ' . $this->db->q($extensiontype));
		}

		$cid = JRequest::getVar('cid',0);

		if ($cid)
		{
			$subQuery = $this->db->getQuery(true);

			$subQuery->select('params')
					->from($this->db->qn('#__ijoomeradv_report'))
					->where($this->db->qn('id') . ' = ' . $this->db->q($cid));

			// Set the query and load the result.
			$this->db->setQuery($subQuery);

			$result = $this->db->loadResult();

			$query->where($this->db->qn('a.params') . ' = ' . $this->db->q($result));

		}
		else
		{
			$query->select('count(a.id) as itemcount');
			$query->group('a.params');
		}

		$query->from($this->db->qn('#__ijoomeradv_report', 'a'));

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'id');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($this->db->escape($orderCol) . ' ' . $this->db->escape($orderDirn));

		return $query;
	}
}
