<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'action',
        'description',
    ];

    /**
     * Get all roles that have this permission
     */
    public function roles()
    {
        return $this->belongsToMany(
            'role',
            'role_permission',
            'permission_id',
            'role'
        );
    }
}
