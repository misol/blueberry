<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryView
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module Controller class
 **/
class blueberryController extends blueberry
{
	/**
	 * @brief initialization
	 **/
	public function init()
	{
	}
	
	/**
	 * @brief insert data
	 **/
	public function procBlueberryInsertData() {
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		
		$text_inputs = ['mid', 'act', 'title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'dosing_route', 'dose_repeat', 'integration_method', 'administration_route'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau', 'molecular_weight'];
		$json_inputs = ['TC_data'];
		
		// setup variables
		$obj = Context::getRequestVars();
		$obj->module_srl = $this->module_srl;
		
		
		// Remove disallowed Unicode symbols.
		foreach ($text_inputs as $key) {
			if (isset($obj->{$key}))
			{
				$obj->{$key} = trim(utf8_clean($obj->{$key}));
			}
		}
		foreach ($json_inputs as $key) {
			if (isset($obj->{$key}) && json_decode($obj->{$key}) && json_last_error() === JSON_ERROR_NONE)
			{
				$obj->{$key} = json_decode($obj->{$key});
			}
		}
		
		if (isset($obj->data_srl))
		{
			$obj->data_srl = intval($obj->data_srl);
		}
		foreach ($float_inputs as $key) {
			if (isset($obj->{$key}))
			{
				$obj->{$key} = floatval($obj->{$key});
			}
		}
		
		$output = self::insertBlueberryInVivoData($obj);
		$returnUrl = getNotEncodedUrl('', 'act', 'dispBlueberryContent', 'mid', Context::get('mid'), 'owner_id', $output->get('owner_id'), 'data_srl', $output->get('data_srl'));
		$this->setRedirectUrl($returnUrl);
		return $output;
	}
	
	/**
	 * @brief update data
	 **/
	public function procBlueberryUpdateData() {
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		
		$text_inputs = ['mid', 'act', 'title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'dosing_route', 'dose_repeat', 'integration_method', 'administration_route'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau', 'molecular_weight'];
		$required_inputs = ['data_srl'];
		$json_inputs = ['TC_data'];
		
		// setup variables
		$obj = Context::getRequestVars();
		$obj->module_srl = $this->module_srl;
		
		
		// Remove disallowed Unicode symbols.
		foreach ($text_inputs as $key) {
			if (isset($obj->{$key}))
			{
				$obj->{$key} = trim(utf8_clean($obj->{$key}));
			}
		}
		foreach ($json_inputs as $key) {
			if (isset($obj->{$key}) && json_decode($obj->{$key}) && json_last_error() === JSON_ERROR_NONE)
			{
				$obj->{$key} = json_decode($obj->{$key});
			}
		}
		foreach ($required_inputs as $key) {
			if (!isset($obj->{$key}) || !$obj->{$key})
			{
				throw new Rhymix\Framework\Exceptions\InvalidRequest;
			}
		}
		
		
		foreach ($float_inputs as $key) {
			if (isset($obj->{$key}))
			{
				$obj->{$key} = floatval($obj->{$key});
			}
		}
		
		$output = self::updateBlueberryInVivoData($obj);
		$returnUrl = getNotEncodedUrl('', 'act', 'dispBlueberryContent', 'mid', Context::get('mid'), 'owner_id', $output->get('owner_id'), 'data_srl', $output->get('data_srl'));
		$this->setRedirectUrl($returnUrl);
		return $output;
	}
	
	/**
	 * Insert the data
	 * @param object $obj
	 * @return object
	 */
	private function insertBlueberryInVivoData($obj) {
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		// Return error if content is too large.
		$document_length_limit = ($this->module_info->document_length_limit ?: 1024) * 1024;
		if (strlen(trim($obj->content)) > $document_length_limit && !$this->grant->manager)
		{
			throw new Rhymix\Framework\Exception('msg_content_too_long');
		}
		
		
		$args = new stdClass;
		// sanitize variables
		$text_inputs = ['title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'administration_route', 'dose_repeat', 'integration_method', 'extrapolation_method', 'password', 'user_id', 'user_name', 'nick_name', 'status', 'comment_status'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau', 'last_dosing', 'time_min', 'time_max', 'duration_of_infusion', 'molecular_weight'];
		$int_inputs = ['data_srl', 'category_srl', 'module_srl', 'group_count', 'repeat_count', 'member_srl', 'regdate', 'last_update', 'list_order', 'update_order'];
		$positive_inputs = ['dose', 'last_dosing_time', 'tau', 'last_dosing', 'time_min', 'time_max', 'duration_of_infusion'];
		$unset_inputs = ['regdate', 'last_update', 'ipaddress', 'allow_trackback', 'notify_message'];
		
		foreach ($text_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = trim(utf8_clean(strval($obj->{$key})));
			}
		}
		
