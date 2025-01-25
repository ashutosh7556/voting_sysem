 let slideIndex = 0;

function showSlides() {
    let slides = document.getElementsByClassName("slides");
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none"; // Hide all slides
    }
    slideIndex++;
    if (slideIndex > slides.length) {
        slideIndex = 1; // Loop back to the first slide
    }
    slides[slideIndex - 1].style.display = "block"; // Show the current slide
    // setTimeout(showSlides, 5000); // Change image every 3 seconds
}

function plusSlides(n) {
    slideIndex += n;
    let slides = document.getElementsByClassName("slides");
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }
    if (slideIndex < 1) {
        slideIndex = slides.length;
    }
    showSlides();
}

showSlides(); // Start the slideshow
