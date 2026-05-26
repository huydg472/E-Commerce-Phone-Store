<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard test</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Đăng nhập thành công</h4>

            <form method="POST" action="{{ route('auth-test.logout') }}">
                @csrf

                <button type="submit" class="btn btn-danger">
                    Đăng xuất
                </button>
            </form>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <h5>Thông tin user hiện tại</h5>

            <table class="table table-bordered mt-3">
                <tr>
                    <th style="width: 180px;">ID</th>
                    <td>{{ auth()->user()->id }}</td>
                </tr>

                <tr>
                    <th>Họ tên</th>
                    <td>{{ auth()->user()->name }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ auth()->user()->email }}</td>
                </tr>

                <tr>
                    <th>Số điện thoại</th>
                    <td>{{ auth()->user()->phone }}</td>
                </tr>

                <tr>
                    <th>Username</th>
                    <td>{{ auth()->user()->username }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>{{ auth()->user()->status }}</td>
                </tr>

                <tr>
                    <th>Role</th>
                    <td>
                        @if (auth()->user()->role)
                            {{ auth()->user()->role->name }}
                        @else
                            Chưa có role
                        @endif
                    </td>
                </tr>
            </table>

           
        </div>
    </div>

</div>

</body>
</html>