function drawHeart(color) {
  let heart = "";

  heart += `${color}    * * *   * * *\n`;
  heart += `${color}  * * * * * * * * *\n`;
  heart += `${color} * * * * * * * * * *\n`;
  heart += `${color} * * * * * * * * * *\n`;
  heart += `${color}  * * * * * * * * *\n`;
  heart += `${color}    * * * * * * *\n`;
  heart += `${color}      * * * * *\n`;
  heart += `${color}        * * *\n`;
  heart += `${color}          *\n`;

  return heart;
}

function getRandomColor() {
  const colors = ["\x1b[31m", "\x1b[32m", "\x1b[33m", "\x1b[34m", "\x1b[35m", "\x1b[36m"];
  return colors[Math.floor(Math.random() * colors.length)];
}

function animateHeart() {
  setInterval(function () {
    const color = getRandomColor();
    console.clear();
    console.log(drawHeart(color));
  }, 500); // Thời gian nhấp nháy (500ms = 0.5 giây)
}

animateHeart();