<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Task;

class TaskController extends Controller
{
    public function list(Request $request)
    {
        $params = $request->validate([
            'status' => ['required', Rule::in(Task::getStatusesInKebabCase())],
            'user-sort' => 'required|in:desc,asc'
        ]);
        
        return Task::query()
            ->where('status', '=', Task::getStatusFromKebabCase($params['status']))
            ->orderBy('user_id', $params['user-sort'])
            ->get();
    }

    public function create(Request $request)
    {
        return response($request->user()->tasks()->save(new Task($request->validate([
            'title' => 'required|max:255',
            'description' => 'required'
        ])))->refresh(), 201);
    }

    public function edit(Request $request, Task $task)
    {
        $task->fill($request->validate([
            'title' => 'nullable|max:255',
            'description' => 'nullable'
        ]))->save();

        return ['message' => 'Task successfully edited'];
    }

    public function setStatus(Request $request, Task $task)
    {
        $task->fill($request->validate([
            'status' => ['required', Rule::in(Task::STATUS_ENUM)]
        ]))->save();

        return ['message' => 'Task status successfully set'];
    }

    public function setUser(Request $request, Task $task)
    {
        $task->fill($request->validate([
            'user_id' => 'required|integer|exists:users'
        ]))->save();

        return ['message' => 'Task user successfully set'];
    }

    public function delete(Task $task)
    {
        $task->delete();
        return response(['message' => 'Task deleted'], 200);
    }
}