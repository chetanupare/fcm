<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Ready for Review</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Quote Ready for Review</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Your technician has completed the diagnosis and prepared a quote for your service.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
            <h2 style="margin-top: 0; color: #1f2937;">Quote Summary</h2>
            <p style="margin: 5px 0; font-size: 24px; font-weight: bold; color: #059669;"><strong>Total: {{ $data['total_amount'] ?? 'N/A' }}</strong></p>
            <p style="margin: 5px 0;"><strong>Ticket #:</strong> {{ $data['ticket_id'] ?? 'N/A' }}</p>
        </div>
        
        <p style="font-size: 16px; margin-top: 30px;">Please review the quote and approve it to proceed with the service.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $data['quote_url'] ?? url('/') }}" 
               style="display: inline-block; background: #f59e0b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Review & Approve Quote
            </a>
        </div>
    </div>
</body>
</html>
