function getTichketFee(person){
 var basePrice = 10000;
 if (person.age < 15){
 	return 10000 * 0.8;
 } else if (person.age > 60){
 	return 10000 * 0.9
 } else {
 	return basePrice;
 }
}
var person = {
	age: 105
};
var fee = getTichketFee(person);
console.log(fee);