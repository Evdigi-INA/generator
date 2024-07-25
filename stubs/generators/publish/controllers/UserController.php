<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use App\Http\Requests\Users\{StoreUserRequest, UpdateUserRequest};
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $avatarPath = '')
    {
        $this->avatarPath = storage_path('app/public/uploads/avatars/');

    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:user view', only: ['index', 'show']),
            new Middleware('permission:user create', only: ['create', 'store']),
            new Middleware('permission:user edit', only: ['edit', 'update']),
            new Middleware('permission:user delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            $users = User::with('roles:id,name');

            return Datatables::of($users)
                ->addColumn('action', 'users.include.action')
                ->addColumn('role', fn($row) => $row->getRoleNames()->toArray() !== [] ? $row->getRoleNames()[0] : '-')
                ->addColumn('avatar', function ($user) {
                    if (!$user->avatar) {
                        return 'https://via.placeholder.com/350?text=No+Image+Avaiable';
                    }

                    return asset('storage/uploads/avatars/' . $user->avatar);
                })
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageService->upload(name: 'avatar', path: $this->avatarPath);
            $validated['password'] = bcrypt($request->password);

            $user = User::create($validated);

            $role = Role::select('id', 'name')->find($request->role);

            $user->assignRole($role->name);

            return to_route('users.index')->with('success', __('The user was created successfully.'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load('roles:id,name');

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $user->load('roles:id,name');

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        return DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageService->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user?->avatar);

            if (!$request->password) {
                unset($validated['password']);
            } else {
                $validated['password'] = bcrypt($request->password);
            }

            $user->update($validated);

            $role = Role::select('id', 'name')->find($request->role);

            $user->syncRoles($role->name);

            return to_route('users.index')->with('success', __('The user was updated successfully.'));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($user) {
                $avatar = $user->avatar;

                $user->delete();

                $this->imageService->delete(image: $this->avatarPath . $avatar);

                return to_route('users.index')->with('success', __('The user was deleted successfully.'));
            });
        } catch (\Exception $e) {
            return to_route('users.index')->with('error', __("The user can't be deleted because it's related to another table."));
        }
    }
}
