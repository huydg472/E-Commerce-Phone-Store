<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký test</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4 class="mb-0">Đăng ký tài khoản test</h4>
                </div>

                <div class="card-body">
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
                            {{-- gọi đến hàm xử lý post --}}
                    <form method="POST" action="{{ route('auth-test.register.post') }}"> 
                        
                        @csrf 

                        <div class="mb-3">
                            <label class="form-label">Họ tên</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                placeholder="Ví dụ: Nguyễn Văn A"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                placeholder="Ví dụ: test@example.com"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="{{ old('phone') }}"
                                placeholder="Ví dụ: 0987654321"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="{{ old('username') }}"
                                placeholder="Ví dụ: testuser"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Tối thiểu 8 ký tự"
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nhập lại mật khẩu</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Nhập lại mật khẩu"
                            >
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Đăng ký
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        {{-- điều hướng đến trang đăng nhập nếu đã có tài khoản --}}
                        <a href="{{ route('auth-test.login') }}">
                            Đã có tài khoản? Đăng nhập
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>