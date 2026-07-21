<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if (! $user) {
            return response()->json(['message' => 'ユーザーが存在しません'], 404);
        }

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => '不正なアクセスです'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => '既に認証済みです']);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'メール認証が完了しました']);
    }
}
