<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <strong class="fw-bold h4 text-danger">QUẢN LÝ DANH MỤC</strong>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/') }}">Trang chủ</a></li>
                        <li class="breadcrumb-item active">Danh mục</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 text-right">
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/category/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3" >
                        <form action="{{route('admin.category.store')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="name">Tên danh mục</label>
                                <input type="text" value="" name="name" id="name" class="form-control">
                                @if($errors->has('name'))
                                    <div class="text-danger">{{ $errors->first('name') ;}}</div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <textarea name="slug" id="slug" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="description">Mô tả</label>
                                <textarea name="description" id="description" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="parent_id">Danh mục cha</label>
                                <select name="parent_id" id="parent_id" class="form-control">
                                    <option value="0">None</option>
                                    @foreach($categors as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>                           
                            <div class="mb-3">
                                <label for="sort_order">Sắp xếp</label>
                                <select name="sort_order" id="sort_order" class="form-control">
                                    <option value="0">None</option>

                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="image">Hình</label>
                                <input type="file" name="image" id="image" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="status">Trạng thái</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="2">Chưa xuất bản</option>
                                    <option value="1">Xuất bản</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <button type="submit" name="create" class="btn btn-success">Thêm danh
                                    mục</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:30px;">#</th>
                                    <th class="text-center" style="width:90px;">Hình</th>
                                    <th>Tên danh mục</th>
                                    <th>Slug</th>
                                    <th>Danh mục cha</th>
                                    <th>Mô tả</th>
                                    <th class="text-center" style="width:200px;">Chức năng</th>
                                    <th class="text-center" style="width:30px;">ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>

                                        <td class="text-center">
                                            <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                        </td>
                                        <td class="text-center">
                                            <img src="{{ asset('/images/category/' . $category->image) }}"
                                                class="img-fluid" alt="{{ $category->image }}">
                                        </td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->slug }}</td>
                                        <td>{{ $category->parent_id }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td class="text-center">
                                            @if ($category->status == 1)
                                                <a href="{{ route('admin.category.status', ['category' => $category->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-on"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.category.status', ['category' => $category->id]) }}"
                                                    class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-toggle-off"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.category.show', ['category' => $category->id]) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.category.edit', ['category' => $category->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.category.delete', ['category' => $category->id]) }}"
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ $category->id }}
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                        <div class= "my-4">
                            {{ $categories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->
</x-layout-backend>
