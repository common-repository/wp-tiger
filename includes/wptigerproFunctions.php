<?php

/**
 * WP Tiger plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

require_once(SM_LB_VTIGER_DIR.'lib/vtwsclib/Vtiger/WSClient.php');
class mainCrmHelper{
	public $username;
	public $accesskey;
	public $url;
	public $result_emails;
	public $result_ids;
	public $result_products;

	public function __construct()
	{
		global $lb_crmm;
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		if(isset($_REQUEST['crmtype']))
		{
			$crmtype = sanitize_text_field($_REQUEST['crmtype']);
			$SettingsConfig = get_option("wp_{$crmtype}_settings");
		}
		else
		{
			$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		}
		$this->username = $SettingsConfig['username'];
		$this->accesskey = $SettingsConfig['accesskey'];
		$this->url = $SettingsConfig['url'];
		$lb_crmm->setConfigurationDetails($SettingsConfig);
	}

	public function login($url,$accesskey,$username)
	{
		$client = new Vtiger_WSClient($url);
		$login = $client->doLogin($username, $accesskey);
		return $client;
	}

	public function testLogin( $url , $username , $accesskey )
	{
		$client = new Vtiger_WSClient($url);
		$login = $client->doLogin($username, $accesskey);
		return $login;
	}

	public function getCrmFields( $module )
	{
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		if(isset($_REQUEST['crmtype']))
		{
			$crmtype = sanitize_text_field($_REQUEST['crmtype']);
			$SettingsConfig = get_option("wp_{$crmtype}_settings");
		}
		else
		{
			$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		}
		$username = $SettingsConfig['username'];
		$accesskey = $SettingsConfig['accesskey'];
		$url = $SettingsConfig['url'];
		$client = $this->login($url,$accesskey,$username);
		$recordInfo = $client->doDescribe($module);
		$config_fields = array();
		if($recordInfo)
		{
			$second_count=0;
			$count=count($recordInfo['fields']);
			for($i=0;$i<$count;$i++)
			{
				if($recordInfo['fields'][$i]['nullable']=="" && $recordInfo['fields'][$i]['editable']=="" ){
				}
				elseif($recordInfo['fields'][$i]['type']['name'] == 'reference'){
				}
				elseif($recordInfo['fields'][$i]['name'] == 'modifiedby' || $recordInfo['fields'][$i]['name'] == 'assigned_user_id' ){
				}
				else{
					$config_fields['fields'][$second_count] = $recordInfo['fields'][$i];
					$config_fields['fields'][$second_count]['order'] = $second_count;
					$config_fields['fields'][$second_count]['publish'] = 1;
					$config_fields['fields'][$second_count]['display_label'] = $recordInfo['fields'][$i]['label'];
					if($recordInfo['fields'][$i]['mandatory']==1)
					{
						$config_fields['fields'][$second_count]['wp_mandatory'] = 1;
						$config_fields['fields'][$second_count]['mandatory'] = 2;
					}
					else
					{
						$config_fields['fields'][$second_count]['wp_mandatory'] = 0;
					}
					$second_count++;
				}
			}
			$config_fields['check_duplicate'] = 0;
			$config_fields['isWidget'] = 0;
			$config_fields['update_record'] = 0;
			$users_list = $this->getUsersList();
			$config_fields['assignedto'] = $users_list['id'][0];
			$config_fields['module'] = $module;
		}
		return $config_fields;
	}

	public function getUsersList()
	{
		$query = "select user_name, id, first_name, last_name  from Users";
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		if(isset($_REQUEST['crmtype']))
		{
			$crmtype = sanitize_text_field($_REQUEST['crmtype']);
			$SettingsConfig = get_option("wp_{$crmtype}_settings");
		}
		else
		{
			$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		}
		$username = $SettingsConfig['username'];
		$accesskey = $SettingsConfig['accesskey'];
		$url = $SettingsConfig['url'];
		$client = $this->login($url,$accesskey,$username);
		$records = $client->doQuery($query);
		if($records) {
			$client->getResultColumns($records);
			foreach($records as $record) {
				$user_details['user_name'][] = $record['user_name'];
				$user_details['id'][] = $record['id'];
				$user_details['first_name'][] = $record['first_name'];
				$user_details['last_name'][] = $record['last_name'];
			}
		}
		else
			$user_details = "";
		return $user_details;
	}

	public function getAssignedToList()
	{
		$user_list_array=[];
		$users_list = $this->getUsersList();
		$count=count($users_list['user_name']) ;
		for($i = 0; $i < $count ; $i++)
		{
			$user_list_array[$users_list['id'][$i]] = $users_list['first_name'][$i] ." ". $users_list['last_name'][$i];
		}
		return $user_list_array;
	}

	public function assignedToFieldId()
	{
		return "assigned_user_id";
	}

	public function mapUserCaptureFields( $user_firstname , $user_lastname , $user_email )
	{
		$post = array();
		$post['firstname'] = $user_firstname;
		$post['lastname'] = $user_lastname;
		$post[$this->duplicateCheckEmailField()] = $user_email;
		return $post;
	}

	public function createRecordOnUserCapture( $module , $module_fields )
	{
		return $this->createRecord( $module , $module_fields );
	}

	public function createRecord($module, $module_fields )
	{
		$data=[];
		$WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
		$activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		if(isset($_REQUEST['crmtype']))
		{
			$crmtype = sanitize_text_field($_REQUEST['crmtype']);
			$SettingsConfig = get_option("wp_{$crmtype}_settings");
		}
		else
		{
			$SettingsConfig = get_option("wp_{$activateplugin}_settings");
		}
		$username = $SettingsConfig['username'];
		$accesskey = $SettingsConfig['accesskey'];
		$url = $SettingsConfig['url'];

		$client = $this->login($url,$accesskey,$username);
		$client->setDebug(true);
		$record = $client->docreate( $module , $module_fields );
		if($record)
		{
			$data['result'] = "success";
			$data['failure'] = 0;
		}
		else
		{
			$data['result'] = "failure";
			$data['failure'] = 1;
			$data['reason'] = "failed adding entry";
		}
		return $data;
	}

	function duplicateCheckEmailField()
	{
		return "email";
	}
}
