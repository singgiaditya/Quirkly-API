<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($errors->unique()) {
                $response = [
                    'Message' => "Email has already taken",
                    'error' => $errors
                ];

                return response()->json($response, 409);
            }

            $response = [
                'Message' => "Validation Error",
                'error' => $errors
            ];
            return response()->json($response, 400);
        }

        try {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $user->__unset('password');

            $response = [
                'message' => 'User registered successfully',
                'token' => $user->createToken('quirkly')->plainTextToken,
                'data' => $user
            ];

            return response()->json($response, 200);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
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
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $user->__unset('password');

                $response = [
                    'message' => 'Login successfully',
                    'token' => $user->createToken('quirkly')->plainTextToken,
                    'data' => $user
                ];
                
                return response()->json($response, 200);
            } else {
                $response = [
                    'message' => 'Email or Password is wrong',
                ];
                return response()->json($response, 400);
            }
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }
}
