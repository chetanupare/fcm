<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Assigned</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Technician Assigned</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">Great news! A technician has been assigned to your service request.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981;">
            <h2 style="margin-top: 0; color: #1f2937;">Technician Details</h2>
            <p style="margin: 5px 0;"><strong>Technician:</strong> {{ $data['technician_name'] ?? 'N/A' }}</p>
            @if(isset($data['estimated_duration']))
                <p style="margin: 5px 0;"><strong>Estimated Arrival:</strong> {{ $data['estimated_duration'] }}</p>
            @endif
            @if(isset($data['distance_km']))
                <p style="margin: 5px 0;"><strong>Distance:</strong> {{ $data['distance_km'] }} km</p>
            @endif
        </div>
        
        <p style="font-size: 16px; margin-top: 30px;">You'll receive updates as the technician makes their way to your location.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $data['tracking_url'] ?? url('/') }}" 
               style="display: inline-block; background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Track Technician
            </a>
        </div>
    </div>
</body>
</html>
