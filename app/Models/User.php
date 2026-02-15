<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'service_id',
        'matricule',
        'fonction',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission($permission)
    {
        return \DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('role_permission.role', $this->role)
            ->where('permissions.name', $permission)
            ->exists();
    }

    /**
     * Check if user can perform an action on a module
     */
    public function can($action, $module = null)
    {
        // If module is provided, check specific permission
        if ($module) {
            return $this->hasPermission("{$module}.{$action}");
        }
        
        // Otherwise, check if permission name is provided directly
        return $this->hasPermission($action);
    }

    /**
     * Get all permissions for this user's role
     */
    public function permissions()
    {
        return \DB::table('role_permission')
            ->join('permissions', 'role_permission.permission_id', '=', 'permissions.id')
            ->where('role_permission.role', $this->role)
            ->select('permissions.*')
            ->get();
    }
}
