<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleAndPermissionController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:role & permission view', only: ['index', 'show']),
            new Middleware('permission:role & permission create', only: ['create', 'store']),
            new Middleware('permission:role & permission edit', only: ['edit', 'store']),
            new Middleware('permission:role & permission delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $users = Role::query();

            return DataTables::of($users)
                ->addColumn('created_at', fn ($row) => $row->created_at->format('Y-m-d H:i:s'))
                ->addColumn('updated_at', fn ($row) => $row->updated_at->format('Y-m-d H:i:s'))
                ->addColumn('action', 'roles.include.action')
                ->toJson();
        }

        return view(view: 'roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view(view: 'roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(attributes: ['name' => $request->name]);
        $role->givePermissionTo(permissions: $request->permissions);

        return to_route(route: 'roles.index')->with('success', __(key: 'The role was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view(view: 'roles.show', data: compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view(view: 'roles.edit', data: compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id): RedirectResponse
    {
        $role = Role::findOrFail($id);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return to_route(route: 'roles.index')->with('success', __(key: 'The role was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->users_count < 1) {
            $role->delete();

            return to_route(route: 'roles.index')->with('success', __(key: 'The role was deleted successfully.'));
        }

        return to_route(route: 'roles.index')->with('error', __(key: 'Can`t delete role.'));
    }
}
