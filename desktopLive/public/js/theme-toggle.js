document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(theme) {
        if(theme === 'dark'){
            document.body.classList.add('dark-theme');
            themeToggle.textContent = '☀️';
        } else {
            document.body.classList.remove('dark-theme');
            themeToggle.textContent = '🌙';
        }
    }

    let savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    themeToggle.addEventListener('click', () => {
        const currentTheme = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    });
});
