<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(5);

        return new UserResource(true, 'List Data Users', $users);
    }

    public function show($id)
    {
        $user = User::where('id', "=" , '1')->first();
        
        if ($user) {
            // return response()->json(['data' => $user], 404);
            return new UserResource(true, 'Detail User',$user);
        } else {
            return response()->json(['message' => 'Post not found'], 404);
        }
    }
}
