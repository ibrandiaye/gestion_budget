<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the permissions for this role
     */
    public function permissions()
    {
        return \DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('role_permission.role', $this->name)
            ->select('permissions.*')
            ->get();
    }

    /**
     * Get the label to display for this role
     */
    public function getLabelAttribute()
    {
        return $this->display_name ?? ucfirst($this->name);
    }
}
