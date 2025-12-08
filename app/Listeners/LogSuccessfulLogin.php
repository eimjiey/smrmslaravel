<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    public function __construct(public Request $request) {}

    public function handle(Login $event): void
    {
        LoginHistory::create([
            'user_id' => $event->user->id,
            'email_attempted' => $event->user->email,
            'device' => $this->request->header('User-Agent'),
            'status' => 'success',
        ]);
    }
}