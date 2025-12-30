<x-layout-frontend>
  <section class="bg-gray-200 ml-5">
    <div class="breadcrumb flex items-center text-gray-600 text-sm">
        <span class="mr-4">Bạn đang ở đây:</span>
        <a href="{{url('/')}}" class="hover:text-orange-500"> Quay lại Trang chủ</a>
        <span class="mx-2">></span>
        <span class="font-semibold text-gray-800">Trang cá nhân</span>
    </div>
  </section>

  <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-lg my-12">
    <div class="flex justify-between items-center mb-6 ml-9">
      <h4 class="text-2xl font-semibold text-center flex-1">Thông tin cá nhân</h4>
      <button id="edit-button" class="text-sm bg-gray-500 text-white px-3 py-1 rounded-md hover:bg-gray-600 focus:outline-none">
        <i class="far fa-edit"></i>
      </button>
    </div>
    

    <form id="profile-form" action="{{ route('site.updateProfile') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="mb-4 text-center">
        <img src="{{ asset('/images/user/' . ($user->image ?? 'default.png')) }}" 
             class="w-24 h-24 rounded-full border-2 border-gray-300 mx-auto" alt="Profile Image">
        <label class="inline-block mt-2 cursor-pointer bg-gray-100 px-4 py-2 rounded-lg text-sm text-gray-600 border border-gray-300 hover:bg-gray-200">
          Chọn ảnh
          <input type="file" name="image" class="hidden" disabled>
        </label>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-1">Tên</label>
          <input type="text" name="fullname" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" value="{{ $user->fullname }}" readonly>
        </div>
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính</label>
          <select name="gender" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" disabled>
            <option value="Nam" {{ $user->gender == 'Nam' ? 'selected' : '' }}>Nam</option>
            <option value="Nữ" {{ $user->gender == 'Nữ' ? 'selected' : '' }}>Nữ</option>
            <option value="Khác" {{ $user->gender == 'Khác' ? 'selected' : '' }}>Khác</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" value="{{ $user->email }}" readonly>
        </div>
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
          <input type="text" name="address" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" value="{{ $user->address }}" readonly>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-1">Tài khoản tham gia vào:</label>
          <input type="text" name="created_at" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" 
            value="{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Đang cập nhật' }}" readonly>
        </div>
        <div class="form-group">
          <label class="block text-sm font-medium text-gray-700 mb-1">Điện thoại</label>
          <input type="text" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" value="{{ $user->phone }}" readonly>
        </div>
      </div>

      <div class="mb-4">
        <button type="submit" id="save-button" class="w-full py-2 px-4 bg-orange-500 text-white rounded-md hover:bg-orange-600 focus:outline-none hidden">
          Lưu thông tin
        </button>
      </div>
    </form>

    <p class="text-center text-sm py-2">
      Bạn có muốn đăng xuất? 
      <a href="{{ route('site.logout') }}" class="text-blue-500 hover:underline">Đăng xuất</a>
    </p>
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
