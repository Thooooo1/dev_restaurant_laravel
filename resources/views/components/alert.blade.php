<script>
    toastr.options = {
        closeButton: true,
        progressBar: true,
        timeOut: 1500,
        extendedTimeOut: 1000,
        positionClass: 'toast-top-right',
    };
</script>

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
