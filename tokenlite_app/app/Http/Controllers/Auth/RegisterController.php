<?php

namespace App\Http\Controllers\Auth;

/**
 * Register Controller
 *
 * @package TokenLite
 * @author Softnio
 * @version 1.1.2
 */

use Cookie;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Referral;
use App\Models\UserMeta;
use App\Helpers\ReCaptcha;
use App\Helpers\IcoHandler;
use Illuminate\Http\Request;
use App\Notifications\ConfirmEmail;
use App\Http\Controllers\Controller;
use App\Models\GlobalMeta;
use App\Models\PrivateInvitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers, ReCaptcha;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     * @version 1.0.0
     */
    protected $redirectTo = '/register/success';

    /**
     * Create a new controller instance.
     *
     * @version 1.0.0
     * @return void
     */
    protected $handler;
    public function __construct(IcoHandler $handler)
    {
        $this->handler = $handler;
        $this->middleware('guest');
    }

   public function showRegistrationForm()
    {
        if (application_installed(true) == false) {
            return redirect(url('/install'));
        }
        $error = false;
        $invite_code = false;
        $uempty_code = false;
        $derror = '';
        $currentDate = Carbon::now();
        if(get_setting('private_invitation_active')==1){
            $ivcode = strip_tags(request()->invite);
            if(empty($ivcode)){
            	$uempty_code = true;
            } elseif(strlen($ivcode) < 4 || strlen($ivcode) > 20) {
	                $error = true;
	            } else {
	                $inviteCode = PrivateInvitation::where('code', $ivcode)->where('status','active')->first();
	                if(!empty($inviteCode)) {
	                    if (empty($inviteCode->start_date) || $currentDate->gte($inviteCode->start_date)) {
	                        if (empty($inviteCode->end_date) || $currentDate->lt($inviteCode->end_date)) {
	                            $error = false;
	                            if(get_setting('pinv_alt') == 1) {
	                                session()->flash('info', get_setting('pinv_alt_msg') ?? __("This is a private invitation. Please register."));
	                            }
	                            $invite_code = $inviteCode->code;
	                            if(!Cookie::has('private_sale_'.$inviteCode->code)) {
	                                $inviteCode->visit = $inviteCode->visit+1;
	                                Cookie::queue('private_sale_'.$inviteCode->code, $inviteCode->code, 15);
	                            }
	                            $inviteCode->save();
	                        } else {
	                            $error = true;
	                        }
	                    } else {
	                        $error = true;
	                    }
	                } else {
	                    $error = true;
	                }
	            }
        }
        if ($error) {
            $derror = __("The invitation link is invalid or already expired.");
        }
        return view('auth.register', [
            'invite_code' => $invite_code, 
            'uempty_code' => $uempty_code,
            'derror' => $derror, 
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        if (recaptcha()) {
            $this->checkReCaptcha($request->recaptcha);
        }
        $have_user = User::where('role', 'admin')->count();
        if ($have_user >= 1 && ! $this->handler->check_body()) {
            return back()->withInput()->with([
                'warning' => $this->handler->accessMessage()
            ]);
        }
        $this->validator($request->all())->validate();
        if (get_setting('private_invitation_active')==1 && !empty($request->invitation_code)) {
            $invite_code = strip_tags($request->invitation_code);
            if(strlen($invite_code) < 4 || strlen($invite_code) > 20) {
                $invite_code = null;
            } else {
                $invite_code = PrivateInvitation::stage_status_chk($invite_code);
            }
            if(empty($invite_code)) {
                throw ValidationException::withMessages(['warning' => __('The invitation link is invalid or already expired.')]);
            }
        } elseif(get_setting('registration_option')=='invite') {
            return redirect()->route('register');
        }

        if(is_active_referral_system() && get_setting('referral_required')==1 && !(get_refer_id() && gws('referral_info_show')==1)) {
            if(get_setting('private_invitation_active')==1 && !empty($invite_code)) {
                if(!empty($request->referral_code)) {
                    $ref_user = $this->check_referral_code($request->referral_code);
                    if(empty($ref_user)) {
                        throw ValidationException::withMessages(['warning' => __("This referral code is invalid. If you don't have a valid code, you can also register by keeping this field empty.")]);
                    }
                }
            } elseif(!empty($request->referral_code)) {
                $ref_user = $this->check_referral_code($request->referral_code);
                if(empty($ref_user)) {
                    throw ValidationException::withMessages(['warning' => __('Your referral code is invalid.')]);
                }
            } else {
                throw ValidationException::withMessages(['warning' => __('A valid referral code is required.')]);
            }
        }

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user) ? : redirect($this->redirectPath());
    }

     /**
     * check referral code
     *
     * @param  string  $referral_code
     * @version 1.8.0
     * @return Modal|null
     */
    private function check_referral_code($referral_code=null) {
        $codePrefix = config('icoapp.user_prefix');
        $ref_user = '';
        if (str_contains($referral_code, $codePrefix)) {
            $ref_code = strip_tags($referral_code);
            $ref_id = (int)(str_replace(config('icoapp.user_prefix'), '', $ref_code));
            $ref_user = User::where('id', $ref_id)->where('role', 'user')->first();
        }
        return $ref_user;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @version 1.0.1
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $term = get_page('terms', 'status') == 'active' ? 'required' : 'nullable';
        return Validator::make($data, [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,9}$/ix', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'terms' => [$term],
            'referral_code' => ['nullable','string'],
            'mobile' => ['required', 'string', 'regex:/^[0-9+\-\s()]{10,20}$/', 'max:20', 'unique:users'],
        ], [
            'terms.required' => __('messages.agree'),
            'email.regex' => __('Please enter a valid email address.'),
            'email.unique' => 'The email address you have entered is already registered. Did you <a href="' . route('password.request') . '">forget your login</a> information?',
            'mobile.required' => __('Mobile number is required.'),  // NEW: Custom message
            'mobile.regex' => __('Enter a valid mobile number (10-20 characters, digits + spaces/hyphens/parentheses allowed).'),  // NEW
            'mobile.unique' => __('This mobile number is already registered.'),
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @version 1.2.1
     * @since 1.0.0
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $have_user = User::where('role', 'admin')->count();
        $type = ($have_user >= 1) ? 'user' : 'admin';
        $email_verified = ($have_user >= 1) ? null : now();
        $user = User::create([
            'name' => strip_tags($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'mobile' => strip_tags($data['mobile']),
            'lastLogin' => date('Y-m-d H:i:s'),
            'role' => $type,
        ]);

        if ($user) {
            if ($have_user <= 0) {
                save_gmeta('site_super_admin', 1, $user->id);
            }
            $user->email_verified_at = $email_verified;
            $refer_blank = true;
            if (is_active_referral_system()) {
                if (array_key_exists('referral_code', $data) || Cookie::has('ico_nio_ref_by')) {
                    $ref_id = (array_key_exists('referral_code', $data)) ? (int) (str_replace(config('icoapp.user_prefix'), '', $data['referral_code'])) : (int) Cookie::get('ico_nio_ref_by');
                    $ref_user = User::where('id', $ref_id)->where('role', 'user')->first();
                    if ($ref_user) {
                        $user->referral = $ref_user->id;
                        $user->referralInfo = json_encode([
                            'user' => $ref_user->id,
                            'name' => $ref_user->name,
                            'time' => now(),
                        ]);
                        $refer_blank = false;
                        $this->create_referral_or_not($user->id, $ref_user->id);
                        Cookie::queue(Cookie::forget('ico_nio_ref_by'));
                    }
                }
            }
            if ($user->role=='user' && $refer_blank==true) {
                $this->create_referral_or_not($user->id);
            }

            if(get_setting('private_invitation_active')==1 && !empty($data['invitation_code'])) {
                $invitation = PrivateInvitation::where('code', $data['invitation_code'])->first();
                $invitation->signup = $invitation->signup+1;
                $invitation->save();

                GlobalMeta::save_meta('private_reg_code', $data['invitation_code'], $user->id);
                Cookie::queue('private_sale_'.$invitation->code, $invitation->code, 43200);
            }

            $user->save();
            $meta = UserMeta::create([ 'userId' => $user->id ]);

            $meta->notify_admin = ($type=='user')?0:1;
            $meta->email_token = str_random(65);
            $cd = Carbon::now(); //->toDateTimeString();
            $meta->email_expire = $cd->copy()->addMinutes(75);
            $meta->save();

            if ($user->email_verified_at == null) {
                try {
                    $user->notify(new ConfirmEmail($user));
                } catch (\Exception $e) {
                    session('warning', 'User registered successfully, but we unable to send confirmation email!');
                }
            }
        }
        return $user;
    }

    /**
     * Create user in referral table.
     *
     * @param  $user, $refer
     * @version 1.0
     * @since 1.1.2
     * @return void
     */
    protected function create_referral_or_not($user, $refer=0)
    {
        Referral::create([ 'user_id' => $user, 'user_bonus' => 0, 'refer_by' => $refer, 'refer_bonus' => 0 ]);
    }
}
