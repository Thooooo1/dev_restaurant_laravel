<x-layout-frontend>
  <section class="bg-gray-200 py-2 px-4">
    <div class="breadcrumb flex items-center text-gray-600 text-sm container mx-auto">
        <span class="mr-4">Bạn đang ở đây:</span>
        <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
        <span class="mx-2">></span>
        <span class="font-semibold text-gray-800">Đăng nhập</span>
    </div>
</section>
<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg my-12 mx-4 md:mx-auto">
        <div class="text-center mb-4">  
          <h4 class="text-2xl font-semibold">Đăng nhập thành viên</h4>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{route('site.dologin')}}" method="POST" id="loginForm">
          @csrf
          <input type="hidden" name="redirect" value="{{ $redirect ?? request()->query('redirect') ?? '' }}">
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="Nhập số điện thoại" required>
            <p id="phone-error" class="text-red-500 text-xs mt-1 hidden"></p>
          </div>
          
          <div id="password-section" class="hidden">
              <div class="mb-4">
                <div class="flex justify-between">
                  <label class="text-sm font-medium text-gray-700 mb-2">Mật khẩu</label>
                  <a href="{{route('site.forgot_password')}}" class="text-sm text-blue-500 hover:underline">Quên mật khẩu?</a>
                </div>
                <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="******">
              </div>
          </div>

          <div id="confirm-password-section" class="hidden">
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nhập lại mật khẩu</label>
                <input type="password" name="confirm_password" id="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="******">
              </div>
          </div>
      
          <div class="mb-4">
            <button type="button" id="btn-next" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
              Tiếp theo
            </button>
            <button type="submit" id="btn-submit" class="hidden w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 focus:outline-none">
              Đăng nhập
            </button>
          </div>
        </form>
      </div>
          
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnNext = document.getElementById('btn-next');
        const btnSubmit = document.getElementById('btn-submit');
        const phoneInput = document.getElementById('phone');
        const passwordSection = document.getElementById('password-section');
        const confirmPasswordSection = document.getElementById('confirm-password-section');
        const phoneError = document.getElementById('phone-error');
        const form = document.getElementById('loginForm');

        // Reset form state on load if not redirecting back with errors
        // Actually, let's keep it simple. User clicks Next again if needed.

        btnNext.addEventListener('click', function() {
            const phone = phoneInput.value;
            if (!phone) {
                phoneError.textContent = 'Vui lòng nhập số điện thoại';
                phoneError.classList.remove('hidden');
                return;
            }
            phoneError.classList.add('hidden');
            
            // Disable button
            btnNext.disabled = true;
            btnNext.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';

            fetch('{{ route("site.check_phone") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone: phone })
            })
            .then(response => response.json())
            .then(data => {
                btnNext.disabled = false;
                btnNext.textContent = 'Tiếp theo';

                if (data.status === 'success') {
                    // Auto login success via backend (Order case)
                    window.location.href = data.redirect;
                } else if (data.status === 'require_password') {
                    passwordSection.classList.remove('hidden');
                    confirmPasswordSection.classList.add('hidden');
                    
                    btnNext.classList.add('hidden');
                    btnSubmit.classList.remove('hidden');
                    btnSubmit.textContent = 'Đăng nhập';
                    
                    // Focus password
                    document.getElementById('password').focus();
                } else if (data.status === 'require_register') {
                    passwordSection.classList.remove('hidden');
                    confirmPasswordSection.classList.remove('hidden');
                    
                    btnNext.classList.add('hidden');
                    btnSubmit.classList.remove('hidden');
                    btnSubmit.textContent = 'Đăng ký mật khẩu & Đăng nhập';
                    
                    document.getElementById('password').focus();
                } else {
                    phoneError.textContent = data.message || 'Có lỗi xảy ra';
                    phoneError.classList.remove('hidden');
                }
            })
            .catch(error => {
                btnNext.disabled = false;
                btnNext.textContent = 'Tiếp theo';
                console.error('Error:', error);
                phoneError.textContent = 'Có lỗi kết nối, vui lòng thử lại';
                phoneError.classList.remove('hidden');
            });
        });
        
        // Allow Enter key on phone input to trigger Next
        phoneInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!btnNext.classList.contains('hidden')) {
                    btnNext.click();
                } else {
                    btnSubmit.click();
                }
            }
        });
    });
  </script>
</x-layout-frontend>