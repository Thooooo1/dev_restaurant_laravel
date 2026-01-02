<form action="{{ route('inquiries.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <input type="text" name="name" placeholder="Tên" class="w-full border rounded px-3 py-2 text-sm" required>
    </div>
    <div class="mb-3">
        <input type="text" name="phone" placeholder="Số Điện Thoại" class="w-full border rounded px-3 py-2 text-sm" required>
    </div>
    <div class="mb-3">
        <textarea name="message" placeholder="Bạn cần tư vấn gì" class="w-full border rounded px-3 py-2 text-sm" required></textarea>
    </div>
    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white rounded px-4 py-2 w-full">Gửi</button>
</form>
