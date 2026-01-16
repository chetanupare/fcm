<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        $permissions = $query->orderBy('group')->orderBy('name')->get();

        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'group' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create($validated);

        return response()->json($permission, 201);
    }

    public function getRolePermissions(Request $request)
    {
        $role = $request->get('role', 'admin');
        
        $permissions = RolePermission::where('role', $role)
            ->with('permission')
            ->get()
            ->pluck('permission');

        return response()->json($permissions);
    }

    public function assignRolePermission(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        RolePermission::firstOrCreate([
            'role' => $validated['role'],
            'permission_id' => $validated['permission_id'],
        ]);

        return response()->json(['message' => 'Permission assigned to role']);
    }

    public function removeRolePermission(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        RolePermission::where('role', $validated['role'])
            ->where('permission_id', $validated['permission_id'])
            ->delete();

        return response()->json(['message' => 'Permission removed from role']);
    }

    public function getUserPermissions($userId)
    {
        $permissions = UserPermission::where('user_id', $userId)
            ->with('permission')
            ->get()
            ->pluck('permission');

        return response()->json($permissions);
    }

    public function assignUserPermission(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
            'granted' => 'boolean',
        ]);

        UserPermission::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'permission_id' => $validated['permission_id'],
            ],
            ['granted' => $validated['granted'] ?? true]
        );

        return response()->json(['message' => 'Permission assigned to user']);
    }

    public function removeUserPermission(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        UserPermission::where('user_id', $validated['user_id'])
            ->where('permission_id', $validated['permission_id'])
            ->delete();

        return response()->json(['message' => 'Permission removed from user']);
    }
}
