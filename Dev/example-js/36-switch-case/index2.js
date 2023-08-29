function findLCM() {
  let number = 1;
  let lcm = 0;

  while (number <= 1000) {
    if (number % 3 === 0 && number % 5 === 0) {
      lcm = number;
    }
    number++;
  }

  return lcm;
}