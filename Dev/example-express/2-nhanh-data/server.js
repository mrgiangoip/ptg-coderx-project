const express = require('express');
const axios = require('axios');
const FormData = require('form-data');
const cors = require('cors');
const ejs = require('ejs');
const paginate = require('express-paginate'); // Sử dụng thư viện express-paginate

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
    const maxPage = 30; // Số trang tối đa bạn muốn lấy (thay đổi theo nhu cầu)
    const levels = ["Vip1", "Vip2", "Vip3", "Vip4", "Vip5"]; // Các mức level bạn muốn lấy
    const pageSize = 50; // Số lượng ID trên mỗi trang

    let allData = [];
    
    for (let currentPage = 1; currentPage <= maxPage; currentPage++) {
        for (const level of levels) {
            let form = new FormData();
            form.append("version", "2.0");
            form.append("appId", "73659");
            form.append("businessId", "21995");
            form.append("accessToken", "u4JBZHWNzxm00sJU75beloRiGzrdC4RoX8tehodCu6pxCwcVjOK4pDwwxscFlUw2Z1Ghq5FlTEVkMATKn4twjb0p0xLS3xGNVOrJNbZawDZJmxGM5fXocSUEBbKZGhLYu4mL0pb1LxT17EHiikfFeDxvD");
            form.append("data", `{"page":${currentPage}, "level": "${level}"}`);
    
            try {
                let response = await axios.post('https://open.nhanh.vn/api/customer/search', form, {
                    headers: form.getHeaders()
                });
    
                if (response.data.code === 1) {
                    const customers = response.data.data.customers;
                    for (const customerId in customers) {
                        const customer = customers[customerId];
                        allData.push({
                            id: customer.id,
                            name: customer.name,
                            mobile: customer.mobile,
                            points: customer.points,
                            level: level, // Thêm trường level vào đối tượng customer
                        });

                        if (allData.length === maxPage * levels.length * pageSize) {
                            // Đã lấy đủ số lượng ID, kết thúc lặp
                            break;
                        }
                    }
                }
    
                if (allData.length === maxPage * levels.length * pageSize) {
                    // Đã lấy đủ số lượng ID, kết thúc lặp
                    break;
                }
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        }

        if (allData.length === maxPage * levels.length * pageSize) {
            // Đã lấy đủ số lượng ID, kết thúc lặp
            break;
        }
    }
    
    res.json(allData);
});


app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
