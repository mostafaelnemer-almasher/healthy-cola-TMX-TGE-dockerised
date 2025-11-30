
<div class="modal fade" id="private_inv_settings" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-md modal-dialog-centered">
        <div class="modal-content">
            <a href="#" class="modal-close" ><em class="ti ti-close"></em></a>
            <div id="base-body" class="popup-body-full">
                <div class="popup-header">
                    <h3 class="popup-title">Private Sale Invitation Settings</h3>
                    <p>You can manage your private sale invitation settings with below options.</p>
                </div>
                <form action="{{ route('admin.ajax.private.invite.update') }}" method="POST" id="private_inv_settings">
                    @csrf
                    <input type="hidden" name="req_type" value="inv_settings_update">
                    <div class="popup-body-innr mt-2">
                        <div class="input-item">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="input-item input-with-label pb-2">
                                        <label class="input-item-label">Registration Option</label>
                                        <div class="row">
                                            <div class="col-md-10">
                                                <span class="input-note pt-0"><strong>'Invite Only'</strong> option will work only when <strong>'Private Sale'</strong> is enabled.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                        <div class="input-wrap">
                                            <select name="registration_option" id="registration-option" class="select select-block select-bordered">
                                                <option {{ (get_setting('registration_option') == 'public' ? 'selected' : '') }} value="public">Public</option>
                                                <option {{ (get_setting('registration_option') == 'invite' ? 'selected' : '') }} value="invite">Invite Only</option>
                                            </select>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <div class="input-item">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <label class="input-item-label">Private Sale Invitation</label>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <input class="input-item-label input-switch input-switch-sm switch-toggle" type="checkbox" name="private_invitation_active"  id="invite-switch"{{ field_value('private_invitation_active')==1 ? ' checked' : '' }}>
                                    <label for="invite-switch">Enable</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-item">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <label class="input-item-label">Private Invite Alert</label>
                                </div>
                                <div class="col-sm-3 col-6">
                                    <input class="input-item-label input-switch input-switch-sm switch-toggle" type="checkbox" name="pinv_alt" id="pinv-alt"{{ field_value('pinv_alt')==1 ? ' checked' : '' }}>
                                    <label for="pinv-alt">Show</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-item">
                            <div class="row align-items-center">
                                <div class="col-sm-12 col-12">
                                    <label class="input-item-label" for="pinv-alt-msg">Private Invite Alert Message</label>
                                    <textarea name="pinv_alt_msg" class="input-bordered input-textarea form-control textarea-reg-disable invite-input-text" id="pinv-alt-msg" rows="3">{{ get_setting('pinv_alt_msg') ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="input-item">
                            <div class="row align-items-center">
                                <div class="col-sm-12 col-12">
                                    <label class="input-item-label" for="disable-reg-msg">Public Registration Off Notice</label>
                                    <textarea name="disable_reg_msg" class="input-bordered input-textarea form-control textarea-reg-disable invite-input-text" id="disable-reg-msg" rows="3">{{ get_setting('disable_reg_msg') ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="popup-footer pt-2">
                        <button class="btn btn-primary save-disabled" type="submit" disabled="disabled">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
