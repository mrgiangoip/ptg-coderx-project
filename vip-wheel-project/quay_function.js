// Create new wheel object specifying the parameters at creation time.
            let theWheel = new Winwheel({
                'outerRadius'     : 212,        // Set outer radius so wheel fits inside the background.
                'innerRadius'     : 75,         // Make wheel hollow so segments don't go all way to center.
                'textFontSize'    : 24,         // Set default font size for the segments.
                'textOrientation' : 'vertical', // Make text vertial so goes down from the outside of wheel.
                'textAlignment'   : 'outer',    // Align text to outside of wheel.
                'numSegments'     : 12,         // Specify number of segments.
                'segments'        :             // Define segments including colour and text.
                [                               // font size and test colour overridden on backrupt segments.
                   {'fillStyle' : '#5dcce9', 'text' : 'WINK', 'textFontSize' : 12},
                   {'fillStyle' : '#5dcce9', 'text' : 'SUNGLASSES', 'textFontSize' : 12},
                   {'fillStyle' : '#fed041', 'text' : 'KISSING', 'textFontSize' : 12},
                   {'fillStyle' : '#fa777d', 'text' : 'RELAXED', 'textFontSize' : 12},
                   {'fillStyle' : '#fa777d', 'text' : 'FLUSHED', 'textFontSize' : 12},
                   {'fillStyle' : '#5dcce9', 'text' : 'GRIN', 'textFontSize' : 12},
                   {'fillStyle' : '#5dcce9', 'text' : 'NEUTRAL', 'textFontSize' : 12},
                   {'fillStyle' : '#000000', 'text' : 'Chúc may mắn', 'textFontSize' : 12, 'textFillStyle' : '#ffffff'},
                   {'fillStyle' : '#fed041', 'text' : 'ANGRY', 'textFontSize' : 12},
                   {'fillStyle' : '#fa777d', 'text' : 'HEART EYES', 'textFontSize' : 12},
                   {'fillStyle' : '#fa777d', 'text' : 'JOY', 'textFontSize' : 12},
                   {'fillStyle' : '#ffffff', 'text' : 'Mất lượt', 'textFontSize' : 12}
                ],
                'animation' :           // Specify the animation to use.
                {
                    'type'     : 'spinToStop',
                    'duration' : 10,    // Duration in seconds.
                    'spins'    : 3,     // Default number of complete spins.
                    'callbackFinished' : alertPrize,
                    'callbackSound'    : playSound,   // Function to call when the tick sound is to be triggered.
                    'soundTrigger'     : 'pin'        // Specify pins are to trigger the sound, the other option is 'segment'.
                },
                'pins' :                // Turn pins on.
                {
                    'number'     : 24,
                    'fillStyle'  : 'silver',
                    'outerRadius': 4,
                }
            });

            // Loads the tick audio sound in to an audio object.
            let audio = new Audio('./tick.mp3');

            // This function is called when the sound is to be played.
            function playSound()
            {
                // Stop and rewind the sound if it already happens to be playing.
                audio.pause();
                audio.currentTime = 0;

                // Play the sound.
                audio.play();
            }

            // Vars used by the code in this page to do power controls.
            let wheelPower    = 0;
            let wheelSpinning = false;

            // -------------------------------------------------------
            // Function to handle the onClick on the power buttons.
            // -------------------------------------------------------
            function powerSelected(powerLevel)
            {
                // Ensure that power can't be changed while wheel is spinning.
                if (wheelSpinning == false) {
                    // Reset all to grey incase this is not the first time the user has selected the power.
                    document.getElementById('pw1').className = "";
                    document.getElementById('pw2').className = "";
                    document.getElementById('pw3').className = "";

                    // Now light up all cells below-and-including the one selected by changing the class.
                    if (powerLevel >= 1) {
                        document.getElementById('pw1').className = "pw1";
                    }

                    if (powerLevel >= 2) {
                        document.getElementById('pw2').className = "pw2";
                    }

                    if (powerLevel >= 3) {
                        document.getElementById('pw3').className = "pw3";
                    }

                    // Set wheelPower var used when spin button is clicked.
                    wheelPower = powerLevel;

                    // Light up the spin button by changing it's source image and adding a clickable class to it.
                    document.getElementById('spin_button').src = "./spin_on.png";
                    document.getElementById('spin_button').className = "clickable";
                }
            }

            // -------------------------------------------------------
            // Click handler for spin button.
            // -------------------------------------------------------
            function startSpin()
            {
                // Ensure that spinning can't be clicked again while already running.
                if (wheelSpinning == false) {
                    // Based on the power level selected adjust the number of spins for the wheel, the more times is has
                    // to rotate with the duration of the animation the quicker the wheel spins.
                    if (wheelPower == 1) {
                        theWheel.animation.spins = 3;
                    } else if (wheelPower == 2) {
                        theWheel.animation.spins = 6;
                    } else if (wheelPower == 3) {
                        theWheel.animation.spins = 10;
                    }

                    // Disable the spin button so can't click again while wheel is spinning.
                    document.getElementById('spin_button').src       = "./spin_off.png";
                    document.getElementById('spin_button').className = "";

                    // Begin the spin animation by calling startAnimation on the wheel object.
                    theWheel.startAnimation();

                    // Set to true so that power can't be changed and spin button re-enabled during
                    // the current animation. The user will have to reset before spinning again.
                    wheelSpinning = true;
                    
                }
            }
              // -------------------------------------------------------
            // Function for reset button.
            // -------------------------------------------------------
            function resetWheel()
            {
                theWheel.stopAnimation(false);  // Stop the animation, false as param so does not call callback function.
                theWheel.rotationAngle = 0;     // Re-set the wheel angle to 0 degrees.
                theWheel.draw();                // Call draw to render changes to the wheel.

                document.getElementById('pw1').className = "";  // Remove all colours from the power level indicators.
                document.getElementById('pw2').className = "";
                document.getElementById('pw3').className = "";

                wheelSpinning = false;          // Reset to false to power buttons and spin can be clicked again.
            }

            // -------------------------------------------------------
            // Called when the spin animation has finished by the callback feature of the wheel because I specified callback in the parameters.
             // -------------------------------------------------------
            function alertPrize(indicatedSegment) {
                    let prizeResult = indicatedSegment.text;
                    let deductSpin = false;  // Khởi tạo biến deductSpin

                    if (indicatedSegment.text == 'Mất lượt') {
                        alert('Xin lỗi bạn đã bị mất lượt.');
                        deductSpin = true; // Đặt deductSpin thành true để trừ lượt quay
                    } else if (indicatedSegment.text == 'Chúc may mắn') {
                        alert('Chúc bạn may mắn lần sau.');
                        deductSpin = true; // Đặt deductSpin thành true để trừ lượt quay
                    } else {
                        alert("Chúc mừng bạn đã được giảm giá " + prizeResult);
                    }

                    // Gửi yêu cầu AJAX để cập nhật kết quả quay thưởng và/hoặc trừ lượt quay vào cơ sở dữ liệu
                    $.ajax({
                        type: "POST",
                        url: "./update_prize.php",
                        data: {
                            prizeResult: prizeResult,
                            deductSpin: deductSpin  // Truyền biến deductSpin
                        },
                        success: function(response) {
                            alert(response);
                            location.reload();
                        }
                    });
                }