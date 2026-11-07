<x-layout-frontend>
  <x-page-hero 
      title="Hồ Sơ Cá Nhân" 
      subtitle="Quản lý thông tin tài khoản thành viên CIVILIZE Restaurant." 
      :breadcrumbs="[['name' => 'Tài khoản của tôi', 'url' => '']]" 
  />

  <div class="container mx-auto px-4 max-w-2xl my-10">
    @if(in_array($user->roles, ['admin', 'employee']))
    <div class="mb-6 p-5 bg-stone-900 border-l-4 border-l-[#C25E00] text-white rounded-none sm:rounded-sm shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-none sm:rounded-sm bg-stone-800 text-amber-400 flex items-center justify-center text-xl flex-shrink-0">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <div class="font-bold text-sm sm:text-base">Tài khoản Quản trị viên (Admin)</div>
          <div class="text-xs text-stone-400 mt-0.5">Bạn có quyền truy cập toàn bộ hệ thống quản trị nhà hàng (Đơn hàng, Bàn ăn, Đặt bàn, Doanh thu...).</div>
        </div>
      </div>
      <a href="{{ route('admin.dashboard') }}" 
         class="px-5 py-2.5 bg-[#C25E00] hover:bg-[#A34E00] text-white font-bold text-xs uppercase tracking-wider rounded-none sm:rounded-sm shadow-xs transition-colors whitespace-nowrap flex items-center gap-2">
        <i class="fa-solid fa-gauge-high"></i>
        <span>Vào Trang Quản Trị</span>
      </a>
    </div>
    @endif

    <div class="bg-white p-6 sm:p-8 rounded-none sm:rounded-sm border border-stone-200/90 shadow-xs">
      <div class="flex justify-between items-center mb-6 pb-4 border-b border-stone-100">
        <h2 class="font-serif text-xl sm:text-2xl font-bold text-stone-900">Thông Tin Cá Nhân</h2>
        <button id="edit-button" class="text-xs uppercase tracking-wider font-bold bg-stone-900 hover:bg-[#C25E00] text-white px-4 py-2 rounded-none sm:rounded-sm transition-colors cursor-pointer">
          <i class="far fa-edit mr-1"></i> Chỉnh sửa
        </button>
      </div>

      <form id="profile-form" action="{{ route('site.updateProfile') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-6 text-center">
          <img src="{{ asset('/images/user/' . ($user->image ?? 'default.png')) }}" 
               class="w-24 h-24 rounded-full border-2 border-stone-200 mx-auto object-cover shadow-2xs" alt="Profile Image">
          <label class="inline-block mt-3 cursor-pointer bg-stone-50 px-4 py-1.5 rounded-none sm:rounded-sm text-xs font-semibold text-stone-700 border border-stone-300 hover:bg-stone-100 transition-colors">
            Chọn ảnh đại diện
            <input type="file" name="image" class="hidden" disabled>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div class="form-group">
            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Họ và tên</label>
            <input type="text" name="fullname" class="w-full px-3.5 py-2 text-xs sm:text-sm border border-stone-300 rounded-none sm:rounded-sm focus:outline-none focus:border-[#C25E00]" value="{{ $user->fullname }}" readonly>
          </div>
          <div class="form-group">
            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Giới tính</label>
            <select name="gender" class="w-full px-3.5 py-2 text-xs sm:text-sm border border-stone-300 rounded-none sm:rounded-sm focus:outline-none focus:border-[#C25E00]" disabled>
              <option value="Nam" {{ $user->gender == 'Nam' ? 'selected' : '' }}>Nam</option>
              <option value="Nữ" {{ $user->gender == 'Nữ' ? 'selected' : '' }}>Nữ</option>
              <option value="Khác" {{ $user->gender == 'Khác' ? 'selected' : '' }}>Khác</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div class="form-group">
            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Email</label>
            <input type="email" class="w-full px-3.5 py-2 text-xs sm:text-sm border border-stone-300 rounded-none sm:rounded-sm focus:outline-none focus:border-[#C25E00]" value="{{ $user->email }}" readonly>
          </div>
          <div class="form-group">
            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Số điện thoại</label>
            <input type="text" name="phone" class="w-full px-3.5 py-2 text-xs sm:text-sm border border-stone-300 rounded-none sm:rounded-sm focus:outline-none focus:border-[#C25E00]" value="{{ $user->phone }}" readonly>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div class="form-group">
            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Địa chỉ</label>
            <input type="text" name="address" class="w-full px-3.5 py-2 text-xs sm:text-sm border border-stone-300 rounded-none sm:rounded-sm focus:outline-none focus:border-[#C25E00]" value="{{ $user->address }}" readonly>
          </div>
          <div class="form-group">
            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">Ngày tham gia</label>
            <input type="text" name="created_at" class="w-full px-3.5 py-2 text-xs sm:text-sm border border-stone-300 rounded-none sm:rounded-sm bg-stone-50" 
              value="{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Đang cập nhật' }}" readonly>
          </div>
        </div>

        <div class="mb-4">
          <button type="submit" id="save-button" class="w-full py-3 px-6 bg-stone-900 hover:bg-[#C25E00] text-white font-bold text-xs uppercase tracking-[0.2em] rounded-none sm:rounded-sm shadow-xs transition-colors hidden cursor-pointer">
            Lưu Thông Tin
          </button>
        </div>
      </form>

      <div class="text-center text-xs text-stone-500 pt-4 border-t border-stone-100 flex items-center justify-center gap-1">
        <span>Bạn muốn đăng xuất khỏi tài khoản?</span>
        <form action="{{ route('site.logout') }}" method="POST" class="inline m-0 p-0">
          @csrf
          <button type="submit" class="text-rose-600 font-bold hover:underline cursor-pointer bg-transparent border-0 p-0 text-xs">
            Đăng xuất ngay
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    const editButton = document.getElementById('edit-button');
    const saveButton = document.getElementById('save-button');
    const formFields = document.querySelectorAll('#profile-form input, #profile-form select');

    editButton.addEventListener('click', () => {
      formFields.forEach(field => field.removeAttribute('readonly'));
      formFields.forEach(field => field.removeAttribute('disabled'));
      saveButton.classList.remove('hidden');
      editButton.classList.add('hidden');
    });
  </script>
</x-layout-frontend>
