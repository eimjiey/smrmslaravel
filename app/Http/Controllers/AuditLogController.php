<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncidentAuditLog; 
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = IncidentAuditLog::query()
            ->with(['incident:id,description']) 
            ->orderBy('changed_at', 'desc')
            ->paginate(15); 

        return response()->json($logs);
    }
}