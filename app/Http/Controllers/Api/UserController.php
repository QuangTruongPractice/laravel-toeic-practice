<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttemptResource;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the authenticated user's exam attempts history.
     */
    public function history(Request $request)
    {
        $user = $request->user();

        $attempts = ExamAttempt::where('user_id', $user->id)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->get();

        return AttemptResource::collection($attempts);
    }
}
