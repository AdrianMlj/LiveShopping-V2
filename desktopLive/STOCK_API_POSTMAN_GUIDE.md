# 📋 Guide Postman - Stock API

## Base URL
```
http://localhost:3000/api/stock
```

## ⚠️ Important
- **user_id** est obligatoire dans toutes les requêtes (remplacez `user_id=1` par l'ID de votre utilisateur)
- Pas besoin de cookie de session, contrairement à l'interface web
- Format des dates : `YYYY-MM-DD`

---

## 1️⃣ GET /api/stock/movements - Mouvements de stock

### URL
```
http://localhost:3000/api/stock/movements?user_id=1&dateD=2025-01-01&dateF=2025-12-31&page=1&limit=10
```

### Method
`GET`

### Query Parameters
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| user_id | integer | ✅ | ID du vendeur |
| dateD | date | ❌ | Date début (défaut: début du mois) |
| dateF | date | ❌ | Date fin (défaut: fin du mois) |
| page | integer | ❌ | Numéro de page (défaut: 1) |
| limit | integer | ❌ | Éléments par page (défaut: 10) |

### Exemple de réponse
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "item_name": "T-shirt",
      "size_value": "M",
      "color_name": "Blue",
      "quantity": 10,
      "movement_type": "IN",
      "movement_date": "2025-10-10 14:30:00",
      "in_item": 10,
      "out_item": 0
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 3,
    "total_items": 25,
    "items_per_page": 10
  },
  "filters": {
    "start_date": "2025-01-01",
    "end_date": "2025-12-31"
  }
}
```

---

## 2️⃣ GET /api/stock/current - Stock actuel

### URL
```
http://localhost:3000/api/stock/current?user_id=1&page=1&limit=10
```

### Method
`GET`

### Query Parameters
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| user_id | integer | ✅ | ID du vendeur |
| page | integer | ❌ | Numéro de page (défaut: 1) |
| limit | integer | ❌ | Éléments par page (défaut: 10) |

### Exemple de réponse
```json
{
  "success": true,
  "data": [
    {
      "item_id": 5,
      "item_name": "Jean",
      "item_size_id": 12,
      "size_value": "L",
      "color_name": "Black",
      "item_size_color_id": 45,
      "current_stock": 45
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 2,
    "total_items": 15,
    "items_per_page": 10
  }
}
```

---

## 3️⃣ GET /api/stock/export - Demandes d'export

### URL
```
http://localhost:3000/api/stock/export?user_id=1&page=1&limit=10
```

### Method
`GET`

### Query Parameters
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| user_id | integer | ✅ | ID du vendeur |
| page | integer | ❌ | Numéro de page (défaut: 1) |
| limit | integer | ❌ | Éléments par page (défaut: 10) |

---

## 4️⃣ GET /api/stock/all - Toutes les données

### URL
```
http://localhost:3000/api/stock/all?user_id=1&dateD=2025-01-01&dateF=2025-12-31&movements_page=1&stock_page=1&export_page=1&limit=5
```

### Method
`GET`

### Query Parameters
| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| user_id | integer | ✅ | ID du vendeur |
| dateD | date | ❌ | Date début |
| dateF | date | ❌ | Date fin |
| movements_page | integer | ❌ | Page mouvements |
| stock_page | integer | ❌ | Page stock |
| export_page | integer | ❌ | Page exports |
| limit | integer | ❌ | Éléments par page |

---

## 5️⃣ POST /api/stock/import - Importer des CSV

### URL
```
http://localhost:3000/api/stock/import
```

### Method
`POST`

### Body Type
`form-data`

### Form Data
| Clé | Type | Requis | Description |
|-----|------|--------|-------------|
| user_id | text | ✅ | ID du vendeur (ex: "1") |
| file1 | file | ❌ | categories.csv |
| file2 | file | ❌ | items.csv |
| file3 | file | ❌ | sizes.csv |
| file4 | file | ❌ | color.csv |

### Comment tester dans Postman
1. Sélectionnez `Body` → `form-data`
2. Ajoutez la clé `user_id` (type: Text) avec valeur `1`
3. Ajoutez `file1` (type: File) et sélectionnez un fichier CSV
4. Ajoutez `file2`, `file3`, `file4` si nécessaire
5. Cliquez sur `Send`

### Exemple de réponse
```json
{
  "success": true,
  "message": "Import successful ✅"
}
```

### Erreur si aucun fichier
```json
{
  "success": false,
  "message": "No files uploaded"
}
```

---

## 6️⃣ POST /api/stock/export/save - Enregistrer des demandes d'export

### URL
```
http://localhost:3000/api/stock/export/save
```

### Method
`POST`

### Headers
```
Content-Type: application/json
```

### Body (raw JSON)
```json
{
  "user_id": 1,
  "demandes": [
    {
      "idItemSize": 15,
      "qty": 20
    },
    {
      "idItemSize": 16,
      "qty": 30
    }
  ]
}
```

### Exemple de réponse
```json
{
  "success": true,
  "message": "Export requests saved successfully!"
}
```

---

## 7️⃣ POST /api/stock/export/generate - Générer les CSV

### URL
```
http://localhost:3000/api/stock/export/generate
```

### Method
`POST`

### Headers
```
Content-Type: application/json
```

### Body (raw JSON)
```json
{
  "user_id": 1,
  "demandes": [
    {
      "id": 45,
      "quantity": 20
    },
    {
      "id": 46,
      "quantity": 30
    }
  ]
}
```

### Exemple de réponse
```json
{
  "success": true,
  "message": "CSV files generated successfully and export_temp cleaned ✅",
  "folder": "/export_20251010_103625",
  "files": [
    "/export_20251010_103625/categories.csv",
    "/export_20251010_103625/items.csv",
    "/export_20251010_103625/sizes.csv",
    "/export_20251010_103625/color.csv"
  ],
  "download_urls": {
    "categories": "http://localhost:3000/export_20251010_103625/categories.csv",
    "items": "http://localhost:3000/export_20251010_103625/items.csv",
    "sizes": "http://localhost:3000/export_20251010_103625/sizes.csv",
    "colors": "http://localhost:3000/export_20251010_103625/color.csv"
  }
}
```

---

## 8️⃣ DELETE /api/stock/export/{id} - Supprimer une demande

### URL
```
http://localhost:3000/api/stock/export/1
```

### Method
`DELETE`

### URL Parameters
| Paramètre | Type | Description |
|-----------|------|-------------|
| id | integer | ID de la demande d'export à supprimer |

### Exemple de réponse
```json
{
  "success": true,
  "message": "Export request deleted successfully"
}
```

### Erreur si introuvable
```json
{
  "success": false,
  "message": "Export request not found"
}
```

