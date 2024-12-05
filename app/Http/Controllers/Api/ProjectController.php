<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $projects = Project::latest()->paginate(5);
            return new ProjectResource(true, 'List Company', $projects);
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
                'team_id' => 'required|int',
                'name' => 'required|string',
                'description' => 'required|string',
                'start_date' => 'required|date_format:"Y-m-d H:i:s"',
                'end_date' => 'required|date_format:"Y-m-d H:i:s"',
                'status' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $response = [
                    'Message' => "Validation Error",
                    'error' => $errors
                ];
                return response()->json($response, 400);
            }

            $team = Team::find($request->team_id);

            if (!$team) {
                return response()->json(['message' => 'Team not found'], 404);
            }

            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $project =  Project::create(
                [
                    'team_id' => $request->team_id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => $request->status,
                ]
            );

            return new ProjectResource(true, 'Project Created Successfully', $project);

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
            $project = Project::where('team_id', '=', $id);

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            return new ProjectResource(true, 'Detail Project', $project);

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
            'start_date' => 'date_format:"Y-m-d H:i:s"',
            'end_date' => 'date_format:"Y-m-d H:i:s"',
            'status' => 'string',
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
            $project = Project::find($id);

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            $team = Team::find($project->team_id);

            if (!$team) {
                return response()->json(['message' => 'Team not found'], 404);
            }
            
            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $project->update(
                [
                    'name' => $request->name ?? $project->name,
                    'description' => $request->description ?? $project->description,
                    'start_date' => $request->start_date ?? $project->start_date,
                    'end_date' => $request->end_date ?? $project->end_date,
                    'status' => $request->status ?? $project->status
                ]
            );

            return new ProjectResource(true, 'Team Updated Successfully', $project);

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

            $project = Team::find($id);

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            $team = Team::find($project->team_id);

            if (!$team) {
                return response()->json(['message' => 'Team not found'], 404);
            }
            
            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }
            


            $project->delete();
            return new ProjectResource(true, 'Success Delete Team!', $project);

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
