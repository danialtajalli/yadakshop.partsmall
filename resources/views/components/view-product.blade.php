<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
@vite(['resources/js/app.js'])
<div>
    <div wire:ignore>
        <div id="map" style="width: 100%; height: 500px;"></div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const map = new window.nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: "map",
                zoom: 11,
                pitch: 0,
                center: [51.389, 35.6892],
                minZoom: 2,
                maxZoom: 21,
                trackResize: true,
                mapKey: "web.6657b65ae6c44634964f23f8b613d46d",
                poi: true,
                traffic: true,
                mapTypeControllerOptions: {
                    show: true,
                    position: 'bottom-left'
                }
            });
            let longitude = {{ $get('longitude') }};
            let latitude = {{ $get('latitude') }};
            let marker;

            marker = new nmp_mapboxgl.Marker()
                .setLngLat([longitude, latitude])
                .addTo(map);

            map.on('click', (e) => {
                const { lng, lat } = e.lngLat;
                console.log(lng, lat);
                if (!marker) {
                    marker = new nmp_mapboxgl.Marker({
                        color: "#1b80e4",
                        draggable: false
                    })
                    .setLngLat([lng, lat])
                    .addTo(map);
                } else {
                    marker.setLngLat([lng, lat]);
                }
                Livewire.dispatch('location-updated', {
                    latitude: lat,
                    longitude: lng
                });
            });
        });
    </script>
</div>
