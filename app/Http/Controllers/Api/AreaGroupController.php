<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AreaGroupService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class AreaGroupController extends Controller
{
    public function __construct(
        protected AreaGroupService $areaGroupService,
        protected UserService $userService,
    )
    {
    }

    public function getAreaGroupAndChildren(?int $id = null)
    {
        return response()->json([
            'user' => $this->userService->getUserStoreState(Auth::user()),
            'area_group' => $this->areaGroupService->getAreaGroupAndChildren($id),
        ]);
    }
}
