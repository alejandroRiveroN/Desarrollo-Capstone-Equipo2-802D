document.addEventListener('DOMContentLoaded', function() {
    // --- Script del Carrusel ---
    const slides = document.querySelectorAll('.carousel-slide');
    const container = document.getElementById('carousel-container');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicatorsContainer = document.getElementById('indicators');
    
    if (container) {
        let currentIndex = 0;
        const totalSlides = slides.length;

        function updateCarousel(index) {
            slides.forEach((slide, i) => {
                slide.style.opacity = (i === index) ? '1' : '0';
            });
            updateIndicators(index);
        }

        function updateIndicators(activeIndex) {
            if (!indicatorsContainer) return;
            indicatorsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const indicator = document.createElement('button');
                indicator.classList.add('w-3', 'h-3', 'rounded-full', 'bg-white', 'mx-1', 'transition', 'duration-300');
                indicator.classList.toggle('bg-opacity-100', i === activeIndex);
                indicator.classList.toggle('bg-opacity-50', i !== activeIndex);
                indicator.classList.toggle('hover:bg-opacity-75', i !== activeIndex);
                indicator.addEventListener('click', () => {
                    currentIndex = i;
                    updateCarousel(currentIndex);
                });
                indicatorsContainer.appendChild(indicator);
            }
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateCarousel(currentIndex);
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateCarousel(currentIndex);
        }

        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);

        const interval = setInterval(nextSlide, 5000);
        container.addEventListener('mouseenter', () => clearInterval(interval));

        updateCarousel(currentIndex);
    }

    // --- Script para auto-ocultar alertas ---
    const alerts = document.querySelectorAll('.alert-exito, .alert-error');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});