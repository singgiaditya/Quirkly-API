<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompaniesResource;
use App\Models\Companies;
use App\Models\User_Companies;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class CompaniesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // $companies = Companies::with('createdBy')->latest()->paginate(5);
            $user = auth('sanctum')->user();
            $user_companies = User_Companies::where('user_id', '=', $user->id)->with('company')->get();
            $company = [];
            foreach ($user_companies as $value) {
                $company[]= $value->company->with('createdBy')->first();
            }
            return new CompaniesResource(true, 'List Company', $company);
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
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $response = [
                    'Message' => "Validation Error",
                    'error' => $errors
                ];
                return response()->json($response, 400);
            }

            $userId = auth('sanctum')->user()->id;

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('public/images');
                $imagePath = basename($imagePath); // Simpan hanya nama file
            }


            $companies = Companies::create(
                [
                    "name" => $request->name,
                    "description" => $request->description,
                    "created_by" => $userId,
                    "image" => $imagePath
                ]
            );

            User_Companies::create(
                [
                    'user_id' => $userId,
                    'company_id' => $companies->id,
                    'role' => 'King',
                    'joined_at' => Carbon::now(),
                ]
            );

            return new CompaniesResource(true, 'Company Created Successfully', $companies);

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
            $company = Companies::find($id);

            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }

            return new CompaniesResource(true, 'Detail Company', $company);

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
            'name' => 'string|max:255',
            'description' => 'string',
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

            $company = Companies::find($id);

            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }

            $isValid = $this->check_company_user($id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $company->update(
                [
                    'name' => $request->name ?? $company['name'],
                    'description' => $request->description ?? $company['description'],
                ]
            );
            return new CompaniesResource(true, 'Success Update Company!', $company);


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

            $company = Companies::find($id);

            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }

            $isValid = $this->check_company_user($id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }


            $company->delete();
            return new CompaniesResource(true, 'Success Delete Company!', $company);

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
