@extends('layouts.auth')
@section('title', __('Sign up'))
@section('content')

@php
$check_users = \App\Models\User::count();
@endphp
@if( recaptcha() )
@push('header')
<script>
    grecaptcha.ready(function () { grecaptcha.execute('{{ recaptcha('site') }}', { action: 'register' }).then(function (token) { if(token) { document.getElementById('recaptcha').value = token; } }); });
</script>
@endpush
@endif
<div class="page-ath-form">
	@if(gws('private_invitation_active')==1 && gws('registration_option')=='invite' && ($uempty_code || $derror))
		<div class="text-black w-100 shadow-sm p-5 mb-5 bg-white rounded">
		<div class="text-center4 p-1">
	    	<p><i class="fa fa-exclamation-triangle h1 text-warning"></i></p>
		@if($derror)
			<p class="lead" style="word-wrap: break-word">{{ __($derror) }}</p>
		@endif
			<p class="lead" style="word-wrap: break-word">{{ __(gws('disable_reg_msg')) ?? __("Registration is temporarily off.") }}</p>
		</div>
		    <a type="button" href="{{ route('login') }}" class="btn btn-primary btn-blockp mt-4">{{__('Sign In')}}</a>
		</div>
	@elseif(gws('private_invitation_active')==1 && gws('registration_option')!='invite' && $derror)
		@if($derror)
		<div class="text-black w-100 shadow-sm p-5 mb-5 bg-white rounded">
			<div class="text-center4 p-1">
    	    	<p><i class="fa fa-exclamation-triangle h1 text-warning"></i></p>
    	        <p class="lead" style="word-wrap: break-word">{{ __($derror) }}</p>
	     	</div>
       		<a type="button" href="{{ route('login') }}" class="btn btn-primary btn-blockp mt-4">{{__('Sign In')}}</a>
		</div>
		@endif
	@elseif(gws('private_invitation_active')==0 && gws('registration_option')=='invite')
	<div class="text-black w-100 shadow-sm p-5 mb-5 bg-white rounded">
        <div class="text-center4 p-1">
            <p><i class="fa fa-exclamation-triangle h1 text-warning"></i></p>
            <p class="lead" style="word-wrap: break-word">{{ __(gws('disable_reg_msg')) ?? __("Registration is temporarily off.") }}</p>
        </div>
        <a type="button" href="{{ route('login') }}" class="btn btn-primary btn-blockp mt-4">{{__('Sign In')}}</a>
	</div>
	@else
    <h2 class="page-ath-heading">{{__('Sign up')}} <small>{{__('Create New')}} {{ site_info('name') }} {{__('Account')}}</small></h2>
    <form class="register-form" method="POST" action="{{ route('register') }}" id="register">
        @csrf
        @include('layouts.messages')
        @if(! is_maintenance() && application_installed(true) && ($check_users == 0) )
        <div class="alert alert-info-alt">
            Please register first your Super Admin account with adminstration privilege.
        </div>
        @endif
        <div class="input-item">
            <input type="text" placeholder="{{__('Your Name')}}" class="input-bordered{{ $errors->has('name') ? ' input-error' : '' }}" name="name" value="{{ old('name') }}" minlength="3" data-msg-required="{{ __('Required.') }}" data-msg-minlength="{{ __('At least :num chars.', ['num' => 3]) }}" required>
        </div>
        <div class="input-item">
            <input type="email" placeholder="{{__('Your Email')}}" class="input-bordered{{ $errors->has('email') ? ' input-error' : '' }}" name="email" value="{{ old('email') }}"data-msg-required="{{ __('Required.') }}" data-msg-email="{{ __('Enter valid email.') }}" required>
        </div>
        <div class="input-item">
            <input type="tel" placeholder="{{__('Mobile Number')}}" class="input-bordered{{ $errors->has('mobile') ? ' input-error' : '' }}" name="mobile" value="{{ old('mobile') }}" pattern="[0-9+\-\s()]{10,20}" data-msg-required="{{ __('Mobile number is required.') }}" data-msg-pattern="{{ __('Enter a valid mobile number (10-20 characters, digits + spaces/hyphens/parentheses allowed).') }}" required> <small class="form-text text-muted">e.g., +1 (123) 456-7890</small>
        </div>
        <div class="input-item">
            <input type="password" placeholder="{{__('Password')}}" class="input-bordered{{ $errors->has('password') ? ' input-error' : '' }}" name="password" id="password" minlength="6" data-msg-required="{{ __('Required.') }}" data-msg-minlength="{{ __('At least :num chars.', ['num' => 6]) }}" required>
        </div>
        <div class="input-item">
            <input type="password" placeholder="{{__('Repeat Password')}}" class="input-bordered{{ $errors->has('password_confirmation') ? ' input-error' : '' }}" name="password_confirmation" data-rule-equalTo="#password" minlength="6" data-msg-required="{{ __('Required.') }}" data-msg-equalTo="{{ __('Enter the same value.') }}" data-msg-minlength="{{ __('At least :num chars.', ['num' => 6]) }}" required>
        </div>
        @if( gws('referral_info_show')==1 && get_refer_id() )
        <div class="input-item">
            <input type="text" class="input-bordered" value="{{ __('Your were invited by :userid', ['userid' => get_refer_id(true)]) }}" disabled readonly>
        </div>
        @endif

        @if(gws('private_invitation_active')==1 && isset($invite_code))
            <input type="hidden" name="invitation_code" value="{{ $invite_code }}">
        @endif
        @if(gws('referral_system')==1 && gws('referral_required')==1 && !(get_refer_id() && gws('referral_info_show')==1))
        <div class="input-item">
            <input type="text" name="referral_code" value="{{ get_refer_id() ? get_refer_id(true) : '' }}" 
                placeholder="{{__('Your Referral Code')}}" class="input-bordered{{ $errors->has('referral_code') ? ' input-error' : '' }}" 
                data-msg-required="{{ __('Required.') }}"{{ get_refer_id() ? " readonly" : "" }}>
        </div>
        @endif
        @if(( application_installed(true)) && ($check_users > 0))
            @if(get_page_link('terms') || get_page_link('policy'))
            <div class="input-item text-left">
                <input name="terms" class="input-checkbox input-checkbox-md" id="agree" type="checkbox" required="required" data-msg-required="{{ __("You should accept our terms and policy.") }}">
                <label for="agree">{!! __('I agree to the') . ' ' .get_page_link('terms', ['target'=>'_blank', 'name' => true, 'status' => true]) . ((get_page_link('terms', ['status' => true]) && get_page_link('policy', ['status' => true])) ? ' '.__('and').' ' : '') . get_page_link('policy', ['target'=>'_blank', 'name' => true, 'status' => true]) !!}.</label>
            </div>
            @else
            <div class="input-item text-left">
                <label for="agree">{{__('By registering you agree to the terms and conditions.')}}</label>
            </div>
            @endif
        @else
            <input name="terms" value="1" type="hidden">
        @endif
        @if( recaptcha() )
        <input type="hidden" name="recaptcha" id="recaptcha">
        @endif
        <button type="submit" class="btn btn-primary btn-block">{{ ( application_installed(true) && ($check_users == 0) ) ? __('Complete Installation') : __('Create Account') }}</button>
    </form>

    @if(application_installed(true) && ($check_users > 0) && Schema::hasTable('settings'))
        @if (
        (get_setting('site_api_fb_id', env('FB_CLIENT_ID', '')) != '' && get_setting('site_api_fb_secret', env('FB_CLIENT_SECRET', '')) != '') ||
        (get_setting('site_api_google_id', env('GOOGLE_CLIENT_ID', '')) != '' && get_setting('site_api_google_secret', env('GOOGLE_CLIENT_SECRET', '')) != '')
        )
        <div class="sap-text"><span>{{__('Or Sign up with')}}</span></div>
        <ul class="row guttar-20px guttar-vr-20px">
            @if(get_setting('site_api_fb_id', env('FB_CLIENT_ID', '')) != '' && get_setting('site_api_fb_secret', env('FB_CLIENT_SECRET', '')) != '')
                <li class="col"><a href="{{ gws('private_invitation_active')==1 && !empty($invite_code) ? route('social.login', ['facebook', 'invite' => $invite_code]) : route('social.login', 'facebook') }}" class="btn btn-outline btn-dark btn-facebook btn-block"><em class="fab fa-facebook-f"></em><span>{{__('Facebook')}}</span></a></li>
            @endif
            @if(get_setting('site_api_google_id', env('GOOGLE_CLIENT_ID', '')) != '' && get_setting('site_api_google_secret', env('GOOGLE_CLIENT_SECRET', '')) != '')
                <li class="col"><a href="{{ gws('private_invitation_active')==1 && !empty($invite_code) ? route('social.login', ['google', 'invite' => $invite_code]) : route('social.login', 'google') }}" class="btn btn-outline btn-dark btn-google btn-block"><em class="fab fa-google"></em><span>{{__('Google')}}</span></a></li>
            @endif
        </ul>
        @endif

        <div class="gaps-4x"></div>
        <div class="form-note">
            {{__('Already have an account ?')}} <a href="{{ route('login') }}"> <strong>{{__('Sign in instead')}}</strong></a>
        </div>
    @endif
    @endif
</div>
@endsection
