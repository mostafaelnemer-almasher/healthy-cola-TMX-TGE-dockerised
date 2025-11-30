
<div class="modal fade" id="private_inv_form" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-md modal-dialog-centered">
        <div class="modal-content">
            <a href="#" class="modal-close" data-dismiss="modal" aria-label="Close"><em class="ti ti-close"></em></a>
            <div class="popup-body popup-body-md">
                <h3 class="popup-title"> {{(!empty($privateInvite)) ? 'Update Invitation' : 'Create New Invitation'}}</h3>
                <form action="{{ route('admin.ajax.private.invite.update') }}" method="POST" class="validate-modern" id="private_inv_form" autocomplete="false">
                    @csrf
                    @if(!empty($privateInvite))
                        <input type="hidden" name="req_type" value="private_invitation_update">
                        <input type="hidden" name="id" value="{{ $privateInvite->id }}">
                    @else
                        <input type="hidden" name="req_type" value="private_invitation_create">
                    @endif
                    <div class="d-sm-flex flex-sm-row">
                        <div class="input-item input-with-label w-100 mr-sm-2">
                            <label class="input-item-label">Label<span class=" text-danger ml-1">*</span></label>
                            <div class="input-wrap">
                                <input type="text" name="invitation_label" class="input-bordered cls" maxlength="25" value="{{ $privateInvite->label ?? '' }}" required>
                            </div>                            
                        </div>
                        <div class="input-item input-with-label w-100 ml-sm-2">
                            <label class="input-item-label">Code</label>
                            <div class="input-wrap">
                                <input type="text" name="invitation_code" class="input-bordered cls" minlength="4" maxlength="20" value="{{ $privateInvite->code ?? '' }}" {{ !empty($privateInvite->code) ? ' readonly' : '' }}>
                            </div>                               
                            <span class="input-note">Code will be auto generated if left blank.</span>
                        </div>
                    </div>
                    <div class="d-sm-flex flex-sm-row">
                        <div class="input-item input-with-label w-100 mr-sm-2">
                            <label class="input-item-label">Start Date</label>
                            <div class="input-wrap">
                                <input type="text" name="invitation_start" class="input-bordered date-picker"  value="{{ !empty($privateInvite->start_date) ? _date($privateInvite->start_date, 'm/d/Y') : '' }}">
                                <span class="input-icon input-icon-right date-picker-icon"><em class="ti ti-calendar"></em></span>
                            </div>
                        </div>

                        <div class="input-item input-with-label w-100 ml-sm-2">
                            <label class="input-item-label">End Date</label>
                            <div class="input-wrap">
                                <input type="text" name="invitation_end" class="input-bordered date-picker valid" value="{{ !empty($privateInvite->end_date) ? _date($privateInvite->end_date, 'm/d/Y') : '' }}">
                                <span class="input-icon input-icon-right date-picker-icon"><em class="ti ti-calendar"></em></span>
                            </div>
                            <span class="input-note">Code will run for unlimited time period if left blank.</span>                    
                        </div>
                    </div>
                    @if(!empty($privateInvite))
                    <div class="input-item">
                        <div class="row align-items-center">
                            <div class="col-sm-3 col-6">
                                <input type="checkbox" class="input-switch input-switch-sm switch-toogle" name="status" id="invite-switch" {{$privateInvite->status == 'active' ? ' checked' :''}}>
                                 <label for="invite-switch"><span>Inactive</span><span class="over">Active</span></label>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="gaps-1x"></div>
                    <div class="d-flex">
                        <button class="btn btn-primary mx-auto mx-sm-0" type="submit">{{ (!empty($privateInvite)?'Update':'Create') }}</button>
                    </div>
                </form>              
            </div>
        </div>
    </div>
</div>