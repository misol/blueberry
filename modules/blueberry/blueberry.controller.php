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
	public function procBlueberryInsertData()
	{
		
		// check grant
		if(!$this->grant->add_data)
		{
			throw new Rhymix\Framework\Exceptions\NotPermitted;
		}
		
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		
		$text_inputs = ['mid', 'act', 'title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'dosing_route', 'dose_repeat', 'integration_method'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau'];
		
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
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getUrl('', 'mid', strval($obj->mid), 'act', 'dispBlueberryContent');
		$this->setRedirectUrl($returnUrl);
		return self::insertBlueberryInVivoData($obj);
	}
	
	/**
	 * Insert the data
	 * @param object $obj
	 * @return object
	 */
	public function insertBlueberryInVivoData($obj) {
		if(!checkCSRF())
		{
			throw new Rhymix\Framework\Exceptions\SecurityViolation;
		}
		
		// Return error if content is too large.
		$document_length_limit = ($this->module_info->document_length_limit ?: 1024) * 1024;
		if (strlen(trim($obj->content)) > $document_length_limit && !$this->grant->manager)
		{
			throw new Rhymix\Framework\Exception('msg_content_too_long');
		}
		
		// begin transaction
		$oDB = DB::getInstance();
		$oDB->begin();
		
		$args = new stdClass;
		// sanitize variables
		$text_inputs = ['title', 'content', 'time_unit', 'amount_unit', 'volume_unit', 'dose_unit', 'administration_route', 'dose_repeat', 'integration_method', 'extrapolation_method', 'password', 'user_id', 'user_name', 'nick_name', 'status', 'comment_status'];
		$float_inputs = ['dose', 'last_dosing_time', 'tau', 'time_min', 'time_max'];
		$int_inputs = ['data_srl', 'category_srl', 'group_count', 'repeat_count', 'member_srl', 'regdate', 'last_update', 'list_order', 'update_order'];
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
		
		if (!is_array($obj->TC_data['time']) || !is_array($obj->TC_data['concentration']) || !is_array($obj->TC_data['lnC']) || !is_array($obj->TC_data['time-concentration']))
		{
			throw new Rhymix\Framework\Exceptions\InvalidRequest;
		}
		
		$obj->TC_data['time'] = array_map(floatval, $obj->TC_data['time']);
		$obj->TC_data['concentration'] = array_map(floatval, $obj->TC_data['concentration']);
		$obj->TC_data['lnC'] = array_map(floatval, $obj->TC_data['lnC']);
		foreach ($obj->TC_data['time-concentration'] as $key => $val) {
			$obj->TC_data['time-concentration'][$key] = array_map(floatval, $val);
		}
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
		$output->add('data_srl',$args->data_srl);
		$output->add('title',$args->title);

		return $output;

	}
}