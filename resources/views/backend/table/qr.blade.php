<x-layout-backend>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Mã QR cho bàn: {{ $table->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.table.index') }}" class="btn btn-sm btn-secondary">Quay lại</a>
            </div>
        </div>
        <div class="card-body text-center">
            <div class="mb-4">
                {!! $qrcode !!}
            </div>
            <p><strong>URL:</strong> <a href="{{ $url }}" target="_blank">{{ $url }}</a></p>
            
            <button onclick="window.print()" class="btn btn-success mt-3">
                <i class="fas fa-print"></i> In mã QR
            </button>
        </div>
    </div>
</x-layout-backend>
