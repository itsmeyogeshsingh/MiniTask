<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('searchTask');
        $priority = $request->input('priorityFilter');

        $query = Task::query();

        if ($searchTerm) {
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        $query->orderBy('created_at', 'desc');

        $tasks = $query->paginate(5);

        return response()->json($tasks);
    }

    public function show($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function edit($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json($task);
    }

    public function update($id, Request $request)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'priority' => 'in:Low,Medium,High',
            'due_date' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->all());
        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $task = Task::create($request->all());

            return response()->json(['status' => 'success', 'message' => 'Task created successfully', 'task' => $task], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred while creating the task.'], 500);
        }
    }



    public function updateTaskStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:Pending,In Progress,Completed',
        ]);

        $task = Task::find($id);
        if ($task) {
            $task->status = $request->status;
            $task->save();
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Task not found']);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully'], 204);
    }

}
