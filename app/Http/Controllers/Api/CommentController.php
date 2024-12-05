<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\comment;
use Illuminate\Http\Request;
use Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $comments = Comment::latest()->paginate(5);
            return new CommentResource(true, 'List Comment', $comments);
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
                'task_id' => 'required|int',
                'user_id' => 'required|int',
                'comment' => 'required|string',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $response = [
                    'Message' => "Validation Error",
                    'error' => $errors
                ];
                return response()->json($response, 400);
            }

            $comment =  Comment::create(
                [
                    'task_id' => $request->task_id,
                    'user_id' => $request->user_id,
                    'comment' => $request->comment
                ]
            );

            return new CommentResource(true, 'Comment Created Successfully', $comment);

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
            $comment = Comment::where("task_id", '=', $id);

            if (!$comment) {
                return response()->json(['message' => 'Comment not found'], 404);
            }

            return new CommentResource(true, 'Detail Task', $comment);

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
            'comment' => 'required|string',
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
            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json(['message' => 'Comment not found'], 404);
            }

            $comment->update(
                [
                    'comment' => $request->comment
                ]
            );

            return new CommentResource(true, 'Comment Updated Successfully', $comment);

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
            $comment = Comment::find($id);

            if (!$comment) {
                return response()->json(['message' => 'Comment not found'], 404);
            }

            $comment->delete();

            return new CommentResource(true, 'Detail Task', $comment);

        } catch (\Throwable $th) {

            $response = [
                'message' => 'Something Went Wrong',
                'error' => $th
            ];

            return response()->json($response, 500);
        }
    }
}
