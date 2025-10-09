/**
 * Fichier de traductions centralisé pour l'application LiveShopping
 * Gère l'internationalisation de l'interface utilisateur
 */

// Traductions complètes pour l'application
const translations = {
    fr: {
        // Navigation
        home: 'Accueil',
        sales_history: 'Historique des ventes',
        items: 'Articles',
        profile: 'Votre profil',
        theme: 'Changer thème',
        language: 'Langue',
        logout: 'Déconnexion',
        search: 'Recherche...',
        mail: 'E-mail',
        address: 'Adresse',
        country: 'Pays',
        contact: 'Contact',
        choose_image: 'Choisir une image',
        username: 'Nom d\'utilisateur',
        cancel: 'Annuler',
        save: 'Enregistrer',
        profil_picture: 'Photo de profil',
        Category: 'Catégorie',

        // Dashboard
        dashboard_title: 'Tableau de bord',
        dashboard_description: 'Visualisez vos performances, statistiques de ventes, chiffre d\'affaires et tendances par période',
        period: 'Période',
        category: 'Catégorie',
        apply: 'Appliquer',
        revenue: 'Chiffre d\'affaires',
        monthly: 'Mensuel',
        trends: 'Tendances',
        by_category: 'Par catégorie',
        distribution: 'Répartition',
        sales: 'Ventes',
        total_turnover: 'Chiffre d\'affaires total',
        total_sales: 'Ventes totales',
        average_bag: 'Panier moyen',
        best_category: 'Meilleure catégorie',

        // Live Form
        create_live: 'Créer un Live Shopping',
        live_creation_description: 'Créez et planifiez votre prochain live shopping en quelques clics',

        // Ranking/Classement
        ranking_title: 'Classement et performances',
        ranking_subtitle: 'Analyse des ventes, des clients et des articles les plus performants',

        // Monthly Goals
        monthly_goals_title: 'Objectifs mensuels',
        monthly_goals_subtitle: 'Suivez vos objectifs, vos réalisations et vos projections mois par mois',

        // History
        history_title: 'Historique des ventes',
        history_subtitle: 'Consultez l\'historique complet des ventes avec filtres par date, état et paiement',

        // Articles/Promotion
        article_management: 'Gestion des articles',
        article_management_subtitle: 'Gérez vos produits, promotions, variantes et stocks',

        // Actions du menu
        actions: 'Actions',
        dashboard: 'Tableau de bord',
        dashboard_subtitle: 'Résumé des performances globales',
        classement: 'Classement',
        classement_subtitle: 'Classements et statistiques',
        objectifs: 'Objectifs',
        objectifs_subtitle: 'Suivi des objectifs mensuels',
        stock: 'Stock',
        stock_subtitle: 'Gestion des stocks et inventaire',
        historique: 'Historique',
        historique_subtitle: 'Historique des ventes et commandes',
        articles: 'Articles',
        articles_subtitle: 'Gestion des articles et promotions',

        // Autres
        favorites: 'Liste de favoris',
        cart: 'Panier'
    },
    en: {
        // Navigation
        home: 'Home',
        sales_history: 'Sales History',
        items: 'Items',
        profile: 'Your profile',
        theme: 'Theme change',
        language: 'Language',
        logout: 'Log out',
        search: 'Search...',
        mail: 'Email',
        address: 'Address',
        country: 'Country',
        contact: 'Contact',
        choose_image: 'Choose image',
        username: 'Username',
        cancel: 'Cancel',
        save: 'Save',
        profil_picture: 'Profile picture',
        Category: 'Category',

        // Dashboard
        dashboard_title: 'Dashboard',
        dashboard_description: 'View your performance, sales statistics, revenue and trends by period',
        period: 'Period',
        category: 'Category',
        apply: 'Apply',
        revenue: 'Revenue',
        monthly: 'Monthly',
        trends: 'Trends',
        by_category: 'By category',
        distribution: 'Distribution',
        sales: 'Sales',
        total_turnover: 'Total turnover',
        total_sales: 'Total sales',
        average_bag: 'Average bag',
        best_category: 'Best category',

        // Live Form
        create_live: 'Create Live Shopping',
        live_creation_description: 'Create and schedule your next live shopping in a few clicks',

        // Ranking/Classement
        ranking_title: 'Ranking and Performance',
        ranking_subtitle: 'Analysis of sales, clients and top performing items',

        // Monthly Goals
        monthly_goals_title: 'Monthly Goals',
        monthly_goals_subtitle: 'Track your goals, achievements and projections month by month',

        // History
        history_title: 'Sales History',
        history_subtitle: 'View complete sales history with filters by date, status and payment',

        // Articles/Promotion
        article_management: 'Article Management',
        article_management_subtitle: 'Manage your products, promotions, variants and stock',

        // Actions du menu
        actions: 'Actions',
        dashboard: 'Dashboard',
        dashboard_subtitle: 'Overall performance summary',
        classement: 'Ranking',
        classement_subtitle: 'Rankings and statistics',
        objectifs: 'Goals',
        objectifs_subtitle: 'Monthly goals tracking',
        stock: 'Stock',
        stock_subtitle: 'Stock and inventory management',
        historique: 'History',
        historique_subtitle: 'Sales and orders history',
        articles: 'Articles',
        articles_subtitle: 'Articles and promotions management',

        // Autres
        favorites: 'Favorites List',
        cart: 'Cart'
    }
};

/**
 * Fonction de traduction principale
 * @param {string} key - Clé de traduction
 * @param {string} fallback - Texte de fallback si la traduction n'existe pas
 * @returns {string} - Texte traduit
 */
function t(key, fallback = '') {
    const currentLang = localStorage.getItem('site_language') || 'en';
    return translations[currentLang]?.[key] || fallback || key;
}

/**
 * Fonction pour définir la langue et appliquer les traductions
 * @param {string} lang - Code de langue ('fr' ou 'en')
 */
function setLanguage(lang) {
    localStorage.setItem('site_language', lang);

    // Appliquer les traductions à tous les éléments avec data-i18n
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        const translatedText = t(key, el.textContent);
        el.textContent = translatedText;
    });

    // Appliquer les traductions aux placeholders
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        const translatedText = t(key, el.getAttribute('placeholder'));
        el.setAttribute('placeholder', translatedText);
    });

    // Appliquer les traductions aux attributs title
    document.querySelectorAll('[data-i18n-title]').forEach(el => {
        const key = el.getAttribute('data-i18n-title');
        const translatedText = t(key, el.getAttribute('title'));
        el.setAttribute('title', translatedText);
    });
}

/**
 * Fonction pour initialiser les traductions au chargement de la page
 */
function initTranslations() {
    const savedLang = localStorage.getItem('site_language') || 'en';
    setLanguage(savedLang);
}

// Initialiser les traductions quand le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTranslations);
} else {
    initTranslations();
}

// Exposer les fonctions globalement pour compatibilité
window.translations = translations;
window.t = t;
window.setLanguage = setLanguage;
