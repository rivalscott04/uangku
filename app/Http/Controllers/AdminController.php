<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil user terbaru, paginasi
        // Eager load subscription/transaction jika perlu nanti
        $users = User::latest()->paginate(10);

        return view('admin.dashboard', compact('users'));
    }

    /**
     * Toggle status billing exempt user.
     */
    public function toggleBillingExempt(User $user)
    {
        $user->billing_exempt = ! $user->billing_exempt;
        $user->save();

        $status = $user->billing_exempt ? 'User now exempt from billing.' : 'User now subject to billing.';

        return back()->with('status', $status);
    }
}
