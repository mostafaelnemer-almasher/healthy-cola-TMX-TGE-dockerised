<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Notifications\UserRegistered;
use Auth;
use Carbon\Carbon;
use App\Notifications\ConfirmEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\ChangeEmailConfirm;
use App\Models\GlobalMeta;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ReferralHelper;


class VerifyController extends Controller
{
    protected $redirectTo = '/verify/success';

    public function __construct()
    {
        $this->middleware('auth')->except(['verify']);
    }

    public function index(Request $request)
    {

        // return view('auth.verify');;
        return $request->user()->hasVerifiedEmail()
            ? redirect($this->redirectTo)
            : (view('auth.verify'));
    }

    public function resend(Request $request)
    {
        $cd = Carbon::now();
        $user = Auth::user();
        $chk = $cd->copy()->addMinutes(10);

        $user->meta->email_token = str_random(65);
        $user->meta->email_expire = $cd->copy()->addMinutes(75);
        $user->meta->save();
        try {
            $gm = GlobalMeta::where(['name' => 'new_change_email', 'pid' => $user->id])->first();        
            $new_email = isset($gm->value) ? $gm->value : null ;
            if(isset($new_email)) {
                Mail::to($new_email)->send(new ChangeEmailConfirm($user));
            } else {
                $user->notify(new ConfirmEmail($user));
            }

            return back()->with('resent', true);
        } catch (\Exception $e) {
            return back()->withErrors(__('messages.email.verify', ['email'=>get_setting('site_email')]));
        }
    }

    public function verify(Request $request, $id='', $token='')
    {
        if ($id != null || Auth::id() != null) {
            $id = $id != null ? $id : Auth::id();
            $user = User::find($id);
            $gm = GlobalMeta::where(['name' => 'new_change_email', 'pid' => $user->id])->first();        
            $new_change_email = isset($gm->value) ? $gm->value : null ;

            if ($user) {
                if ($user->email_verified_at != null && empty($new_change_email)) {
                    return redirect()->route('login');//->with('info', __('messages.verify.verified'));
                }
                if ($user->meta->email_token == $token) {
                    if (_date($user->meta->email_expire, 'Y-m-d H:i:s', false, false) >= date('Y-m-d H:i:s')) {
                        $user->email_verified_at = now();
                        $user->meta->email_token = null;
                        $user->meta->email_expire = null;
                        if(!empty($new_change_email)) {
                            $user->email = $new_change_email;
                            $globalMeta = GlobalMeta::where(['name' => 'new_change_email', 'pid' => $user->id])->first();
                            $globalMeta->value = null;
                            $globalMeta->save();
                        }
                        $user->save();
                        $user->meta->save();

                        if(is_active_referral_system()) {
                            ReferralHelper::referral_signup_bonus($user);
                        }

                        $chk_mail_stat = is_mail_disable($user);
                        if (!$chk_mail_stat) {
                            $user->notify(new UserRegistered($user));
                        }

                        Auth::logout();
                        return redirect($this->redirectTo);
                    } else {
                        if (!Auth::guest()) {
                            Auth::logout();
                        }
                        return redirect()->route('login')->with('info', __('messages.verify.expired'));
                    }
                } else {
                    if (!Auth::guest()) {
                        Auth::logout();
                    }
                    return redirect()->route('login')->with('error', __('messages.verify.invalid'));
                }
            } else {
                if (!Auth::guest()) {
                    Auth::logout();
                }
                return redirect()->route('login')->with('warning', __('messages.verify.not_found'));
            }
        } else {
            return redirect()->route('login')->with('warning', __('messages.verify.not_found'));
        }
    }
}
