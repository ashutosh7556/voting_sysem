 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Slideshow</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .slideshow-container {
            max-width: 1000px;
            position: relative;
            margin: auto;
        }

        .slides {
            display: none;
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .prev, .next {
            position: absolute;
            top: 50%;
            padding: 16px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .prev {
            left: 0;
        }

        .next {
            right: 0;
        }

        .prev:hover, .next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
    </style>
</head>
<body>

<div class="slideshow-container">
    <img class="slides" src="image1.jpg" alt="Image 1">
    <img class="slides" src="image2.jpg" alt="Image 2">
    <img class="slides" src="image3.jpg" alt="Image 3">

    <button class="prev" onclick="plusSlides(-1)">&#10094;</button>
    <button class="next" onclick="plusSlides(1)">&#10095;</button>
</div>

<script>
    let slideIndex = 0;

    function showSlides() {
        let slides = document.getElementsByClassName("slides");
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        slideIndex++;
        if (slideIndex > slides.length) {
            slideIndex = 1;
        }
        slides[slideIndex - 1].style.display = "block";
        setTimeout(showSlides, 3000); // Change image every 3 seconds
    }

    function plusSlides(n) {
        slideIndex += n;
        if (slideIndex > slides.length) {
            slideIndex = 1;
        }
        if (slideIndex < 1) {
            slideIndex = slides.length;
        }
        showSlides();
    }

    showSlides(); // sStart the slideshow
</script>

</body>
</html>
