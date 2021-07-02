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
	static public $statusList = array(
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
	
	static public $available_routes = array(
		'IVeBo' => 'iv_bolus',
		'IVeIf'=> 'iv_infusion',
		'ExtVe'=> 'extravenous'
	);
	
	static public $available_interpolation = array(
		'linear-trapezoidal' => 'linear_trapezoidal_method',
		'logarithmic-trapezoidal' => 'log_trapezoidal_method',
		'linear-trapezoidal-with-end-correction' => 'linear_trapezoidal_method_w_end_corr'
	);
	
	static public $search_option = array('title_content','title','dose_dose_unit','dosing_route','user_name','nick_name','last_updater', 'email_address' , 'regdate', 'last_update','user_id','tag'); /// search option

	static public $order_target = array('list_order', 'update_order', 'regdate', 'voted_count', 'blamed_count', 'readed_count', 'comment_count', 'title', 'nick_name', 'user_name', 'user_id'); // 정렬 옵션

	static public $skin = "default"; ///< skin name
	static public $list_count = 20; ///< the number of documents displayed in a page
	static public $page_count = 10; ///< page number
	static public $category_list = NULL; ///< category list
	
	/**
	 * constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @brief install the module
	 **/
	public function moduleInstall()
	{
		// enabled in the admin model
		$oModuleController = getController('module');

		// install triggers
		foreach (self::$trigger_list as $trigger) {
			$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}

	}

	/**
	 * @brief chgeck module method
	 **/
	public function checkUpdate()
	{
		
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');

		// register blueberry mid
		if(!$oModuleModel->getModuleInfoByMid('blueberry')) return true;

		// test triggers
		foreach (self::$trigger_list as $trigger) {
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
		}

		return false;
	}

	/**
	 * @brief update module
	 **/
	public function moduleUpdate()
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
			$args->skin = 'default';
			$output = $oModuleController->insertModule($args);
			$msg_code = 'success_registed';
			if(!$output->toBool()) return $output;
			$this->setMessage($msg_code);
		}
		
		// update triggers
		foreach (self::$trigger_list as $trigger) {
			if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
	}

	public function moduleUninstall()
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