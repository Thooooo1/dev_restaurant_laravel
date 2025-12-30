<x-layout-backend>
    <!-- CONTENT -->
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
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
                    <a href="{{ url('admin/product/')}}" class="btn btn-sm btn-primary">Xem Sản phẩm</a>
                    <a href="{{ url('admin/category/')}}" class="btn btn-sm btn-info">Xem Danh mục</a>
                    <a href="{{ url('admin/brand/')}}" class="btn btn-sm btn-success">Xem Thương hiệu</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a href="{{ url('admin/product/')}}" class="small-box bg-primary" style="text-decoration: none; color: inherit;">
                        <div class="inner">
                            <h3>{{ $productCount }}</h3> 
                            <p>Sản phẩm</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ url('admin/category/')}}" class="small-box bg-info" style="text-decoration: none; color: inherit;">
                        <div class="inner">
                            <h3>{{ $categoryCount }}</h3> 
                            <p>Danh mục</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ url('admin/brand/')}}" class="small-box bg-success" style="text-decoration: none; color: inherit;">
                        <div class="inner">
                            <h3>{{ $brandCount }}</h3> 
                            <p>Thương hiệu</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($todayRevenue, 0) }} VNĐ</h3>
                    <p>Doanh thu hôm nay</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($monthlyRevenue, 0) }} VNĐ</h3>
                    <p>Doanh thu tháng</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Món ăn có nhiều đánh giá nhất</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-center" style="width:90px;">Hình</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng đánh giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topRatedProducts as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="text-center">
                                <img src="{{asset('/images/product/'.$product->image)}}" class="img-fluid" alt="{{$product->image}}">
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->reviews_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
</section>

<!-- /.CONTENT -->
</x-layout-backend>
