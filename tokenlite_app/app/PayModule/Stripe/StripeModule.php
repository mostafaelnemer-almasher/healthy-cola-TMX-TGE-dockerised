<?php

namespace App\PayModule\Stripe;

/**
 * Stripe Payment Module
 * @version v1.1.0
 * @since v1.3.1
 */

use App\PayModule\Stripe\StripePayment;
use Auth;
use Route;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Setting;
use App\Models\IcoStage;
use App\Helpers\IcoHandler;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\EmailTemplate;
use App\PayModule\ModuleHelper;
use App\PayModule\PmInterface;
use App\Notifications\TnxStatus;
use App\Helpers\TokenCalculate as TC;

class StripeModule implements PmInterface
{
    const SLUG = 'stripe';
    const SUPPORT_CURRENCY = ['USD', 'GBP', 'EUR', 'RUB', 'CAD', 'AUD', 'INR', 'BRL', 'NZD', 'PLN', 'JPY', 'MYR', 'MXN', 'PHP', 'CHF', 'SGD', 'CZK', 'NOK', 'SEK', 'DKK', 'HKD'];
    const ZERO_DECIMAL_CURRENCY = ['BIF', 'PYG', 'CLP', 'RWF', 'DJF', 'UGX', 'GNF', 'VND', 'JPY', 'VUV', 'KMF', 'XAF', 'KRW', 'XOF', 'MGA', 'XPF'];
    const VERSION = '1.1.0';
    const APP_VERSION = '^1.6.0';
    const STRIPE_VERSION = '2022-11-15';

    public function routes()
    {
        Route::get('stripe/success', 'Stripe\StripeController@success')->name('stripe.success');
        Route::get('stripe/cancel', 'Stripe\StripeController@cancel')->name('stripe.cancel');
        Route::post('stripe/notify', 'Stripe\StripeController@email_notify')->name('stripe.notify');
    }

    public function admin_views()
    {
        $pmData = PaymentMethod::get_data(self::SLUG, true);
        $name = self::SLUG;
        return ModuleHelper::view('Stripe.views.card', compact('pmData', 'name'));
    }

    public function admin_views_details()
    {
        $pmData = PaymentMethod::get_data(self::SLUG, true);
        return ModuleHelper::view('Stripe.views.admin', compact('pmData'));
    }

    public function show_action()
    {
        $pmData = PaymentMethod::get_data(self::SLUG, true);
        $html = '<li class="pay-item"><div class="input-wrap">
                    <input type="radio" class="pay-check" Value="'.self::SLUG.'" name="pay_option" required="required" id="pay-'.self::SLUG.'" data-msg-required="'.__('Select your payment method.').'">
                    <label class="pay-check-label" for="pay-'.self::SLUG.'"><span class="pay-check-text" title="'.$pmData->details.'">'.$pmData->title.'</span><img class="pay-check-img" src="'.asset('assets/images/pay-stripe.png').'" alt="'.ucfirst(self::SLUG).'"></label>
                </div></li>';
        return [
            'currency' => $this->check_currency(),
            'html' => ModuleHelper::str2html($html)
        ];
    }

    public function check_currency()
    {
        return self::SUPPORT_CURRENCY;
    }

    public function get_zero_decimal_currencies()
    {
        return self::ZERO_DECIMAL_CURRENCY;
    }

    public function get_stripe_version()
    {
        return self::STRIPE_VERSION;
    }

    public function transaction_details($transaction)
    {
        $stripe = new StripePayment();
        $stripePk = $stripe->getPublicKey();
        if ($transaction->status=='pending' || $transaction->status == 'onhold') {
            $session = $stripe->createClientSession($transaction->amount, $transaction->currency, base64_encode($transaction->id));
            if ($session) {
                $transaction->payment_id = data_get($session, 'id');
                $transaction->extra = json_encode([
                    'session_id' => data_get($session, 'id'),
                    'payment_intent' => data_get($session, 'payment_intent'),
                    'info' => [$session]
                ]);
                $transaction->save();
            }
        }
        $transaction->fresh();

        return ModuleHelper::view('Stripe.views.tnx_details', compact('transaction', 'stripePk'));
    }

    public function email_details($transaction)
    {
        $data = json_decode($transaction->extra);
        $pm = get_pm(self::SLUG, true);
        $pay_url = (isset($data->url) ? $data->url : null);

        $pay_address = ($pay_url == null ? route('user.token') : '<tr><td>Payment to </td><td>:</td><td><a href="'.$pay_url.'" target="_blank">'.$pm->title.'</a></td></tr>');
    }

    public function create_transaction(Request $request)
    {
        $helper = new StripePayment();
        if (method_exists($helper, 'stripePay')) {
            return $helper->stripePay($request);
        }
        $response['msg'] = 'info';
        $response['message'] = __('messages.nothing');
        return $response;
    }

