<x-layout-backend>
    <section class="content-header">
        <h1>Thùng rác</h1>
        <a href="{{ route('admin.user.index') }}" class="btn btn-primary py-2 mt-2">Quay lại danh sách </a>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:30px;">#</th>
                            <th class="text-center" style="width:90px;">Hình</th>
                            <th>Tên người dùng</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-center" style="width:200px;">Chức năng</th>
                            <th class="text-center" style="width:30px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                </td>
                                <td class="text-center">
                                    <img src="{{ asset('/images/user/' . $user->image) }}" class="img-fluid"
                                        alt="{{ $user->image }}">
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->fullname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>
                                    <a href="{{ route('admin.user.restore', ['user' => $user->id]) }}" class="bg-blue-600 hover:bg-blue-700  rounded px-4 py-2 inline-flex items-center space-x-2">
                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                        
                                    </a>
                                    <form action="{{ route('admin.user.destroy', ['user' => $user->id]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700  rounded px-4 py-2 inline-flex items-center space-x-2">
                                            <i class="fa-solid fa-trash"></i>
                                            
                                        </button>
                                    </form>
                                </td>
                                
                                <td>{{ $user->id }}</td>
                        

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layout-backend>
