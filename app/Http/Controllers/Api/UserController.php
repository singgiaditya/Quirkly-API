<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::latest()->paginate(5);
            return new UserResource(true, 'List data', $users);
        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return new UserResource(true, 'Detail User', $user);

        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        try {

            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->delete();
            return new UserResource(true, 'Success Delete User!', $user);

        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    public function update(Request $request ,$id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone_number' => 'string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            $response = [
                'Message' => "Validation Error",
                'error' => $errors
            ];
            return response()->json($response, 400);
        }

        try {

            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->update(
                [
                    'name' => $request->name ?? $user['name'],
                    'phone_number' => $request->phone_number ?? $user['phone_number'],
                ]
            );
            return new UserResource(true, 'Success Update User!', $user);


        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }
}
