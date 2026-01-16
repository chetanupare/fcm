<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Booking Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Service Request Confirmed</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Thank you for your service request! We've received your booking and will assign a technician shortly.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
            <h2 style="margin-top: 0; color: #1f2937;">Booking Details</h2>
            <p style="margin: 5px 0;"><strong>Ticket #:</strong> {{ $data['ticket_id'] ?? 'N/A' }}</p>
            <p style="margin: 5px 0;"><strong>Device:</strong> {{ $data['device'] ?? 'N/A' }}</p>
            <p style="margin: 5px 0;"><strong>Issue:</strong> {{ $data['issue'] ?? 'N/A' }}</p>
            <p style="margin: 5px 0;"><strong>Expected Response:</strong> Within 5 minutes</p>
        </div>
        
        <p style="font-size: 16px; margin-top: 30px;">We'll notify you as soon as a technician is assigned to your request.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $data['tracking_url'] ?? url('/') }}" 
               style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Track Your Request
            </a>
        </div>
        
        <p style="font-size: 14px; color: #6b7280; margin-top: 30px;">If you have any questions, please don't hesitate to contact us.</p>
        
        <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">Best regards,<br>{{ config('app.name') }} Team</p>
    </div>
</body>
</html>
