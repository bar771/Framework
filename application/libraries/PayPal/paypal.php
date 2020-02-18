<?php

class PayPal {
  /*
   * Functions:
   * Build payment form.
   * Finalize payment with IPN- activate whatever you want to activate, or log the transaction in your db.
  **/
  function __construct()
  {
    // Constructor
  }

  function buildPayment() {

  }

  // https://developer.paypal.com/docs/ipn/
  // https://developer.paypal.com/docs/ipn/integration-guide/IPNImplementation/
  // https://developer.paypal.com/docs/ipn/integration-guide/ht-ipn/
  // https://developer.paypal.com/docs/ipn/integration-guide/IPNSetup/
  function receiveIPN() {
    $rawData = stripslashes(RAW_DATA);

    

    header('HTTP/1.1 200 OK');
  }
}


?>
