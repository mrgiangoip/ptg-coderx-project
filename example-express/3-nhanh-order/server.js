const express = require('express');
const bodyParser = require('body-parser');
const axios = require('axios');

const app = express();
const PORT = 3000;

app.set('view engine', 'ejs');
app.use(bodyParser.json());

app.get('/', async (req, res) => {
    let allOrderIds = [];
    const maxPages = 80; // Giả sử có tối đa 200 trang
    const saleChannelForStore = 2; // Giả sử giá trị saleChannel cho mua hàng tại cửa hàng là 2

    for (let page = 1; page <= maxPages; page++) {
        let form = new URLSearchParams();
        form.append("version", "2.0");
        form.append("appId", "73659");
        form.append("businessId", "21995");
        form.append("accessToken", "u4JBZHWNzxm00sJU75beloRiGzrdC4RoX8tehodCu6pxCwcVjOK4pDwwxscFlUw2Z1Ghq5FlTEVkMATKn4twjb0p0xLS3xGNVOrJNbZawDZJmxGM5fXocSUEBbKZGhLYu4mL0pb1LxT17EHiikfFeDxvD");
        form.append("data", `{ "page": ${page}, "saleChannel": ${saleChannelForStore} }`);

        try {
            const response = await axios.post('https://open.nhanh.vn/api/order/index', form);
            if (response.data && response.data.data && response.data.data.orders) {
                const orderIds = Object.values(response.data.data.orders).map(order => order.id);
                allOrderIds = allOrderIds.concat(orderIds);
            } else {
                // Nếu không có thêm dữ liệu, dừng vòng lặp
                break;
            }
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    res.json(allOrderIds);
});

app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
