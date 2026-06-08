<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->with('role')
            ->orderBy('id');

        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('name', 'ilike', '%' . $search . '%')
                    ->orWhere('username', 'ilike', '%' . $search . '%')
                    ->orWhere('email', 'ilike', '%' . $search . '%')
                    ->orWhere('phone', 'ilike', '%' . $search . '%')
                    ->orWhereHas('role', function ($roleQuery) use ($search) {
                        $roleQuery
                            ->where('name', 'ilike', '%' . $search . '%')
                            ->orWhere('display_name', 'ilike', '%' . $search . '%');
                    });
            });
        }

        $perPage = max(1, min((int) $request->integer('per_page', 10), 50));
        $user = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $user,
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create($data);
        $user->load('role');

        return response()->json([
            'message' => 'thÃªm thÃ nh cÃ´ng',
            'data' => $user
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('role');

        return response()->json([
            'data' => $user
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (array_key_exists('password', $data) && blank($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->refresh()->load('role');

        return response()->json([
            'message' => 'update thÃ nh cÃ´ng',
            'data' => $user
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'xÃ³a thÃ nh cÃ´ng',
        ]);
    }
}
