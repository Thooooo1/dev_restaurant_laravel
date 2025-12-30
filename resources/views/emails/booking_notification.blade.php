<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đặt bàn</title>
</head>
<body>
    <h2>Xin chào {{ $booking->name }},</h2>
    <p>Bạn đã đặt bàn thành công tại nhà hàng DevRestaurant.</p>
    <p><strong>Thông tin đặt bàn:</strong></p>
    <ul>
        <li><strong>Số người:</strong> {{ $booking->number_of_people }}</li>
        <li><strong>Ngày:</strong> {{ $booking->booking_date }}</li>
        <li><strong>Giờ:</strong> {{ $booking->booking_time }}</li>
        <li><strong>Ghi chú:</strong> {{ $booking->note ?? 'Không có' }}</li>
    </ul>
    <p>Chúng tôi mong được phục vụ bạn!</p>
</body>
</html>
