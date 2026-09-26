<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Demo User
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Alex Taylor',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Seed Categories
        $categoriesData = [
            ['name' => 'Frontend Development', 'color' => '#6366f1', 'icon' => 'layout'],
            ['name' => 'Backend & API', 'color' => '#10b981', 'icon' => 'server'],
            ['name' => 'UI/UX Design', 'color' => '#ec4899', 'icon' => 'palette'],
            ['name' => 'Database & Security', 'color' => '#f59e0b', 'icon' => 'database'],
            ['name' => 'Team & Review', 'color' => '#8b5cf6', 'icon' => 'users'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[$data['name']] = Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                ['color' => $data['color'], 'icon' => $data['icon']]
            );
        }

        // Seed Tasks
        $tasks = [
            [
                'title' => 'Implement JWT Authentication & Refresh Tokens',
                'description' => 'Build token generator, middleware verification, and secure Bearer token headers.',
                'category_id' => $categories['Backend & API']->id,
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => Carbon::yesterday()->toDateString(),
            ],
            [
                'title' => 'Design Task Management Dashboard UI',
                'description' => 'Create a modern, clean responsive layout with stats cards, filter pills, and task list.',
                'category_id' => $categories['UI/UX Design']->id,
                'status' => 'completed',
                'priority' => 'medium',
                'due_date' => Carbon::today()->toDateString(),
            ],
            [
                'title' => 'Connect React Frontend to Laravel RESTful API',
                'description' => 'Integrate Axios/fetch service with JWT auth headers, error handling, and toast alerts.',
                'category_id' => $categories['Frontend Development']->id,
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => Carbon::tomorrow()->toDateString(),
            ],
            [
                'title' => 'Add Task Filter & Sorting by Status and Priority',
                'description' => 'Enable dynamic filtering by Pending/Completed status and sort by due dates or priorities.',
                'category_id' => $categories['Frontend Development']->id,
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => Carbon::now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Optimize SQLite Database Queries & Indexing',
                'description' => 'Review foreign keys on tasks and categories, benchmark query performance with eager loading.',
                'category_id' => $categories['Database & Security']->id,
                'status' => 'pending',
                'priority' => 'medium',
                'due_date' => Carbon::now()->addDays(4)->toDateString(),
            ],
            [
                'title' => 'Conduct Sprint Demo & Team Code Review',
                'description' => 'Walk through fullstack Task Management CRUD functionality with the team.',
                'category_id' => $categories['Team & Review']->id,
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => Carbon::now()->addDays(5)->toDateString(),
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $taskData['title'],
                ],
                $taskData
            );
        }
    }
}
