@extends('layouts.app')

@section('title', 'Live Map')
@section('page-title', 'Technician Live Map')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Real-Time Technician Locations</h3>
                <p class="text-sm text-slate-500 mt-1">Track technicians on duty</p>
            </div>
            <div class="px-4 py-2 bg-blue-50 rounded-xl border border-blue-100">
                <span class="text-xs text-blue-600 font-medium">On Duty: {{ count($technicians) }}</span>
            </div>
        </div>
        <div id="map" style="height: 600px; width: 100%;" class="rounded-xl border border-slate-200 overflow-hidden"></div>
    </div>

    <!-- Technician Cards -->
    @if(count($technicians) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($technicians as $tech)
                <div class="bg-white rounded-xl border border-slate-100 shadow-lg p-4 ticket-enter">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($tech['name'], 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-slate-800">{{ $tech['name'] }}</h4>
                            <p class="text-xs text-slate-500">Active Jobs: {{ $tech['active_jobs_count'] }}</p>
                        </div>
                    </div>
                    @if($tech['latitude'] && $tech['longitude'])
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <p class="text-xs text-slate-400 font-mono">
                                {{ number_format($tech['latitude'], 6) }}, {{ number_format($tech['longitude'], 6) }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-12 text-center">
            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <p class="text-slate-500 text-lg font-medium">No technicians on duty</p>
            <p class="text-slate-400 text-sm mt-1">Technicians will appear here when they go on duty</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@php
    $googleMapsApiKey = \App\Models\Setting::get('google_maps_api_key', 'AIzaSyCX5KEm1rEGxp05USWcE2XwUlh9KiVnhVs');
@endphp
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initMap" async defer></script>
<script>
    function initMap() {
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: { lat: 0, lng: 0 },
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });

        const technicians = @json($technicians);
        
        if (technicians.length > 0) {
            const bounds = new google.maps.LatLngBounds();
            let hasValidLocation = false;
            
            technicians.forEach(tech => {
                if (tech.latitude && tech.longitude) {
                    hasValidLocation = true;
                    const position = { lat: parseFloat(tech.latitude), lng: parseFloat(tech.longitude) };
                    bounds.extend(position);
                    
                    new google.maps.Marker({
                        position: position,
                        map: map,
                        title: tech.name,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 10,
                            fillColor: '#3B82F6',
                            fillOpacity: 1,
                            strokeColor: '#fff',
                            strokeWeight: 3
                        },
                        label: {
                            text: tech.name.charAt(0),
                            color: '#fff',
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    });
                }
            });
            
            if (hasValidLocation) {
                if (technicians.length > 1) {
                    map.fitBounds(bounds);
                } else {
                    const tech = technicians.find(t => t.latitude && t.longitude);
                    map.setCenter({ lat: parseFloat(tech.latitude), lng: parseFloat(tech.longitude) });
                    map.setZoom(12);
                }
            } else {
                map.setCenter({ lat: 20.5937, lng: 78.9629 });
                map.setZoom(5);
            }
        } else {
            map.setCenter({ lat: 20.5937, lng: 78.9629 });
            map.setZoom(5);
        }
    }
</script>
@endpush
