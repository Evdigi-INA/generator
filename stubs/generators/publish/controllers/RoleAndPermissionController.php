<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\{StoreRoleRequest, UpdateRoleRequest};
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleAndPermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role & permission view')->only('index', 'show');
        $this->middleware('permission:role & permission create')->only('create', 'store');
        $this->middleware('permission:role & permission edit')->only('edit', 'update');
        $this->middleware('permission:role & permission delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
    {
        if (request()->ajax()) {
            $users = Role::query();

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })->addColumn('updated_at', function ($row) {
                    return $row->updated_at->format('Y-m-d H:i:s');
                })
                ->addColumn('action', 'roles.include.action')
                ->toJson();
        }

        return view('roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $role = Role::create(['name' => $request->name]);

        $role->givePermissionTo($request->permissions);

        return to_route('roles.index')->with('success', __('The role was created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): \Illuminate\Contracts\View\View
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): \Illuminate\Contracts\View\View
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id): \Illuminate\Http\RedirectResponse
    {
        $role = Role::findOrFail($id);

        $role->update(['name' => $request->name]);

        $role->syncPermissions($request->permissions);

        return to_route('roles.index')->with('success', __('The role was updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\RedirectResponse
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->users_count < 1) {
            $role->delete();

            return to_route('roles.index')->with('success', __('The role was deleted successfully.'));
        }

        return to_route('roles.index')->with('error', __('Can`t delete role.'));
    }
}
