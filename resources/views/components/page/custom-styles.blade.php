@props(['page'])

@php
    $rawCss = $page->getAttribute('custom_css');
    $pageCss = app(\App\Services\PageCss::class)->forPage(
        is_string($rawCss) ? $rawCss : null,
        (int) $page->getKey(),
    );
@endphp

@if ($pageCss !== '')
    <style data-page-custom-css="{{ $page->getKey() }}">{!! $pageCss !!}</style>
@endif
