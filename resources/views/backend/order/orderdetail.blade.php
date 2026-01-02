<x-layout-backend>
    <!-- CONTENT -->
			<section class="content-header">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1>Quản lý đơn hàng</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
								<li class="breadcrumb-item"><a href="{{ url('admin/')}}">Trang chủ</a></li>
                                <li class="breadcrumb-item"><a href="{{ url('admin/order')}}">Đơn hàng</a></li>
								<li class="breadcrumb-item active">Chi tiết đơn hàng</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3>Thông tin đơn hàng</h3>
                        <a href="{{ route('admin.order.printorder', $order->id) }}" class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-print"></i> In đơn hàng
                        </a>
                        
                    </div>
                    <div class="card-body">
                        <p><strong>Họ tên:</strong> {{ $order->name }}</p>
                        <p><strong>Điện thoại:</strong> {{ $order->phone }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
                        <p><strong>Ghi chú:</strong> {{ $order->note }}</p>
                        <p><strong>Phương thức thanh toán:</strong> {{ $order->payment_method }}</p>
                        <p><strong>Ngày tạo:</strong> {{ date('H:i d/m/Y', strtotime($order->created_at)) }}</p>
                    </div>
                </div>
        
                <div class="card">
                    <div class="card-header">
                        <h3>Chi tiết sản phẩm</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-center" style="width:90px;">Hình</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderDetails as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-center">
                                        <img src="{{asset('/images/product/'.$detail->product->image)}}" class="img-fluid" alt="{{$detail->product->image}}">
                                    </td>
                                    <td>{{ $detail->product->name }}</td>
                                    <td>{{ number_format($detail->price, decimals: 0) }} VNĐ</td>
                                    
                                    <td>{{ $detail->qty }}</td>
                                    <td>{{ number_format($detail->amount, 0) }} VNĐ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
			<!-- /.CONTENT -->
</x-layout-backend>