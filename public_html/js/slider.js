// image slider
(function () {
    // define and initialise varibles here
    var image_urls;
    var image_urls_length;
    var is_slider_initiated = false;
    var loaded_images = [];
    var loaded_img_counter = 0;
    var anim_time;
    var wait_interval;
    var img_slider_call_back;
    var image_cont;
    var image_elem_1;
    var image_elem_2;
    var running = false;
    var wait = false;
    var curr_img_counter = 0;
    var prev_img_counter = 0;
    var req_anim_handler;
    var starting_anim = null;
    var requestAnimationFrame = window.requestAnimationFrame
        || window.mozRequestAnimationFrame
        || window.webkitRequestAnimationFrame
        || function (fn) { return window.setTimeout(fn, 16); };
    var cancelAnimationFrame = window.cancelAnimationFrame
        || window.mozCancelAnimationFrame
        || window.webkitCancelAnimationFrame
        || function (request_id) { clearTimeout(request_id); };

    //lineary load all image one after the other
    function preloadImages(call_back) {
        loaded_images[loaded_img_counter] = new Image();
        loaded_images[loaded_img_counter].src = image_urls[loaded_img_counter];
        loaded_images[loaded_img_counter].onload = function () {
            //increment
            loaded_img_counter++;

            //check if all images is fully loaded
            if (loaded_img_counter >= image_urls_length) { //case base
                call_back();

            } else {
                call_back();
                preloadImages(call_back);
            }
        };

        loaded_images[loaded_img_counter].onerror = function () {
            //increment
            loaded_img_counter++;

            //check if all images is fully loaded
            if (loaded_img_counter >= image_urls_length) { //base case
                call_back();

            } else {
                call_back();
                preloadImages(call_back);
            }
        };
    };

    // animate the image
    function animate(direction) {
        wait = true; // wait for animation to complete

        switch (direction) {
            case "forward":
                //increase counter by one
                if (curr_img_counter == loaded_img_counter - 1) {
                    curr_img_counter = 0;
                }
                else {
                    curr_img_counter++;
                }

                image_elem_1.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[prev_img_counter] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

                    // set animation properties
                    "opacity: 0;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 10;"
                );

                // show image
                image_elem_2.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[curr_img_counter] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

                    // set animation properties
                    "opacity: 1;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 20;"
                );

                // swap the two image element
                temp_elem = image_elem_2;
                image_elem_2 = image_elem_1;
                image_elem_1 = temp_elem;

                // call user pass in function
                img_slider_call_back(prev_img_counter, curr_img_counter);

                // set previous image counter
                prev_img_counter = curr_img_counter;

                // wait for animation to finish
                setTimeout(function () {
                    wait = false; // animation has finished executing

                }, anim_time * 1000);

                break;

            case "backward":
                // decrease counter by one | loaded_img_counter
                if (curr_img_counter == 0) {
                    curr_img_counter = loaded_img_counter - 1;

                } else {
                    curr_img_counter--;
                }

                // place image to front
                image_elem_1.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[prev_img_counter] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

                    // set animation properties
                    "opacity: 1;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 20;"
                );

                // place second image at back
                image_elem_2.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[curr_img_counter] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

                    // set animation properties
                    "opacity: 1;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 10;"
                );

                // fade out first image
                image_elem_1.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[prev_img_counter] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

                    // set animation properties
                    "opacity: 0;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 20;"
                );

                // swap the two image element
                temp_elem = image_elem_2;
                image_elem_2 = image_elem_1;
                image_elem_1 = temp_elem;

                // call user pass in function
                img_slider_call_back(prev_img_counter, curr_img_counter);

                // set previous image counter
                prev_img_counter = curr_img_counter;

                // wait for animation to finish
                setTimeout(function () {
                    wait = false; // animation has finished executing

                }, anim_time * 1000);

                break;

            default:
                // you shouldn't be here
                wait = false; // animation has finished executing
        }
    }

    // this function start image animation
    function startAnimation() {
        if (!running) { // if is not running start animation
            running = true;
            var elapsed_time;
            var time_duration = Date.now() + wait_interval * 1000;

            var step = function () {
                elapsed_time = Date.now();

                if (elapsed_time > time_duration) {
                    time_duration = Date.now() + wait_interval * 1000;
                    animate("forward");
                }

                req_anim_handler = requestAnimationFrame(step);
            };

            step(); // start
        }
    }

    // this function stop image animation
    function stopAnimation() {
        if (running) { // if is running, stop animation
            cancelAnimationFrame(req_anim_handler);
            running = false;
        }
    }

    // call this method to start animation
    window.imageSlider = function (img_urls, anim_t, wait_int, call_back) {
        if (is_slider_initiated) {
            return;
        }

        is_slider_initiated = true;

        image_urls = img_urls;
        image_urls_length = img_urls.length;
        anim_time = anim_t;
        wait_interval = wait_int ? (wait_int > anim_t ? wait_int : anim_t + 4) : anim_t + 4;
        img_slider_call_back = call_back;

        // start loading image
        preloadImages(function () {
            if (loaded_img_counter === 1) {
                // get parent element define by the user
                image_cont = document.getElementById("imageslider");

                // create element for first image
                image_elem_1 = document.createElement("div");
                image_elem_1.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[0] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

                    // set animation properties
                    "opacity: 1;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 10;"
                );

                //append element
                image_cont.appendChild(image_elem_1);
            }
            else if (loaded_img_counter === 2) {
                // create element for first image
                image_elem_2 = document.createElement("div");
                image_elem_2.setAttribute("style",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // set animation properties
                    "opacity: 0;" +
                    "transition: opacity " + anim_time + "s;" +
                    "z-index: 20;"
                );

                //append element
                image_cont.appendChild(image_elem_2);

                //start animation
                startAnimation();
            }
        });
    };

    // slide next image
    window.nextImage = function () {
        // check if animation is running
        if (!wait) {
            // stop the animation
            stopAnimation();

            animate("forward");

            if (starting_anim !== null) {
                clearTimeout(starting_anim);
            }

            // wait before starting the animation
            starting_anim = setTimeout(function () {
                starting_anim = null;
                startAnimation();
            }, anim_time * 1000);
        }
    };

    // slide previous image
    window.prevImage = function () {
        // check if animation is running
        if (!wait) {
            // stop the animation
            stopAnimation();

            animate("backward");

            if (starting_anim !== null) {
                clearTimeout(starting_anim);
            }

            // wait before starting the animation
            starting_anim = setTimeout(function () {
                starting_anim = null;
                startAnimation();
            }, anim_time * 1000);
        }
    };

    // animate click image
    window.gotoImage = function (goto_index) {
        // check if animation is running
        if (!wait) {
            // check if pass index is valid
            if (typeof goto_index === "number" && goto_index % 1 === 0) {
                // check if is within bounds
                if (goto_index > -1 && goto_index < loaded_img_counter) {
                    // stop the animation
                    stopAnimation();

                    // determin direction to animate
                    if (goto_index > curr_img_counter) { //forward
                        curr_img_counter = goto_index - 1;
                        animate("forward");

                        if (starting_anim !== null) {
                            clearTimeout(starting_anim);
                        }

                        // wait before starting the animation
                        starting_anim = setTimeout(function () {
                            starting_anim = null;
                            startAnimation();
                        }, anim_time * 1000);
                    }
                    else if (goto_index < curr_img_counter) { //backward
                        curr_img_counter = goto_index + 1;
                        animate("backward");

                        if (starting_anim !== null) {
                            clearTimeout(starting_anim);
                        }

                        //wait before starting the animation
                        starting_anim = setTimeout(function () {
                            starting_anim = null;
                            startAnimation();
                        }, anim_time * 1000);
                    }
                }
            }
        }
    };
})()