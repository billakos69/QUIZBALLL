document.addEventListener('DOMContentLoaded', () => {

  // ===== USER MENU =====
  const userMenu = document.querySelector('.user-menu');
  const userDropdown = userMenu.querySelector('.dropdown');

  userMenu.addEventListener('click', (e) => {
    e.stopPropagation(); // μην προχωράει στο global click
    userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
  });

  // ===== DROPDOWNS ΓΙΑ LI ΜΕ ΥΠΟΜΕΝΟΥ =====
  const dropdownParents = document.querySelectorAll('nav ul li');
  dropdownParents.forEach(parent => {
    const submenu = parent.querySelector('ul');
    const link = parent.querySelector('a');

    if (!submenu || parent.classList.contains('user-menu')) return;

    link.addEventListener('click', (e) => {
      // Αν υπάρχει submenu, μπλοκάρουμε default href
      e.preventDefault();
      e.stopPropagation();

      const isOpen = submenu.style.display === 'block';

      // Κλείνουμε όλα τα άλλα submenu
      document.querySelectorAll('nav ul li ul').forEach(ul => {
        if (ul !== submenu && !ul.closest('.user-menu')) ul.style.display = 'none';
      });

      // Άνοιγμα/Κλείσιμο αυτού
      submenu.style.display = isOpen ? 'none' : 'block';
    });
  });

  // ===== Κλείσιμο αν πατήσεις έξω =====
  document.addEventListener('click', () => {
    document.querySelectorAll('nav ul li ul').forEach(ul => ul.style.display = 'none');
  });

});
