<?php

namespace App\Http\Controllers;

use App\Generators\Services\ImageServiceV2;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(public ImageServiceV2 $imageServiceV2, public string $avatarPath = 'avatars', public string $disk = 'storage.public')
    {
        //
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(middleware: 'permission:user view', only: ['index', 'show']),
            new Middleware(middleware: 'permission:user create', only: ['create', 'store']),
            new Middleware(middleware: 'permission:user edit', only: ['edit', 'update']),
            new Middleware(middleware: 'permission:user delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $users = User::with(relations: ['roles:id,name']);

            return Datatables::of(source: $users)
                ->addColumn(name: 'action', content: 'users.include.action')
                ->addColumn(name: 'role', content: fn ($row) => $row->getRoleNames()->toArray() !== [] ? $row->getRoleNames()[0] : '-')
                ->toJson();
        }

        return view(view: 'users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view(view: 'users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        return DB::transaction(callback: function () use ($request): RedirectResponse {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageServiceV2->upload(name: 'avatar', path: $this->avatarPath);
            $validated['password'] = bcrypt(value: $request->password);

            $user = User::create(attributes: $validated);

            $role = Role::select(columns: ['id', 'name'])->find(id: $request->role);

            $user->assignRole(roles: $role->name);

            return to_route(route: 'users.index')->with(key: 'success', value: __(key: 'The user was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load(relations: ['roles:id,name']);

        return view(view: 'users.show', data: compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $user->load(relations: ['roles:id,name']);

        return view(view: 'users.edit', data: compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        return DB::transaction(callback: function () use ($request, $user): RedirectResponse {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageServiceV2->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user?->avatar);

            if (! $request->password) {
                unset($validated['password']);
            } else {
                $validated['password'] = bcrypt(value: $request->password);
            }

            $user->update(attributes: $validated);

            $role = Role::select(columns: ['id', 'name'])->find(id: $request->role);

            $user->syncRoles(roles: $role->name);

            return to_route(route: 'users.index')->with(key: 'success', value: __(key: 'The user was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            return DB::transaction(callback: function () use ($user): RedirectResponse {
                $avatar = $user->avatar;

                $user->delete();

                $this->imageServiceV2->delete(path: $this->avatarPath, image: $avatar, disk: $this->disk);

                return to_route(route: 'users.index')->with(key: 'success', value: __(key: 'The user was deleted successfully.'));
            });
        } catch (\Exception $e) {
            return to_route(route: 'users.index')->with(key: 'error', value: __(key: "The user can't be deleted because it's related to another table."));
        }
    }
}
