@extends('admin.layouts.app')

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
                        {{ __('Users') }}
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
                <div class="col-sm-12 col-lg-12">
                    <div class="card">
                        <div class="table-responsive px-2 py-2">
                            <table class="table table-vcenter card-table" id="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('S.No') }}</th>
                                        <th>{{ __('Full Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Current Plan') }}</th>
                                        <th>{{ __('Joined') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="w-1">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- User count --}}
                                    @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td><a href="{{ route('admin.view.user', $user->id)}}">{{ $user->name }}</a>
                                        </td>
                                        <td class="text-muted">
                                            {{ $user->email }}
                                        </td>
                                        <td class="text-muted text-capitalize">
                                            <?php $plan_data = json_decode($user->plan_details,TRUE); ?>
                                            @if ($plan_data == null)
                                            {{ __('No Plan') }}
                                            @else
                                            {{ __($plan_data['name']) }}
                                            <span>
                                                @if ($plan_data['price'] == '0')
                                                ({{ __('Free') }})
                                                @else
                                                ({{ $config[1]->config_value}}
                                                {{ $plan_data['price'] }})
                                                @endif
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ date('d-m-Y h:m A', strtotime($user->created_at)) }}
                                        </td>
                                        <td class="text-muted">
                                            @if ($user->status == 0)
                                            <span class="badge bg-red">{{ __('Inactive') }}</span>
                                            @else
                                            <span class="badge bg-green">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a class="btn btn-primary btn-sm"
                                                    href="{{ route('admin.edit.user', $user->id)}}">{{ __('Edit')
                                                    }}</a>
                                                <a class="btn btn-primary btn-sm"
                                                    href="{{ route('admin.change.user.plan', $user->id)}}">{{
                                                    __('Change Plan') }}</a>
                                                @if ($user->status == 0)
                                                <a href="#" class="btn btn-primary btn-sm"
                                                    onclick="getUser('{{ $user->id }}'); return false;">{{
                                                    __('Activate') }}</a>
                                                @else
                                                <a href="#" class="btn btn-primary btn-sm"
                                                    onclick="getUser('{{ $user->id }}'); return false;">{{
                                                    __('Deactivate') }}</a>
                                                @endif
                                                <a href="#" class="btn btn-primary btn-sm"
                                                    onclick="deleteUser('{{ $user->id }}'); return false;">{{
                                                    __('Delete') }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    @include('admin.includes.footer')
</div>

{{-- Active/deactive user modal --}}
<div class="modal modal-blur fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-title">{{ __('Are you sure?')}}</div>
                <div>{{ __('If you proceed, you will active/deactivate this user data.')}}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{
                    __('Cancel')}}</button>
                <a class="btn btn-danger" id="user_id">{{ __('Yes, proceed')}}</a>
            </div>
        </div>
    </div>
</div>

{{-- User delete modal --}}
<div class="modal modal-blur fade" id="delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="modal-title text-danger text-capitalize">{{ __('WARNING!')}}</div>
                <div>{{ __('This action will remove user account and user data. It is not revertable action.')}}</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{
                    __('Cancel')}}</button>
                <a class="btn btn-danger" id="deleted_user_id">{{ __('Yes, proceed')}}</a>
            </div>
        </div>
    </div>
</div>

{{-- Custom JS --}}
@section('custom-js')
<script>
    function getUser(parameter) {
    "use strict";
    $("#status-modal").modal("show");
    var link = document.getElementById("user_id");
    link.getAttribute("href");
    link.setAttribute("href", "/admin/update-status?id=" + parameter);
}

function deleteUser(parameter) {
    "use strict";
    $("#delete-modal").modal("show");
    var link = document.getElementById("deleted_user_id");
    link.getAttribute("href");
    link.setAttribute("href", "/admin/delete-user?id=" + parameter);
}
</script>
@endsection
@endsection