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
								<li class="breadcrumb-item active">Đơn hàng</li>
							</ol>
						</div>
					</div>
				</div>
			</section>
			<section class="content">
				<div class="card">
					<div class="card-header">
						<form method="GET" class="form-inline">
							<label for="status" class="mr-2">Lọc theo trạng thái:</label>
							<select name="status" id="status" class="form-control mr-2">
								<option value="5" {{ $statusFilter == 5 ? 'selected' : '' }}>Tất cả</option>
								<option value="0" {{ $statusFilter == 0 ? 'selected' : '' }}>Đang chờ xử lý</option>
								<option value="1" {{ $statusFilter == 1 ? 'selected' : '' }}>Đang vận chuyển</option>
								<option value="2" {{ $statusFilter == 2 ? 'selected' : '' }}>Đã giao hàng</option>
								<option value="3" {{ $statusFilter == 3 ? 'selected' : '' }}>Đã hủy</option>
							</select>
							<button type="submit" class="btn btn-primary">Lọc</button>
						</form>
					</div>
		
					<div class="card-body">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>#</th>
									<th>Họ tên</th>
									<th>Điện thoại</th>
									<th>Địa chỉ</th>
									<th>Ngày tạo</th>
									<th>Trạng thái</th>
									<th>Chức năng</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($orders as $index => $order)
								<tr>
									<td>{{ $orders->firstItem() + $index }}</td>
									<td>{{ $order->name }}</td>
									<td>{{ $order->phone }}</td>
									<td>{{ $order->address }}</td>
									<td>{{ date('H:i d/m/Y', strtotime($order->created_at)) }}</td>
									<td>
										@switch($order->status)
											@case(0)
												Đang chờ xử lý
											@break
					
											@case(1)
												Đang vận chuyển
											@break
					
											@case(2)
												Đã giao hàng
											@break
					
											@case(3)
												Đã hủy
											@break
										@endswitch
									</td>
									<td>
										<a href="{{ route('admin.order.orderdetail', $order->id) }}" class="btn btn-sm btn-info">
											<i class="far fa-eye"></i>
										</a>
										<a href="{{ route('admin.order.edit', $order->id) }}" class="btn btn-sm btn-primary">
											<i class="far fa-edit"></i>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						
						<div class= "my-4">
							{{$orders->appends(request()->query())->links()}}
		
						</div>
					</div>
				</div>
			</section>
			<!-- /.CONTENT -->
</x-layout-backend>