<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCompaniesResource;
use App\Models\User;
use App\Models\User_Companies;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class UserCompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user_companies = User_Companies::latest()->paginate(5);
            return new UserCompaniesResource(true, 'List Company', $user_companies);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'company_id' => 'required|int',
                'role' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $response = [
                    'Message' => "Validation Error",
                    'error' => $errors
                ];
                return response()->json($response, 400);
            }

            $isValid = $this->check_company_user($request->company_id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $user = User::where("email", "=", $request->email)->first();

            if(!$user){
                return response()->json(['message' => 'User Not Found'], 404);
            }

            $user_companies =  User_Companies::create(
                [
                    'user_id' => $user->id,
                    'company_id' => $request->company_id,
                    'role' => $request->role,
                    'joined_at' => Carbon::now(),
                ]
            );

            return new UserCompaniesResource(true, 'Company Created Successfully', $user_companies);

        } catch (\Throwable $th) {
            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user_companies = User_Companies::find($id);

            if (!$user_companies) {
                return response()->json(['message' => 'User Company Not Found'], 404);
            }

            return new UserCompaniesResource(true, 'Detail User Team', $user_companies);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string',
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

            $user_companies = User_Companies::find($id);

            if (!$user_companies) {
                return response()->json(['message' => 'User Companies not found'], 404);
            }

            $isValid = $this->check_company_user($user_companies->company_id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $user_companies->update(
                [
                    'role' => $request->role
                ]
            );
            return new UserCompaniesResource(true, 'Success Update Company!', $user_companies);


        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $user_companies = User_Companies::find($id);

            if (!$user_companies) {
                return response()->json(['message' => 'User Companies not found'], 404);
            }

            $isValid = $this->check_company_user($id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }


            $user_companies->delete();
            return new UserCompaniesResource(true, 'Success Delete User Companies!', $user_companies);

        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    private function check_company_user($id): bool
    {
        $user = auth('sanctum')->user()->user_companies()->where('company_id', '=', $id)->first();
        if (!$user)
            return false;
        $userRole = $user->role;

        if ($userRole != 'King' && $userRole != 'QuestKeeper')
            return false;

        return true;
    }

}
