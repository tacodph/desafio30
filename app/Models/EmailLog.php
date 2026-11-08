<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['recipients' => 'array', 'sender' => 'array', 'reply_to' => 'array', 'attempted_at' => 'datetime', 'sent_at' => 'datetime'];
    }
}
