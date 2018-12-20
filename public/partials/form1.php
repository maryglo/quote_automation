<?php
if(in_array('contactId', $data_keys) && !empty($data['contactId'])){

//get contact data by id
$infusionsoft = new Infusion_Request();
$contact_data = $infusionsoft->getContactDataByID($data['contactId']);

if(!empty($contact_data['data']) && is_array($contact_data['data'])){
    $contact_data = $contact_data['data'];

    //get dealers by car make and contactType
    $query = array('ContactType' => 'Dealer');

    if(isset($contact_data['_MakeDropdown']) && !empty($contact_data['_MakeDropdown'])){
    $query['_DealerMakesOffered'] = '%'.$contact_data['_MakeDropdown'].'%';
    }

    $return_fields = array('Id','FirstName','LastName','Email', 'Company', '_DealerStatesServiced', 'Phone1');

    //query dealers
    $dealers = $infusionsoft->getContactbyParameters($query,$return_fields);
    //create the options for dealers dropdown
    $dealer_opts = createDealersOpts($dealers);
?>
    <div class="tvb_wrapper">
    <div class="alert alert-success" style="display: none">
    </div>
    <form id="tvb_request_quote_form" method="post">
        <input type="hidden" name="contactId" value="<?php echo $data['contactId']; ?>"/>
        <input type="hidden" name="_VehicleToPurchaseSummary" value="<?php if (isset($contact_data['_VehicleToPurchaseSummary'])) { echo $contact_data['_VehicleToPurchaseSummary']; } ?>"/>
        <div class="form-group">
            <label for="tvb_FirstName">Name: <?php if (isset($contact_data['FirstName'])) { echo $contact_data['FirstName']; } ?> <?php if (isset($data['LastName'])) { echo $data['LastName']; } ?></label>
        </div>
        <div class="form-group">
            <label for="tvb_Email">Email: <?php if (isset($contact_data['Email'])) { echo $contact_data['Email']; } ?></label>
        </div>
        <div class="form-group">
            <label for="tvb_Phone1">Phone: <?php if (isset($contact_data['Phone1'])) { echo $contact_data['Phone1']; } ?></label>
        </div>
        <div class="form-group">
            <label for="tvb_MakeDropdown">Make: <?php if (isset($contact_data['_MakeDropdown'])) { echo $contact_data['_MakeDropdown']; } ?></label>
        </div>
        <div class="form-group">
            <label for="tvb_VehicleToPurchaseSummary">
                Vehicle to Source Summary:
            </label>
            <textarea class="form-control" id="tvb_VehicleToPurchaseSummary" style="height:100%" disabled><?php if (isset($contact_data['_VehicleToPurchaseSummary'])) { echo $contact_data['_VehicleToPurchaseSummary']; } ?></textarea>
        </div>
        <div class="form-group">
            <label for="tvb_BrokerageFee">Fee Offered: <?php if (isset($contact_data['_BrokerageFee'])) { echo $contact_data['_BrokerageFee']; } ?></label>
        </div>
        <div class="form-group">
            <label for="tvb_Dealer1Email">Dealer 1 </label>
            <select data-position="1" class="form-control dealers_opts" name="tvb_Dealer[0][Email]" id="tvb_Dealer1Email" required>
                <option value="">Select a Dealer</option>
                <?php echo  $dealer_opts; ?>
            </select>
            <input type="hidden" class="form-control" name="tvb_Dealer[0][Id]" id="tvb_DealerId_1" value="" required/>
            <input type="hidden" class="form-control" name="tvb_Dealer[0][Name]" id="tvb_DealerName_1" required value=""/>
            <input type="hidden" class="form-control" name="tvb_Dealer[0][Tag]" id="tvb_DealerTag_1" required value="573"/>
        </div>
        <div class="form-group">
            <label for="tvb_Dealer2Email">Dealer 2 </label>
            <select data-position="2" class="form-control dealers_opts" name="tvb_Dealer[1][Email]" id="tvb_Dealer2Email">
                <option value="">Select a Dealer</option>
                <?php echo  $dealer_opts; ?>
            </select>
            <input type="hidden" class="form-control" name="tvb_Dealer[1][Id]" id="tvb_DealerId_2" value=""/>
            <input type="hidden" class="form-control" name="tvb_Dealer[1][Name]" id="tvb_DealerName_2" value=""/>
            <input type="hidden" class="form-control" name="tvb_Dealer[1][Tag]" id="tvb_DealerTag_2" required value="575"/>
        </div>
        <div class="form-group">
            <label for="tvb_Dealer3Email">Dealer 3 </label>
            <select data-position="3" class="form-control dealers_opts" name="tvb_Dealer[2][Email]" id="tvb_Dealer3mail">
                <option value="">Select a Dealer</option>
                <?php echo  $dealer_opts; ?>
            </select>
            <input type="hidden" class="form-control" name="tvb_Dealer[2][Id]" id="tvb_DealerId_3" value=""/>
            <input type="hidden" class="form-control" name="tvb_Dealer[2][Name]" id="tvb_DealerName_3" value=""/>
            <input type="hidden" class="form-control" name="tvb_Dealer[2][Tag]" id="tvb_DealerTag_3" required value="577"/>
        </div>
        <div class="form-group">
            <label for="tvb_DealerQuoteNotes">
                Additional Dealer Notes
            </label>
            <textarea class="form-control" rows="3" name="tvb_DealerQuoteNotes"></textarea>
        </div>
        <div class="alert alert-danger" style="display: none">

        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-default" id="tvb_submit">SUBMIT</button>
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
        <strong>Error!</strong> Missing contactId value!
    </div>
<?php
}