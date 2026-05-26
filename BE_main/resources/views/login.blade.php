<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập test</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4 class="mb-0">Đăng nhập test</h4>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Có lỗi xảy ra:</strong>

                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                        {{-- form sẽ được gửi đến hàm auth-test.login.php ở web.php --}}
                    <form method="POST" action="{{ route('auth-test.login.post') }}">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="{{ old('username') }}"
                                placeholder="Nhập username"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Nhập mật khẩu"
                            >
                        </div>

                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="form-check-input"
                            >

                            <label for="remember" class="form-check-label">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Đăng nhập
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        {{-- điều hướng đến trang đăng ký --}}
                        <a href="{{ route('auth-test.register') }}">
                            Chưa có tài khoản? Đăng ký
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>