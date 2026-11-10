<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeAccessType extends Model
{
    public const ASAAS = 'asaas';
    public const ASAAS_ATTESTED = 'asaas_attested';
    public const COURTESY = 'courtesy';
    public const EXTERNAL = 'external';
    public const LEGACY = 'legacy';

    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
