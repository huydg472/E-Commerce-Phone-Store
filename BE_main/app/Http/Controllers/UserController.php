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

        $search = trim((string)$request->query('q', ''));

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

        $perPage = max(1, min((int)$request->integer('per_page', 10), 50));
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
            'message' => 'Thêm thành công',
            'data' => $user,
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('role');

        return response()->json([
            'data' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $originalEmail = $user->email;

        if (array_key_exists('password', $data) && blank($data['password'])) {
            unset($data['password']);
        }

        $emailChanged = array_key_exists('email', $data)
            && strcasecmp((string)$data['email'], (string)$originalEmail) !== 0;

        if ($emailChanged) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);
        $user->refresh()->load('role');

        if ($emailChanged) {
            $sent = $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => $sent
                    ? 'Cập nhật thành công. Đã gửi email xác minh tới địa chỉ mới.'
                    : 'Cập nhật thành công. Email xác minh gần nhất vẫn còn hiệu lực trong 5 phút.',
                'data' => $user,
            ]);
        }

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $user,
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'Xóa thành công',
        ]);
    }
}
