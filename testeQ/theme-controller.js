// Controlador de Temas Simples
class ThemeController {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'default';
        this.applyTheme(this.currentTheme);
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'default' ? 'theme-alemanha' : 'default';
        this.applyTheme(this.currentTheme);
        localStorage.setItem('theme', this.currentTheme);
    }

    applyTheme(theme) {
        document.body.classList.remove('theme-alemanha');
        if (theme === 'theme-alemanha') {
            document.body.classList.add('theme-alemanha');
        }
    }
}

// Inicializa o controlador
let themeController;
document.addEventListener('DOMContentLoaded', function() {
    themeController = new ThemeController();
});