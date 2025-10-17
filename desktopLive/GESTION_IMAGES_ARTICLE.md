# 🎨 Gestion des Images d'Articles et Variantes - Cloudinary

## ✅ Fonctionnalités Ajoutées

Vous pouvez maintenant gérer complètement les images de vos articles directement depuis l'interface de gestion :

### 📸 Upload d'Image Principale
- Upload de l'image principale de l'article vers Cloudinary
- Prévisualisation instantanée
- Suppression automatique de l'ancienne image
- Mise à jour immédiate dans l'interface

### 🎨 Gestion des Images de Variantes de Couleur
- Affichage de toutes les variantes de couleur de l'article
- Upload d'une image spécifique pour chaque couleur
- Mise à jour automatique de toutes les tailles de cette couleur
- Prévisualisation en temps réel

---

## 🚀 Comment Utiliser

### 1. Éditer un Article

1. **Ouvrez la page de gestion des articles** (`/promotion`)
2. **Cliquez sur le bouton d'édition** (icône crayon) sur une carte produit
3. Le modal s'ouvre avec :
   - L'image principale actuelle
   - Les informations de l'article (nom, prix, description)
   - Toutes les variantes de couleur disponibles

### 2. Modifier l'Image Principale

1. **Cliquez sur "Choisir une image"** dans la section "Image principale"
2. **Sélectionnez votre image** depuis votre ordinateur
3. ✅ **L'image est automatiquement :**
   - Prévisualisée dans le modal
   - Uploadée vers Cloudinary (dossier `items/`)
   - Redimensionnée à 800x800px maximum
   - Mise à jour dans la base de données
   - Affichée immédiatement dans la grille d'articles

### 3. Modifier l'Image d'une Variante de Couleur

1. **Dans la section "Images des variantes de couleur"**, vous voyez toutes les couleurs
2. **Chaque couleur a :**
   - Un aperçu de l'image actuelle
   - Un bouton "Changer l'image"
3. **Cliquez sur "Changer l'image"** pour la couleur souhaitée
4. **Sélectionnez la nouvelle image**
5. ✅ **L'image est automatiquement :**
   - Prévisualisée dans le modal
   - Uploadée vers Cloudinary (dossier `items/variants/`)
   - Redimensionnée à 800x800px maximum
   - Appliquée à **toutes les tailles** de cette couleur
   - Mise à jour dans l'interface

---

## 🔧 Endpoints API Créés

### 1. Upload Image Principale d'un Article
```
POST /promotion/item/{id}/upload-image
```

**Paramètres :**
- `image` (file) : Le fichier image à uploader

**Réponse :**
```json
{
  "success": true,
  "message": "Image uploadée avec succès.",
  "image_url": "https://res.cloudinary.com/ddsocampb/image/upload/v1729166789/items/xyz123.jpg"
}
```

### 2. Récupérer les Variantes de Couleur d'un Article
```
GET /promotion/item/{id}/colors
```

**Réponse :**
```json
{
  "success": true,
  "colors": [
    {
      "id": 1,
      "name": "Rouge",
      "item_size_color_id": 5,
      "image": "https://res.cloudinary.com/.../items/variants/rouge.jpg"
    },
    {
      "id": 2,
      "name": "Bleu",
      "item_size_color_id": 6,
      "image": "https://res.cloudinary.com/.../items/variants/bleu.jpg"
    }
  ]
}
```

### 3. Upload Image d'une Variante de Couleur
```
POST /promotion/color/{id}/upload-image
```

**Paramètres :**
- `image` (file) : Le fichier image à uploader

**Réponse :**
```json
{
  "success": true,
  "message": "Image de la variante uploadée avec succès.",
  "image_url": "https://res.cloudinary.com/.../items/variants/rouge_new.jpg",
  "color_name": "Rouge"
}
```

---

## 📂 Organisation sur Cloudinary

```
ddsocampb/
├── users/
│   └── profiles/              ← Photos de profil (500x500px)
├── items/                     ← Images principales d'articles (800x800px)
│   ├── tshirt_noir.jpg
│   ├── basket_rouge.jpg
│   └── ...
└── items/
    └── variants/              ← Images des variantes de couleur (800x800px)
        ├── rouge_tshirt.jpg
        ├── bleu_tshirt.jpg
        └── ...
```

---

## 🎯 Fonctionnement Technique

### Upload d'Image Principale

1. **Déclenchement :** Sélection d'un fichier via le bouton "Choisir une image"
2. **Prévisualisation :** Affichage immédiat avec FileReader API
3. **Upload AJAX :** Envoi vers `/promotion/item/{id}/upload-image`
4. **Traitement Backend :**
   - Suppression de l'ancienne image Cloudinary (si existe)
   - Upload vers Cloudinary (dossier `items/`)
   - Redimensionnement automatique à 800x800px max
   - Mise à jour de `Item.images` en base de données
5. **Mise à jour UI :** Rafraîchissement de l'image dans la grille

### Upload d'Image de Variante

1. **Chargement des couleurs :** Appel GET `/promotion/item/{id}/colors`
2. **Affichage :** Création dynamique des cartes de couleur
3. **Sélection d'image :** Clic sur "Changer l'image" pour une couleur
4. **Upload AJAX :** Envoi vers `/promotion/color/{item_size_color_id}/upload-image`
5. **Traitement Backend :**
   - Suppression de l'ancienne image Cloudinary (si existe)
   - Upload vers Cloudinary (dossier `items/variants/`)
   - Redimensionnement automatique à 800x800px max
   - Mise à jour de **toutes les `ItemSizeColor`** de cette couleur pour cet article
