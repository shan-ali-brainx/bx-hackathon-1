<?php

namespace App\Enums;

enum GeneratedTaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In progress',
            self::Blocked => 'Blocked',
            self::Done => 'Completed',
        };
    }
}
