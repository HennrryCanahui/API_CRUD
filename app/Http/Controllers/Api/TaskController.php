<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(): JsonResponse
    {
        $tasks = auth()->user()->tasks()->with('category')->orderBy('created_at', 'desc')->get();
        
        $formatted = $tasks->map(function ($task) {
            return [
                'id' => (string) $task->id,
                'title' => $task->title,
                'categoryId' => $task->category_id ? (string) $task->category_id : null,
                'completed' => (bool) $task->completed,
                'category_name' => $task->category ? $task->category->name : null,
                'category_color' => $task->category ? $task->category->color : null,
            ];
        });

        return response()->json($formatted);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        if ($request->category_id) {
            $category = auth()->user()->categories()->find($request->category_id);
            if (!$category) {
                return response()->json(['message' => 'Category not found or unauthorized'], 422);
            }
        }

        $task = auth()->user()->tasks()->create($request->validated());
        $task->load('category');
        
        return response()->json([
            'id' => (string) $task->id,
            'title' => $task->title,
            'categoryId' => $task->category_id ? (string) $task->category_id : null,
            'completed' => (bool) $task->completed,
            'category_name' => $task->category ? $task->category->name : null,
            'category_color' => $task->category ? $task->category->color : null,
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $task->load('category');
        return response()->json([
            'id' => (string) $task->id,
            'title' => $task->title,
            'categoryId' => $task->category_id ? (string) $task->category_id : null,
            'completed' => (bool) $task->completed,
            'category_name' => $task->category ? $task->category->name : null,
            'category_color' => $task->category ? $task->category->color : null,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->has('category_id') && $request->category_id) {
            $category = auth()->user()->categories()->find($request->category_id);
            if (!$category) {
                return response()->json(['message' => 'Category not found or unauthorized'], 422);
            }
        }

        // Handle case where category_id is explicitly set to null
        if ($request->has('category_id') && $request->category_id === null) {
            $task->category_id = null;
        }

        $task->update($request->validated());
        $task->load('category');
        
        return response()->json([
            'id' => (string) $task->id,
            'title' => $task->title,
            'categoryId' => $task->category_id ? (string) $task->category_id : null,
            'completed' => (bool) $task->completed,
            'category_name' => $task->category ? $task->category->name : null,
            'category_color' => $task->category ? $task->category->color : null,
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();
        return response()->json(null, 204);
    }
}