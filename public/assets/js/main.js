document.addEventListener('DOMContentLoaded', () => {
  // Menu category filter (works on menu.php)
  const filterButtons = document.querySelectorAll('.menu-filters button');
  const cards = document.querySelectorAll('.menu-card');

  filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      filterButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const category = btn.dataset.category;

      cards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // Simple client-side validation feedback for the reservation form
  const reserveForm = document.getElementById('reserveForm');
  if (reserveForm) {
    reserveForm.addEventListener('submit', (e) => {
      const guests = reserveForm.querySelector('[name=guests]');
      if (guests && (guests.value < 1 || guests.value > 30)) {
        e.preventDefault();
        alert('Number of guests must be between 1 and 30.');
      }
    });
  }
});
