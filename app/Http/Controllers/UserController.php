<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function list()
    {
        return User::paginate(10);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|max:32',
            'last_name' => 'required|max:32',
            'email' => 'required|max:60|email:rfc,dns|unique:users',
            'password' => 'required|max:32'
        ]);

        $data['password'] = User::hashPassword($data['password']);
        $data['api_token'] = User::generateApiToken();

        $user = new User($data);
        $user->save();
        return response($user, 201);
    }

    public function editSelf(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => 'nullable|min:1|max:32',
            'last_name' => 'nullable|min:1|max:32',
            'email' => "nullable|max:60|email:rfc,dns|unique:users,email,{$user->user_id},user_id",
            'password' => 'nullable|max:32'
        ]);

        if (isset($data['password'])) $data['password'] = User::hashPassword($data['password']);

        $responseData = ['message' => 'User successfully edited'];
        if (isset($data['email']) || isset($data['password']))
            $data['api_token'] = $responseData['new_token'] = User::generateApiToken();

        $user->fill($data)->save();
        return $responseData;
    }

    public function deleteSelf()
    {
        Auth::user()->delete();
        return ['message' => 'User deleted'];
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|exists:users',
            'password' => 'required'
        ]);

        if (Auth::attempt($data)) {
            $user = User::where('email', $data['email'])->first();
            return ['api_token' => $user->api_token];
        } else {
            $validator = Validator::make([], []);
            $validator->errors()->add('common', 'Incorrect email or password');
            throw new ValidationException($validator);
        }
    }

    public function item(User $user)
    {
        return $user;
    }
}