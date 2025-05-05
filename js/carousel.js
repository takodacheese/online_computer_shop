// Carousel functionality
document.addEventListener('DOMContentLoaded', function() {
    const carouselTrack = document.querySelector('.carousel-track');
    const carouselItems = document.querySelectorAll('.carousel-item');
    const prevButton = document.querySelector('.carousel-prev');
    const nextButton = document.querySelector('.carousel-next');
    
    if (!carouselTrack || !prevButton || !nextButton) {
        console.error('Carousel elements not found');
        return;
    }

    let currentSlide = 0;
    const slidesToShow = 4;
    let containerWidth = document.querySelector('.carousel-container').offsetWidth;
    let slideWidth = containerWidth / slidesToShow;

    // Set initial position
    carouselTrack.style.transform = `translateX(-${currentSlide * slideWidth}px)`;

    // Update carousel width based on number of items
    carouselTrack.style.width = `${carouselItems.length * slideWidth}px`;

    // Add click event listeners
    prevButton.addEventListener('click', () => {
        if (currentSlide > 0) {
            currentSlide--;
            updateCarousel();
        }
    });

    nextButton.addEventListener('click', () => {
        if (currentSlide < carouselItems.length - slidesToShow) {
            currentSlide++;
            updateCarousel();
        }
    });

    // Auto slide every 5 seconds
    setInterval(() => {
        if (currentSlide < carouselItems.length - slidesToShow) {
            currentSlide++;
        } else {
            currentSlide = 0;
        }
        updateCarousel();
    }, 5000); // 5 seconds

    function updateCarousel() {
        carouselTrack.style.transform = `translateX(-${currentSlide * slideWidth}px)`;
        console.log(`Current slide: ${currentSlide}`);
    }

    // Add resize event listener to update slide width
    window.addEventListener('resize', () => {
        containerWidth = document.querySelector('.carousel-container').offsetWidth;
        slideWidth = containerWidth / slidesToShow;
        updateCarousel();
    });
});