		foreach ($float_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = floatval($obj->{$key});
			}
		}
		
		foreach ($positive_inputs as $key) {
			if(isset($obj->{$key})) {
				if(floatval($obj->{$key}) < 0) {
					$args->{$key} = 0;
				}
			}
		}
		
		foreach ($int_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = intval($obj->{$key});
			}
		}
		
		foreach ($unset_inputs as $key) {
			if(isset($obj->{$key})) {
			
				unset($obj->{$key});
			}
		}
		
		if(isset($obj->sampling_type)) {
			if (in_array($obj->sampling_type, array('serial', 'sparse'))) {
				$args->sampling_type = $obj->sampling_type;
			}
		}
		else {
			$args->sampling_type = 'serial';
		}
		if(isset($obj->dose_repeat)) {
			if (in_array($obj->dose_repeat, array('single', 'multiple'))) {
				$args->dose_repeat = strtoupper(substr($obj->dose_repeat, 0, 1));
			}
		}
		else {
			$args->dose_repeat = 'S';
		}
		
		if(!isset(blueberry::$available_routes[$args->administration_route])) {
			$args->administration_route = 'IVeBo';
		}
		
		if(!isset(lang('blueberry.blueberry_dose_units')[$args->dose_unit])) {
			$args->dose_unit = 'mg';
		}
		
		if (!isset($obj->TC_data->time) || !isset($obj->TC_data->concentration) || !is_array($obj->TC_data->time) || !is_array($obj->TC_data->concentration))
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		if (count($obj->TC_data->time) < 1 || count($obj->TC_data->concentration)  < 1)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		$obj->TC_data->time = array_map( "floatval", $obj->TC_data->time);
		$obj->TC_data->concentration = array_map( "floatval", $obj->TC_data->concentration);
		$args->time_concentration = serialize($obj->TC_data);
		
		// Register it if no given document_srl exists
		if(!$obj->data_srl)
		{
			$args->data_srl = getNextSequence();
		}
		else {
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		// Initiate records.
		$args->readed_count = 0;
		$args->update_order = $args->list_order = $args->data_srl * -1;
		
		$logged_info = Context::get('logged_info');
		
		if($args->homepage)
		{
			$obj->homepage = escape($obj->homepage);
			if(!preg_match('/^[a-z]+:\/\//i',$obj->homepage))
			{
				$obj->homepage = 'http://'.$obj->homepage;
			}
		}
		
		if(Context::get('is_logged'))
		{
			$args->last_updater = $args->member_srl = $logged_info->member_srl;

			// user_id, user_name and nick_name already encoded
			$args->user_id = htmlspecialchars_decode($logged_info->user_id);
			$args->user_name = htmlspecialchars_decode($logged_info->user_name);
			$args->nick_name = htmlspecialchars_decode($logged_info->nick_name);
			$args->email_address = $logged_info->email_address;
			$args->homepage = $logged_info->homepage;
		}
		
		if($args->title == '')
		{
			$args->title = cut_str(trim(strip_tags(nl2br($args->content))),20,'...');
		}
		if($args->title == '')
		{
			$args->title = 'Untitled';
		}
		
		$args->title = removeHackTag($args->title);
		$args->content = removeHackTag($args->content);
		
		// Check the status of password hash for manually inserting. Apply hashing for otherwise.
		if($args->password && !$obj->password_is_hashed)
		{
			$args->password = MemberModel::hashPassword($args->password);
		}
		
		
		// An error appears if both log-in info and user name don't exist.
		if(!$logged_info->member_srl && !$args->nick_name) {
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		unset($obj);
		
		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();
		
		// Insert data into the DB
		$output = executeQuery('blueberry.insertInVivoData', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}
		
		ModuleHandler::triggerCall('blueberry.insertInVivoData', 'after', $args);

		// commit
		$oDB->commit();

		// return
		$output->add('data_srl', $args->data_srl);
		$output->add('owner_id', $args->user_id);
		$output->add('title', $args->title);
		
		return $output;
	}
	

	/**
	 * Updtae the data
	 * @param object $obj
	 * @return object
	 */
	private function updateBlueberryInVivoData($obj) {
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		// Return error if content is too large.
		$document_length_limit = ($this->module_info->document_length_limit ?: 1024) * 1024;
		if (strlen(trim($obj->content)) > $document_length_limit && !$this->grant->manager)
		{
			throw new Rhymix\Framework\Exception('msg_content_too_long');
		}
		
		
		$args = new stdClass;
		// sanitize variables
		$text_inputs = ['title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'administration_route', 'dose_repeat', 'integration_method', 'extrapolation_method', 'password', 'user_id', 'user_name', 'nick_name', 'status', 'comment_status'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau', 'last_dosing', 'time_min', 'time_max', 'duration_of_infusion', 'molecular_weight'];
		$int_inputs = ['data_srl', 'category_srl', 'module_srl', 'group_count', 'repeat_count', 'member_srl', 'regdate', 'last_update', 'list_order', 'update_order'];
		$positive_inputs = ['dose', 'last_dosing_time', 'tau', 'last_dosing', 'time_min', 'time_max', 'duration_of_infusion'];
		$unset_inputs = ['regdate', 'last_update', 'ipaddress', 'allow_trackback', 'notify_message'];
		$required_inputs = ['data_srl'];
		
		foreach ($required_inputs as $key) {
			if (!isset($obj->{$key}) || !$obj->{$key})
			{
				throw new Rhymix\Framework\Exceptions\InvalidRequest;
			}
		}
		
		foreach ($text_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = trim(utf8_clean(strval($obj->{$key})));
			}
		}
		
		foreach ($float_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = floatval($obj->{$key});
			}
		}
		
		foreach ($positive_inputs as $key) {
			if(isset($obj->{$key})) {
				if(floatval($obj->{$key}) < 0) {
					$args->{$key} = 0;
				}
			}
		}
		
		foreach ($int_inputs as $key) {
			if(isset($obj->{$key})) {
			
				$args->{$key} = intval($obj->{$key});
			}
		}
		
		foreach ($unset_inputs as $key) {
			if(isset($obj->{$key})) {
			
				unset($obj->{$key});
			}
		}
		
		if(isset($obj->sampling_type)) {
			if (in_array($obj->sampling_type, array('serial', 'sparse'))) {
				$args->sampling_type = $obj->sampling_type;
			}
		}
		else {
			$args->sampling_type = 'serial';
		}
		if(isset($obj->dose_repeat)) {
			if (in_array($obj->dose_repeat, array('single', 'multiple'))) {
				$args->dose_repeat = strtoupper(substr($obj->dose_repeat, 0, 1));
			}
		}
		else {
			$args->dose_repeat = 'S';
		}
		
		if(!isset(blueberry::$available_routes[$args->administration_route])) {
			$args->administration_route = 'IVeBo';
		}
		
		if(!isset(lang('blueberry.blueberry_dose_units')[$args->dose_unit])) {
			$args->dose_unit = 'mg';
		}
		
		if (!is_array($obj->TC_data->time) || !is_array($obj->TC_data->concentration))
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		if (!count($obj->TC_data->time) || !count($obj->TC_data->concentration) || (!count($obj->TC_data->time) || !count($obj->TC_data->concentration)))
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		$obj->TC_data->time = array_map( "floatval", $obj->TC_data->time);
		$obj->TC_data->concentration = array_map( "floatval", $obj->TC_data->concentration);
		$args->time_concentration = serialize($obj->TC_data);
		
		// Register it if no given document_srl exists
		if(!$obj->data_srl)
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		// Initiate records.
		$args->list_order = $args->data_srl * -1;
		
		// Change the update order
		$args->update_order = getNextSequence() * -1;
		
		$logged_info = Context::get('logged_info');
		
		if($args->title == '')
		{
			$args->title = cut_str(trim(strip_tags(nl2br($args->content))),20,'...');
		}
		if($args->title == '')
		{
			$args->title = 'Untitled';
		}
		
		$args->title = removeHackTag($args->title);
		$args->content = removeHackTag($args->content);
		
		// Check the status of password hash for manually inserting. Apply hashing for otherwise.
		if($args->password && !$obj->password_is_hashed)
		{
			$args->password = MemberModel::hashPassword($args->password);
		}
		
		
		// An error appears if both log-in info and user name don't exist.
		if(!$logged_info->member_srl && !$args->nick_name) {
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		
		unset($obj);
		// get original data
		$oData = blueberryModel::getData($args->data_srl);
		if(!$oData->isExists()) {
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		
		if(Context::get('is_logged'))
		{
			$args->last_updater = $logged_info->member_srl;

			// user_id, user_name and nick_name already encoded
			$args->user_id = htmlspecialchars_decode($logged_info->user_id);
			$args->user_name = htmlspecialchars_decode($logged_info->user_name);
			$args->nick_name = htmlspecialchars_decode($logged_info->nick_name);
			$args->email_address = $logged_info->email_address;
			$args->homepage = $logged_info->homepage;
		}
		
		// user_id, user_name and nick_name already encoded
		$args->member_srl = $oData->getMemberSrl();
		
		if($args->homepage) {
			$obj->homepage = escape($obj->homepage);
			if(!preg_match('/^[a-z]+:\/\//i',$obj->homepage))
			{
				$obj->homepage = 'http://'.$obj->homepage;
			}
		} else {
			$args->homepage = $oData->getHomepageUrl();
		}
		
		// set count
		$args->readed_count = $oData->getReadedCount();
		
		
		
		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();
		// Insert data into the DB
		$output = executeQuery('blueberry.updateInVivoData', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}
		
		
		ModuleHandler::triggerCall('blueberry.updateInVivoData', 'after', $args);

		// commit
		$oDB->commit();

		// return
		$output->add('data_srl', $args->data_srl);
		$output->add('owner_id', $args->user_id);
		$output->add('title', $args->title);
		
		return $output;
	}


	/**
	 * @brief delete the document
	 **/
	public function procBlueberryDeleteData() {
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		// get the document_srl
		$data_srl = intval(Context::get('data_srl'));

		// if the document is not existed
		if(!$data_srl)
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}

		$oData = blueberryModel::getData($data_srl);
		if (!$oData || !$oData->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if (!$oData->isGranted())
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		if(strval(Context::get('owner_id')) !== $oData->getUserID()) {
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		
		$logged_info = Context::get('logged_info');
		if($oData->getMemberSrl() !== $logged_info->member_srl && $logged_info->is_admin !== 'Y') {
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		// generate document module controller object
		/*
		if($this->module_info->trash_use == 'Y')
		{
			$output = $this->moveDataToTrash($oData);
			if(!$output->toBool())
			{
				return $output;
			}
		}
		else
		{
			*/
			// delete the document
			$output = $this->deleteData($oData);
			if(!$output->toBool())
			{
				return $output;
			}
			/*
		}
		*/

		// alert an message
		$this->setRedirectUrl(getNotEncodedUrl('', 'mid', Context::get('mid'), 'act', '', 'page', Context::get('page'), 'owner_id', '', 'data_srl', ''));
		$this->add('mid', Context::get('mid'));
		$this->add('page', Context::get('page'));
		$this->setMessage('success_deleted');
	}
	
	
	/**
	 * Deleting Data
	 * @param blueberryItem $oData
	 * @return object
	 */
	private function deleteData($oData)
	{
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		if(!Context::get('is_logged')) {
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		// Call a trigger (before)
		$trigger_obj = new stdClass();
		$trigger_obj->data_srl = $oData->getDataSrl();
		$output = ModuleHandler::triggerCall('blueberry.deleteData', 'before', $trigger_obj);
		if(!$output->toBool()) return $output;

		// begin transaction
		$oDB = &DB::getInstance();
		$oDB->begin();

		$oData = blueberryModel::getData($trigger_obj->data_srl);
		if (!$oData || !$oData->isExists())
		{
			throw new Rhymix\Framework\Exceptions\TargetNotFound;
		}
		if (!$oData->isGranted())
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		

		$logged_info = Context::get('logged_info');
		$member_info = MemberModel::getMemberInfo($oData->getMemberSrl());
		if($member_info->is_admin === 'Y' && $logged_info->is_admin !== 'Y')
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}

		//if empty trash, document already deleted, therefore document not delete
		$args = new stdClass();
		$args->data_srl = $oData->getDataSrl();
		// Delete the document
		$output = executeQuery('blueberry.deleteData', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return $output;
		}
		
		// Update category information if the category_srl exists.
		//if($oDocument->get('category_srl')) $this->updateCategoryCount($oDocument->get('module_srl'),$oDocument->get('category_srl'));
		// Delete a declared list
		//executeQuery('document.deleteDeclared', $args);
		// Delete extra variable
		//$this->deleteDocumentExtraVars($oDocument->get('module_srl'), $oDocument->document_srl);

		// Call a trigger (after)
		//$trigger_obj = $oDocument->getObjectVars();
		//$trigger_obj->isEmptyTrash = $isEmptyTrash ? true : false;
		ModuleHandler::triggerCall('blueberry.deleteData', 'after', $trigger_obj);
		
		// declared document, log delete
		$this->_deleteDataReadedLog($args);
		$this->_deleteDataVotedLog($args);

		//remove from cache
		self::clearDataCache($document_srl);
		
		// commit
		$oDB->commit();

		return $output;
	}
	
	/**
	 * Delete readed log
	 * @param string $dataSrls (ex: 1, 2,56, 88)
	 * @return void
	 */
	private function _deleteDataReadedLog($dataSrls)
	{
		executeQuery('blueberry.deleteDataReadedLog', $dataSrls);
	}
	
	/**
	 * Delete readed log
	 * @param string $dataSrls (ex: 1, 2,56, 88)
	 * @return void
	 */
	private function _deleteDataVotedLog($dataSrls)
	{
		executeQuery('blueberry.deleteDataVotedLog', $dataSrls);
	}
	/**
	 * Clear document cache
	 */
	private static function clearDataCache($data_srl)
	{
		Rhymix\Framework\Cache::delete('blueberry_item:' . getNumberingPath($data_srl) . $data_srl);
		unset($_SESSION['data_accessible'][$data_srl]);
		unset($_SESSION['data_management'][$data_srl]);
		unset($_SESSION['voted_data'][$data_srl]);
	}
	
	/**
	 * Update read counts of the document
	 * @param documentItem $oDocument
	 * @return bool|void
	 */
	public static function updateReadedCount(&$oData) {
		// Pass if Crawler access
		if (\Rhymix\Framework\UA::isRobot())
		{
			return false;
		}
		
		// Get the view count option, and use the default if the value is empty or invalid.
		$valid_options = array(
			'all' => true,
			'some' => true,
			'once' => true,
			'none' => true,
		);
		
		$config = DocumentModel::getDocumentConfig();
		if (!$config->view_count_option || !isset($valid_options[$config->view_count_option]))
		{
			$config->view_count_option = 'once';
		}

		// If not counting, return now.
		if ($config->view_count_option == 'none')
		{
			return false;
		}
		
		// Get document and user information.
		$data_srl = $oData->getDataSrl();
		$member_srl = $oData->get('member_srl');
		$logged_info = Context::get('logged_info');
		
		// Option 'some': only count once per session.
		if ($config->view_count_option != 'all' && $_SESSION['readed_data'][$data_srl])
		{
			return false;
		}

		// Option 'once': check member_srl and IP address.
		if ($config->view_count_option == 'once')
		{
			// Pass if the author's IP address is as same as visitor's.
			if($oData->get('ipaddress') == \RX_CLIENT_IP)
			{
				if (Context::getSessionStatus())
				{
					$_SESSION['readed_data'][$data_srl] = true;
				}
				return false;
			}
			
			// Pass if the author's member_srl is the same as the visitor's.
			if($member_srl && $logged_info->member_srl && $logged_info->member_srl == $member_srl)
			{
				$_SESSION['readed_data'][$data_srl] = true;
				return false;
			}
		}

		// Call a trigger when the read count is updated (before)
		$trigger_output = ModuleHandler::triggerCall('blueberry.updateReadedCount', 'before', $oDocument);
		if(!$trigger_output->toBool()) return $trigger_output;
		
		// Update read counts
		$oDB = DB::getInstance();
		$oDB->begin();
		$args = new stdClass;
		$args->data_srl = $data_srl;
		executeQuery('blueberry.updateReadedCount', $args);

		// Call a trigger when the read count is updated (after)
		ModuleHandler::triggerCall('blueberry.updateReadedCount', 'after', $oDocument);
		$oDB->commit();

		// Register session
		if(!isset($_SESSION['readed_data'][$data_srl]) && Context::getSessionStatus()) 
		{
			$_SESSION['readed_data'][$data_srl] = true;
		}

		return TRUE;
	}
}