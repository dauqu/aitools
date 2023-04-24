@extends('user.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="container-xl">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('AI Images') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">

            {{-- Failed --}}
            @if (Session::has("failed"))
            <div class="alert alert-important alert-danger alert-dismissible" role="alert">
                <div class="d-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="alert-icon icon icon-tabler icon-tabler-alert-circle"
                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <circle cx="12" cy="12" r="9"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        {{Session::get('failed')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            {{-- Success --}}
            @if(Session::has("success"))
            <div class="alert alert-important alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <svg xmlns="http://www.w3.org/2000/svg" class="alert-icon icon icon-tabler icon-tabler-check"
                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M5 12l5 5l10 -10"></path>
                    </svg>
                    <div>
                        {{Session::get('success')}}
                    </div>
                </div>
                <a class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
            @endif

            <div class="row row-deck row-cards">
                {{-- Parameters --}}
                <div class="col-xl-12">
                    <form action="javascript:void(0)" id="saveForm" method="POST" class="card">
                        @csrf
                        <div class="card-body">
                            <div class="row row-cards">
                                {{-- Image you want to create?? --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Image you want to create?')
                                            }}</label>
                                        <input type="text" class="form-control text-capitalize" name="name" id="name"
                                            placeholder="{{ __('Eg: Dog')}}" maxlength="110" required>
                                    </div>
                                </div>
                                {{-- Size --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Size')
                                            }}</label>
                                        <select class="form-control form-select" name="size" id="size" required>
                                            <option disabled selected>{{ __('Size') }}</option>
                                            <option value="256x256" selected>{{ __('256x256') }}</option>
                                            <option value="512x512">{{ __('512x512') }}</option>
                                            <option value="1024x1024">{{ __('1024x1024') }}</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- Styles --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('Styles') }}</label>
                                        <select class="form-select tomselected" name="style" id="style" value="" required>
                                            {{-- Styles --}}
                                            @include('user.pages.ai-images.includes.styles')
                                        </select>
                                    </div>
                                </div>
                                {{-- No. Of Results --}}
                                <div class="col-sm-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">{{ __('No. Of Results')
                                            }}</label>
                                        <select class="form-control form-select" name="results" id="results" required>
                                            <option disabled selected>{{ __('Results') }}</option>
                                            {{-- Images options --}}
                                            @for ($i = 1; $i <= $config[42]->config_value; $i++)
                                                <option value="{{ $i }}" {{ $i == 1 ? 'selected' : ''}}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" id="submit" class="btn btn-primary">{{
                                __('Generate')}}</button>
                        </div>
                    </form>
                </div>

                {{-- Result data --}}
                <div class="col-xl-12 px-2 d-none" id="response">
                    <div class="row row-cards">
                        <!-- Result -->
                        <div class="col-lg-12 my-4">
                            <h2 class="page-title">
                                {{ __('Results') }}
                            </h2>
                        </div>
                        {{-- Photo --}}
                        <div id="result" class="row">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Footer --}}
@include('user.includes.footer')
</div>

{{-- Custom JS --}}
@section('custom-js')
<script src="{{ asset('js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('js/tom-select.base.min.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        "use strict";
    	var el;
        // Style
    	window.TomSelect && (new TomSelect(el = document.getElementById('style'), {
    		copyClassesToDropdown: false,
    		dropdownClass: 'dropdown-menu ts-dropdown',
    		optionClass:'dropdown-item',
    		controlInput: '<input>',
    		render:{
    			item: function(data,escape) {
    				if( data.customProperties ){
    					return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
    				}
    				return '<div>' + escape(data.text) + '</div>';
    			},
    			option: function(data,escape){
    				if( data.customProperties ){
    					return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
    				}
    				return '<div>' + escape(data.text) + '</div>';
    			},
    		},
    	}));
    });
</script>
<script>
    // Fill
    (function($) { 
        "use strict";
        $("#saveForm").validate({
            submitHandler: function(form) 
            {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('#submit').html(`{{ __('Please Wait...') }}`);
                // $("#submit"). attr("disabled", true);
                $.ajax({
                    url: "{{ route('user.generate.ai.image') }}",
                    type: "POST",
                    data: $('#saveForm').serialize(),
                    success: function(response) {
                        // Check result
                        if (response[0] != null) {
                            // Remove attribute
                            $('#submit').html(`{{ __('Generate') }}`);
                            $("#submit").attr("disabled", false);
                            // Result
                            var html = "";
                            $.each(response, function (i) {
                                $.each(response[i], function (key, val) {
                                    var number = 1 + Math.floor(Math.random() * 9999999999);
                                    html += '<div class="col-lg-3"><div class="img-responsive img-responsive-1x1 rounded-3 border mb-3 result" style="background-image: url(data:image/png;base64,'+val+')"></div><a href="data:image/png;base64,'+val+'" class="btn btn-dark" download="'+number+'.png"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo-down" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M15 8h.01"></path><path d="M12 20h-5a3 3 0 0 1 -3 -3v-10a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v5"></path><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l4 4"></path><path d="M14 14l1 -1c.617 -.593 1.328 -.793 2.009 -.598"></path><path d="M19 16v6"></path><path d="M22 19l-3 3l-3 -3"></path></svg>{{ __("Download") }}</a></div>';
                                });
                            });
                            $('#response').removeClass('d-none');
                            $('#result').html(html)
                        } else {
                            Swal.fire(
                                `{{ __('Image Creation Failed') }}`,
                                `{{ __('For more details please contact administrator.') }}`,
                                'error'
                            );
                            $('#submit').html(`{{ __('Generate') }}`);
                            $("#submit").attr("disabled", false);
                        }
                    }
                });
            }
        })
    })(jQuery);
</script>
@endsection
@endsection