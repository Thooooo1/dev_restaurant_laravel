@if(session('success'))
    <script>
        toastr.success("{{ session('success') }}", "Thông báo");
    </script>
@endif

@if(session('error'))
    <script>
        toastr.error("{{ session('error') }}", "Báo lỗi");
    </script>
@endif
