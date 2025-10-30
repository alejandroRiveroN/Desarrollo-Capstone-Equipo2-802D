document.addEventListener("DOMContentLoaded", function() {

    // --- Carousel Logic ---
    const slides = document.querySelectorAll('.carousel-slide');
    const container = document.getElementById('carousel-container');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const indicatorsContainer = document.getElementById('indicators');
    let currentIndex = 0;

    if (slides.length > 0 && container && prevBtn && nextBtn && indicatorsContainer) {
        const totalSlides = slides.length;

        function updateCarousel(index) {
            slides.forEach((slide, i) => {
                slide.style.opacity = (i === index) ? '1' : '0';

                // Animación del texto del slide
                const title = slide.querySelector('.hero-title');
                const subtitle = slide.querySelector('.hero-subtitle');

                if (i === index) {
                    // Si es el slide activo, añade las clases para animar
                    title?.classList.add('animate-in');
                    subtitle?.classList.add('animate-in');
                } else {
                    // Si no, quítalas para que se pueda animar de nuevo la próxima vez
                    title?.classList.remove('animate-in');
                    subtitle?.classList.remove('animate-in');
                }
            });
            updateIndicators(index);
        }

        function updateIndicators(activeIndex) {
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

        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);

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
            setTimeout(() => { alert.style.display = 'none'; }, 500);
        }, 5000);
    });

    // --- Script para validación en tiempo real del formulario de contacto ---
    const contactForm = {
        nombre: document.getElementById('nombre'),
        email: document.getElementById('email'),
        mensaje: document.getElementById('mensaje'),
        submitBtn: document.getElementById('submit-contact'),
        errors: {
            nombre: document.getElementById('nombre-error'),
            email: document.getElementById('email-error'),
            mensaje: document.getElementById('mensaje-error'),
            recaptcha: document.getElementById('recaptcha-error'),
        },
        state: {
            nombre: false,
            email: false,
            mensaje: false,
            recaptcha: false,
        }
    };

    if (contactForm.submitBtn) {
        function validateContactForm() {
            const { nombre, email, mensaje, recaptcha } = contactForm.state;
            contactForm.submitBtn.disabled = !(nombre && email && mensaje && recaptcha);
        }

        contactForm.nombre.addEventListener('input', (e) => {
            const isValid = e.target.value.trim().length > 0;
            contactForm.state.nombre = isValid;
            contactForm.errors.nombre.classList.toggle('hidden', isValid);
            validateContactForm();
        });

        contactForm.email.addEventListener('input', (e) => {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const isValid = emailRegex.test(e.target.value.trim());
            contactForm.state.email = isValid;
            contactForm.errors.email.classList.toggle('hidden', isValid);
            validateContactForm();
        });

        contactForm.mensaje.addEventListener('input', (e) => {
            const isValid = e.target.value.trim().length > 0;
            contactForm.state.mensaje = isValid;
            contactForm.errors.mensaje.classList.toggle('hidden', isValid);
            validateContactForm();
        });

        // Validar al cargar por si el navegador autocompleta los campos
        validateContactForm();
    }

    const sectionsToAnimate = document.querySelectorAll('.fade-in-section');

    if (sectionsToAnimate.length > 0) {
        const options = {
            root: null, // Observa en relación al viewport
            rootMargin: '0px',
            threshold: 0.1 // Se activa cuando al menos el 10% del elemento es visible
        };

        const observer = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                // Si el elemento está intersectando (visible en la pantalla)
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    // Dejamos de observar el elemento una vez que ha sido animado para mejorar el rendimiento
                    observer.unobserve(entry.target);
                }
            });
        }, options);

        // Por cada sección, le decimos al observador que la vigile
        sectionsToAnimate.forEach(section => { observer.observe(section); });
    }
});