    public function save_data(Request $request)
    {
        $response['msg'] = 'info';
        $response['message'] = __('messages.nothing');
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'details' => 'required',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('title')) {
                $message = __($validator->errors()->first(), ['attribute' => 'title']);
            } elseif ($validator->errors()->has('details')) {
                $message = __($validator->errors()->first(), ['attribute' => 'details']);
            } else {
                $message = __('messages.form.wrong');
            }

            $response['msg'] = 'warning';
            $response['message'] = $message;
        } else {
            $old = PaymentMethod::get_single_data(self::SLUG);
            $stripe_data = [
                'email' => $request->email,
                'sandbox' => isset($request->sandbox) ? 1 : 0,
                'pk_key' => $request->pk_key,
                'sk_key' => $request->sk_key,
                'test_pk_key' => $request->test_pk_key,
                'test_sk_key' => $request->test_sk_key,
                'is_active' => (isset($old->secret) ? $old->secret->is_active : 0)
            ];
            $stripe = PaymentMethod::where('payment_method', 'stripe')->first();
            if (! $stripe) {
                $stripe = new PaymentMethod();
                $stripe->payment_method = 'stripe';
            }
            $stripe->title = $request->input('title');
            $stripe->description = $request->input('details');
            $stripe->status = isset($request->status) ? 'active' : 'inactive';
            $stripe->data = json_encode($stripe_data);

            if ($stripe->save()) {
                $response['msg'] = 'success';
                $response['message'] = __('messages.update.success', ['what' => 'Stripe payment information']);
            } else {
                $response['msg'] = 'error';
                $response['message'] = __('messages.update.failed', ['what' => 'Stripe payment information']);
            }
        }
        return $response;
    }

    public function demo_data()
    {
        $data = [
            'email' => null,
            'sandbox' => 0,
            'sk_key' => null,
            'pk_key' => null,
            'test_sk_key' => null,
            'test_pk_key' => null,
            'is_active' => 0
        ];
        $template = [
            'order-submit-online-user' => [
                'name' => 'Token Purchase - Order Placed by Online Gateway (USER)',
                'slug' => 'order-submit-online-user',
                'subject' => 'Order placed for Token Purchase #[[order_id]]',
                'greeting' => 'Thank you for your contribution!',
                'message' => "You have requested to purchase [[token_symbol]] token. Your order has been received and is now being waiting for payment. You order details are show below for your reference. \n\n[[order_details]]\n\nYour token balance will appear in your account as soon as we have confirmed your payment from [[payment_gateway]].\n\nFeel free to contact us if you have any questions.\n",
                'regards' => "true"
            ],
            'order-canceled-user' => [
                'name' => 'Token Purchase - Order Unpaid/Rejected by Gateway (USER)',
                'slug' => 'order-canceled-user',
                'subject' => 'Unpaid Order Canceled #[[order_id]]',
                'greeting' => 'Hello [[user_name]],',
                'message' => "We noticed that you just tried to purchase [[token_symbol]] token, however we have not received your payment of [[payment_amount]] via [[payment_gateway]] for [[total_tokens]] Token.\n\nIt looks like your payment gateway ([[payment_gateway]]) has been rejected the transaction. \n\n[[order_details]]\n\nIf you want to pay manually, please feel free to contact us via [[support_email]]\n ",
                'regards' => "true"
            ],
            'order-successful-admin' => [
                'name' => 'Token Purchase - Payment Approved by Gateway (ADMIN)',
                'slug' => 'order-successful-admin',
                'subject' => 'Payment Received - Order #[[order_id]]',
                'greeting' => 'Hello Admin,',
                'message' => "You just received a payment of [[payment_amount]] for order (#[[order_id]]) via [[payment_gateway]]. \n\nThis order has now been approved automatically and token balance added to contributor ([[user_email]]) account. \n\n\nPS. Do not reply to this email.  \nThank you.\n ",
                'regards' => "false"
            ],
            'order-rejected-admin' => [
                'name' => 'Token Purchase - Payment Rejected/Canceled by Gateway (ADMIN)',
                'slug' => 'order-rejected-admin',
                'subject' => 'Payment Rejected - Order #[[order_id]]',
                'greeting' => 'Hello Admin,',
                'message' => "The order (#[[order_id]]) has been canceled, however the payment was not successful and [[payment_gateway]] rejected or canceled the transaction. \n\n\n[[order_details]] \n\n\nPS. Do not reply to this email.  \nThank you.\n ",
                'regards' => "false"
            ],
        ];

        foreach ($template as $key => $value) {
            $check = EmailTemplate::where('slug', $key)->count();
            if ($check <= 0) {
                EmailTemplate::create($value);
            }
        }

        if (PaymentMethod::check(self::SLUG)) {
            $stripe = new PaymentMethod();
            $stripe->payment_method = self::SLUG;
            $stripe->title = 'Pay with Stripe';
            $stripe->description = 'Make your payment with credit or debit card.';
            $stripe->data = json_encode($data);
            $stripe->status = 'inactive';
            $stripe->save();
        }
    }
}
