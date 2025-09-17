Feuille 1 : Categories
| name\_category | description                |
| -------------- | -------------------------- |
| Chaussures     | Tous types de chaussures   |
| T-shirts       | Vêtements haut homme/femme |

Feuille 2 : Items
| name\_item     | id\_category | image\_url (optionnel) |
| -------------- | ------------ | ---------------------- |
| Nike Air Zoom  | 1            | nike.jpg               |
| T-shirt Adidas | 2            | tshirt.jpg             |

Feuille 3 : Sizes
| name\_size |
| ---------- |
| 40         |
| 41         |
| M          |
| L          |

Feuille 4 : Item_Size
| id\_item | id\_size | value\_size |
| -------- | -------- | ----------- |
| 1        | 1        | 40          |
| 1        | 2        | 41          |
| 2        | 3        | M           |
| 2        | 4        | L           |

Feuille 5 : Items_Stock
| id\_item\_size | in\_item | out\_item | date\_move |
| -------------- | -------- | --------- | ---------- |
| 1              | 100      | 0         | 2025-09-09 |
| 2              | 50       | 0         | 2025-09-09 |
| 3              | 80       | 0         | 2025-09-09 |

Feuille 6 : Price_Items
| id\_item | price  | date\_price |
| -------- | ------ | ----------- |
| 1        | 120.00 | 2025-09-09  |
| 2        | 30.00  | 2025-09-09  |
