document.addEventListener('DOMContentLoaded', function() {
    // Interactive hover effects for all seats
    const seats = document.querySelectorAll('.seat');
    seats.forEach(seat => {
        seat.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.2)';
        });
        seat.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});