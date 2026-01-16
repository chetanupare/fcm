<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Complete</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Service Complete!</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Your service has been completed successfully!</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981;">
            <h2 style="margin-top: 0; color: #1f2937;">Service Summary</h2>
            <p style="margin: 5px 0;"><strong>Ticket #:</strong> {{ $data['ticket_id'] ?? 'N/A' }}</p>
            <p style="margin: 5px 0;"><strong>Service Date:</strong> {{ $data['completed_at'] ?? now()->format('M d, Y') }}</p>
            @if(isset($data['total_amount']))
                <p style="margin: 5px 0; font-size: 18px;"><strong>Total Amount:</strong> {{ $data['total_amount'] }}</p>
            @endif
        </div>
        
        <p style="font-size: 16px; margin-top: 30px;">We hope you're satisfied with our service. Please rate your experience!</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $data['rating_url'] ?? url('/') }}" 
               style="display: inline-block; background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
                Rate Service
            </a>
            @if(isset($data['invoice_url']))
                <a href="{{ $data['invoice_url'] }}" 
                   style="display: inline-block; background: #6b7280; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    View Invoice
                </a>
            @endif
        </div>
    </div>
</body>
</html>
