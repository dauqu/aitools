@extends('layouts.guest')

@section('content')

{{-- Custom JS --}}
@section('custom-css')
{{-- AdSense status --}}
@if ($setting->adsense_code != "DISABLE")
@if ($setting->adsense_code != "")
{{-- AdSense code --}}
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $setting->adsense_code }}"
    crossorigin="anonymous"></script>
@endif
@endif
@endsection

{{-- Topbar --}}
@include('website.includes.topbar')

{{-- About us --}}
<section class="pt-24 bg-white" style="background-image: url({{ asset('images/web/elements/pattern-white.svg') }}); background-position: center;">
    <div class="container px-4 mx-auto" data-aos="fade-up">
        {{-- Page content --}}
        @if (!empty($page->body))
        {!! __($page->body) !!}
        @endif
    </div>
</section>

{{-- Footer --}}
@include('website.includes.footer')
@endsection