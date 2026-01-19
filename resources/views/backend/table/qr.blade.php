<x-layout-backend>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Mã QR cho bàn: {{ $table->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.table.index') }}" class="btn btn-sm btn-secondary">Quay lại</a>
            </div>
        </div>
        <div class="card-body text-center">
            <div class="mb-4" id="qrcode-container">
                {!! $qrcode !!}
            </div>
            <p><strong>URL:</strong> <a href="{{ $url }}" target="_blank">{{ $url }}</a></p>
            
            <button onclick="downloadQR()" class="btn btn-success mt-3">
                <i class="fas fa-download"></i> Tải ảnh JPG
            </button>
        </div>
    </div>
    
    <script>
        function downloadQR() {
            const svg = document.querySelector('#qrcode-container svg');
            if (!svg) return;
            
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const svgData = new XMLSerializer().serializeToString(svg);
            const img = new Image();
            
            // Chuyển đổi SVG thành base64
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const url = URL.createObjectURL(svgBlob);
            
            img.onload = function() {
                canvas.width = img.width;
                canvas.height = img.height;
                // Vẽ nền trắng
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0);
                
                const jpgUrl = canvas.toDataURL('image/jpeg');
                const link = document.createElement('a');
                link.href = jpgUrl;
                link.download = 'QR_Ban_{{ $table->name }}.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            };
            
            img.src = url;
        }
    </script>
</x-layout-backend>
