<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Generators\Services\ImageService;
use Illuminate\Routing\Controllers\{HasMiddleware, Middleware};
use App\Http\Requests\Users\{StoreUserRequest, UpdateUserRequest};
use Illuminate\Support\Facades\DB;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(public ImageService $imageService, public string $avatarPath = '/uploads/images/avatars/')
    {
        //
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
    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
    {
        if (request()->ajax()) {
            $users = User::with('roles:id,name');

            return Datatables::of($users)
                ->addColumn('action', 'users.include.action')
                ->addColumn('role', function ($row) {
                    return $row->getRoleNames()->toArray() !== [] ? $row->getRoleNames()[0] : '-';
                })
                ->addColumn('avatar', function ($row) {
                    if ($row->avatar == null) {
                        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($row->email))) . '&s=500';
                    }
                    return asset($this->avatarPath . $row->avatar);
                })
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): \Illuminate\Http\RedirectResponse
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
    public function show(User $user): \Illuminate\Contracts\View\View
    {
        $user->load('roles:id,name');

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): \Illuminate\Contracts\View\View
    {
        $user->load('roles:id,name');

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        return DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();

            $validated['avatar'] = $this->imageService->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user->avatar);

            switch (is_null($request->password)) {
                case true:
                    unset($validated['password']);
                    break;
                default:
                    $validated['password'] = bcrypt($request->password);
                    break;
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
    public function destroy(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->avatar != null && file_exists($oldAvatar = public_path($this->avatarPath . $user->avatar))) {
            unlink($oldAvatar);
        }

        $user->delete();

        return to_route('users.index')->with('success', __('The user was deleted successfully.'));
    }
}
