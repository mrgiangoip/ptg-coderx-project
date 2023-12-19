// function tossCoin() {
// 	var value = Math.random();
// 	if (value < 0.5){
// 		console.log('Mặt sấp');
// 	} else {
// 		console.log('Mặt ngửa');
// 	}
// }
// tossCoin();

// function ShouldIDateHer() {
// 	var value = Math.random();
// 	if (value < 0.5){
// 		console.log('Yes, of course');
// 	} else {
// 		console.log('No, she has a boyfriend');
// 	}
// }
// ShouldIDateHer();

function countBills(bills){
	var total = 0;

	for (var bill of bills){
		if (!bill.fake) {
			total += bill.value;
	    } else {
		console.log('fake bill',bill);
		break;
	    }
	  }
 return total;
}

var bills = [
 {value: 10000},
 {value: 20000},
 {value: 20000, fake: true},
 {value: 500000},
];

var total = countBills(bills);
console.log(total);