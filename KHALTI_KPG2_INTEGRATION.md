# Khalti KPG-2 Integration Guide - FashionWear

## Overview

This document details the implementation of Khalti's latest Web Checkout (KPG-2) API as per the official documentation. This replaces the previous widget-based integration with a more robust server-to-server approach.

## 🎯 What Changed from Previous Integration

### Before (Old Widget Integration):

- Client-side widget loading from CDN
- Direct payment processing in browser
- Limited error handling
- Dependency on JavaScript widget availability

### After (KPG-2 API Integration):

- Server-to-server payment initiation
- Official Khalti payment portal redirect
- Comprehensive callback handling
- Robust verification with lookup API

## 🔧 Implementation Details

### 1. Configuration Updated (`payment/payment_config.php`)

```php
// New Khalti KPG-2 Configuration
define('KHALTI_TEST_SECRET_KEY', '05bf95cc57244045b8df5fad06748dab');
define('KHALTI_TEST_API_URL', 'https://dev.khalti.com/api/v2/');
define('KHALTI_LIVE_API_URL', 'https://khalti.com/api/v2/');
define('WEBSITE_URL', 'http://localhost/fashionwear/');
define('RETURN_URL', 'http://localhost/fashionwear/payment/khalti_callback.php');
```

### 2. Payment Initiation (`payment/khalti_payment_new.php`)

**Process Flow:**

1. Validate user session and payment amount
2. Generate unique purchase order ID
3. Prepare payment payload with customer info
4. Make POST request to Khalti initiate API
5. Store payment details in session
6. Redirect user to Khalti payment portal

**API Endpoint:** `POST /epayment/initiate/`

**Required Payload:**

```json
{
  "return_url": "http://localhost/fashionwear/payment/khalti_callback.php",
  "website_url": "http://localhost/fashionwear/",
  "amount": 150000, // Amount in paisa
  "purchase_order_id": "ORDER_1704887654_123",
  "purchase_order_name": "FashionWear Order",
  "customer_info": {
    "name": "Customer Name",
    "email": "customer@example.com",
    "phone": "9800000000"
  }
}
```

**Success Response:**

```json
{
  "pidx": "bZQLD9wRVWo4CdESSfuSsB",
  "payment_url": "https://test-pay.khalti.com/?pidx=bZQLD9wRVWo4CdESSfuSsB",
  "expires_at": "2023-05-25T16:26:16.471649+05:45",
  "expires_in": 1800
}
```

### 3. Callback Handling (`payment/khalti_callback.php`)

**Process Flow:**

1. Receive callback from Khalti with payment status
2. Validate session and PIDX match
3. Use lookup API to verify payment status
4. Update database based on verification result
5. Redirect to success or failure page

**Callback Parameters:**

- `pidx` - Payment identifier
- `status` - Transaction status
- `transaction_id` - Khalti transaction ID
- `amount` - Amount paid in paisa
- `mobile` - Payer's Khalti ID

### 4. Payment Verification (Lookup API)

**API Endpoint:** `POST /epayment/lookup/`

**Request:**

```json
{
  "pidx": "HT6o6PEZRWFJ5ygavzHWd5"
}
```

**Possible Response Statuses:**

- `Completed` - Payment successful (provide service)
- `Pending` - Payment in progress (hold)
- `Expired` - Payment link expired (deny service)
- `User canceled` - User cancelled payment (deny service)
- `Refunded` - Payment refunded (deny service)

## 🧪 Testing

### Test Credentials for Sandbox:

- **Khalti ID:** 9800000000, 9800000001, 9800000002, 9800000003, 9800000004, 9800000005
- **MPIN:** 1111
- **OTP:** 987654
- **Login OTP for Merchant:** 987654

### Test Process:

1. Access: `http://localhost/fashionwear/test_payments.php`
2. Click "Test Khalti Payment (KPG-2)"
3. Enter test amount (minimum NPR. 10)
4. User redirected to Khalti payment portal
5. Login with test credentials
6. Complete payment process
7. Verify callback and database updates

## 📁 File Structure

```
payment/
├── payment_config.php          # Updated configuration
├── khalti_payment_new.php      # New payment initiation
├── khalti_callback.php         # Callback handler
├── process_payment.php         # Updated for KPG-2
└── khalti_checkout.php         # Old file (now unused)
```

## 🔒 Security Features

### 1. Server-Side Processing

- All sensitive operations on server
- Secret key never exposed to client
- Secure API communication

### 2. Payment Verification

- Mandatory lookup API verification
- Session validation for callbacks
- PIDX matching for security

### 3. Error Handling

- Comprehensive error logging
- Graceful failure handling
- User-friendly error messages

## 🚀 Production Deployment

### Steps to Go Live:

1. **Get Production Credentials:**

   - Sign up at [Khalti Merchant Portal](https://admin.khalti.com)
   - Get live secret key

2. **Update Configuration:**

   ```php
   define('PAYMENT_ENV', 'live');
   define('KHALTI_LIVE_SECRET_KEY', 'your_live_secret_key');
   define('WEBSITE_URL', 'https://yourdomain.com/');
   define('RETURN_URL', 'https://yourdomain.com/payment/khalti_callback.php');
   ```

3. **SSL Certificate:**

   - Ensure HTTPS for production
   - Update all URLs to HTTPS

4. **Test Thoroughly:**
   - Test with small amounts first
   - Verify all payment flows
   - Monitor error logs

## 📊 Advantages of KPG-2 Implementation

### 1. **Reliability:**

- No dependency on client-side scripts
- Robust server-to-server communication
- Official Khalti payment portal

### 2. **Security:**

- Server-side payment processing
- Secure credential handling
- Comprehensive verification

### 3. **User Experience:**

- Professional payment portal
- Better mobile experience
- Consistent UI across devices

### 4. **Maintenance:**

- No widget updates required
- API versioning support
- Better error diagnostics

## 🔍 Troubleshooting

### Common Issues:

1. **Payment Initiation Fails:**

   - Check secret key validity
   - Verify API endpoint URLs
   - Ensure proper JSON payload format

2. **Callback Not Received:**

   - Verify return_url accessibility
   - Check server logs for errors
   - Ensure proper session management

3. **Verification Fails:**
   - Check PIDX parameter
   - Verify API authorization
   - Review lookup API response

### Debug Tips:

- Enable error logging in PHP
- Check browser network tab
- Monitor Khalti dashboard
- Use test credentials first

## 📞 Support

### Resources:

- [Khalti Documentation](https://docs.khalti.com/)
- [Merchant Portal](https://admin.khalti.com)
- [Test Environment](https://test-admin.khalti.com)

This implementation follows Khalti's official KPG-2 documentation and provides a production-ready payment integration for the FashionWear e-commerce platform.
