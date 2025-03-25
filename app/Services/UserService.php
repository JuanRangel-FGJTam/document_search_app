<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{

    public function getAll($select = ['*'], $with = [])
    {
        return User::select($select)->with($with)->get();
    }

    public function getAllTrashed($select = ['*'], $with = [])
    {
        return User::select($select)->with($with)->onlyTrashed()->get();
    }

    public function getById(string $user_id, $select = ['*'], $with = [])
    {
        return User::select($select)->with($with)->where('id', $user_id)->first();
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function store(array $RequestData)
    {
        return User::create([
            'name' => $RequestData['name'],
            'email' => $RequestData['email'],
            'password' => Hash::make($RequestData['password']),
            'is_admin' => $RequestData['is_admin'] ? 1 : 0
        ]);
    }

    public function update(array $RequestData, string $user_id)
    {
        return User::where('id', $user_id)->update([
            'name' => $RequestData['name'],
            'email' => $RequestData['email'],
            'is_admin' => $RequestData['is_admin'] ? 1 : 0
        ]);
    }

    public function delete(string $user_id)
    {
        $user = User::find($user_id);
        $user->delete();
        return back()->with('success');
    }
}
