<?php

namespace App\Http\Controllers\API;

use App\Generators\Services\ImageServiceV2;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\Users\UserCollection;
use App\Http\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
            // 'auth:sanctum',

            // new Middleware('permission:user:view', only: ['index', 'show']),
            // new Middleware('permission:user:create', only: ['create', 'store']),
            // new Middleware('permission:user:edit', only: ['edit', 'update']),
            // new Middleware('permission:user:delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): UserCollection
    {
        $users = User::query()->with(['roles:id,name'])->paginate(perPage: request()->query(key: 'per_page', default: 10));

        return (new UserCollection(resource: $users))
            ->additional(data: [
                'message' => 'The users was received successfully.',
                // 'success' => true,
                // 'status_code' => Response::HTTP_OK,
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        return DB::transaction(callback: function () use ($request): JsonResponse {
            $validated = $request->validated();
            $validated['avatar'] = $this->imageServiceV2->upload(name: 'avatar', path: $this->avatarPath);
            $validated['password'] = bcrypt($request->password);

            $user = User::create($validated);

            $role = Role::select('id', 'name')->find($request->role);

            $user->assignRole($role->name);

            return (new UserResource(resource: $user->refresh()))
                ->additional(data: [
                    'message' => 'The user was created successfully.',
                    // 'success' => true,
                    // 'status_code' => Response::HTTP_CREATED,
                ])
                ->response()
                ->setStatusCode(code: Response::HTTP_CREATED);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string|int $id): UserResource
    {
        $user = User::with(relations: ['roles:id,name'])->findOrFail(id: $id);

        return (new UserResource(resource: $user))
            ->additional(data: [
                'message' => 'The user was received successfully.',
                // 'success' => true,
                // 'status_code' => Response::HTTP_OK,
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string|int $id): UserResource
    {
        return DB::transaction(callback: function () use ($request, $id): UserResource {
            $user = User::findOrFail($id);
            $validated = $request->validated();
            $validated['avatar'] = $this->imageServiceV2->upload(name: 'avatar', path: $this->avatarPath, defaultImage: $user?->avatar);

            if (! $request->password) {
                unset($validated['password']);
            } else {
                $validated['password'] = bcrypt($request->password);
            }

            $user->update($validated);

            $role = Role::select('id', 'name')->find($request->role);

            $user->syncRoles($role->name);

            return (new UserResource(resource: $user->refresh()))
                ->additional(data: [
                    'message' => 'The user was updated successfully.',
                    // 'success' => true,
                    // 'status_code' => Response::HTTP_OK,
                ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id): UserResource|JsonResponse
    {
        try {
            return DB::transaction(callback: function () use ($id): UserResource {
                $user = User::findOrFail($id);
                $avatar = $user->avatar;

                $user->delete();

                $this->imageServiceV2->delete(path: $this->avatarPath, image: $avatar, disk: $this->disk);

                return (new UserResource(resource: null))
                    ->additional(data: [
                        'message' => 'The user was deleted successfully.',
                        // 'success' => true,
                        // 'status_code' => Response::HTTP_OK,
                    ]);
            });
        } catch (\Exception $e) {
            return (new UserResource(resource: null))
                ->additional(data: [
                    'message' => $e->getMessage(),
                    // 'success' => false,
                    // 'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                ])
                ->response()
                ->setStatusCode(code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
