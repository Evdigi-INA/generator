<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\StoreRoleRequest;
// use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Resources\Roles\RoleCollection;
use App\Http\Resources\Roles\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class RoleAndPermissionController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            // 'auth:sanctum',

            // new Middleware(middleware: 'permission:role_permission:view', only: ['index', 'show']),
            // new Middleware(middleware: 'permission:role_permission:create', only: ['create', 'store']),
            // new Middleware(middleware: 'permission:role_permission:edit', only: ['edit', 'store']),
            // new Middleware(middleware: 'permission:role_permission:delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): RoleCollection
    {
        $roles = Role::query()->paginate(perPage: request()->query(key: 'per_page', default: 10));

        return (new RoleCollection(resource: $roles))
            ->additional(data: [
                'message' => 'The roles was received successfully.',
                // 'success' => true,
                // 'status_code' => Response::HTTP_OK,
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RoleResource|JsonResponse
    {
        return DB::transaction(callback: function () use ($request): JsonResponse {
            $role = Role::create(attributes: $request->validated());

            $role->givePermissionTo(permissions: array_map(callback: 'intval', array: $request->permissions));

            $role->load(relations: ['permissions']);
            $role->permissions_count = $role->permissions->count();

            return (new RoleResource(resource: $role))
                ->additional(data: [
                    'message' => 'The role was created successfully.',
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
    public function show(int $id): RoleResource
    {
        $role = Role::query()->with(relations: ['permissions:id,name,group'])->findOrFail(id: $id);

        return (new RoleResource(resource: $role))
            ->additional(data: [
                'message' => 'The role was received successfully.',
                // 'success' => true,
                // 'status_code' => Response::HTTP_OK,
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string|int $id): RoleResource
    {
        return DB::transaction(callback: function () use ($request, $id): RoleResource {
            $role = Role::findOrFail(id: $id);
            $role->update(attributes: $request->validated());
            $role->syncPermissions(permissions: array_map(callback: 'intval', array: $request->permissions));

            return (new RoleResource(resource: $role))
                ->additional(data: [
                    'message' => 'The role was updated successfully.',
                    // 'success' => true,
                    // 'status_code' => Response::HTTP_OK,
                ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string|int $id): RoleResource|JsonResponse
    {
        return DB::transaction(callback: function () use ($id): RoleResource|JsonResponse {
            $role = Role::findOrFail(id: $id);

            if ($role->users()->count() < 1) {
                $role->delete();

                return (new RoleResource(resource: null))
                    ->additional(data: [
                        'message' => 'The role was deleted successfully.',
                        // 'success' => true,
                        // 'status_code' => Response::HTTP_OK,
                    ]);
            }

            return (new RoleResource(resource: $role))
                ->additional(data: [
                    'message' => 'Can`t delete role.',
                    // 'success' => false,
                    // 'status_code' => Response::HTTP_FORBIDDEN,
                ])
                ->response()
                ->setStatusCode(code: Response::HTTP_FORBIDDEN);
        });
    }
}
