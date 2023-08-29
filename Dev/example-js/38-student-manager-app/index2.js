var readlineSync = require('readline-sync');
var fs = require('fs');

var contacts = [];

function loadData() {
var fileContent = fs.readFileSync('./data2.json');
contacts = JSON.parse(fileContent);
return contacts; }

function creatContact() {
var name = readlineSync.question('Name: ');
var phone = readlineSync.question('Phone: ');
var contact = {
name: name,
phone: phone
};
contacts.push(contact);
var content = JSON.stringify(contacts);
fs.writeFileSync('./data2.json', content, { encoding: 'utf8' });
}

function editContact() {
loadData();
var name = readlineSync.question('Enter name contact want edit: ');
var newName = readlineSync.question('Enter new name for contact '+name+': ');
var newPhone = readlineSync.question('Enter new phone for contact '+name+': ');
for (contact of contacts) {
if (contact.name === name) {
contact.name = newName;
contact.phone = newPhone;
console.log(contact);
}
}

var content = JSON.stringify(contacts);
fs.writeFileSync('./data2.json', content, { encoding: 'utf8' }); }

function deleteContact() {
loadData();
var name = readlineSync.question('Enter name contact will delete: ');
for (contact of contacts) {
if (contact.name === name) {
indexContactDelete = contacts.indexOf(contact);
contacts.splice(indexContactDelete,1);
}
}

var content = JSON.stringify(contacts);
fs.writeFileSync('./data2.json', content, { encoding: 'utf8' }); }

function searchContact() {
var contacts = loadData();
var foundContact = [];
var notFoundContact = [];
var name = readlineSync.question('Enter name contact: ');
for (contact of contacts) {
if (contact.name === name) {
foundContact.push(contact);
console.log(foundContact);
} else {
notFoundContact.push(name);
}
}
return foundContact;
}

function showMenu() {
console.log("=======================");
console.log('1. Create a new contact');
console.log('2. Edit contact');
console.log('3. Delete contact');
console.log('4. Search contact');

var option = readlineSync.question('> ');
//console.log(option);
switch (option) {
case '1':
creatContact();
showMenu();
break;
case '2':
editContact();
showMenu();
break;
case '3':
deleteContact();
showMenu();
break;
case '4':
searchContact();
showMenu();
break;
default:
console.log("Wrong option");
showMenu();
break;
}

}

function main() {
loadData();
showMenu();
}

main();