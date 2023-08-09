<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UnregisterDeleteRequest;
use Illuminate\Support\Facades\Hash;
use App\Services\UserService;

class UnregisterController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * 
     */
    public function show()
    {
        $loginUser = Auth::user();

        if ($loginUser->is_test_user) {
            return redirect('/weather');
        }

        return view('unregister.show', ['loginUserId' => $loginUser->id]);
    }

    /**
     * 
     */
    public function unregister(UnregisterDeleteRequest $request)
    {
        $loginUser = Auth::user();

        if (!Hash::check($request->password, $loginUser->password)) {
            return back()->withErrors(['password' => 'パスワードが間違っています。']);
        }

        if (!$this->userService->unregister($loginUser->id, $loginUser->email)) {
            return back()->with('alert', ['msg' => 'アカウントの削除に失敗しました。', 'type' => 'error']);
        }

        return view('unregister.complete');
    }
}
