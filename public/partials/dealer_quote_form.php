<?php
if(in_array('contactId', $data_keys) && !empty($data['contactId']) && count($data) > 1){

//get contact data by id
    $infusionsoft = new Infusion_Request();
    $form_values = get_transient( "tvb_dealer_quote_form_values" );

    if($infusionsoft->conn !== false){
        //get the email parameter
        $dealer_email = array_values($data)[1];
        $dealer_email = str_replace(' ', '+',$dealer_email);
        $return_fields = array('Id','FirstName','LastName','_VehicleToPurchaseSummary','_DealerQuoteNotes','_BrokerageFee', '_Dealer1Email', '_Dealer2Email','_Dealer3Email');
        $contact_data = $infusionsoft->getContactDataByID($data['contactId'], $return_fields);

        if(!empty($contact_data['data']) && is_array($contact_data['data']) ){

            if(!in_array($dealer_email, array_values($contact_data['data']), TRUE)){
            ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> You have already submitted a quote for, or are no longer assigned to this lead.
                </div>
            <?php
                return;
            }
                $contact_data = $contact_data['data'];


            if(isset($_GET['success']) && $_GET['success'] == 'true'){
            ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> Quote details have been submitted successfully.
                </div>
            <?php
                delete_transient("tvb_dealer_quote_form_values");
                return;
            }

            //query dealer id by email
            $dealer_data = $infusionsoft->getContactbyParameters(array('ContactType' => 'Dealer', 'Email'=>$dealer_email),array('Id', 'FirstName', 'LastName'));
            $dealer_name = $dealer_data['data'][0]['FirstName']." ".$dealer_data['data'][0]['LastName'];

            $contact_name = $contact_data['FirstName']." ".$contact_data['LastName'];
        ?>
            <div class="tvb_wrapper">
                <div class="alert alert-success" style="display: none">
                </div>
                <form id="tvb_dealer_quote_form" method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>"  enctype="multipart/form-data">
                    <?php wp_nonce_field( basename( __FILE__ ), 'tvb_dealer_quote_form' ); ?>
                    <input type="hidden" name="action" value="dealer_quote_post_data">
                    <input type="hidden" name="contactId" value="<?php echo $data['contactId']; ?>"/>
                    <input type="hidden" name="contactName" value="<?php echo $contact_name; ?>"/>
                    <input type="hidden" name="dealerEmailKey" value="<?php echo $data_keys[1]; ?>"/>
                    <input type="hidden" name="dealerEmail" value="<?php echo $dealer_email; ?>"/>
                    <input type="hidden" name="dealerId" value="<?php echo $dealer_data['data'][0]['Id']; ?>"/>
                    <input type="hidden" name="dealerName" value="<?php echo $dealer_name;?>" />
                    <input type="hidden" name="_VehicleToPurchaseSummary" value="<?php if (isset($contact_data['_VehicleToPurchaseSummary'])) { echo$contact_data['_VehicleToPurchaseSummary']; }?>" />
                    <div class="form-group">
                        <label for="tvb_VehicleToPurchaseSummary">
                            Vehicle to Source Summary:
                        </label>
                       <?php if (isset($contact_data['_VehicleToPurchaseSummary'])) { ?>
                               <textarea class="form-control" id="tvb_VehicleToPurchaseSummary" style="height:100%" disabled><?php echo $contact_data['_VehicleToPurchaseSummary']; ?></textarea>

                       <?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="tvb_BrokerageFee">Fee Offered: <?php if (isset($contact_data['_BrokerageFee'])) { echo $contact_data['_BrokerageFee']; } ?></label>
                    </div>
                    <div class="form-group">
                        <label for="tvb_DealerQuoteNotes">
                            Additional Dealer Notes:
                        </label>
                        <?php if (isset($contact_data['_DealerQuoteNotes'])) { ?>
                                <textarea class="form-control" id="tvb_DealerQuoteNotes" style="height:100%" disabled><?php echo $contact_data['_DealerQuoteNotes']; ?></textarea>

                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="tvb_DriveawayPrice">
                            Driveaway price
                        </label>
                        <input type="number" class="form-control" id="tvb_DriveawayPrice" name="tvb_DriveawayPrice" placeholder="0.00" value = "<?php if($form_values && isset($form_values['tvb_DriveawayPrice'])){ echo $form_values['tvb_DriveawayPrice']; } ?>" required/>
                    </div>
                    <div class="form-group">
                        <label for="tvb_Leadtime">
                            Lead Time
                        </label>
                        <input type="text" class="form-control" id="tvb_Leadtime" name="tvb_Leadtime" placeholder="Lead Time" value = "<?php if($form_values && isset($form_values['tvb_Leadtime'])){ echo $form_values['tvb_Leadtime']; } ?>"  required/>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputFile">Quote PDF</label>
                        <input type="file" id="quotePdF" accept=".pdf" name="quotePDF"/>
                        <p class="help-block">only .pdf format is allowed</p>
                    </div>
                    <?php  $error = get_transient( "tvb_dealer_quote_form" );

                    if($error  && (isset($_GET['success']) && $_GET['success'] == "false")) {?>
                     <div class="alert alert-danger">
                         <strong>Error!</strong> <?php echo  $error->get_error_message(); ?>
                     </div>
                    <?php
                        delete_transient("tvb_dealer_quote_form");
                    }
                    ?>
                    <div class="form-group">
                        <button type="submit" class="btn btn-default" id="tvb_dealer_submit">SUBMIT</button>
                        <div class="tvb_loader" style="display: none"></div>
                    </div>
                </form>
            </div>
    <?php
    } else {
        ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> <?php echo  $contact_data['message']; ?>
            </div>
        <?php
    }

  } else {
     ?>
            <div class="alert alert-danger">
                <strong>Error!</strong> Cannot connect to Infusionsoft. Please try again.
            </div>
    <?php
    }
} else {
    ?>
    <div class="alert alert-danger">
        <strong>Error!</strong> Missing required parameters!
    </div>
<?php
}