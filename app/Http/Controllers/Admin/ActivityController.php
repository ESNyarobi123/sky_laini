<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LineRequest;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = LineRequest::with(['customer.user', 'agent.user'])
            ->latest()
            ->paginate(20);
            
        return view('admin.activity.index', compact('activities'));
    }
}
