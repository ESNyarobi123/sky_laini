<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = LineRequest::with(['customer.user', 'agent.user'])
            ->latest()
            ->paginate(15);
            
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = LineRequest::with(['customer.user', 'agent.user'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }
}