6. **Mise à jour UI :** Rafraîchissement des miniatures de couleur

---

## 💡 Avantages

### ✅ Gestion Centralisée
- Toutes les images gérées depuis une seule interface
- Pas besoin d'aller dans la base de données
- Interface intuitive et visuelle

### ✅ Stockage Cloudinary
- **Stockage cloud** : Pas de fichiers sur votre serveur
- **CDN mondial** : Chargement ultra-rapide partout
- **Redimensionnement auto** : 800x800px max pour les articles
- **URLs sécurisées** : HTTPS automatique
- **Optimisation** : Compression et formats optimaux

### ✅ Cohérence
- Une image par couleur appliquée à toutes les tailles
- Mise à jour immédiate dans toute l'application
- Plus de problèmes de synchronisation

---

## 🔍 Détails Techniques

### Entités Modifiées

#### `Item`
- Champ `images` : Stocke l'URL Cloudinary de l'image principale

#### `ItemSizeColor`
- Champ `images` : Stocke l'URL Cloudinary de l'image de cette variante

### Contrôleur `PromotionController`

**Nouvelles méthodes :**
1. `uploadItemImage($id, Request, CloudinaryService)` - Upload image principale
2. `getItemColors($id)` - Récupère les variantes de couleur
3. `uploadColorImage($id, Request, CloudinaryService)` - Upload image de variante

### Service `CloudinaryService`

**Méthodes utilisées :**
- `uploadImageResized()` - Upload avec redimensionnement
- `deleteImage()` - Suppression d'anciennes images

---

## 🎨 Interface Utilisateur

### Modal d'Édition Amélioré

Le modal d'édition d'article contient maintenant :

1. **Section Image Principale**
   - Zone de prévisualisation (250px de hauteur)
   - Placeholder avec icône si pas d'image
   - Bouton "Choisir une image"

2. **Informations de l'Article**
   - Nom (input text)
   - Prix (input number)
   - Description (textarea)

3. **Section Variantes de Couleur**
   - Grille responsive (auto-fill, min 180px par carte)
   - Chaque carte affiche :
     - Aperçu de l'image (120px de hauteur)
     - Nom de la couleur
     - Bouton "Changer l'image"

### Styles CSS

```css
/* Grille des variantes */
.color-variants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
}

/* Carte de variante */
.color-variant-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.3s ease;
}

.color-variant-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
```

---

## 🧪 Test de la Fonctionnalité

### Test 1 : Upload Image Principale

1. Ouvrez la page `/promotion`
2. Cliquez sur le bouton d'édition (crayon) d'un article
3. Cliquez sur "Choisir une image" dans la section Image principale
4. Sélectionnez une image
5. ✅ Vérifiez :
   - L'image s'affiche dans le modal
   - Message de succès : "Image principale uploadée avec succès !"
   - L'image est mise à jour dans la grille d'articles
   - L'image apparaît sur Cloudinary (dashboard)

### Test 2 : Upload Image de Variante

1. Ouvrez le modal d'édition d'un article qui a des variantes de couleur
2. Descendez jusqu'à "Images des variantes de couleur"
3. Cliquez sur "Changer l'image" pour une couleur
4. Sélectionnez une image
5. ✅ Vérifiez :
   - L'image s'affiche dans la carte de couleur
   - Message : "Image de [Couleur] uploadée avec succès !"
   - Les miniatures de couleur sont mises à jour
   - L'image apparaît sur Cloudinary dans `items/variants/`

---

## 📊 Exemple de Workflow Complet

### Scénario : Ajout d'un T-Shirt avec Variantes

1. **Créer l'article** : "T-Shirt Classique" à 29.99€
2. **Upload image principale** : Photo du t-shirt blanc
3. **Créer les variantes** :
   - Taille S : Couleur Rouge, Bleu, Vert
   - Taille M : Couleur Rouge, Bleu, Vert
   - Taille L : Couleur Rouge, Bleu, Vert
4. **Upload images des couleurs** :
   - **Couleur Rouge** → Photo du t-shirt rouge (appliquée à S, M, L)
   - **Couleur Bleu** → Photo du t-shirt bleu (appliquée à S, M, L)
   - **Couleur Vert** → Photo du t-shirt vert (appliquée à S, M, L)

**Résultat :**
- 1 image principale sur Cloudinary
- 3 images de variantes sur Cloudinary
- Toutes les combinaisons taille/couleur ont leur image
- Total : 4 images au lieu de 9 (économie d'espace !)

---

## 🎉 Résumé

Vous pouvez maintenant :
- ✅ **Modifier l'image principale** d'un article en un clic
- ✅ **Gérer les images de toutes les couleurs** depuis une seule interface
- ✅ **Prévisualiser** avant upload
- ✅ **Stockage automatique** sur Cloudinary
- ✅ **Redimensionnement** automatique à 800x800px
- ✅ **Mise à jour instantanée** de l'interface

Toutes les images sont maintenant sur Cloudinary, avec un CDN mondial pour un chargement ultra-rapide !

---

**🌩️ Gestion d'images moderne et professionnelle avec Cloudinary !**

