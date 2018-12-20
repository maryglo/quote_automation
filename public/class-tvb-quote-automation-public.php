<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
class TVB_Quote_Automation_Public {
    public function __construct() {
        //define admin hooks here
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));

        //create the form for request a quote with contactId parameter on the url
        add_shortcode('request-a-quote',array($this, 'create_request_a_quote_by_contact'));

        //post quote data
        add_action( 'wp_ajax_tvb_post_dealers_data', array( $this, 'tvb_post_dealers_data' ) );
        add_action( 'wp_ajax_nopriv_tvb_post_dealers_data', array( $this, 'tvb_post_dealers_data' ) );

        //create the form for request a quote with contactId parameter on the url
        add_shortcode('dealer-quote',array($this, 'create_dealer_quote_form'));

        add_action('admin_post_dealer_quote_post_data', array($this, 'dealer_quote_post_data'));
        add_action('admin_post_nopriv_dealer_quote_post_data', array($this, 'dealer_quote_post_data'));

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts_styles() {
        wp_enqueue_style( 'tvb-quote-automation-css', TVB_QUOTE_AUTOMATION_URL . 'public/css/tvb-quote-automation.css', array(), TVB_QUOTE_AUTOMATION_VERSION, 'all' );
        wp_enqueue_script("tvb-quote-automation_jquery_form", TVB_QUOTE_AUTOMATION_URL . 'public/js/jquery.validate.min.js', array('jquery'),TVB_QUOTE_AUTOMATION_VERSION, false );
        wp_enqueue_script('tvb-quote-automation-js', TVB_QUOTE_AUTOMATION_URL . 'public/js/tvb-quote-automation.js', array( 'jquery'), TVB_QUOTE_AUTOMATION_VERSION, false );
        wp_localize_script( 'tvb-quote-automation-js', 'tvb_quote_automation_object', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'tvb_quote_automation' )
        ) );

    }

    /**
     * @param array $atts
     * @param null $content
     * @param string $tag
     * @return string
     */
    public function create_request_a_quote_by_contact($atts = [], $content = null, $tag = ''){
        global $wpdb;
        $parameters = $_SERVER['QUERY_STRING'];
        parse_str( $parameters, $data );
        $data_keys = array_keys($data);
        $a = shortcode_atts( array(
        ), $atts );

        ob_start();

        include_once( 'partials/form1.php' );

        $output_string = ob_get_contents();
        ob_end_clean();

        return $output_string;
    }

    /**
     * function for request a quote ajax
     */
    public function tvb_post_dealers_data(){
        parse_str($_POST['post_data'], $data);
        $success = false;

        //check if our data is valid
        if(!empty($data['tvb_Dealer']) && checkDealerData($data['tvb_Dealer'])){

            $infusionsoft = new Infusion_Request();
            //check if we can connect to infusionsoft
            if($infusionsoft->conn !== false){
                $merge_vars = array();
                $set_dealers = array();

                //update contact with dealers data
                foreach($data['tvb_Dealer'] as $key=>$dealer){
                    $dealer_index = $key + 1;
                    $merge_vars['_Dealer'.$dealer_index.'Name'] = $dealer['Name'];
                    $merge_vars['_Dealer'.$dealer_index.'Email'] = $dealer['Email'];

                    //get dealer data that has been set or not blank
                    if(!empty($dealer['Email']) && !empty($dealer['Id'])){
                        $set_dealers[$key]['tag'] = $dealer['Tag'];
                        $set_dealers[$key]['id'] = $dealer['Id'];
                        $set_dealers[$key]['name'] = $dealer['Name'];
                    }
                }

                $merge_vars['_DealerQuoteNotes'] = $data['tvb_DealerQuoteNotes'];
                //update the contact with dealers data
               $infusionsoft->app->updateCon($data['contactId'], $merge_vars);

                //apply the tags to main contact based
                foreach($set_dealers as $tag){
                    $infusionsoft->app->grpAssign($data['contactId'], $tag['tag']);
                }

                //apply tag Dealers Assigned to main contact
                $infusionsoft->app->grpAssign($data['contactId'], 591);

                //add action to a every dealer selected
                $action_data = array('ObjectType'=>'Note','CreationNotes' => $data['_VehicleToPurchaseSummary']);
                foreach($set_dealers as $dealer_id){
                    $action_data['ContactId'] = $dealer_id['id'];
                    $contact_name = $dealer_id['name'];
                    $action_data['ActionDescription'] =  'New Quote Request For '.$contact_name.' Sent';
                    $infusionsoft->app->dsAdd('ContactAction', $action_data);
                }

                $message = 'Data has been successfully updated!';
                $success = true;

            } else {
                $message = 'Cannot connect to Infusionsoft. Please try again';
                $success = false;
            }
        } else {
            $message = 'Please select at least one dealer!';
            $success = false;
        }

        return  wp_send_json(array('success'=>$success, 'message'=>$message));
        wp_die();
    }

    public function create_dealer_quote_form($atts = [], $content = null, $tag = ''){
        global $wpdb;
        $parameters = $_SERVER['QUERY_STRING'];
        parse_str( $parameters, $data );
        $data_keys = array_keys($data);
        $a = shortcode_atts( array(
        ), $atts );

        ob_start();

        include_once( 'partials/dealer_quote_form.php' );

        $output_string = ob_get_contents();
        ob_end_clean();

        return $output_string;
    }

    public function dealer_quote_post_data(){
        $infusionsoft = new Infusion_Request();
        $dealer_index = absint(preg_replace("/[^0-9]/", '', $_POST['dealerEmailKey']));
        $success = false;

        //check if we can connect to infusionsoft
        if($infusionsoft->conn !== false){
            $res = 1;

            try{
              if($_FILES['quotePDF']['error'] == 0 || $_FILES['quotePDF']['error'] == 4){
                  $ext = pathinfo($_FILES['quotePDF']['name'], PATHINFO_EXTENSION);

                  if(!empty($ext)){

                      if($ext == 'pdf'){
                          $res = $this->upload_file_to_contact($_FILES['quotePDF'], $_POST['contactId']);

                          if($res == 0){
                              $success = "false";
                              $error = new WP_Error( 'tvb_pdf_upload_error', 'Error uploading file! Please try again', '' );
                              set_transient("tvb_dealer_quote_form", $error, 45);
                          }
                      } else {
                          $res = 0;
                          $success = "false";
                          $error = new WP_Error( 'tvb_pdf_upload_error', 'Please upload a file with .pdf extension!', '' );
                          set_transient("tvb_dealer_quote_form", $error, 45);
                      }
                  }

              } else {
                  $res = 0;
                  throw new TVB_UploadException($_FILES['quotePDF']['error']);
                  $success = "false";
              }
            } catch(TVB_UploadException $e){
                $error = new WP_Error( 'tvb_file_upload_error', $e->getMessage(), '' );
                set_transient("tvb_dealer_quote_form", $error, 45);
                $res = 0;
                $success = "false";
            }

            if($res == 1){
                $quote_details = "Driveaway Price: ".$_POST['tvb_DriveawayPrice']."\n Lead time: ".$_POST['tvb_Leadtime'];
                $merge_vars['_Dealer'.$dealer_index.'Quote'] = $quote_details;
                $settings = tvb_settings();

                $infusionsoft->app->updateCon($_POST['contactId'], $merge_vars);
                $infusionsoft->app->grpAssign($_POST['contactId'], $settings['dealers_quoted'][$dealer_index]); //assign the tag

                //add a note to the dealer
                $dealer_action_data = array('ObjectType'=>'Note',
                                     'CreationNotes' => $quote_details,
                                     'ContactId'=>$_POST['dealerId'],
                                     'ActionDescription' =>'Dealer Submitted Quote For '.$_POST['contactName'].' Sent',
                );
                $infusionsoft->app->dsAdd('ContactAction', $dealer_action_data);

                //add note the contact
                $contact_action_data = array('ObjectType'=>'Note',
                    'CreationNotes' => $_POST['_VehicleToPurchaseSummary'],
                    'ContactId'=>$_POST['contactId'],
                    'ActionDescription' =>'New Quote Request For '.$_POST['dealerName'].' Sent',
                );
                $infusionsoft->app->dsAdd('ContactAction', $contact_action_data);

                $success = "true";
            }

        } else {
            $error = new WP_Error( 'tvb_infusionsoft_connection_error', 'Cannot connect to Infusionsoft. Please try again', '' );
            set_transient("tvb_dealer_quote_form", $error, 45);
            $success = "false";
        }

        set_transient("tvb_dealer_quote_form_values", array('tvb_DriveawayPrice'=>$_POST['tvb_DriveawayPrice'], 'tvb_Leadtime'=>$_POST['tvb_Leadtime']), 45);

        wp_redirect($_POST['_wp_http_referer']."&success=".$success);

    }

    /**
     * @param $file
     * @param $contactID
     */
    public function upload_file_to_contact($file, $contactID){
        $infusionsoft = new Infusion_Request();
        $fileUploadSuccess = 1;

        $userFileName = $file['name'];
        $userTmpFile  = $file['tmp_name'];
        $fileOpen = fopen($userTmpFile, 'r');
        $data = fread($fileOpen, filesize($userTmpFile));
        fclose($fileOpen);
        $dataEncoded = base64_encode($data);
        $uploadFile = $infusionsoft->app->uploadFile($userFileName, $dataEncoded, $contactID);
        // write result to log file for troubleshooting
        $file=fopen("file_error_log.txt","a+");
        $currentDate = date_format(date_create(), 'Ymd H:i:s');
        fwrite($file, "n $currentDate n");
        if ($uploadFile && !is_string($uploadFile)) {
            fwrite($file, "Uploaded $userFileName for ContactId ".$_POST['contactId']." Upload Id is $uploadFile n");
        } else {
            fwrite($file, "Unable to upload $userFileName for ContactId ".$contactID." Upload Id is $uploadFile n");
            $fileUploadSuccess = 0;
        }
        fclose($file);

        return $fileUploadSuccess;

    }
}

return new TVB_Quote_Automation_Public();