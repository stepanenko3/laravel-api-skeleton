<?php

namespace Stepanenko3\LaravelApiSkeleton\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

class ApiAccessToken extends Model
{
    use HasPermissions;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'tokenable_id',
        'tokenable_type',
        'title',
        'token',
        'abilities',
        'comment',
        'expires_at',
        'last_used_at',
        'created_at',
        'updated_at',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
