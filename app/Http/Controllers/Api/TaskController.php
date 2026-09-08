<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // Paginación eficiente (Hallazgo 5) y filtrada por el usuario autenticado
        $tasks = $request->user()->tasks()->latest()->paginate(15);
        
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): TaskResource
    {
        $task = $request->user()->tasks()->create($request->validated());

        return new TaskResource($task);
    }

    public function show(Request $request, Task $task): TaskResource|JsonResponse
    {
        // Verificación de propiedad (evita IDOR si la ruta fuera global)
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource|JsonResponse
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->delete();

        return response()->json(null, 204);
    }
}