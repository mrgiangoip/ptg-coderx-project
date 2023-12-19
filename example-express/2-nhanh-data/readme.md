đây là fire views/index.ejs :"<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Data</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Mobile</th>
            <th>Points</th>
        </tr>
    </thead>
    <tbody id="dataList"></tbody>
</table>

<script>
    async function fetchDataAndDisplay() {
        try {
            const response = await fetch('http://localhost:3000/fetchData');
            const data = await response.json();

            const dataList = document.getElementById('dataList');
            dataList.innerHTML = data.map(customer => `
                <tr>
                    <td>${customer.id}</td>
                    <td>${customer.name}</td>
                    <td>${customer.mobile}</td>
                    <td>${customer.points}</td>
                </tr>
            `).join('');

        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    window.onload = fetchDataAndDisplay;
</script>
</body>
</html>
"
còn đây là file server.js:"const express = require('express');
const axios = require('axios');
const FormData = require('form-data');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Để cho phép frontend gọi API này
app.use(cors());

// Set EJS as the view engine
app.set('view engine', 'ejs');

// Route for the main page
app.get('/', (req, res) => {
    res.render('index');
});

app.get('/fetchData', async (req, res) => {
    let form = new FormData();
    form.append("version", "2.0");
    form.append("appId", "73659");
    form.append("businessId", "21995");
    form.append("accessToken", "u4JBZHWNzxm00sJU75beloRiGzrdC4RoX8tehodCu6pxCwcVjOK4pDwwxscFlUw2Z1Ghq5FlTEVkMATKn4twjb0p0xLS3xGNVOrJNbZawDZJmxGM5fXocSUEBbKZGhLYu4mL0pb1LxT17EHiikfFeDxvD");
    form.append("data", "{\"page\":1, \"level\": \"Vip1\"}");

    try {
        let response = await axios.post('https://open.nhanh.vn/api/customer/search', form, {
            headers: form.getHeaders()
        });
        
        res.json(response.data);
    } catch (error) {
        res.status(500).send("Error: " + error.message);
    }
});

app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
"
tôi truy cập vào http://localhost:3000/fetchData Tôi làm theo hướng dẫn và chạy file lấy được dữ liệu:"{"code":1,"data":{"totalPages":32428,"page":1,"customers":{"10231341":{"id":"10231341","type":"1","name":"zp anh cương","mobile":"0988992942","gender":null,"email":null,"address":"times city","birthday":null,"code":"KH31232","level":"Vip1","group":"VIP1","levelId":"2031","groupId":"1228","cityLocationId":"254","districtLocationId":"","wardLocationId":"","totalMoney":"250000","startedDate":"2017-11-02","startedDepotId":"","points":0,"totalBills":"0","lastBoughtDate":"","taxCode":"","businessName":"","businessAddress":"","description":""},"10231342":{"id":"10231342","type":"1","name":"Yến Nhi","mobile":"0979915807","gender":null,"email":null,"address":"28/60 Ngọc Hà","birthday":null,"code":"KHC9100","level":"","group":"","levelId":"","groupId":"","cityLocationId":"254","districtLocationId":"318","wardLocationId":"","totalMoney":"0","startedDate":"2017-11-02","startedDepotId":"","points":0,"totalBills":"0","lastBoughtDate":"","taxCode":"","businessName":"","businessAddress":"","description":""},"10231343":{"id":"10231343","type":"1","name":"yến nhi","mobile":"0969819656","gender":null,"email":null,"address":"55C Hàng Đậu","birthday":null,"code":"KHC7643","level":"","group":"VIP1","levelId":"","groupId":"1228","cityLocationId":"254","districtLocationId":"323","wardLocationId":"","totalMoney":"85000","startedDate":"2017-11-02","startedDepotId":"","points":0,"totalBills":"0","lastBoughtDate":"","taxCode":"","businessName":"","businessAddress":"","description":""},"10231371":{"id":"10231371","type":"1","name":"Vương Ngọc Thư","mobile":"0983624453","gender":null,"email":null,"address":"340 kim ngưu- quận Hai bà Trưng- HN","birthday":null,"code":"KH93375","level":"","group":"VIP1","levelId":"","groupId":"1228","cityLocationId":"","districtLocationId":"","wardLocationId":"","totalMoney":"90000","startedDate":"2017-11-02","startedDepotId":"","points":0,"totalBills":"0","lastBoughtDate":"","taxCode":"","businessName":"","businessAddress":"","description":""}}}}" tôi muốn biến chúng thành các bảng và cột html có chia số trang mỗi 1 trang khoảng 50 id, Tôi muốn lấy dữ liệu từ page: 1 đến page: cuối cùng và từ level: Vip1 đến Vip5, và các giá trị có trong data: {} bao gồm id, name, mobile, points thì phải làm thế nào hiển thị những thứ trên lên website?