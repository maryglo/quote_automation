<?php
function tvb_settings() {
    $defaults = require TVB_QUOTE_AUTOMATION_DIR . '/includes/config/default_settings.php';
    return $defaults;
}

function createDealersOpts($data){
    $opts = "";

    if(!empty($data['data'])){
        foreach($data['data'] as $options){
            $fname = isset($options['FirstName']) ? $options['FirstName']: "";
            $lname = isset($options['LastName']) ? $options['LastName']: "";
            $display = $fname." ".$lname." (".$options['Email'].") ";

            if(!empty($options['Company'])){
                $display .= "- ".$options['Company'];
            }

            if(isset($options['_DealerStatesServiced']) && !empty($options['_DealerStatesServiced'])){
                $display .= "- ".$options['_DealerStatesServiced'];
            }

            $opts .= "<option data-id='".$options['Id']."' data-name='".$fname." ".$lname."'  value='".$options['Email']."'>".$display."</option>";
        }
    }
    return $opts;
}

function checkDealerData($data){
    $valid_arr = array();

    if(!empty($data)){
        foreach($data as $key=>$d){
            if(!empty($d['Email']) && !empty($d['Id'])){
                $valid_arr[] = $key;
            }
        }

        if(count($valid_arr) > 0){
            return true;
        } else {
            return false;
        }

    } else {
        return false;
    }
}
