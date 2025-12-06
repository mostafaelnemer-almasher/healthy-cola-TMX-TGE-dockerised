@extends('layouts.user')
@section('title', __('KYC Details'))

@section('content')
@include('layouts.messages')
<div class="content-area card">
        <div class="card-innr">
            <div class="card-head d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{__('Your KYC Document')}}</h4>
                    <div class="d-flex align-items-center guttar-20px">
                        <div class="flex-col d-sm-block d-none">
                            <a href="{{ URL::previous() }}" class="btn btn-light btn-sm btn-auto btn-primary"><em class="fas fa-arrow-left mr-3"></em>Back</a>
                        </div>
                        <div class="flex-col d-sm-none">
                            <a href="{{ URL::previous() }}" class="btn btn-light btn-icon btn-sm btn-primary"><em class="fas fa-arrow-left"></em></a>
                        </div>
                    </div>
                </div>
                <div class="gaps-1-5x"></div>
                <h6 class="card-sub-title">{{__('Personal Information')}}</h6>
                <ul class="data-details-list">
                    <li>
                        <div class="data-details-head">{{__('First Name')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->firstName) ? $kyc->firstName : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Last Name')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->lastName) ? $kyc->lastName : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Email Address')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->email) ? $kyc->email : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Phone Number')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->phone) ? $kyc->phone : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Date of Birth')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->dob) ? _date2dob($kyc->dob) : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Full Address')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->address1) ? $kyc->address1 . ',' : '&nbsp;' !!} {!! !empty($kyc->address2) ? $kyc->address2 . ',' : '&nbsp;' !!} {!! !empty($kyc->city) ? $kyc->city . ',' : '&nbsp;' !!} {!! !empty($kyc->state) ? $kyc->state . ',' : '&nbsp;' !!} {!! !empty($kyc->zip) ? $kyc->zip . '.' : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Country of Residence')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->country) ? $kyc->country : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                     <li>
                        <div class="data-details-head">{{__('Wallet Type')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->walletName) ? $kyc->walletName : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                     <li>
                        <div class="data-details-head">{{__('Wallet Address')}}</div>
                        <div class="data-details-des">{!! !empty($kyc->walletAddress) ? $kyc->walletAddress : '&nbsp;' !!}</div>
                    </li>{{-- li --}}
                    <li>
                        <div class="data-details-head">{{__('Telegram Username')}}</div>
                        <div class="data-details-des"><span{{ '@'.preg_replace('/@/', '', $kyc->telegram, 1) }} </span><a href="https://t.me/{{preg_replace('/@/', '', $kyc->telegram, 1)}}" target="_blank"><em class="far fa-paper-plane"></em></a></div>
                    </li>{{-- li --}}
                </ul>
                <div class="gaps-3x"></div>
                <h6 class="card-sub-title">{{__('Uploaded Documnets')}}</h6>
                <ul class="data-details-list">
                    <li>
                        <div class="data-details-head">
                            @if($kyc->documentType == 'nidcard')
                            {{__('National ID Card')}}
                            @elseif($kyc->documentType == 'passport')
                            {{__('Passport')}}
                            @elseif($kyc->documentType == 'license')
                            {{__('Driving License')}}
                            @else
                            Documents
                            @endif
                        </div>
                        @if($kyc->document != NULL)
                        <ul class="data-details-docs">
                            @if($kyc->document != NULL)
                            <li>
                                <span class="data-details-docs-title">{{ $kyc->documentType == 'nidcard' ? __('Front Side') : __('Document') }}</span>
                                <div class="data-doc-item data-doc-item-lg">
                                    <div class="data-doc-image">
                                        @if(pathinfo(storage_path('app/'.$kyc->document), PATHINFO_EXTENSION) == 'pdf')
                                        <em class="kyc-file fas fa-file-pdf"></em>
                                        @else
                                        <img src="{{ route('user.kycs.file', ['file'=>$kyc->id, 'doc'=>1]) }}" src="">
                                        @endif
                                    </div>
                                    <ul class="data-doc-actions">
                                        <li><a href="{{ route('user.kycs.file', ['file'=>$kyc->id, 'doc'=>1]) }}" target="_blank" ><em class="ti ti-import"></em></a></li>
                                    </ul>
                                </div>
                            </li>{{-- li --}}
                            @endif
                            @if($kyc->document2 != NULL)
                            <li>
                                <span class="data-details-docs-title">{{ $kyc->documentType == 'nidcard' ? __('Back Side') : __('Proof') }}</span>
                                <div class="data-doc-item data-doc-item-lg">
                                    <div class="data-doc-image">
                                        @if(pathinfo(storage_path('app/'.$kyc->document2), PATHINFO_EXTENSION) == 'pdf')
                                        <em class="kyc-file fas fa-file-pdf"></em>
                                        @else
                                        <img src="{{ route('user.kycs.file', ['file'=>$kyc->id, 'doc'=>2]) }}" src="">
                                        @endif
                                    </div>
                                    <ul class="data-doc-actions">
                                        <li><a href="{{ route('user.kycs.file', ['file'=>$kyc->id, 'doc'=>2]) }}" target="_blank"><em class="ti ti-import"></em></a></li>
                                    </ul>
                                </div>
                            </li>{{-- li --}}
                            @endif

                            @if($kyc->document3 != NULL)
                            <li>
                                <span class="data-details-docs-title">{{__('Proof')}}</span>
                                <div class="data-doc-item data-doc-item-lg">
                                    <div class="data-doc-image">
                                        @if(pathinfo(storage_path('app/'.$kyc->document3), PATHINFO_EXTENSION) == 'pdf')
                                        <em class="kyc-file fas fa-file-pdf"></em>
                                        @else
                                        <img src="{{ route('user.kycs.file', ['file'=>$kyc->id, 'doc'=>3]) }}" src="">
                                        @endif
                                    </div>
                                    <ul class="data-doc-actions">
                                        <li><a href="{{ route('user.kycs.file', ['file'=>$kyc->id, 'doc'=>3]) }}" target="_blank"><em class="ti ti-import"></em></a></li>
                                    </ul>
                                </div>
                            </li>{{-- li --}}
                            @endif
                        </ul>
                        @else
                        {{__('No document uploaded.')}}
                        @endif
                    </li>{{-- li --}}
                </ul>
                <div class="gaps-3x"></div>
                <div class="data-details d-md-flex flex-wrap align-items-center justify-content-between">
                    <div class="fake-class">
                        <span class="data-details-title">{{__('Submited On')}}</span>
                        <span class="data-details-info">{{ _date($kyc->created_at) }}</span>
                    </div>

                    @if($kyc->reviewedBy != 0)
                    <div class="fake-class">
                        <span class="data-details-title">{{__('Last Checked')}}</span>
                        <span class="data-details-info">{{ _date($kyc->updated_at) }}</span>
                    </div>
                    @else
                    <div class="fake-class">
                        <span class="data-details-title">{{__('Checked On')}}</span>
                        <span class="data-details-info">{{__('Not reviewed yet')}}</span>
                    </div> 
                    @endif
                    
                    @if($kyc->status !== NULL)                   
                    <div class="fake-class">
                        <span class="data-details-title">{{__('KYC Status')}}</span>                
                        <span class="text-{{ __status($kyc->status,'status')}} ucap">{{ __(__status($kyc->status,'text')) }}</span>
                    </div>
                    @endif
                    @if($kyc->notes !== NULL)
                    <div class="gaps-2x w-100 d-none d-md-block"></div>
                    <div class="w-100">
                        <span class="data-details-title">{{__('Admin Note')}}</span>
                        <span class="data-details-info">{!! $kyc->notes !!}</span>
                    </div>
                    @endif
                </div>
            </div>{{-- .card-innr --}}
        </div>{{-- .card --}}
    </div>{{-- .container --}}
</div>{{-- .page-content --}}
@endsection
