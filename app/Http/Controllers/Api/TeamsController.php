<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Companies;
use App\Models\Team;
use App\Models\User_Teams;
use Illuminate\Http\Request;
use Validator;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = auth('sanctum')->user();
            $user_teams = User_Teams::where('user_id', '=', $user->id)->with('teams')->get();
            return new TeamResource(true, 'List Teams', $user_teams);
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
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|int',
            'name' => 'required|string',
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

        try {
            $company = Companies::find($request->company_id);

            if (!$company) {
                return response()->json(['message' => 'Company not found'], 404);
            }
            
            $isValid = $this->check_company_user($request->company_id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('public/images');
                $imagePath = basename($imagePath); // Simpan hanya nama file
            }

            $team = Team::create(
                [
                    'company_id' => $request->company_id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'image' => $imagePath
                ]
            );

            return new TeamResource(true, 'Team Created Successfully', $team);

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
            $team = Team::find($id);

            if (!$team) {
                return response()->json(['message' => 'Team not found'], 404);
            }

            return new TeamResource(true, 'Detail Team', $team);

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
            'name' => 'string',
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
            $team = Team::find($id);

            if (!$team) {
                return response()->json(['message' => 'Team not found'], 404);
            }
            
            $isValid = $this->check_company_user($team->company_id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $team->update(
                [
                    'name' => $request->name ?? $team->name,
                    'description' => $request->description ?? $team->description
                ]
            );

            return new TeamResource(true, 'Team Updated Successfully', $team);

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

            $team = Team::find($id);

            if (!$team) {
                return response()->json(['message' => 'Team not found'], 404);
            }

            $isValid = $this->check_company_user($team->company_id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }


            $team->delete();
            return new TeamResource(true, 'Success Delete Team!', $team);

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
