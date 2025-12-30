<x-layout-backend>
    <!-- CONTENT -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý menu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Blank Page</li>
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
                        <a class="btn btn-sm btn-danger" href="{{ url('admin/menu/trash') }}">
                            <i class="fas fa-trash"></i> Thùng rác
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="accordion" id="accordionExample">
                                <div class="card p-3">
                                    <label for="postion">Vị trí</label>
                                    <select name="postion" id="postion" class="form-control">
                                        <option value="2">Chưa xuất bản</option>
                                        <option value="1">Xuất bản</option>
                                    </select>
                                </div>

                                <div class="card">
                                    <div class="card-header" id="headingCategory">
                                        <a class="d-block" data-toggle="collapse" data-target="#collapseCategory"
                                            aria-expanded="true" aria-controls="collapseCategory">
                                            Danh mục
                                        </a>
                                    </div>
                                    <div id="collapseCategory" class="collapse" aria-labelledby="headingCategory"
                                        data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="categoryId">
                                                <label class="form-check-label" for="categoryId">
                                                    Default checkbox
                                                </label>
                                            </div>
                                            <div class="mb-3">
                                                <a type="submit" name="createCategory"
                                                    href="{{ url('admin/category/') }}" class="btn btn-success">Thêm
                                                    menu</a>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->
                                <div class="card">
                                    <div class="card-header" id="headingBrand">
                                        <a class="d-block" data-toggle="collapse" data-target="#collapseBrand"
                                            aria-expanded="true" aria-controls="collapseBrand">
                                            Thuong hiệu
                                        </a>
                                    </div>
                                    <div id="collapseBrand" class="collapse" aria-labelledby="headingBrand"
                                        data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="brandId">
                                                <label class="form-check-label" for="brandId">
                                                    Default checkbox
                                                </label>
                                            </div>
                                            <div class="mb-3">
                                                <a type="submit" name="createBrand" class="btn btn-success"
                                                    href="{{ url('admin/brand/') }}">Thêm
                                                    menu</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->
                                <div class="card">
                                    <div class="card-header" id="headingTopic">
                                        <a class="d-block" data-toggle="collapse" data-target="#collapseTopic"
                                            aria-expanded="true" aria-controls="collapseTopic">
                                            Chủ đề
                                        </a>
                                    </div>
                                    <div id="collapseTopic" class="collapse" aria-labelledby="headingTopic"
                                        data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="topicId">
                                                <label class="form-check-label" for="topicId">
                                                    Default checkbox
                                                </label>
                                            </div>
                                            <div class="mb-3">
                                                <a type="submit" name="createTopic" href="{{ url('admin/topic/') }}"
                                                    class="btn btn-success">Thêm menu</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->
                                <div class="card">
                                    <div class="card-header" id="headingPage">
                                        <a class="d-block" data-toggle="collapse" data-target="#collapsePage"
                                            aria-expanded="true" aria-controls="collapsePage">
                                            Trang đơn
                                        </a>
                                    </div>
                                    <div id="collapsePage" class="collapse" aria-labelledby="headingPage"
                                        data-parent="#accordionExample">
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" value=""
                                                    id="pageId">
                                                <label class="form-check-label" for="pageId">
                                                    Default checkbox
                                                </label>
                                            </div>
                                            <div class="mb-3">
                                                <a type="submit" name="createPage" class="btn btn-success"
                                                    href="{{ url('admin/blog/create') }}">Thêm
                                                    menu</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->
                                <div class="card">
                                    <div class="card-header" id="headingCustom">
                                        <a class="d-block" data-toggle="collapse" data-target="#collapseCustom"
                                            aria-expanded="true" aria-controls="collapseCustom">
                                            Thêm menu
                                        </a>
                                    </div>

                                    <div id="collapseCustom" class="collapse" aria-labelledby="headingCustom"
                                        data-parent="#accordionExample">

                                        <div class="card-body">
                                            <form action="{{ route('admin.menu.store') }}" method="post"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="name">Tên menu</label>
                                                    <input type="text" value="" name="name"
                                                        id="name" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="link">Liên kết</label>
                                                    <input type="text" value="" name="link"
                                                        id="link" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="type">Loại</label>
                                                    <input type="text" value="" name="type"
                                                        id="type" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="position">Vị trí</label>
                                                    <select name="position" id="position" class="form-control">
                                                        <option value="mainmenu">Main Menu</option>
                                                        <option value="footermenu">Footer Menu</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="parent_id">Menu cha</label>
                                                    <select name="parent_id" id="parent_id" class="form-control">
                                                        <option value="0">None</option>
                                                        @foreach ($mumus as $menu)
                                                            <option value="{{ $menu->id }}">{{ $menu->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="sort_order">Thứ tự</label>
                                                    <input type="number" name="sort_order" id="sort_order"
                                                        class="form-control" value="0">
                                                </div>
                                                <div class="card p-3">
                                                    <label for="status">Trạng thái</label>
                                                    <select name="status" id="status" class="form-control">
                                                        <option value="2">Chưa xuất bản</option>
                                                        <option value="1">Xuất bản</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <button type="submit" name="create"
                                                        class="btn btn-success">Thêm danh
                                                        mục</button>
                                                </div>
                                            </form>
                                        </div>

                                    </div>

                                </div>
                                <!-- end card -->

                            </div>
                        </form>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:30px;">#</th>
                                    <th>Tên menu</th>
                                    <th>Liên kết</th>
                                    <th>Loại</th>
                                    <th>Bảng</th>
                                    <th>Menu cha</th>
                                    <th>Vị trí</th>
                                    <th class="text-center" style="width:200px;">Chức năng</th>
                                    <th class="text-center" style="width:30px;">ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menus as $menu)
                                    <tr>

                                        <td class="text-center">
                                            <input type="checkbox" id="checkId" value="1" name="checkId[]">
                                        </td>
                                        <td>{{ $menu->name }}</td>
                                        <td>{{ $menu->link }}</td>
                                        <td>{{ $menu->type }}</td>
                                        <td>{{ $menu->position }}</td>
                                        <td>{{ $menu->parent_id }}</td>
                                        <td>{{ $menu->sort_order }}</td>


                                        <td class="text-center">
                                            @if ($menu->status == 1)
                                                <a href="{{ route('admin.menu.status', ['menu' => $menu->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-on"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.menu.status', ['menu' => $menu->id]) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fas fa-toggle-off"></i>
                                                </a>
                                            @endif

                                            <a href="{{ route('admin.menu.show', ['menu' => $menu->id]) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.menu.edit', ['menu' => $menu->id]) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.menu.delete', ['menu' => $menu->id]) }}"
                                                class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            {{ $menu->id }}
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                        <div class= "my-4">
                            {{ $menus->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.CONTENT -->

</x-layout-backend>
