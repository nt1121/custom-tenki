<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UsersAreaIdPatchRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     *
     */
    public function getUserStoreState()
    {
        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
        ]);
    }

    /**
     *
     */
    public function updateAreaId(UsersAreaIdPatchRequest $request)
    {
        $user = $this->userService->updateAreaId(Auth::user(), $request->area_id);
        return response()->json([
            'user' => $this->userService->getUserStoreState($user),
        ]);
    }

    /**
     *
     */
    public function updatePassword()
    {

    }
}
