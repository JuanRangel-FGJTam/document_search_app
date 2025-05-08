<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Level;
use App\Models\Menu;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }
    //
    public function index(Request $request)
    {
        $search = $request->search;
        $totalUsers = $this->userService->getAll(
            ['id', 'name', 'email', 'is_admin'],
        );
        $users = \App\Support\Pagination::paginate($totalUsers, $request);
        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    private function paginate($items, Request $request, $perPage = 15)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $currentPageItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = $this->userService->store($request->all());

        return to_route('users.index');
    }


    public function edit(string $user_id)
    {
        $user = $this->userService->getById(
            $user_id,
            ['id', 'name', 'email', 'is_admin'],
        );

        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }


    public function update(Request $request, string $user_id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id,
        ]);

        $user = $this->userService->update($request->all(), $user_id);
        $user = $this->userService->getById(
            $user_id,
            ['id', 'name', 'email', 'is_admin']
        );

        if (!empty($request['password'])) {
            $user->password = Hash::make($request['password']);
            $user->save();
        }
        return to_route('users.index');
    }

    public function delete(string $user_id)
    {
        $user = $this->userService->delete($user_id);
        Log::info('User deleted', ['id' => $user_id]);
        return to_route('users.index');
    }

    public function refund(string $user_id)
    {
        $user = User::onlyTrashed()->find($user_id);

        if ($user) {
            $user->restore();
            Log::info('User refunded', ['id' => $user_id]);
        }
        return to_route('users.index');
    }

    public function getApi()
    {
        $user = \App\Models\User::where('email', auth()->user()->email)->first();
        $token = JWTAuth::fromUser($user, ['exp' => now()->addYear()->timestamp]);
        return Inertia::render('API', [
            'token' => $token,
            'user' => $user,
        ]);
    }
}
