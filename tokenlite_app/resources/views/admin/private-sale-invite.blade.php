@extends('layouts.admin')
@section('title', ' Private Sale Invitation')

@section('content')
    <div class="container">
        @include('layouts.messages')
        @include('vendor.notice')
        <div class="card content-area content-area-mh">
            <div class="card-innr">
                <div class="card-head has-aside">
                    <h4 class="card-title"> Private Sale Invitation</h4>
                    <div class="card-opt">
                        <ul class="btn-grp btn-grp-block guttar-20px">
                            <li>
                                <a href="javascript:void(0)" data-type="invitation_update" class="btn btn-auto btn-sm btn-primary private_inv_update">
                                    <em class="fas fa-plus-circle"></em><span> Create</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" data-type="invitation_settings" class="btn btn-auto btn-sm btn-primary private_inv_update">
                                    <em class="ti ti-settings"></em><span> Settings</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="gaps-1x"></div>
                <table class="data-table dt-filter-init">
                    <thead>
                        <tr class="data-item data-head">
                            <th class="data-col dt-label">{{ __('Label') }}</th>
                            <th class="data-col dt-code text-no-wrap" width="270px">{{ __('Invitation Code') }}</th>
                            <th class="data-col dt-visit dt-xs">{{ __('Visit') }}</th>
                            <th class="data-col dt-signup dt-mb">{{ __('Signup') }}</th>
                            <th class="data-col dt-start-date dt-xl">{{ __('Start Date') }}</th>
                            <th class="data-col dt-end-date dt-lg">{{ __('End Date') }}</th>
                            <th class="data-col invite-status dt-mb">{{ __('Status') }}</th>
                            <th class="data-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($privateInvites as $privateInvite)
                        <tr class="data-item data-item-row invite-item-{{ $privateInvite->id }}">

                            <td class="data-col">
                                    <span class="d-inline-block text-truncate" style="max-width: 120px;"><span>{{ $privateInvite->label }}</span></span>
                                </div>
                            </td>
                            <td class="data-col dt-code-cell">
                                <div class="copy-wrap">
                                    <span class="copy-feedback"></span>
                                    <input type="text" class="copy-address" value="{{ $privateInvite->code }}" disabled/>
                                    <button class="copy-trigger copy-clipboard" data-copy-success="Copied" data-copy-failed="Failed" data-clipboard-text="{{ route('register').'?invite='.$privateInvite->code }}"><em class="ti ti-files inv-copy-icon"></em></button>
                                </div>
                            </td>
                            <td class="data-col dt-xs">{{ $privateInvite->visit }}</td>
                            <td class="data-col dt-mb">{{ $privateInvite->signup }}</td>
                            <td class="data-col text-nowrap dt-xl">{{ _date($privateInvite->start_date, null, true) ?? '-' }}</td>
                            <td class="data-col text-nowrap dt-lg">{{ _date($privateInvite->end_date, null, true) ?? '-' }}</td>
                            <td class="data-col invite-status dt-mb">
                                <span class="invite-status-md badge badge-outline badge-md badge-{{ __status($privateInvite->status,'status') }}">{{ ucfirst($privateInvite->status) }}</span>
                            </td>                        
                            <td class="data-col text-right">
                                <div class="relative d-inline-block">
                                    <a href="#" class="btn btn-light-alt btn-xs btn-icon toggle-tigger"><em class="ti ti-more-alt"></em></a>
                                    <div class="toggle-class dropdown-content dropdown-content-top-left">
                                        <ul class="dropdown-list more-menu more-menu-{{$privateInvite->id}}">
                                            <li><a href="javascript:void(0)" data-id="{{ $privateInvite->id }}" data-type="invitation_update" class="private_inv_update"><em class="far fa-edit"></em>Edit</a></li>
                                            @if($privateInvite->status == 'active')
                                                <li><a href="javascript:void(0)" class="private_inv_status" data-id="{{ $privateInvite->id }}" data-current="inactive"><em class="fas fa-ban"></em>Inactive</a></li>
                                            @endif
                                            @if($privateInvite->status == 'inactive')
                                                <li><a href="javascript:void(0)" class="private_inv_status" data-id="{{ $privateInvite->id }}" data-current="active"><em class="fas fa-check"></em>Active</a></li>
                                            @endif
                                            <li><a href="javascript:void(0)" class="private_inv_status invite_delete" data-id="{{ $privateInvite->id }}" data-current="delete"><em class="fas fa-trash-alt"></em>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <style>
    .copy-trigger {
        top: 0px;
        padding: 5px 0;
    }
    .copy-address {
        padding: 10px 50px 10px 5px ;
        border: 0 none;
    }
    .dt-filter-init .dt-code-cell{
        padding-right: 40px;
    }
    .copy-feedback {
        border: 0 none;
        text-align: left;
    }
    </style>
@endsection