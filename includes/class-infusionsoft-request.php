<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Infusion_Request{

    public $app;
    public $conn;
    public function __construct() {
        $this->app = new iSDK;
        $this->conn = $this->test_api();
    }

    /**
     * @return bool
     */
    public function test_api(){
        $settings = tvb_settings();

        if(isset($settings['api_key']) && !empty($settings['api_key']) && isset($settings['app_name']) && !empty($settings['app_name'])){
            if ($this->app->cfgCon($settings['app_name'],$settings['api_key'])) {
                $this->conn =  $this->app->cfgCon($settings['app_name'],$settings['api_key']);
            } else {
               return false;
            }
        } else {
            return false;
        }

    }

    /**
     * @param $contactID
     * @param array $returnFields
     * @return array
     */
    public function getContactDataByID($contactID, $returnFields= array()) {
        if($this->conn !== false){

            if(empty($returnFields)){
                $returnFields = array('Email', 'FirstName', 'LastName', 'Phone1', '_VehicleToPurchaseSummary', '_BrokerageFee', '_MakeDropdown');
            }

            $conData = $this->app->loadCon($contactID, $returnFields);
            if(is_array($conData) && !empty($conData)){
                return array('data'=>$conData, 'message'=>'');
            } else {
                return array('data'=>'','message'=>$conData);
            }

        } else{
            return array('data'=>'','message'=>'Connnection Failed. Please try again');
        }
    }

    /**
     * @param $parameters
     * @param $return_fields
     * @return array
     */
    public function getContactbyParameters($parameters, $return_fields){
        if($this->conn !== false){

            $results = $this->app->dsQuery("Contact", 100, 0, $parameters, $return_fields);
            return array('data'=>$results,'message'=>'');

        } else {
            return array('data'=>'','message'=>'Connnection Failed. Please try again');
        }
    }

}