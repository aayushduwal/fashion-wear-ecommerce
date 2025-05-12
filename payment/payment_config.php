<?php
// Payment Gateway Configuration

// eSewa Configuration
define('ESEWA_MERCHANT_ID', 'EPAYTEST'); 
define('ESEWA_TEST_URL', 'https://uat.esewa.com.np/epay/main');
define('ESEWA_LIVE_URL', 'https://epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_TEST_VERIFY_URL', 'https://uat.esewa.com.np/epay/transrec');
define('ESEWA_LIVE_VERIFY_URL', 'https://epay.esewa.com.np/api/epay/transaction/v2/status');

// Khalti Configuration
define('KHALTI_PUBLIC_KEY', 'test_public_key_ce25e5d5f64a4b8f91c85831e89f1234'); 
define('KHALTI_SECRET_KEY', 'test_secret_key_ce25e5d5f64a4b8f91c85831e89f1234'); 
define('KHALTI_TEST_URL', 'https://a.khalti.com/api/v2/epayment/initiate/');
define('KHALTI_LIVE_URL', 'https://khalti.com/api/v2/epayment/initiate/');
define('KHALTI_TEST_VERIFY_URL', 'https://a.khalti.com/api/v2/epayment/verify/');
define('KHALTI_LIVE_VERIFY_URL', 'https://khalti.com/api/v2/epayment/verify/');

// Environment Setting
define('PAYMENT_ENV', 'test'); // Change to 'live' for production

// Get URLs based on environment
function getEsewaUrl() {
    return PAYMENT_ENV === 'live' ? ESEWA_LIVE_URL : ESEWA_TEST_URL;
}

function getEsewaVerifyUrl() {
    return PAYMENT_ENV === 'live' ? ESEWA_LIVE_VERIFY_URL : ESEWA_TEST_VERIFY_URL;
}

function getKhaltiUrl() {
    return PAYMENT_ENV === 'live' ? KHALTI_LIVE_URL : KHALTI_TEST_URL;
}

function getKhaltiVerifyUrl() {
    return PAYMENT_ENV === 'live' ? KHALTI_LIVE_VERIFY_URL : KHALTI_TEST_VERIFY_URL;
}
?>
