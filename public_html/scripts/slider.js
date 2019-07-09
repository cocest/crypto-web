// image slider
(function () {
    // define and initialise varibles here
    var image_urls;
    var image_urls_length;
    var loaded_images = [];
    var loaded_img_counter = 0;
    var anim_time;
    var wait_interval;
    var img_slider_call_back;
    var image_cont;
    var image_elem_1;
    var image_elem_2;
    var play;
    var running = false;
    var curr_img_counter = 0;
    var prev_img_counter = 0;

    //lineary load all image one after the other
    function preloadImages(call_back) {
        loaded_images[loaded_img_counter] = new Image();
        loaded_images[loaded_img_counter].src = image_urls[loaded_img_counter];
        loaded_images[loaded_img_counter].onload = function () {
            //increment
            loaded_img_counter++;

            //check if all images is fully loaded
            if (loaded_img_counter === image_urls_length) { //case base
                call_back();

            } else {
                call_back();
                preloadImages();
            }
        };

        loaded_images[loaded_img_counter].onerror = function () {
            //increment
            loaded_img_counter++;

            //check if all images is fully loaded
            if (loaded_img_counter === image_urls_length) { //base case
                call_back();

            } else {
                call_back();
                preloadImages();
            }
        };
    };

    // animate the image
    function animate(direction) {
        switch (direction) {
            case "forward":
                //increase counter by one
                if (curr_img_counter == image_urls_length - 1) {
                    curr_img_counter = 0;
                }
                else {
                    curr_img_counter++;
                }

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

                // wait for animation to finish
                setTimeout(function () {
                    // start here

                }, anim_time * 1000);

                // call user pass in function
                img_slider_call_back(prev_img_counter, curr_img_counter);

                // set previous image counter
                prev_img_counter = curr_img_counter;

                break;
            case "backward":
                // code here

                break;

            default:
            // you shouldn't be here
        }
    }

    // this function start image animation
    function startAnimation() {
        if (!running) { // if is not running start animation
            play = setInterval(function () {

                animate("forward");

            }, wait_interval);

            running = true;
        }
    }

    // this function stop image animation
    function stopAnimation() {
        if (running) { // if is running, stop animation
            clearInterval(play);
            running = false;
        }
    }

    // call this method to start animation
    window.imageSlider = function (img_urls, anim_t, wait_int, call_back) {
        image_urls = img_urls;
        image_urls_length = img_urls.length;
        anim_time = anim_t;
        wait_interval = wait_int ? (wait_int > anim_t ? wait_int : anim_t + 4) : anim_t + 4;
        img_slider_call_back = call_back;

        // start loading image
        preloadImages(function () {
            if (loaded_img_counter === 1) {
                //get parent element define by the user
                image_cont = document.getElementById("imageslider-cont");

                // create element for first image
                image_elem_1 = document.createElement("div");
                image_elem_1.setAttribute("style ",
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
                image_elem_2.setAttribute("style ",
                    "position: absolute;" +
                    "top: 0px;" +
                    "width: inherit;" +
                    "height: inherit;" +

                    // background image
                    "background-image: url(" + image_urls[1] + ");" +
                    "background-repeat: no-repeat;" +
                    "background-position: center;" +
                    "background-size: cover;" +

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
})()