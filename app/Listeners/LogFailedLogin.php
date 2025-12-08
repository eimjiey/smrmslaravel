<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class LogFailedLogin
{
    public function __construct(public Request $request) {}

    public function handle(Failed $event): void
    {
        // Get the email from the credentials array, as $event->user is null on failure.
        $emailAttempted = $event->credentials['email'] ?? 'Unknown';

        LoginHistory::create([
            'user_id' => null, // Crucial: user_id is NULL for failed attempts
            'email_attempted' => $emailAttempted,
            'device' => $this->request->header('User-Agent'),
            'status' => 'failure',
        ]);
    }
}