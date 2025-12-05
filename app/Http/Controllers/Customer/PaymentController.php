<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = auth()->user()->customer->lineRequests()
            ->whereNotNull('payment_order_id')
            ->with('payment') // Assuming relationship exists or we use lineRequests directly
            ->latest()
            ->paginate(10);

        return view('customer.payments.index', compact('payments'));
    }
}
