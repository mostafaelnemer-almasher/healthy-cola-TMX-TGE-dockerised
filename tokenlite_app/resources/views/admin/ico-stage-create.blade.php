@extends('layouts.admin')
@section('title', 'ICO/STO Stage')

@section('content')
<div class="page-content">
    <div class="container">
        <div class="card content-area">
            <div class="card-innr">
                {{-- Title & Back --}}
                <div class="card-head d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">ICO/STO Stage Create</h4>
                    <div class="d-flex guttar-15px">
                        <div class="fake-class">
                            <a href="{{route('admin.stages')}}" class="btn btn-sm btn-auto btn-primary d-sm-inline-block d-none"><em class="fas fa-arrow-left"></em><span>Back</span></a>
                            <a href="{{route('admin.stages')}}" class="btn btn-icon btn-sm btn-primary d-sm-none"><em class="fas fa-arrow-left"></em></a>
                        </div>
                    </div>
                </div>
                {{-- Title & Back End --}}
                <div class="gaps-1x"></div>
                <hr>        
                {{-- Stage Details Start --}}
                <div class="tab-content" id="ico-details">
                    <div class="tab-pane fade show active" id="ico_stage_create">
                        <form action="{{ route('admin.ajax.stages.save') }}" class=" _reload validate-modern" method="POST" id="ico_stage_create" autocomplete="off">
                            @csrf    
                            <div id="stageDetails" class="wide-max-md">
                                <div class="input-item input-with-label">
                                    <label class="input-item-label">Stage Title/Name</label>
                                   <div class="input-wrap">
                                        <input class="input-bordered" type="text" name="name" placeholder="Enter stage name" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-item input-with-label">
                                            <label class="input-item-label">Total Token Issues</label>
                                           <div class="input-wrap">
                                            <input class="input-bordered" type="number" min="1" name="total_tokens" placeholder="0" required>
                                            </div>
                                            <span class="input-note">Define how many tokens available for sale on stage.</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-item input-with-label">
                                            <label class="input-item-label">Base Token Price</label>
                                            <div class="input-wrap">
                                                <input class="input-bordered" type="number" min="0" name="base_price" placeholder="0" required>
                                            </div>
                                            <span class="input-note">Define your token rate. Usually <strong>0.015 {{ base_currency(true) }}</strong> per token. <em class="ti ti-help-alt" title="Support up-to 8 decimals but recommended to use up-to 6 decimals." data-toggle="tooltip"></em></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-item input-with-label">
                                            <label class="input-item-label">Min and Max Per Transaction</label>
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <div class="relative">
                                                       <div class="input-wrap">
                                                            <input class="input-bordered" type="number" placeholder="0" name="min_purchase" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="relative">
                                                       <div class="input-wrap">
                                                            <input class="input-bordered" type="number" placeholder="0" name="max_purchase" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <span class="input-note">Purchase min or max amount of token per tranx.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-sm">
                                                <div class="input-item input-with-label">
                                                    <label class="input-item-label">Soft Cap</label>
                                                    <div class="input-wrap">
                                                        <input class="input-bordered" type="number" placeholder="0" name="soft_cap">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm">
                                                <div class="input-item input-with-label">
                                                    <label class="input-item-label">Hard Cap</label>
                                                    <div class="input-wrap">
                                                        <input class="input-bordered" type="number" placeholder="0" name="hard_cap">
                                                    </div>
                                                </div>
                                            </div>        
                                        </div>
                                    </div>           
                                    <div class="col-12 mb-4 mt-1">
                                        <div class="sap"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-item input-with-label">
                                            <label class="input-item-label">Start Date</label>
                                            <div class="row guttar-15px align-items-center">
                                                <div class="col-7 col-sm-7">
                                                    <div class="input-wrap">
                                                        <input class="input-bordered date-picker" type="text" name="start_date" required>
                                                        <span class="input-icon input-icon-right date-picker-icon"><em class="ti ti-calendar"></em></span>
                                                    </div>
                                                </div>
                                                <div class="col-5 col-sm-5">
                                                    <div class="input-wrap">
                                                        <input class="input-bordered time-picker" type="text" name="start_time">
                                                        <span class="input-icon input-icon-right time-picker-icon"><em class="ti ti-alarm-clock"></em></span>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <span class="input-note">Start date/time for sale. Can't purchase before time.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-item input-with-label">
                                            <label class="input-item-label">End Date</label>
                                            <div class="row guttar-15px align-items-center">
                                                <div class="col-7 col-sm-7">
                                                    <div class="input-wrap">
                                                        <input class="input-bordered custom-date-picker" type="text" name="end_date" required>
                                                        <span class="input-icon input-icon-right date-picker-icon"><em class="ti ti-calendar"></em></span>
                                                    </div>
                                                </div>
                                                <div class="col-5 col-sm-5">
                                                   <div class="input-wrap">
                                                        <input class="input-bordered time-picker" type="text" name="end_time">
                                                        <span class="input-icon input-icon-right time-picker-icon"><em class="ti ti-alarm-clock"></em></span>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <span class="input-note">Finish date/time for sale. Can't purchase after time.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                 
                                </div>
                                <div class="gaps-1-5x"></div>
                                <div class="d-flex">
                                    <button class="btn btn-primary save-disabled" type="submit" disabled="">Create</button>
                                </div>
                                <div class="gaps-1-5x"></div>         
                                <span class="input-note">Pricing and bonus option can be set after stage create.</span>                   
                            </div>
                        </form>
                    </div> {{-- stage details end --}}
                </div> {{-- tab content end --}}
            </div>
        </div>
    </div>
</div>
@endsection