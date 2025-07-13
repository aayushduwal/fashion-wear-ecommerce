<?php
// Payment Gateway Configuration

// eSewa Configuration
define('ESEWA_MERCHANT_ID', 'EPAYTEST'); 
define('ESEWA_TEST_URL', 'https://uat.esewa.com.np/epay/main');
define('ESEWA_LIVE_URL', 'https://epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_TEST_VERIFY_URL', 'https://uat.esewa.com.np/epay/transrec');
define('ESEWA_LIVE_VERIFY_URL', 'https://epay.esewa.com.np/api/epay/transaction/v2/status');

// Khalti KPG-2 Configuration
define('KHALTI_TEST_SECRET_KEY', '7dd2c37f8b384e5998aa212d35045cb7'); // Your real test secret key
define('KHALTI_TEST_PUBLIC_KEY', '78072db1d1c84fef9f807ac0443c5d7c'); // Your real test public key
define('KHALTI_LIVE_SECRET_KEY', 'live_secret_key_your_khalti_secret_key');
define('KHALTI_TEST_API_URL', 'https://dev.khalti.com/api/v2/');
define('KHALTI_LIVE_API_URL', 'https://khalti.com/api/v2/');

// Environment Setting
define('PAYMENT_ENV', 'test'); // Change to 'live' for production

// Website Configuration
define('WEBSITE_URL', 'http://localhost/fashionwear/');
define('RETURN_URL', 'http://localhost/fashionwear/payment/khalti/khalti_callback.php');

// Get URLs based on environment
function getEsewaUrl() {
    return PAYMENT_ENV === 'live' ? ESEWA_LIVE_URL : ESEWA_TEST_URL;
}

function getEsewaVerifyUrl() {
    return PAYMENT_ENV === 'live' ? ESEWA_LIVE_VERIFY_URL : ESEWA_TEST_VERIFY_URL;
}

function getKhaltiSecretKey() {
    return PAYMENT_ENV === 'live' ? KHALTI_LIVE_SECRET_KEY : KHALTI_TEST_SECRET_KEY;
}

function getKhaltiApiUrl() {
    return PAYMENT_ENV === 'live' ? KHALTI_LIVE_API_URL : KHALTI_TEST_API_URL;
}

function getWebsiteUrl() {
    return WEBSITE_URL;
}

function getReturnUrl() {
    return RETURN_URL;
}
?>