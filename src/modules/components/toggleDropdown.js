function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';
}

// Cierra el dropdown al hacer clic fuera
document.addEventListener('click', function (event) {
    const profile = document.querySelector('.profile');
    const dropdown = document.getElementById('profileDropdown');

    if (!profile.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
