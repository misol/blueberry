<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryAdminController
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module item class
 */
class blueberryItem extends BaseObject
{
	/**
	 * Document number
	 * @var int
	 */
	public $data_srl = 0;
	private $reload = false;
	/**
	 * grant
	 * @var bool
	 */
	private $grant_cache = null;


	/**
	 * Get data from database, and set the value to blueberryItem object
	 * @param int data_srl: target data serial number
	 * @param bool reload: set true to force reloading
	 * @return void
	 */
	public function __construct($data_srl = 0, $reload = false)
	{
		$this->setDataSrl($data_srl, $reload);
	}

	public function setDataSrl($data_srl, $reload = false)
	{
		if(!$data_srl) return;
		$this->data_srl = $data_srl;
		$this->reload = $reload;
		$this->_loadFromDB();
	}


	/**
	 * Get data from database, and set the value to blueberryItem object
	 * @param 
	 * @return void
	 */
	private function _loadFromDB()
	{
		if(!$this->isExists())
		{
			return;
		}
		
		$blueberry_item = false;
		$columnList = array();
		$reload_counts = false;
		
		if ($this->reload === true)
		{
			$reload_counts = true;
		}

		// cache control
		$cache_key = 'blueberry_item:' . getNumberingPath($this->data_srl) . $this->data_srl;
		$blueberry_item = Rhymix\Framework\Cache::get($cache_key);

		if(!$blueberry_item || $reload_counts)
		{
			$args = new stdClass();
			$args->data_srl = $this->data_srl;
			$output = executeQuery('blueberry.getData', $args, $columnList);
			
			$blueberry_item = $output->data;
			if($blueberry_item)
			{
				Rhymix\Framework\Cache::set($cache_key, $blueberry_item);
			}
		}

		$this->setAttribute($blueberry_item);
	}

	public function setAttribute($attribute)
	{
		if(!is_object($attribute) || !$attribute->data_srl)
		{
			$this->data_srl = null;
			return;
		}
		
		$this->data_srl = $attribute->data_srl;
		$this->adds($attribute);
		
		
	}


	public function isExists()
	{
		return (bool) ($this->data_srl);
	}


	public function isGranted()
	{
		if(!$this->isExists())
		{
			return false;
		}
		
		if ($this->grant_cache !== null)
		{
			return $this->grant_cache;
		}
		
		$logged_info = Context::get('logged_info');
		if (!$logged_info->member_srl)
		{
			return $this->grant_cache = false;
		}
		if ($logged_info->is_admin == 'Y')
		{
			return $this->setGrant();
		}
		if ($this->get('member_srl') && abs($this->get('member_srl')) == $logged_info->member_srl)
		{
			return $this->setGrant();
		}
		
		$grant = ModuleModel::getGrant(ModuleModel::getModuleInfoByModuleSrl($this->get('module_srl')), $logged_info);
		if ($grant->manager)
		{
			return $this->setGrant();
		}
		
		return $this->grant_cache = false;
	}
	
	private function setGrant()
	{
		$this->grant_cache = true;
	}
	
	public function isAccessible()
	{
		if(!$this->isExists())
		{
			return false;
		}
		
		if (isset($_SESSION['accessible'][$this->data_srl]) && $_SESSION['accessible'][$this->data_srl] === $this->get('last_update'))
		{
			return true;
		}
		
		$oBlueberry = getClass('blueberry');
		if ($this->get('status') === $oBlueberry->statusList['public'])
		{
			$this->setAccessible();
			return true;
		}
		
		if ($this->isGranted())
		{
			$this->setAccessible();
			return true;
		}
		
		return false;
	}


	private function setAccessible()
	{
		if(Context::getSessionStatus())
		{
			$_SESSION['accessible'][$this->data_srl] = $this->get('last_update');
		}
	}

	public function doCart()
	{
		if(!$this->isExists())
		{
			return false;
		}
		
		$this->isCarted() ? $this->removeCart() : $this->addCart();
	}

	public function addCart()
	{
		$_SESSION['data_management'][$this->data_srl] = true;
	}

	public function removeCart()
	{
		unset($_SESSION['data_management'][$this->data_srl]);
	}

	public function isCarted()
	{
		return isset($_SESSION['data_management'][$this->data_srl]);
	}

