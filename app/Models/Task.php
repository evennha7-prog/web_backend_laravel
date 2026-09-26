<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    /**
     * Attribute type casting.
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relationship: Belongs to User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Belongs to Category (optional).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope: Filter by authenticated user ID.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by status ('pending' or 'completed').
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        if (!empty($status) && in_array($status, ['pending', 'completed'])) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter by category ID.
     */
    public function scopeFilterCategory(Builder $query, mixed $categoryId): Builder
    {
        if (!empty($categoryId) && is_numeric($categoryId)) {
            return $query->where('category_id', (int) $categoryId);
        }
        return $query;
    }

    /**
     * Scope: Filter by priority ('low', 'medium', 'high').
     */
    public function scopeFilterPriority(Builder $query, ?string $priority): Builder
    {
        if (!empty($priority) && in_array($priority, ['low', 'medium', 'high'])) {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    /**
     * Scope: Search in title and description.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!empty($term)) {
            $cleaned = trim($term);
            return $query->where(function (Builder $q) use ($cleaned) {
                $q->where('title', 'like', "%{$cleaned}%")
                  ->orWhere('description', 'like', "%{$cleaned}%");
            });
        }
        return $query;
    }

    /**
     * Scope: Apply safe ordering.
     */
    public function scopeApplySorting(Builder $query, ?string $sortBy, ?string $sortOrder): Builder
    {
        $direction = strtolower($sortOrder ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedColumns = ['created_at', 'due_date', 'priority', 'title', 'status'];

        if (in_array($sortBy, $allowedColumns)) {
            if ($sortBy === 'due_date') {
                return $query->orderByRaw("due_date IS NULL ASC, due_date {$direction}");
            }
            return $query->orderBy($sortBy, $direction);
        }

        return $query->orderBy('created_at', 'desc');
    }
}
