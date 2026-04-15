<?php

namespace App\Models;

use App\Enums\GeneratedTaskStatus;
use Database\Factories\GeneratedTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_name',
    'description',
    'technology_stack',
    'dependencies',
    'status',
    'assigned_to',
])]
class GeneratedTask extends Model
{
    /** @use HasFactory<GeneratedTaskFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'technology_stack' => 'array',
            'dependencies' => 'array',
            'status' => GeneratedTaskStatus::class,
        ];
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
