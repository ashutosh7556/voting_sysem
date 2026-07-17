let slideIndex = 0;
let autoPlayTimer;

function showSlides() {
    const slides = document.getElementsByClassName("slides");
    const dots   = document.getElementsByClassName("dot");

    for (let i = 0; i < slides.length; i++) {
        slides[i].classList.remove("active");
        if (dots[i]) dots[i].classList.remove("active");
    }

    slideIndex++;
    if (slideIndex > slides.length) slideIndex = 1;

    slides[slideIndex - 1].classList.add("active");
    if (dots[slideIndex - 1]) dots[slideIndex - 1].classList.add("active");

    autoPlayTimer = setTimeout(showSlides, 4000);
}

function plusSlides(n) {
    clearTimeout(autoPlayTimer);
    slideIndex += n - 1; // -1 because showSlides() will increment
    showSlides();
}

function goToSlide(n) {
    clearTimeout(autoPlayTimer);
    slideIndex = n - 1;
    showSlides();
}

showSlides();
