<?php

/**
 * Stripe Payment Module
 * @version v1.1.0
 * @since v1.3.1
 */

namespace App\PayModule\Stripe;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\PayModule\ModuleHelper;
use App\PayModule\Stripe\StripePayment;
use App\Http\Controllers\Controller;

class StripeController extends Controller
{
    private $instance;

    public function __construct(StripePayment $stripeInstance)
    {
        $this->instance = $stripeInstance;
    }

    public function success(Request $request)
    {
        if (method_exists($this->instance, 'success')) {
            return $this->instance->success($request);
        }
    }

    public function cancel(Request $request)
    {
        if (method_exists($this->instance, 'cancel')) {
            return $this->instance->cancel($request);
        }
    }

    public function email_notify(Request $request)
    {
        $tnx_id = isset($request->tnx) ? $request->tnx : false;
        $mail_type = isset($request->notify) ? $request->notify : false;

        if ($tnx_id && $mail_type) {
            $tnx = Transaction::where('id', $tnx_id)->where('user', auth()->user()->id)->first();
            if (empty($tnx)) {
                return false;
            }
            return ModuleHelper::enotify($tnx, $mail_type, $request);
        }
        return false;
    }
}
