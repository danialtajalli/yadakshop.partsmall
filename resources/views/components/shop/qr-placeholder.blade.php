@props([
    'seed',
    'size' => 120,
    'class' => '',
])

@php
    $gridSize = 25;
    $moduleSize = $size / ($gridSize + 2);
    $hash = md5((string) $seed);

    $isFinder = function (int $x, int $y) use ($gridSize): bool {
        $inTopLeft = $x <= 6 && $y <= 6;
        $inTopRight = $x >= $gridSize - 7 && $y <= 6;
        $inBottomLeft = $x <= 6 && $y >= $gridSize - 7;

        return $inTopLeft || $inTopRight || $inBottomLeft;
    };

    $finderFill = function (int $x, int $y) use ($gridSize): bool {
        $regions = [
            [0, 0], [$gridSize - 7, 0], [0, $gridSize - 7],
        ];

        foreach ($regions as [$ox, $oy]) {
            $lx = $x - $ox;
            $ly = $y - $oy;

            if ($lx < 0 || $ly < 0 || $lx > 6 || $ly > 6) {
                continue;
            }

            $outer = $lx === 0 || $ly === 0 || $lx === 6 || $ly === 6;
            $inner = $lx >= 2 && $lx <= 4 && $ly >= 2 && $ly <= 4;

            if ($outer || $inner) {
                return true;
            }
        }

        return false;
    };

    $isModuleFilled = function (int $x, int $y) use ($hash, $gridSize, $isFinder, $finderFill): bool {
        if ($isFinder($x, $y)) {
            return $finderFill($x, $y);
        }

        $index = ($y * $gridSize + $x) % 32;
        $byte = hexdec(substr($hash, $index, 1));

        return ($byte % 2) === 1;
    };
@endphp

<svg
    {{ $attributes->merge(['class' => $class]) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 {{ $size }} {{ $size }}"
    role="img"
    aria-hidden="true"
>
    <rect width="{{ $size }}" height="{{ $size }}" fill="#ffffff" />
    @for ($y = 0; $y < $gridSize; $y++)
        @for ($x = 0; $x < $gridSize; $x++)
            @if ($isModuleFilled($x, $y))
                <rect
                    x="{{ ($x + 1) * $moduleSize }}"
                    y="{{ ($y + 1) * $moduleSize }}"
                    width="{{ $moduleSize }}"
                    height="{{ $moduleSize }}"
                    fill="#0f172a"
                />
            @endif
        @endfor
    @endfor
</svg>
