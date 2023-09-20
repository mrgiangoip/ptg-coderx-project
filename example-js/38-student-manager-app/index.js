var readlineSync = require('readline-sync');
var fs = require('fs');

var students = [];

function loadData(){
	var Filecontent = fs.readFileSync('./data.json');
	students = JSON.parse(Filecontent);
}

function showMenu(){
 console.log('1. Show All Student');
 console.log('2. Show Create a new Student');
 console.log('3. Show Save And Exit');

 var option = readlineSync.question('> ');
	switch (option){
	 case '1':
	 	showStudents();
	 	showMenu();
	 	break;
	 case '2':
	 	showCreateStudent();
	 	showMenu();
	 	break;
	 case '3':
	 	saveAndExit();
	 	break;
	  default:
	  console.log('wrong option');
	  showMenu();
	  break;
	}
};

function showStudents(){
	for(var student of students){
		console.log(student.name, student.age, student.class);
	}
}

function showCreateStudent(){
	var name = readlineSync.question('Name: ');
	var age = readlineSync.question('Age: ');
	var className = readlineSync.question('Class: ');
	var student = {
		name: name,
		age: parseInt(age),
		class: className
	}
	students.push(student);
}
function saveAndExit(){
	var content = JSON.stringify(students);
	fs.writeFileSync('./data.json', content, { encoding: 'utf8'});
}

function main() {
 loadData();
 showMenu();
}

main();