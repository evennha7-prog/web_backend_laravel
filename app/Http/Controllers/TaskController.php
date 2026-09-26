<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TaskResource;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of tasks with filters, sorting, and search.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Build query using clean scopes
        $tasks = Task::forUser($userId)
            ->with('category')
            ->filterStatus($request->query('status'))
            ->filterCategory($request->query('category_id'))
            ->filterPriority($request->query('priority'))
            ->search($request->query('search'))
            ->applySorting($request->query('sort_by'), $request->query('sort_order'))
            ->get();

        // Calculate summary counts for UI badges
        $today = Carbon::today()->toDateString();
        $stats = [
            'total' => Task::forUser($userId)->count(),
            'pending' => Task::forUser($userId)->where('status', 'pending')->count(),
            'completed' => Task::forUser($userId)->where('status', 'completed')->count(),
            'overdue' => Task::forUser($userId)
                ->where('status', 'pending')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $today)
                ->count(),
        ];

        return response()->json([
            'status' => true,
            'data' => TaskResource::collection($tasks),
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $validated = $request->validated();

        $task = Task::create([
            'user_id' => $userId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $task->load('category');

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully.',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $task = Task::forUser($request->user()->id)
            ->with('category')
            ->find($id);

        if (!$task) {
            return $this->error('Task not found.', 404);
        }

        return response()->json([
            'status' => true,
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        $task = Task::forUser($request->user()->id)->find($id);

        if (!$task) {
            return $this->error('Task not found.', 404);
        }

        $task->update($request->validated());
        $task->load('category');

        return response()->json([
            'status' => true,
            'message' => 'Task updated successfully.',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Toggle the status between 'pending' and 'completed'.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $task = Task::forUser($request->user()->id)->find($id);

        if (!$task) {
            return $this->error('Task not found.', 404);
        }

        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        $task->update(['status' => $newStatus]);
        $task->load('category');

        return response()->json([
            'status' => true,
            'message' => "Task marked as {$newStatus}.",
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = Task::forUser($request->user()->id)->find($id);

        if (!$task) {
            return $this->error('Task not found.', 404);
        }

        $task->delete();

        return $this->success(null, 'Task deleted successfully.');
    }

    /**
     * Get detailed analytics for the dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $today = Carbon::today()->toDateString();

        $total = Task::forUser($userId)->count();
        $pending = Task::forUser($userId)->where('status', 'pending')->count();
        $completed = Task::forUser($userId)->where('status', 'completed')->count();
        $overdue = Task::forUser($userId)
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->count();

        $priorityBreakdown = [
            'high' => Task::forUser($userId)->where('priority', 'high')->count(),
            'medium' => Task::forUser($userId)->where('priority', 'medium')->count(),
            'low' => Task::forUser($userId)->where('priority', 'low')->count(),
        ];

        $categories = Category::where('user_id', $userId)
            ->withCount('tasks')
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'total' => $total,
                'pending' => $pending,
                'completed' => $completed,
                'overdue' => $overdue,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                'priorities' => $priorityBreakdown,
                'categories' => CategoryResource::collection($categories),
            ],
        ]);
    }
}
