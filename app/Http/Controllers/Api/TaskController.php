<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\Request;
use Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $tasks = Task::latest()->paginate(5);
            return new TaskResource(true, 'List Task', $tasks);
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
                'project_id' => 'required|int',
                'title' => 'required|string',
                'description' => 'required|string',
                'priority' => 'required|string',
                'status' => 'required|string',
                'due_date' => 'required|date_format:"Y-m-d H:i:s"',
                'assign_to' => 'required|int',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $response = [
                    'Message' => "Validation Error",
                    'error' => $errors
                ];
                return response()->json($response, 400);
            }

            $project = Project::find($request->project_id);

            if (!$project) {
                return response()->json(['message' => 'Project not found'], 404);
            }

            $team = Team::find($project->team_id);

            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $project =  Task::create(
                [
                    'project_id' => $request->project_id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'due_date' => $request->due_date,
                    'assign_to' => $request->assign_to,
                ]
            );

            return new TaskResource(true, 'Task Created Successfully', $project);

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
            $task = Task::where("project_id", '=', $id);

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            return new TaskResource(true, 'Detail Task', $task);

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
            'title' => 'string',
            'description' => 'string',
            'priority' => 'string',
            'status' => 'string',
            'due_date' => 'date_format:"Y-m-d H:i:s"',
            'assign_to' => 'int',
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
            $task = Task::find($id);

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $project = Project::find($task->project_id);

            $team = Team::find($project->team_id);
            
            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }

            $task->update(
                [
                    'title' => $request->title ?? $project->title,
                    'description' => $request->description ?? $project->description,
                    'priority' => $request->priority ?? $project->priority,
                    'status' => $request->status ?? $project->status,
                    'due_date' => $request->due_date ?? $project->due_date,
                    'assign_to' => $request->assign_to ?? $project->assign_to,
                ]
            );

            return new TaskResource(true, 'Team Updated Successfully', $task);

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

            $task = Task::find($id);

            if (!$task) {
                return response()->json(['message' => 'task not found'], 404);
            }
            
            $project = Project::find($task->project_id);

            $team = Team::find($project->team_id);
            
            $isValid = $this->check_user_authority($team->company_id, $team->id);

            if (!$isValid) {
                return response()->json(['message' => 'You Dont Have Access To Do It'], 403);
            }
            
            $task->delete();

            return new TaskResource(true, 'Success Delete Task!', $task);

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
