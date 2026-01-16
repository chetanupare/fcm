<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">{{ $title }}</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">{{ $message }}</p>
        
        @if(!empty($data))
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
                @foreach($data as $key => $value)
                    @if(!in_array($key, ['action_url', 'tracking_url', 'payment_url', 'quote_url', 'rating_url', 'invoice_url']))
                        <p style="margin: 5px 0;"><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                    @endif
                @endforeach
            </div>
        @endif
        
        @if(isset($data['action_url']))
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ $data['action_url'] }}" 
                   style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    View Details
                </a>
            </div>
        @endif
        
        <p style="font-size: 14px; color: #6b7280; margin-top: 30px;">Best regards,<br>{{ config('app.name') }} Team</p>
    </div>
</body>
</html>
