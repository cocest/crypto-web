Using The Resize Image PHP Class
Because we have created this to allow you to resize the image in multiple ways it means that there are different ways of using the class.

Resize the image to an exact size.
Resize the image to a max width size keeping aspect ratio of the image.
Resize the image to a max height size keeping aspect ratio of the image.
Resize the image to a given width and height and allow the code to work out which way of resizing is best keeping the aspect ratio.
You can save the created resize image on the server.
You can download the created resize image on the server.
Resize Exact Size
To resize an image to an exact size you can use the following code. First pass in the image we want to resize in the class constructor, then define the width and height with the scale option of exact. The class will now have the create dimensions to create the new image, now call the function saveImage() and pass in the new file location to the new image.

$resize = new ResizeImage('images/Be-Original.jpg');
$resize->resizeTo(100, 100, 'exact');
$resize->saveImage('images/be-original-exact.jpg');
Resize Max Width Size
If you choose to set the image to be an exact size then when the image is resized it could lose it's aspect ratio, which means the image could look stretched. But if you know the max width that you want the image to be you can resize the image to a max width, this will keep the aspect ratio of the image.

$resize = new ResizeImage('images/Be-Original.jpg');
$resize->resizeTo(100, 100, 'maxWidth');
$resize->saveImage('images/be-original-maxWidth.jpg');
Resize Max Height Size
Just as you can select a max width for the image while keeping aspect ratio you can also select a max height while keeping aspect ratio.

$resize = new ResizeImage('images/Be-Original.jpg');
$resize->resizeTo(100, 100, 'maxHeight');
$resize->saveImage('images/be-original-maxHeight.jpg');
Resize Auto Size From Given Width And Height
You can also allow the code to work out the best way to resize the image, so if the image height is larger than the width then it will resize the image by using the height and keeping aspect ratio. If the image width is larger than the height then the image will be resized using the width and keeping the aspect ratio.

$resize = new ResizeImage('images/Be-Original.jpg');
$resize->resizeTo(100, 100);
$resize->saveImage('images/be-original-default.jpg');
Download The Resized Image
The default behaviour for this class is to save the image on the server, but you can easily change this to download by passing in a true parameter to the saveImage method.

$resize = new ResizeImage('images/Be-Original.jpg');
$resize->resizeTo(100, 100, 'exact');
$resize->saveImage('images/be-original-exact.jpg', "100", true);