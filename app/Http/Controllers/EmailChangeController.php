<?php

namespace App\Http\Controllers;

use App\Models\EmailChangeRequest;
use App\Services\EmailChangeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailChangeController extends Controller
{
    public function __construct(
        protected EmailChangeService $emailChangeService
    ) {
    }

    /**
     * 会員のメールアドレスを変更する
     *
     * @param  string $token 確認URLのトークン
     * @return Illuminate\View\View
     */
    public function changeUserEmail(string $token): View
    {
        $emailChangeRequest = EmailChangeRequest::where('token', $token)->where('expires_at', '>=', date('Y-m-d H:i:s'))->first();

        if (empty($emailChangeRequest)) {
            return view('expired_url');
        }

        $user = $emailChangeRequest->user;

        // データベースの外部キー制約があるため、基本的には紐づくユーザーが登録されていないことはありえない
        if (empty($user)) {
            return view('expired_url');
        }

        $loginUser = Auth::user();

        if ($loginUser && $loginUser->id !== $user->id) {
            return view('need_logout', ['isEmailChange' => true]);
        }

        if (!$this->emailChangeService->changeUserEmail($user, $emailChangeRequest->email, $emailChangeRequest->id)) {
            return view('email_change.fail');
        }

        return view('email_change.complete');
    }
}
