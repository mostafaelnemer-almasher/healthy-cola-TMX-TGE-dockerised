@php
    $title = $user->email_verified_at != null ? __('Change Email') : __('Verify Email Address');
@endphp

<div class="modal fade" id="change-unverified-email" tabindex="-1">
    <div class="modal-dialog modal-dialog-md modal-dialog-centered">
        <div class="modal-content">
            <a href="#" class="modal-close" data-dismiss="modal" aria-label="Close"><em class="ti ti-close"></em></a>
            <div class="popup-body popup-body-md">
            <h4 class="popup-title">{{ $title }}</h4>
            <div class="gaps-1x"></div>
            @if(($user->email_verified_at != null || $user->email_verified_at == null) && !empty($new_email))
                <p>{!! __("Your current email is :email, but there's a pending email change request to :new_email awaiting verification. Simply verify the new email to update your email.", ['email' => '<strong>'.$user->email.'</strong>', 'new_email' => '<strong>'.$new_email.'</strong>']) !!}</p>
                <p>{{__("Resend the verification email if you haven't received it.")}}</p>
            @elseif($user->email_verified_at == null && empty($new_email))
                <p>{!! __('Please verify your email :email as it has not been verified yet.',['email' => '<strong>'.$user->email.'</strong>']) !!}
                <p>{{ __("Resend the verification email if you haven't received it.") }}</p>
            @elseif($user->email_verified_at != null && empty($new_email))
                <p> {!! __('Your current email is :email. If you want to change it, a verification link will be sent to your new requested email. Once verified, your email will update accordingly.', [ 'email' => '<strong>'.$user->email.'</strong>']) !!}</p>
            @endif

            @if( ($user->email_verified_at != null || $user->email_verified_at == null) && !empty($new_email) || ($user->email_verified_at == null && empty($new_email)))
                {!! UserPanel::resend_verification_email() !!}
                <div class="gaps-1x"></div>
                <p class="text">{{__('If you registered with wrong email address, change it now.') }}</p>
            @endif
            <form class="validate-modern _reload" action="{{ route('user.ajax.change.email.update') }}" method="POST" id="change-email-form" autocomplete="off">
                @csrf
                <div class="input-item input-with-label">
                    <div class="input-wrap">
                        <input class="input-bordered" type="text" id="user_new_change_email" name="user_new_change_email" required="required" placeholder="{{ __('Enter new email') }}">
                    </div>
                </div>
                <div class="gaps-1x"></div>
                <div class="d-sm-flex justify-content-between align-items-center">
                    <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
                </div>
            </form>
            </div>
        </div>
    </div>

<script type="text/javascript">
    (function($) {
        var $nio_unverified_email = $('#change-email-form');
        if ($nio_unverified_email.length > 0) { ajax_form_submit($nio_unverified_email, true, 'ti ti-alert', true); }
    })(jQuery);
</script>