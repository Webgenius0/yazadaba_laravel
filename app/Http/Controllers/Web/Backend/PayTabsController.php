<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayTabsController extends Controller
{
    // Show payment form
    public function showPaymentPage(Request $request)
    {
        $amount = $request->input('amount', 100); // Default amount of 100
        $orderId = Str::uuid(); // Unique order ID
        $email = $request->user()->email ?? 'test@example.com'; // Use authenticated user's email or a default

        return view('paytabs.payment', compact('amount', 'orderId', 'email'));
    }

    // Handle the PayTabs callback
    public function paymentCallback(Request $request)
    {
        $status = $request->input('status');
        $transactionId = $request->input('transaction_id');
        $orderId = $request->input('order_id');

        if ($status == 'success') {
            // Update order status in the database
            // Example: $order = Order::where('order_id', $orderId)->first();
            // $order->update(['status' => 'paid', 'transaction_id' => $transactionId]);

            return redirect()->route('payment.success');
        } else {
            return redirect()->route('payment.failed');
        }
    }

    // Payment success page
    public function paymentSuccess()
    {
        return view('backend.layout.paytabs.success');
    }

    // Payment failed page
    public function paymentFailed()
    {
        return view('backend.layout.paytabs.failed');
    }
}
