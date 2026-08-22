import './bootstrap';


document.addEventListener('DOMContentLoaded', () => {

    const reveals = document.querySelectorAll('.reveal');

    const observer = new IntersectionObserver((entries) => {

        entries.forEach(entry => {

            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target);
            }

        });

    }, {
        threshold: 0.15
    });

    reveals.forEach(element => {
        observer.observe(element);
    });

});