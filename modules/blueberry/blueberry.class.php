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

		// delete triggers
		$oModuleController->deleteModuleTriggers('blueberry');
		foreach ($this->trigger_list as $trigger) {
			if($oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
				$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
		

		return new BaseObject();
	}
}