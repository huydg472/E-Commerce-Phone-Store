<script setup>

</script>

<template>
    <div class="forgot-form w-100">
        <RouterLink to="/auth/login"
            class="back-link d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold mb-5">
            <i class="bi bi-arrow-left"></i>
            Quay lại đăng nhập
        </RouterLink>

        <div class="text-center forgot-content">
            <div class="forgot-icon mx-auto mb-4">
                <i class="bi bi-lock"></i>
                <i class="bi bi-key key-icon"></i>
            </div>

            <h1 class="forgot-title fw-bold mb-3">
                Quên mật khẩu
            </h1>

            <p class="forgot-desc mx-auto mb-5">
                Nhập email của bạn, chúng tôi sẽ gửi liên kết đặt lại mật khẩu về email đó.
            </p>
        </div>

        <form>
            <div class="mb-4">
                <label class="form-label fw-bold">Email</label>

                <div class="input-group auth-input">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>

                    <input type="email" class="form-control" placeholder="Nhập email của bạn" />
                </div>
            </div>

            <button type="button" class="btn btn-primary w-100 auth-btn">
                Gửi liên kết đặt lại mật khẩu
            </button>
        </form>
    </div>
</template>

<style scoped>
.forgot-form {
    max-width: 560px;
    padding-top: 26px;
}

.back-link {
    color: #0066ff;
    font-size: 17px;
}

.forgot-content {
    margin-top: 34px;
}

.forgot-icon {
    width: 132px;
    height: 132px;
    border-radius: 50%;
    background: #edf4ff;
    color: #0066ff;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-size: 56px;
}

.key-icon {
    position: absolute;
    right: 32px;
    bottom: 32px;
    font-size: 30px;
}

.forgot-title {
    color: #061c46;
    font-size: 34px;
}

.forgot-desc {
    max-width: 430px;
    color: #52627a;
    font-size: 18px;
    line-height: 1.6;
}

.form-label {
    font-size: 16px;
    color: #111827;
}

.auth-input {
    height: 62px;
    border: 1px solid #d8e1ee;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
}

.auth-input .input-group-text {
    background: #ffffff;
    border: none;
    color: #66789f;
    font-size: 22px;
    padding: 0 18px;
}

.auth-input .form-control {
    border: none;
    box-shadow: none;
    font-size: 17px;
}

.auth-btn {
    height: 64px;
    border-radius: 10px;
    background: #0066ff;
    border-color: #0066ff;
    font-size: 18px;
    font-weight: 700;
}
</style>