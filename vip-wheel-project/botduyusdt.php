<?php
require './vendor/simple_html_dom.php';

// Bot Telegram
$botToken = '6499262263:AAFBF8Ow5W809zLp9SD_LBoKE3b82vY1-T8';
$chatId = '-4023402810';

// Các URL của Binance
$buyUrl = 'https://p2p.binance.com/vi/trade/sell/USDT?fiat=VND&payment=all-payments';
$sellUrl = 'https://p2p.binance.com/vi/trade/all-payments/USDT?fiat=VND';

// Hàm để lấy tỷ giá từ URL
function getPrices($url) {
    // Sử dụng CURL để lấy nội dung trang
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
    $content = curl_exec($ch);
    curl_close($ch);

    $html = str_get_html($content);

    // Kiểm tra nếu không lấy được nội dung trang
    if (!$html) {
        return "Không thể truy cập trang Binance.";
    }

    // Tìm phần tử có data-tutorial-id="trade_filter"
    $tradeFilter = $html->find('[data-tutorial-id="trade_filter"]', 0);

    if (!$tradeFilter) {
        return "Không tìm thấy phần tử trade_filter.";
    }

    // Tìm các phần tử có class "bn-table-cell"
    $elements = $tradeFilter->find('.bn-table-cell');

    // Kiểm tra xem có đủ phần tử không
    if (count($elements) < 14) { // Cần ít nhất 14 phần tử cho Mua và Bán
        return "Không đủ dữ liệu cho Mua và Bán.";
    }

    // Lấy giá trị từ hàng thứ 7 cho Mua và Bán
    $buyPrice = $elements[6]->plaintext; // Giá bên mua hàng thứ 7
    $sellPrice = $elements[13]->plaintext; // Giá bên bán hàng thứ 7

    return "Tỷ giá Mua: $buyPrice\nTỷ giá Bán: $sellPrice";
}

// Lấy tỷ giá từ hai URL
$buyMessage = getPrices($buyUrl);
$sellMessage = getPrices($sellUrl);

// Tạo thông điệp tổng hợp
$message = "Tỷ giá Mua từ URL 1:\n$buyMessage\n\nTỷ giá Mua từ URL 2:\n$sellMessage";

// In giá trị ra màn hình
echo $message;

// Gửi thông tin vào nhóm Telegram
$telegramApiUrl = "https://api.telegram.org/bot$botToken/sendMessage";
$postData = [
    'chat_id' => $chatId,
    'text' => $message,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegramApiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// In ra phản hồi từ Telegram để kiểm tra
var_dump($response);
?>