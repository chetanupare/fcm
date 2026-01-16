<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician En Route</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Technician On The Way</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Your technician is now en route to your location!</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3b82f6;">
            <h2 style="margin-top: 0; color: #1f2937;">Arrival Information</h2>
            @if(isset($data['eta_text']))
                <p style="margin: 5px 0; font-size: 18px;"><strong>Estimated Arrival:</strong> {{ $data['eta_text'] }}</p>
            @endif
            @if(isset($data['arrival_window']))
                <p style="margin: 5px 0;"><strong>Arrival Window:</strong> {{ $data['arrival_window'] }}</p>
            @endif
        </div>
        
        <p style="font-size: 16px; margin-top: 30px;">You can track the technician's location in real-time using the link below.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $data['tracking_url'] ?? url('/') }}" 
               style="display: inline-block; background: #3b82f6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Track Live Location
            </a>
        </div>
    </div>
</body>
</html>
