<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;

class LoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized: You do not have permission to view the Login History.'
            ], 403);
        }

        try {
            $logs = LoginHistory::query()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($logs);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('LoginHistory SQL Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Database error during log retrieval.'
            ], 500);
        }
    }
}
