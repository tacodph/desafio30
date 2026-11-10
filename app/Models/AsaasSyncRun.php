<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsaasSyncRun extends Model
{
    protected $fillable = ['source', 'status', 'checked', 'failed', 'started_at', 'finished_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
