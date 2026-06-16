@props(['icon' => 'part'])

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.75', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }} aria-hidden="true">
    @switch($icon)
        @case('spark-plug')
            <path d="M12 2v5" /><path d="M9 7h6" /><path d="M10 7v3a2 2 0 0 0 4 0V7" /><path d="M11 14h2v6h-2z" /><path d="M10 20h4" />
            @break
        @case('sensor')
            <circle cx="12" cy="12" r="3" /><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2" />
            @break
        @case('injector')
            <path d="M8 4h8v4l-2 14H10L8 8V4z" /><path d="M10 8h4" /><path d="M12 4V2" />
            @break
        @case('fuse')
            <rect x="7" y="8" width="10" height="8" rx="1" /><path d="M9 8V6M15 8V6M9 16v2M15 16v2" /><path d="M10 12h4" />
            @break
        @case('antenna')
            <path d="M12 21V9" /><path d="M8 13c0-2.2 1.8-4 4-4s4 1.8 4 4" /><path d="M6 11c0-3.3 2.7-6 6-6s6 2.7 6 6" />
            @break
        @case('horn')
            <path d="M6 10h3l4-3v10l-4-3H6z" /><path d="M17 9a3 3 0 0 1 0 6" />
            @break
        @case('radar')
            <path d="M12 20a8 8 0 0 0 0-16" /><path d="M12 16a4 4 0 0 0 0-8" /><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none" />
            @break
        @case('key-remote')
            <rect x="5" y="8" width="14" height="10" rx="2" /><circle cx="9" cy="13" r="1" fill="currentColor" stroke="none" /><circle cx="12" cy="13" r="1" fill="currentColor" stroke="none" /><circle cx="15" cy="13" r="1" fill="currentColor" stroke="none" /><path d="M12 4v4" />
            @break
        @case('starter')
            <circle cx="12" cy="12" r="7" /><path d="M10 8v8l6-4-6-4z" fill="currentColor" stroke="none" />
            @break
        @case('alternator')
            <circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /><path d="M12 6v2M12 16v2M6 12h2M16 12h2" />
            @break
        @case('battery')
            <rect x="4" y="7" width="16" height="10" rx="2" /><path d="M8 7V5M16 7V5" /><path d="M8 12h8" />
            @break
        @case('abs-unit')
            <rect x="5" y="6" width="14" height="12" rx="2" /><path d="M8 10h8M8 14h5" /><path d="M15 14h1" />
            @break
        @case('airbag')
            <circle cx="12" cy="12" r="7" /><path d="M8 12c1.5-2 6.5-2 8 0M9 15c1-1 5-1 6 0" />
            @break
        @case('brake-pad')
            <path d="M6 8h12v8H6z" /><path d="M9 8V6h6v2" /><path d="M8 12h8" />
            @break
        @case('brake-caliper')
            <path d="M7 9h10v6H7z" /><path d="M10 9V7h4v2" /><circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
            @break
        @case('brake-system')
            <circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /><path d="M12 6v2M12 16v2" /><path d="M8 10l-2-2M16 14l2 2" />
            @break
        @case('brake-disc')
            <circle cx="12" cy="12" r="7" /><circle cx="12" cy="12" r="3" /><path d="M12 5v2M12 17v2M5 12h2M17 12h2" />
            @break
        @case('shock-absorber')
            <path d="M10 4v16M14 4v16" /><path d="M8 8h8M8 12h8M8 16h8" />
            @break
        @case('control-arm')
            <path d="M4 16 12 8l8 8" /><circle cx="4" cy="16" r="2" /><circle cx="12" cy="8" r="2" /><circle cx="20" cy="16" r="2" />
            @break
        @case('ball-joint')
            <circle cx="8" cy="16" r="3" /><circle cx="16" cy="8" r="3" /><path d="M10.5 13.5 13.5 10.5" />
            @break
        @case('spring')
            <path d="M8 4v16M16 4v16" /><path d="M8 6h8M8 10h8M8 14h8M8 18h8" />
            @break
        @case('wheel-hub')
            <circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /><path d="M12 6v2M12 16v2M6 12h2M16 12h2" />
            @break
        @case('axle')
            <path d="M3 12h18" /><circle cx="6" cy="12" r="2" /><circle cx="18" cy="12" r="2" />
            @break
        @case('bearing')
            <circle cx="12" cy="12" r="7" /><circle cx="12" cy="12" r="3" /><path d="M12 5v2M12 17v2M5 12h2M17 12h2" />
            @break
        @case('bushing')
            <circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2.5" />
            @break
        @case('engine-mount')
            <path d="M8 6h8v4H8z" /><path d="M10 10v8M14 10v8" /><path d="M7 18h10" />
            @break
        @case('engine-internal')
            <circle cx="12" cy="12" r="6" /><path d="M9 9h6v6H9z" /><path d="M12 6v3M12 15v3" />
            @break
        @case('engine-block')
            <path d="M7 6h10l2 4v8H5v-8l2-4z" /><path d="M9 10h6M9 14h6" />
            @break
        @case('timing-belt')
            <circle cx="8" cy="8" r="3" /><circle cx="16" cy="16" r="3" /><path d="M10.5 10.5 13.5 13.5" />
            @break
        @case('belt')
            <ellipse cx="12" cy="12" rx="7" ry="4" /><path d="M5 12h14" />
            @break
        @case('water-pump')
            <circle cx="12" cy="12" r="5" /><path d="M12 7v10M7 12h10" /><path d="M9 9l6 6M15 9l-6 6" />
            @break
        @case('oil')
            <path d="M12 3c-3 4-6 6-6 9a6 6 0 0 0 12 0c0-3-3-5-6-9z" />
            @break
        @case('filter')
            <path d="M4 5h16l-6 7v6l-4 2v-8L4 5z" />
            @break
        @case('radiator')
            <rect x="6" y="5" width="12" height="14" rx="1" /><path d="M9 8v10M12 8v10M15 8v10" />
            @break
        @case('ac')
            <circle cx="12" cy="12" r="6" /><path d="M8 8l8 8M16 8l-8 8" /><path d="M12 6v12M6 12h12" />
            @break
        @case('fan')
            <circle cx="12" cy="12" r="2" /><path d="M12 4v3M12 17v3M4 12h3M17 12h3M6.3 6.3l2.1 2.1M15.6 15.6l2.1 2.1M17.7 6.3l-2.1 2.1M8.4 15.6l-2.1 2.1" />
            @break
        @case('gearbox')
            <circle cx="8" cy="12" r="3" /><circle cx="16" cy="12" r="3" /><path d="M11 12h2" />
            @break
        @case('clutch')
            <circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /><path d="M12 6v2M12 16v2" />
            @break
        @case('driveshaft')
            <path d="M4 12h16" /><circle cx="6" cy="12" r="2" /><circle cx="18" cy="12" r="2" /><path d="M10 10l4 4M14 10l-4 4" />
            @break
        @case('differential')
            <rect x="6" y="8" width="12" height="8" rx="2" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" />
            @break
        @case('exhaust')
            <path d="M6 8c0 0 2 1 6 1s6-1 6-1v8c0 0-2 1-6 1s-6-1-6-1V8z" /><path d="M6 12H3M18 12h3" />
            @break
        @case('bumper')
            <path d="M4 14h16v3H4z" /><path d="M7 14V9h10v5" /><circle cx="8" cy="17.5" r="1" /><circle cx="16" cy="17.5" r="1" />
            @break
        @case('fender')
            <path d="M6 18c4-8 8-10 12-12" /><path d="M6 18h4M14 6v4" />
            @break
        @case('door')
            <rect x="7" y="4" width="10" height="16" rx="1" /><circle cx="14" cy="12" r="1" fill="currentColor" stroke="none" />
            @break
        @case('hood-trunk')
            <path d="M5 14h14l-2-6H7l-2 6z" /><path d="M8 14v4h8v-4" />
            @break
        @case('mirror')
            <path d="M6 8h8a3 3 0 0 1 3 3v5H6V8z" /><path d="M17 11h2v4h-2" />
            @break
        @case('glass')
            <rect x="5" y="6" width="14" height="12" rx="1" /><path d="M5 10h14" />
            @break
        @case('sunroof')
            <rect x="5" y="8" width="14" height="8" rx="1" /><path d="M8 5h8" /><path d="M12 5v3" />
            @break
        @case('wiper')
            <path d="M5 17c4-6 10-8 14-10" /><path d="M16 7l3-2" /><path d="M5 17h3" />
            @break
        @case('light')
            <path d="M8 10h8l-1 8H9l-1-8z" /><path d="M10 6h4v4h-4z" /><path d="M11 18h2" />
            @break
        @case('wheel')
            <circle cx="12" cy="12" r="7" /><circle cx="12" cy="12" r="2.5" /><path d="M12 5v2M12 17v2M5 12h2M17 12h2" />
            @break
        @case('tire')
            <circle cx="12" cy="12" r="7" /><path d="M8.5 8.5c3 1 4 6 0 7M15.5 8.5c-3 1-4 6 0 7" />
            @break
        @case('steering')
            <circle cx="12" cy="12" r="6" /><circle cx="12" cy="12" r="2" /><path d="M12 6v2M12 16v2M6 12h2M16 12h2" />
            @break
        @case('hydraulic')
            <path d="M8 6h8v12H8z" /><path d="M10 10h4M10 14h4" /><path d="M6 12h2M16 12h-2" />
            @break
        @case('seat')
            <path d="M7 10h10v8H7z" /><path d="M9 10V7h6v3" /><path d="M7 14h10" />
            @break
        @case('interior')
            <path d="M5 16h14V9l-7-4-7 4v7z" /><path d="M9 16v3M15 16v3" />
            @break
        @case('audio')
            <rect x="5" y="7" width="14" height="10" rx="2" /><circle cx="9" cy="12" r="2" /><path d="M13 10h4M13 14h4" />
            @break
        @case('fuel')
            <path d="M8 6h6v12H8z" /><path d="M14 9h2v6h-2" /><path d="M10 4h2v2h-2z" />
            @break
        @case('gasket-seal')
            <circle cx="12" cy="12" r="6" /><path d="M8 12h8M12 8v8" stroke-dasharray="2 2" />
            @break
        @case('hose')
            <path d="M5 8c4 0 4 8 8 8s4-8 8-8" />
            @break
        @case('pump')
            <circle cx="10" cy="12" r="4" /><path d="M14 12h6M17 10v4" />
            @break
        @case('bracket')
            <path d="M6 6h6v6H6z" /><path d="M12 12h6v6h-6z" />
            @break
        @case('motor')
            <circle cx="12" cy="12" r="5" /><path d="M12 7v10M7 12h10" />
            @break
        @case('suspension-link')
            <path d="M6 18 12 6l6 12" /><circle cx="6" cy="18" r="1.5" fill="currentColor" stroke="none" /><circle cx="18" cy="18" r="1.5" fill="currentColor" stroke="none" />
            @break
        @case('body')
            <path d="M5 15h14l-1.5-4.5a1 1 0 0 0-.95-.7H7.45a1 1 0 0 0-.95.7L5 15z" /><circle cx="8" cy="16.5" r="1.25" /><circle cx="16" cy="16.5" r="1.25" />
            @break
        @case('electric')
            <path d="M13 3 7 13h5l-1 8 8-12h-6l0-6Z" />
            @break
        @default
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z" />
    @endswitch
</svg>
