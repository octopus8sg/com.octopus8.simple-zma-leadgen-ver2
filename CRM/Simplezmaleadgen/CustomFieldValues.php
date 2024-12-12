<?php
use CRM_Simplezmaleadgen_Utils as U;

class CustomFieldValues
{
    private $subscribeChoice;
    private $donationMethod;
    private $donationAmount;

    public function setSubscribeChoice($subscribeChoice){
        $this->subscribeChoice = $subscribeChoice;
    }
    
    public function getSubscribeChoice(){
        return $this->subscribeChoice;
    }

    public function setDonationMethod($donationMethod){
        $this->donationMethod = $donationMethod;
    }

    public function getDonationMethod(){
        return $this->donationMethod;
    }

    public function setDonationAmount($donationAmount){
        $this->donationAmount = $donationAmount;
    }

    public function getDonationAmount(){
        return $this->donationAmount;
    }
}