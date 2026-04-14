<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserType;
use App\Models\Status;
use App\Http\Resources\PendingUserResource;
use App\Http\Resources\PendingUserDetailResource;
use Inertia\Response;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->where('user_type_id', UserType::BASIC)
            ->where('status_id', Status::PENDING_FOR_MEMBER)
            ->with([
                'status:id,name',
                'userType:id,name',
            ])
            ->get();

        return Inertia::render('dashboard/Index', [
            'pendingUsers' => PendingUserResource::collection($users),
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'status:id,name',
            'userType:id,name',
        ]);

        return PendingUserDetailResource::make($user);
    }
}
