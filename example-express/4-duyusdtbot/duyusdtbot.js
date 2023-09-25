const puppeteer = require('puppeteer');
const axios = require('axios');

const botToken = '6499262263:AAFBF8Ow5W809zLp9SD_LBoKE3b82vY1-T8';
const chatId = '-4023402810';

async function fetchPrice(url, rowIndex, cellIndex) {
    const browser = await puppeteer.launch({ headless: "new" }); // Use the new headless mode
    const page = await browser.newPage();
    await page.goto(url, { waitUntil: 'networkidle2' });

    // Wait for the element to appear
    await page.waitForSelector(`tr:nth-child(${rowIndex + 1}) td:nth-child(${cellIndex + 1})`, { timeout: 5000 });

    try {
        const price = await page.$eval(`tr:nth-child(${rowIndex + 1}) td:nth-child(${cellIndex + 1})`, el => el.textContent.trim());
        return price;
    } catch (error) {
        console.error('Error fetching price:', error.message);
        return null;
    } finally {
        await browser.close();
    }
}

(async () => {
    const buyUrl = 'https://p2p.binance.com/vi/trade/sell/USDT?fiat=VND&payment=all-payments';
    const sellUrl = 'https://p2p.binance.com/vi/trade/all-payments/USDT?fiat=VND';

    const buyPrice = await fetchPrice(buyUrl, 7, 2);
    const sellPrice = await fetchPrice(sellUrl, 7, 2);

    const message = `Giá mua vào: ${sellPrice}\nGiá bán ra: ${buyPrice}`;
    console.log(message);

    try {
        const response = await axios.post(`https://api.telegram.org/bot${botToken}/sendMessage`, {
            chat_id: chatId,
            text: message,
        });
        console.log(response.data);
    } catch (error) {
        console.error('Error sending message:', error.message);
    }
})();
