<div class="page-content">
    <div class="container">
        <div class="card content-area">
            <div class="card-innr">
                <div class="card-head d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Stripe Payment Setting <span class="badge badge-xs badge-purple ucap">Add-ons</span></h4>
                    <a href="{{ route('admin.payments.setup') }}" class="btn btn-sm btn-auto btn-outline btn-primary d-sm-inline-block"><em class="fas fa-arrow-left"></em><span class="d-none d-sm-inline-block">Back</span></a>
                </div>
                <div class="card-text wide-max-md">
                    <p>Stipe is online payment gateway that allow to accept payments form your contributors.</p>
                </div>
                <div class="gaps-2x"></div>
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('admin.ajax.payments.update') }}" method="POST" class="payment_methods_form validate-modern">
                            @csrf
                            <input type="hidden" name="req_type" value="stripe">
                            <div class="row align-items-center">
                                <div class="col-sm col-md-3">
                                    <label class="card-title card-title-sm">Active or Deactive</label>
                                </div>
                                <div class="col-sm col-md-3">
                                    <div class="fake-class">
                                        <div class="input-wrap input-wrap-switch">
                                            <input class="input-switch" {{ $pmData->status == 'active' ? 'checked' : '' }} id="status" name="status" type="checkbox">
                                            <label for="status">
                                                <span class="over">Inactive</span><span>Active Gateway</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="gaps-1x"></div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-item input-with-label">
                                        <label class="input-item-label">Method Title</label>
                                        <div class="input-wrap">
                                            <input class="input-bordered" value="{{ $pmData->title }}" type="text" name="title" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-item input-with-label">
                                        <label class="input-item-label">Description</label>
                                        <div class="input-wrap">
                                            <input class="input-bordered" value="{{ $pmData->details }}" placeholder="You can pay via your stripe account." type="text" name="details">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-sm">
                                            <div class="input-item input-with-label">
                                                <label class="input-item-label">Stripe Account Email</label>
                                                <div class="input-wrap">
                                                    <input class="input-bordered" value="{{ $pmData->secret->email }}" placeholder="Enter your stripe account email" type="text" name="email">
                                                </div>
                                                <div class="input-note"><em>Use for admin (only) reference for received payment.</em></div>
                                            </div>
                                        </div>
                                        <div class="col-sm">
                                            <label class="input-item-label">Test API / Sandbox</label>
                                            <div class="input-switch-middle">
                                                <div class="input-wrap input-wrap-switch">
                                                    <input class="input-switch" id="stripe-sandbox" type="checkbox" name="sandbox" {{ $pmData->secret->sandbox == 1 ? 'checked' : '' }}>
                                                    <label for="stripe-sandbox">
                                                        <span class="over"></span><span>Enable</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h5 class="card-title card-title-sm pdt-1x pdb-1x text-primary">API Credentials</h5>
                            
                            <p>Enter your API credentials to receive payment and verify payment automatically.</p>
                            <div class="row">
                                <div class="col-sm col-md-6">
                                    <div class="input-item input-with-label">
                                        <label class="input-item-label">Live Public Key</label>
                                        <div class="input-wrap">
                                            <input class="input-bordered" autocomplete="new-password" placeholder="Enter your live public key" type="text" name="pk_key" value="{{ $pmData->secret->pk_key }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm col-md-6">
                                    <div class="input-item input-with-label">
                                        <label class="input-item-label">Live Secret Key</label>
                                        <div class="input-wrap">
                                            <input class="input-bordered" autocomplete="new-password" placeholder="Enter your live secret key" type="password" name="sk_key" value="{{ $pmData->secret->sk_key }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm col-md-6">
                                    <div class="input-item input-with-label">
                                        <label class="input-item-label">Test Public Key</label>
                                        <div class="input-wrap">
                                            <input class="input-bordered" autocomplete="new-password" placeholder="Enter your test public key" type="text" name="test_pk_key" value="{{ $pmData->secret->test_pk_key }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm col-md-6">
                                    <div class="input-item input-with-label">
                                        <label class="input-item-label">Test Secret Key</label>
                                        <div class="input-wrap">
                                            <input class="input-bordered" autocomplete="new-password" placeholder="Enter your test secret key" type="password" name="test_sk_key" value="{{ $pmData->secret->test_sk_key }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="gaps-1x"></div>
                            <div class="d-flex pb-1">
                                <button class="btn btn-md btn-primary save-disabled" type="submit">UPDATE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>