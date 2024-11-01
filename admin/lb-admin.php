<?php
if ( ! defined( 'ABSPATH' ) )
        exit; // Exit if accessed directly


class VtigerCrmSmLBHelper {

	public function __construct() {

        }

  public function setEventObj()
  {
    $obj = new mainCrmHelper();
    return $obj;
  }

  
	public function user_module_mapping_view() {
		include ('views/form-usermodulemapping.php');
	}

	public function mail_sourcing_view() {
		include('views/form-campaign.php');
	}

	public function new_lead_view() {
	       global $lb_crm;
               include ('views/form-managefields.php');
        }

	public function new_contact_view() {
               global $lb_crm;
               $module = "Contacts";
               $lb_crm->setModule($module);
               include ('views/form-managefields.php');
        }


	public function show_form_crm_forms() {
               include ('views/form-crmforms.php');
        }

	public function show_form_settings() {
               include ('views/form-settings.php');
        }

	public function show_usersync() {
               include ('views/form-usersync.php');
        }

	public function show_ecom_integ() {
               include ('views/form-ecom-integration.php');
        }

	public function show_vtiger_crm_config() {
               include ('views/form-vtigercrmconfig.php');
        }

	public function show_sugar_crm_config() {
               include ('views/form-sugarcrmconfig.php');
        }

	public function show_suite_crm_config() {
               include ('views/form-sugarcrmconfig.php');
        }

	public function show_zoho_crm_config() {
               include ('views/form-zohocrmconfig.php');
        }

	public function show_zohoplus_crm_config() {
               include ('views/form-zohocrmconfig.php');
        }

	public function show_freshsales_crm_config() {
               include ('views/form-freshsalescrmconfig.php');
        }

	public function show_salesforce_crm_config() {
		include('views/form-salesforcecrmconfig.php');
	}

      

   public function tigerproSettings( $tigerSettArray )
    {
        $config=[];
        $result=[];
        $tiger_config_array = $tigerSettArray['REQUEST'];
        $fieldNames = array(

            'url' => __('Vtiger Url' , SM_LB_URL ),
            'username' => __('Vtiger User Name' , SM_LB_URL ),
            'accesskey' => __('Vtiger Access Key' , SM_LB_URL ),
            'smack_email' => __('Smack Email' , SM_LB_URL ),
            'email' => __('Email id' , SM_LB_URL ),
            'emailcondition' => __('Emailcondition' , SM_LB_URL ),
            'debugmode' => __('Debug Mode' , SM_LB_URL ),
        );

        foreach ($fieldNames as $field=>$value){
            if(isset($tiger_config_array[$field]))
            {
                $config[$field] = trim($tiger_config_array[$field]);
            }
        }
        require_once(SM_LB_VTIGER_DIR . "includes/wptigerproFunctions.php");
        $FunctionsObj = new mainCrmHelper();
        $testlogin_result = $FunctionsObj->testLogin( $tiger_config_array['url'] , $tiger_config_array['username'] , $tiger_config_array['accesskey'] );
        if($testlogin_result == 1)
        {
            $successresult = "Settings Saved";
            $result['error'] = 0;
            $result['success'] = $successresult;
            $WPCapture_includes_helper_Obj = new WPCapture_includes_helper_PRO();
            $activateplugin = $WPCapture_includes_helper_Obj->ActivatedPlugin;
            update_option("wp_{$activateplugin}_settings", $config);
        }
        else
        {
            $vtigercrmerror = "Please Verify your Vtiger Credentials.";

            $result['error'] = 1;
            $result['errormsg'] = $vtigercrmerror ;
            $result['success'] = 0;
        }
        return $result;
    }

	
}

global $lb_crm;
$lb_crm = new VtigerCrmSmLBHelper();
