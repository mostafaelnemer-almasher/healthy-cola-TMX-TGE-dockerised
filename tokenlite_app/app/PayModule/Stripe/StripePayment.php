<?php

/**
 * Stripe Payment Module
 * @version v1.1.0
 * @since v1.3.1
 */

namespace App\PayModule\Stripe;

use App\Helpers\ReferralHelper;
use App\Helpers\TokenCalculate as TC;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\IcoStage;
use App\Notifications\TnxStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PayPalHttp\HttpException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\StripeClient;

class StripePayment
{
    private $key;
    private $stripeModule;

    public function __construct()
    {
        $this->stripeModule = new StripeModule();
    }

    private function getSecretKey()
    {
        $configs = PaymentMethod::get_data('stripe');
        return ($configs->sandbox == 1) ? $configs->test_sk_key : $configs->sk_key;
    }

    public function getPublicKey()
    {
        $configs = PaymentMethod::get_data('stripe');
        return ($configs->sandbox == 1) ? $configs->test_pk_key : $configs->pk_key;
    }

    public function updateTnxAmount($currency, $amount)
    {
        return in_array($currency, $this->stripeModule->get_zero_decimal_currencies()) ? round($amount) : ($amount * 100);
    }

    public function createClientSession($amount, $currency, $reference)
    {
        try {
            $client = new StripeClient(['api_key' => $this->getSecretKey(), 'stripe_version' => $this->stripeModule->get_stripe_version()]);

            return $client->checkout->sessions->create([
                'success_url' => route('payment.stripe.success', ['token' => $reference]),
                'cancel_url' => route('payment.stripe.cancel', ['token' => $reference]),
                'payment_method_types' => ['card'],
                'client_reference_id' => $reference,
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Token purchase',
                        ],
                        'unit_amount_decimal' => $this->updateTnxAmount($currency, $amount),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
            ]);
        } catch (RateLimitException $e) {
            Log::error('STRIPE_PAYMENT_RATE_LIMIT', [$e->getMessage()]);
        } catch (InvalidRequestException $e) {
            Log::error('STRIPE_PAYMENT_INVALID_REQUEST', [$e->getMessage()]);
        } catch (AuthenticationException $e) {
            Log::error('STRIPE_PAYMENT_AUTHENTICATION', [$e->getMessage()]);
        } catch (ApiConnectionException $e) {
            Log::error('STRIPE_PAYMENT_API_CONNECTION', [$e->getMessage()]);
        } catch (ApiErrorException $e) {
            Log::error('STRIPE_PAYMENT_API', [$e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('STRIPE_PAYMENT', [$e->getMessage()]);
        }
    }

    /**
     * Make Payment via PayPal
     *
     * @return void
     * @since 1.0.2
     * @version 1.0.1
     */
    public function stripePay(Request $request)
    {
        if (version_compare(phpversion(), '7.1', '>=')) {
            ini_set('precision', get_setting('token_decimal_max', 8));
            ini_set('serialize_precision', -1);
        }
        $tc = new TC();
        $token = $request->input('pp_token');
        $calc_token = $tc->calc_token($token, 'array');
        $current_price = $tc->get_current_price();
        $exrate = Setting::exchange_rate($current_price, 'array');

        $currency = strtolower($request->input('pp_currency'));
        $currency_rate = isset($exrate['fx'][$currency]) ? $exrate['fx'][$currency] : 0;
        $base_currency = strtolower(base_currency());
        $all_currency_rate = isset($exrate['except']) ? json_encode($exrate['except']) : json_encode([]);
        $base_currency_rate = isset($exrate['base']) ? $exrate['base'] : 0;
        $trnx_amount = round($calc_token['price']->$currency, 2);

        if ($trnx_amount < 0.1) {
            $ret['msg'] = 'info';
            $ret['message'] = __('Sorry, unable to proceed. Your payment amount is very low.');

            if ($request->ajax()) {
                return response()->json($ret);
            }
            return redirect(route('user.token'))->with(['info' => $ret['message']]);
        }

        $trnx_data = [
            'token' => round($token, min_decimal()),
            'bonus_on_base' => round($calc_token['bonus-base'], min_decimal()),
            'bonus_on_token' => round($calc_token['bonus-token'], min_decimal()),
            'total_bonus' => round($calc_token['bonus'], min_decimal()),
            'total_tokens' => round($calc_token['total'], min_decimal()),
            'base_price' => round($calc_token['price']->base, max_decimal()),
            'amount' => in_array(strtoupper($currency), $this->stripeModule->get_zero_decimal_currencies()) ? round($trnx_amount) : $trnx_amount,
        ];

        try {
            $tnxId = set_id(rand(100, 999), 'trnx');
            $save_data = [
                'created_at' => Carbon::now()->toDateTimeString(),
                'tnx_id' => $tnxId,
                'tnx_type' => 'purchase',
                'tnx_time' => Carbon::now()->toDateTimeString(),
                'tokens' => $trnx_data['token'],
                'bonus_on_base' => $trnx_data['bonus_on_base'],
                'bonus_on_token' => $trnx_data['bonus_on_token'],
                'total_bonus' => $trnx_data['total_bonus'],
                'total_tokens' => $trnx_data['total_tokens'],
                'stage' => active_stage()->id,
                'user' => Auth::id(),
                'amount' => $trnx_data['amount'],
                'base_amount' => $trnx_data['base_price'],
                'base_currency' => $base_currency,
                'base_currency_rate' => $base_currency_rate,
                'currency' => $currency,
                'currency_rate' => $currency_rate,
                'all_currency_rate' => $all_currency_rate,
                'payment_method' => 'stripe',
                'receive_currency' => strtoupper($currency),
                'payment_to' => get_pm('stripe')->email,
                'details' => 'Tokens Purchase',
                'status' => 'pending',
            ];
            $ret['msg'] = 'success';
            $ret['message'] = __('messages.trnx.created');
            $iid = Transaction::insertGetId($save_data);

            $session = $this->createClientSession($trnx_data['amount'], strtoupper($currency), base64_encode($iid));
            if ($session) {
                $tnx = Transaction::where('id', $iid)->first();
                $tnx->tnx_id = set_id($iid, 'trnx');
                $tnx->payment_id = data_get($session, 'id');
                $tnx->extra = json_encode([
                    'session_id' => data_get($session, 'id'),
                    'payment_intent' => data_get($session, 'payment_intent'),
                    'info' => [$session]
                ]);
                $tnx->save();

                IcoStage::token_add_to_account($tnx, 'add');
                $ret['stripe'] = [
                    'pk' => $this->getPublicKey(),
                    'session_id' => data_get($session, 'id'),
                ];
                $ret['msg'] = 'success';
                $ret['message'] = __('Redirecting to stripe');
                return $ret;
            } else {
                $ret['msg'] = 'error';
                $ret['message'] = __('messages.trnx.canceled');
                Transaction::where('id', $iid)->delete();
            }

            if ($request->ajax()) {
                return response()->json($ret);
            }
            return $this->cancel($request);
        } catch (HttpException $ex) {
            if ($request->ajax()) {
                $ret['msg'] = 'info';
                $ret['message'] = !empty($ex->getMessage()) ? $ex->getMessage() : 'Unable to connect with Stripe.';
                return response()->json($ret);
            }
            return $this->cancel($request, $ex->getMessage());
        }
    }

    public function verifyPayment($token)
    {
        $client = new StripeClient(['api_key' => $this->getSecretKey(), 'stripe_version' => $this->stripeModule->get_stripe_version()]);

        try {
            $res = $client->checkout->sessions->retrieve($token);
            $paymentIntent = data_get($res, 'payment_intent');

            if (data_get($res, 'payment_status') == 'paid' && $paymentIntent) {
                $res = $client->paymentIntents->retrieve($paymentIntent);

                if (data_get($res, 'status') == 'succeeded') {
                    return $res;
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::error('STRIPE_VERIFICATION', [$e->getMessage()]);
            return false;
        }
    }

    public function success(Request $request)
    {
        try {
            $paymentId = base64_decode($request->get('token'));
            try {
                $tranx = Transaction::findOrFail($paymentId);
                if (blank($tranx)) {
                    $this->cancel($request);
                }
                $payment = $this->verifyPayment(data_get($tranx, 'payment_id'));
                $_old_status = $tranx->status;
                $currency = strtoupper(data_get($payment, 'currency'));
                $tranx->status = (data_get($payment, 'status') === 'succeeded') ? 'approved' : (($payment === null) ? 'rejected' : 'canceled');
                if (data_get($payment, 'status') === 'succeeded') {
                    $tranx->wallet_address = data_get($payment, 'customer');
                    $tranx->receive_amount = in_array($currency, $this->stripeModule->get_zero_decimal_currencies()) ? data_get($payment, 'amount_received') : (data_get($payment, 'amount_received') / 100);
                    $tranx->checked_by = json_encode(['name' => 'stripe', 'id' => data_get($tranx, 'payment_id')]);
                    $tranx->checked_time = Carbon::now()->toDateTimeString();
                    $tranx->extra = json_encode((array) $payment);
                    $tranx->save();

                    if ($_old_status == 'deleted') {
                        $tranx->status = 'missing';
                        $tranx->save();
                        return redirect()->route('user.token')->with(['info', 'Thank you! We received your payment but we found something wrong in your transaction, please contact with us with the order id: ' . $tranx->tnx_id . '.']);
                    } else {
                        if ($tranx->status == 'approved' && is_active_referral_system()) {
                            $referral = new ReferralHelper($tranx);
                            $referral->addToken('refer_to');
                            $referral->addToken('refer_by');
                        }
                        IcoStage::token_add_to_account($tranx, null, 'add');
                        try {
                            $tranx->tnxUser->notify((new TnxStatus($tranx, 'successful-user')));
                            if (get_emailt('order-successful-admin', 'notify') == 1) {
                                notify_admin($tranx, 'successful-admin');
                            }
                        } catch (\Exception $e) {
                            $response['error'] = $e->getMessage();
                        }
                        return redirect(route('user.token'))->with(['success' => __('Thank You, We have received your payment!'), 'modal' => 'success']);
                    }
                } else {
                    $tranx->save();
                    IcoStage::token_add_to_account($tranx, 'sub');

                    return redirect(route('user.token'))->with(['warning' => 'Sorry, We have not received your payment!', 'modal' => 'failed']);
                }
            } catch (\Exception $ex) {
                return $this->cancel($request);
            }
        } catch (\Exception $ex) {
            return $this->cancel($request);
        }
    }

    public function cancel(Request $request, $name = 'Order has been canceled due to unsuccessful payment!')
    {
        if ($request->get('token')) {
            $id = base64_decode($request->get('token'));
            $apv_name = ucfirst('stripe');
            if (!empty($id)) {
                $tnx = Transaction::find($id);
            } else {
                return redirect(route('user.token'))->with(['danger' => "Sorry, we're unable to proceed the transaction. This transaction may deleted. Please contact with administrator.", 'modal' => 'danger']);
            }

            if (!blank($tnx)) {
                $_old_status = $tnx->status;
                if ($_old_status == 'deleted' || $_old_status == 'canceled') {
                    $name = "Your transaction is already " . $_old_status . ". Sorry, we're unable to proceed the transaction.";
                } elseif ($_old_status == 'approved') {
                    $name = "Your transaction is already " . $_old_status . ". Please check your account balance.";
                } elseif (!empty($tnx) && ($tnx->status == 'pending' || $tnx->status == 'onhold')) {
                    $tnx->status = 'canceled';
                    $tnx->checked_by = json_encode(['name' => $apv_name, 'id' => data_get($tnx, 'payment_id')]);
                    $tnx->checked_time = Carbon::now()->toDateTimeString();
                    $tnx->save();

                    IcoStage::token_add_to_account($tnx, 'sub');
                    try {
                        $tnx->tnxUser->notify((new TnxStatus($tnx, 'canceled-user')));
                        if (get_emailt('order-rejected-admin', 'notify') == 1) {
                            notify_admin($tnx, 'rejected-admin');
                        }
                    } catch (\Exception $e) {
                        $response['error'] = $e->getMessage();
                    }
                }
            } else {
                $name = "Transaction is not found!!";
            }
        } else {
            $name = "Transaction id or key is not valid!";
        }
        return redirect(route('user.token'))->with(['danger' => $name, 'modal' => 'danger']);
    }
}
