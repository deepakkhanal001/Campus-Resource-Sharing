@props(['bytes' => 0])

@php
    $size = max(0, (int) $bytes);
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = $size > 0 ? min((int) floor(log($size, 1024)), count($units) - 1) : 0;
    $value = $size / (1024 ** $power);
    $formatted = $power === 0 ? number_format($value) : rtrim(rtrim(number_format($value, 1), '0'), '.');
@endphp

{{ $formatted }} {{ $units[$power] }}
