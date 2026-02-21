<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'module_permission';

    protected $fillable = [
        'role_id',
        'module',
        'read',
        'create',
        'update',
        'delete'
    ];

    protected $casts = [
        'read' => 'boolean',
        'create' => 'boolean',
        'update' => 'boolean',
        'delete' => 'boolean',
    ];

    protected static $logAttributes = ['*'];
    protected static $logFillable = true;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logOnlyDirty = true;
    protected static $logUnguarded = true;
    protected static $logName = 'module_permission';

    public static function moduleList(): array
    {
        static $modules = null;

        if ($modules === null) {
            $modules = array_values(array_unique(config('acl.modules', [])));
        }

        return $modules;
    }

    public static function ensureRolePermissionRows(int|string $roleId): void
    {
        $modules = self::moduleList();
        if ($modules === []) {
            return;
        }

        $existing = self::query()
            ->where('role_id', $roleId)
            ->whereIn('module', $modules)
            ->pluck('module')
            ->all();

        $missing = array_values(array_diff($modules, $existing));
        if ($missing === []) {
            return;
        }

        $now = now();
        $rows = array_map(static fn (string $module) => [
            'role_id' => $roleId,
            'module' => $module,
            'create' => false,
            'read' => false,
            'update' => false,
            'delete' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], $missing);

        self::query()->insert($rows);
    }

    public static function checkCRUDPermissionToUser($moduleName, $permissionType)
    {
        $loggedInUser = Auth::user();
        if (! $loggedInUser) {
            return false;
        }

        // Super admin/master admin has all permissions
        if ($loggedInUser->hasAnyRole(['Super Admin', 'Master Admin'])) {
            return true;
        }

        $roleId = optional($loggedInUser->roles()->first())->id;

        // Check user-specific permission
        $userPermission = UserPermission::where('user_id', $loggedInUser->id)
            ->where('module', $moduleName)
            ->value($permissionType);

        // Check role-based permission
        $rolePermission = $roleId
            ? Permission::where('role_id', $roleId)->where('module', $moduleName)->value($permissionType)
            : null;

        // Return true if either is "on", 1, or truthy
        return ($userPermission === 'on' || $userPermission == 1 || $userPermission === true)
            || ($rolePermission === 'on' || $rolePermission == 1 || $rolePermission === true);
    }


    public static function isSuperAdmin()
    {
        $loggedInUser = Auth::user();

        return $loggedInUser && $loggedInUser->hasAnyRole(['Super Admin', 'Master Admin']);
    }

    public function getActivitylogOptions(): LogOptions
    {
        $userName = Auth::check() ? Auth::user()->name : 'System';

        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Permission')
            ->setDescriptionForEvent(function (string $eventName) use ($userName) {
                return "{$userName} has {$eventName} Permission";
            });
    }
}
