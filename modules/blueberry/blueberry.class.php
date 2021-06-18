<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryModel
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module high class
 **/
class blueberry extends ModuleObject
{
	static private $trigger_list = array(
		// 2020. 10. 08 insert blueberry menu trigger
		array('blueberry.getMemberMenu', 'blueberry', 'controller', 'triggerMemberMenu', 'after'),
	);
	
	/**
	 * List of status texts supported by Rhymix.
	 * 
	 * Also see status constants in common/constants.php
	 * and integer status codes used in the comment module.
	 * 
	 * @var array
	 */
	public static $statusList = array(
		'temp' => 'TEMP',
		'private' => 'PRIVATE',
		'public' => 'PUBLIC',
		'secret' => 'SECRET',
		'embargo' => 'EMBARGO',
		'trash' => 'TRASH',
		'censored' => 'CENSORED',
		'censored_by_admin' => 'CENSORED_BY_ADMIN',
		'deleted' => 'DELETED',
		'deleted_by_admin' => 'DELETED_BY_ADMIN',
		'other' => 'OTHER',
	);
	
	/**
	 * constructor
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @brief install the module
	 **/
	function moduleInstall()
	{
		// enabled in the admin model
		$oModuleController = getController('module');

		// install triggers
		foreach ($this->trigger_list as $trigger) {
			$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

	}

	/**
	 * @brief chgeck module method
	 **/
	function checkUpdate()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		// register blueberry mid
		if(!$oModuleModel->getModuleInfoByMid('blueberry')) return true;

		// test triggers
		foreach ($this->trigger_list as $trigger) {
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
		}

		return false;
	}

	/**
	 * @brief update module
	 **/
	function moduleUpdate()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		// register blueberry mid
		if(!$oModuleModel->getModuleInfoByMid('blueberry')) {
			$args = new stdClass();
			$args->module = 'blueberry';
			$args->mid = 'blueberry';
			
			$args->browser_title = 'Blueberry';
			$args->meta_keywords = 'Pharmacokinetics, Pharmaceutics, Non-compartmental analysis, Moment analysis';
			$args->meta_description = 'The standard moment analysis module for Pharmacokinetics.';
			$output = $oModuleController->insertModule($args);
			$msg_code = 'success_registed';
			if(!$output->toBool()) return $output;
			$this->setMessage($msg_code);
		}
		
		// update triggers
		foreach ($this->trigger_list as $trigger) {
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
	}

	function moduleUninstall()
	{
		@set_time_limit(0);

		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		// delete blueberry mid
		if($oModuleModel->getModuleInfoByMid('blueberry')) {
			$module_info = $oModuleModel->getModuleInfoByMid('blueberry');
			if ($module_info->module_srl) {
				$output = $oModuleController->deleteModule($module_info->module_srl);
				if(!$output->toBool()) return $output;
				
			}
		}

		// delete triggers
		$oModuleController->deleteModuleTriggers('blueberry');
		foreach ($this->trigger_list as $trigger) {
			if($oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
				$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
		

		return new BaseObject();
	}

	/**
	 * Return default status
	 * @return string
	 */
	public static function getDefaultStatus()
	{
		return self::$statusList['private'];
	}
}