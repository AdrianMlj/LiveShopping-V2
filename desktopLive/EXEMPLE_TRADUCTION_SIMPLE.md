# 📖 Guide d'utilisation des traductions pour les données de la base

## ✅ Approche Simple

Au lieu de stocker les traductions dans la base de données, on les ajoute directement dans le fichier `public/js/translations.js`.

## 1. Ajouter les traductions dans translations.js

```javascript
const translations = {
    fr: {
        // ... traductions existantes ...
        
        // Produits
        item_1_name: 'T-shirt Bleu',
        item_1_desc: 'Un magnifique t-shirt bleu en coton premium.',
        item_2_name: 'Jean Slim',
        item_2_desc: 'Jean slim noir avec coupe moderne.',
        
        // Lives
        live_1_title: 'Soldes d\'Été 2025',
        live_1_desc: 'Découvrez nos meilleures offres de l\'été!',
    },
    en: {
        // ... traductions existantes ...
        
        // Products
        item_1_name: 'Blue T-shirt',
        item_1_desc: 'A beautiful blue t-shirt in premium cotton.',
        item_2_name: 'Slim Jeans',
        item_2_desc: 'Black slim jeans with modern cut.',
        
        // Lives
        live_1_title: 'Summer Sales 2025',
        live_1_desc: 'Discover our best summer deals!',
    }
};
```

## 2. Utilisation dans HTML (Twig)

### Méthode A : Avec data-i18n (statique)

```twig
{# Dans votre template #}
<div class="product">
    <h3 data-i18n="item_1_name">T-shirt Bleu</h3>
    <p data-i18n="item_1_desc">Un magnifique t-shirt...</p>
</div>
```

### Méthode B : Avec data-i18n dynamique

```twig
{# Si vous avez une liste de produits #}
{% for item in items %}
    <div class="product-card">
        <h3 data-i18n="item_{{ item.id }}_name">{{ item.nameItem }}</h3>
        <p data-i18n="item_{{ item.id }}_desc">{{ item.description }}</p>
    </div>
{% endfor %}
```

La traduction se fera automatiquement quand l'utilisateur change de langue !

## 3. Utilisation dans JavaScript

### Récupérer une traduction simple

```javascript
const productName = t('item_1_name');
console.log(productName); // "T-shirt Bleu" ou "Blue T-shirt" selon la langue
```

### Afficher dynamiquement un produit

```javascript
function displayProduct(productId) {
    const name = t(`item_${productId}_name`, 'Nom non disponible');
    const desc = t(`item_${productId}_desc`, 'Description non disponible');
    
    const html = `
        <div class="product-card">
            <h3>${name}</h3>
            <p>${desc}</p>
        </div>
    `;
    
    document.getElementById('products-container').innerHTML += html;
}

// Afficher le produit ID 1
displayProduct(1);
```

### Lors du chargement de données API

```javascript
// Exemple avec fetch
fetch('/api/live/active')
    .then(response => response.json())
    .then(data => {
        data.lives.forEach(live => {
            // Utiliser la traduction au lieu des données brutes
            const title = t(`live_${live.id}_title`, live.titre);
            const desc = t(`live_${live.id}_desc`, live.description);
            
            console.log(title, desc);
        });
    });
```

## 4. Exemple Complet : Page de Live

```html
<!DOCTYPE html>
<html>
<head>
    <script src="/js/translations.js"></script>
</head>
<body>
    <div id="live-container">
        <!-- Le contenu sera généré dynamiquement -->
    </div>

    <script>
        // Récupérer les lives actifs
        fetch('/api/live/active')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('live-container');
                
                data.lives.forEach(live => {
                    // Chercher la traduction, sinon utiliser les données de la DB
                    const title = t(`live_${live.id}_title`, live.titre);
                    const desc = t(`live_${live.id}_desc`, live.description);
                    
                    container.innerHTML += `
                        <div class="live-card">
                            <h2>${title}</h2>
                            <p>${desc}</p>
                            <button onclick="joinLive(${live.id})">
                                ${t('start_live')}
                            </button>
                        </div>
                    `;
                });
            });
    </script>
</body>
</html>
```

## 5. Astuce : Générer automatiquement les clés

Si vous avez beaucoup de produits, vous pouvez créer un script PHP pour générer automatiquement les clés :

```php
// generate_translations.php
<?php
require_once 'vendor/autoload.php';

use App\Repository\ItemRepository;

$items = $itemRepository->findAll();

$frTranslations = [];
$enTranslations = [];

foreach ($items as $item) {
    $id = $item->getId();
    
    // Français (depuis la DB)
    $frTranslations["item_{$id}_name"] = $item->getNameItem();
    $frTranslations["item_{$id}_desc"] = $item->getDescription();
    
    // Anglais (à traduire manuellement ou avec API)
    $enTranslations["item_{$id}_name"] = "TO TRANSLATE: " . $item->getNameItem();
    $enTranslations["item_{$id}_desc"] = "TO TRANSLATE: " . $item->getDescription();
}

echo "// Ajoutez ceci dans translations.js:\n";
echo "// FR:\n";
echo json_encode($frTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n// EN:\n";
echo json_encode($enTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
```

## 6. Avantages de cette approche

✅ **Simple** : Pas de modification de la base de données  
✅ **Cohérent** : Utilise le même système que vos autres traductions  
✅ **Rapide** : Pas de requêtes supplémentaires à la base  
✅ **Flexible** : Facile d'ajouter de nouvelles traductions  

## 7. Inconvénients

⚠️ **Manuel** : Il faut ajouter manuellement chaque traduction  
⚠️ **Fichier volumineux** : Si vous avez beaucoup de produits, le fichier translations.js peut devenir gros  

## 8. Solution hybride (si vous avez beaucoup de produits)

Vous pouvez créer un fichier séparé juste pour les traductions de produits :

```javascript
// public/js/product-translations.js
const productTranslations = {
    fr: {
        item_1_name: 'T-shirt Bleu',
        item_1_desc: '...',
        // ... tous vos produits ...
    },
    en: {
        item_1_name: 'Blue T-shirt',
        item_1_desc: '...',
    }
};

// Fusionner avec les traductions principales
Object.keys(productTranslations).forEach(lang => {
    window.translations[lang] = {
        ...window.translations[lang],
        ...productTranslations[lang]
    };
});
```

Puis dans votre HTML :
```html
<script src="/js/translations.js"></script>
<script src="/js/product-translations.js"></script>
```

Voilà ! C'est l'approche la plus simple et cohérente avec votre système existant. 🎉