	public function getIpAddress()
	{
		if($this->isGranted())
		{
			return $this->get('ipaddress');
		}
		
		return '*' . strstr($this->get('ipaddress'), '.');
	}

	public function getHomepageUrl()
	{
		if(!$url = trim($this->get('homepage')))
		{
			return;
		}
		
		if(!preg_match('@^[a-z]+://@i', $url))
		{
			$url = 'http://' . $url;
		}
		
		return escape($url, false);
	}

	public function getMemberSrl()
	{
		return $this->get('member_srl');
	}

	public function getUserID()
	{
		return escape($this->get('user_id'), false);
	}

	public function getUserName()
	{
		return escape($this->get('user_name'), false);
	}

	public function getNickName()
	{
		return escape($this->get('nick_name'), false);
	}

	public function getLastUpdater()
	{
		return escape($this->get('last_updater'), false);
	}

	public function getTitle($cut_size = 0, $tail = '...')
	{
		if(!$this->isExists())
		{
			return;
		}
		
		return $cut_size ? escape(cut_str($this->get('title'), $cut_size, $tail)) : escape($this->get('title'));
	}
	
	
	public function getContent($cut_size = 0, $tail = '...')
	{
		if(!$this->isExists())
		{
			return;
		}
		
		return $cut_size ? escape(cut_str($this->get('content'), $cut_size, $tail)) : escape($this->get('content'));
	}
	
	public function getVoted()
	{
		return $this->getMyVote();
	}

	public function getMyVote()
	{
		if(!$this->isExists())
		{
			return false;
		}

		if(isset($_SESSION['voted_data'][$this->data_srl]))
		{
			return $_SESSION['voted_data'][$this->data_srl];
		}
		
		$logged_info = Context::get('logged_info');
		if(!$logged_info->member_srl)
		{
			$module_info = ModuleModel::getModuleInfoByModuleSrl($this->get('module_srl'));
			if($module_info->non_login_vote !== 'Y')
			{
				return false;
			}
		}

		$args = new stdClass;
		if($logged_info->member_srl)
		{
			$args->member_srl = $logged_info->member_srl;
		}
		else
		{
			$args->ipaddress = \RX_CLIENT_IP;
		}
		$args->data_srl = $this->data_srl;
		$output = executeQuery('blueberry.getDataVotedLog', $args);
		if(isset($output->data) && $output->data->point)
		{
			return $_SESSION['voted_data'][$this->data_srl] = $output->data->point;
		}
		return $_SESSION['voted_data'][$this->data_srl] = false;
	}
	
	
	public function getRegdate($format = 'Y.m.d H:i:s', $conversion = true)
	{
		return zdate($this->get('regdate'), $format, $conversion);
	}

	public function getRegdateTime()
	{
		return ztime($this->get('regdate'));
	}

	public function getRegdateGM($format = 'r')
	{
		return gmdate($format, $this->getRegdateTime());
	}

	public function getRegdateDT($format = 'c')
	{
		return Rhymix\Framework\DateTime::formatTimestampForCurrentUser($format, $this->getRegdateTime());
	}

	public function getUpdate($format = 'Y.m.d H:i:s', $conversion = true)
	{
		return zdate($this->get('last_update'), $format, $conversion);
	}

	public function getUpdateTime()
	{
		return ztime($this->get('last_update'));
	}

	public function getUpdateGM($format = 'r')
	{
		return gmdate($format, $this->getUpdateTime());
	}

	public function getUpdateDT($format = 'c')
	{
		return Rhymix\Framework\DateTime::formatTimestampForCurrentUser($format, $this->getUpdateTime());
	}
	
	
	/**
	 * Return the status code.
	 * 
	 * @return string
	 */
	function getStatus()
	{
		$oBlueberry = getClass('blueberry');
		$status = $this->get('status');
		return $status ?: $oBlueberry->getDefaultStatus();
	}

	/**
	 * Return the status in human-readable text.
	 * 
	 * @return string
	 */
	function getStatusText()
	{
		$status = $this->get('status');
		$statusList = lang('document.status_name_list');
		if ($status && isset($statusList[$status]))
		{
			return $statusList[$status];
		}
		else
		{
			return $statusList[$oBlueberry->getDefaultStatus()];
		}
	}

}

/* End of file blueberry.item.php */
/* Location: ./modules/blueberry/blueberry.item.php */