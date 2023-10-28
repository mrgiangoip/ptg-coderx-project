// db.js
var low = require('lowdb');
var FileSync = require('lowdb/adapters/FileSync');

const adapter = new FileSync('db.json');
const db = low(adapter);

db.defaults({ users: [], products: [] }).write();

module.exports = db;  // Xuất khẩu trực tiếp đối tượng db
