<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserTeamResource;
use App\Models\Team;
use App\Models\User;
use App\Models\User_Teams;
use Illuminate\Http\Request;
use Validator;

class UserTeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user_teams = User_Teams::latest()->paginate(5);
            return new UserTeamResource(true, 'List Company', $user_teams);
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
                'team_id' => 'required|int',
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

            $user = User::where("email", "=", $request->email)->first();

            if(!$user){
                return response()->json(['message' => 'User Not Found'], 404);
            }

            $team = Team::find($request->team_id);

            if (!$team) {
                return response()->json(['message' => 'Team Not Found'], 404);
            }

            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $user_team = User_Teams::create(
                [
                    "user_id" => $user->id,
                    "team_id" => $request->team_id,
                    "role" => $request->role
                ]
            );

            return new UserTeamResource(true, 'User_Team Created Successfully', $user_team);

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
            $user_teams = User_Teams::find($id);

            if (!$user_teams) {
                return response()->json(['message' => 'User Team Not Found'], 404);
            }

            return new UserTeamResource(true, 'Detail User Team', $user_teams);
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
            $user_teams = User_Teams::find($id);

            if (!$user_teams) {
                return response()->json(['message' => 'User Team Not Found'], 404);
            }


            $team = Team::find($user_teams->team_id);


            if (!$team) {
                return response()->json(['message' => 'Team Not Found'], 404);
            }

            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $user_teams->update(
                ['role' => $request->role]
            );

            return new UserTeamResource(true, 'Success Update User Team', $user_teams);

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
            $user_teams = User_Teams::find($id);

            if (!$user_teams) {
                return response()->json(['message' => 'User Team Not Found'], 404);
            }

            $team = Team::find($user_teams->team_id);

            if (!$team) {
                return response()->json(['message' => 'Team Not Found'], 404);
            }
            

            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $user_teams->delete();

            return new UserTeamResource(true, 'Success Delete User Team', $user_teams);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }

    private function check_user_authority($companyID, $teamId)
    {
        $isValid = $this->check_company_user($companyID);

        if ($isValid) {
            return true;
        }

        $isValid = $this->check_team_user($teamId);

        if ($isValid) {
            return true;
        }

        return false;
    }

    private function check_company_user($id): bool
    {
        $user = auth('sanctum')->user()->user_companies()->where('company_id', '=', $id)->first();
        if (!$user)
            return false;

        $userRole = $user->role;

        if ($userRole != 'King' && $userRole != 'QuestKeeper') {
            return false;
        }

        return true;
    }

    private function check_team_user($id)
    {
        $user = auth('sanctum')->user()->user_teams()->where('team_id', '=', $id)->first();

        if (!$user)
            return true;

        $userRole = $user->role;

        if ($userRole != "Master Plan")
            return false;

        return true;
    }
}
