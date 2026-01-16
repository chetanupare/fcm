<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Payment Reminder</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">This is a friendly reminder that payment is due for your recent service.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ef4444;">
            <h2 style="margin-top: 0; color: #1f2937;">Payment Details</h2>
            <p style="margin: 5px 0; font-size: 24px; font-weight: bold; color: #059669;"><strong>Amount Due: {{ $data['amount'] ?? 'N/A' }}</strong></p>
            <p style="margin: 5px 0;"><strong>Ticket #:</strong> {{ $data['ticket_id'] ?? 'N/A' }}</p>
            @if(isset($data['due_date']))
                <p style="margin: 5px 0;"><strong>Due Date:</strong> {{ $data['due_date'] }}</p>
            @endif
        </div>
        
        <p style="font-size: 16px; margin-top: 30px;">Please complete your payment at your earliest convenience.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $data['payment_url'] ?? url('/') }}" 
               style="display: inline-block; background: #ef4444; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Pay Now
            </a>
        </div>
    </div>
</body>
</html>
