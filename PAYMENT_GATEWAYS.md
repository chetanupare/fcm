# Payment Gateway Integration Guide

## Supported Gateways

1. **Razorpay** - Indian payment gateway
2. **PhonePe** - Indian UPI payment gateway  
3. **Paytm** - Indian payment gateway
4. **Stripe** - International payment gateway (placeholder)
5. **PayPal** - International payment gateway (placeholder)
6. **Cash** - Cash on delivery
7. **COD** - Cash on delivery (alias)

## Configuration

Add the following to your `.env` file:

### Razorpay
```env
RAZORPAY_KEY_ID=your_key_id
RAZORPAY_KEY_SECRET=your_key_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
```

### PhonePe
```env
PHONEPE_MERCHANT_ID=your_merchant_id
PHONEPE_SALT_KEY=your_salt_key
PHONEPE_SALT_INDEX=1
PHONEPE_SANDBOX=true
```

### Paytm
```env
PAYTM_MERCHANT_ID=your_merchant_id
PAYTM_MERCHANT_KEY=your_merchant_key
PAYTM_WEBSITE=WEBSTAGING
PAYTM_INDUSTRY_TYPE=Retail
PAYTM_CHANNEL_ID=WEB
PAYTM_SANDBOX=true
```

## Payment Flow

### For Online Payments (Razorpay/PhonePe/Paytm)

1. **Initiate Payment**: `POST /api/technician/jobs/{id}/payment/initiate`
   - Creates payment order with gateway
   - Returns gateway URL/redirect data
   - Frontend redirects user to gateway

2. **Gateway Redirect**: User completes payment on gateway

3. **Webhook Confirmation**: Gateway sends webhook to `/api/webhooks/{gateway}`
   - Verifies payment signature
   - Updates payment status to 'completed'
   - Releases technician automatically

### For Cash/COD Payments

1. **Record Payment**: `POST /api/technician/jobs/{id}/payment`
   - Technician records cash payment
   - Payment status set to 'completed' immediately
   - Technician released immediately

## Webhook Endpoints

- `/api/webhooks/razorpay` - Razorpay webhook
- `/api/webhooks/phonepe` - PhonePe webhook
- `/api/webhooks/paytm` - Paytm webhook
- `/api/webhooks/stripe` - Stripe webhook (placeholder)
- `/api/webhooks/paypal` - PayPal webhook (placeholder)

## Important Notes

1. **Payment Amount Validation**: System validates payment amount matches quote total (with 0.01 tolerance)

2. **Technician Release**: 
   - Cash/COD: Released immediately
   - Online gateways: Released only after webhook confirmation

3. **Payment Status**:
   - `pending`: Payment initiated, awaiting confirmation
   - `completed`: Payment confirmed
   - `failed`: Payment failed
   - `refunded`: Payment refunded

4. **Security**: All webhooks verify signatures before processing
