var low = require('lowdb');
var FileSync = require('lowdb/adapters/FileSync');

const adapter = new FileSync('db.json');
const db = low(adapter);

db.defaults({ users: [], products: [] }).write();

function getProducts() {
    return db.get('products').value();
}

module.exports = {
    db,
    getProducts
};
