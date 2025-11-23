# 🐘 Support PostgreSQL pour Smart Stock

## ✅ Configuration complète

Votre application a été configurée pour fonctionner avec **PostgreSQL** sur Render tout en restant compatible avec **MySQL** en développement local.

## 🔧 Ce qui a été fait

### 1. **Dockerfile** mis à jour
- Extension `pgsql` et `pdo_pgsql` ajoutées
- Compatible MySQL et PostgreSQL

### 2. **Configuration de base de données** (`config/db_conn.php`)
- Détection automatique PostgreSQL/MySQL
- Wrapper de compatibilité complet
- Conversion automatique des requêtes SQL

### 3. **Schéma PostgreSQL** (`database/db_postgresql.sql`)
- Conversion complète depuis MySQL
- Types de données adaptés (SERIAL, CHECK, etc.)

### 4. **Helpers de compatibilité**
- `includes/db_compat_helper.php` - Fonctions réutilisables
- `includes/historique_helper.php` - Compatible PostgreSQL
- `includes/permissions_helper.php` - Compatible PostgreSQL

### 5. **Scripts d'installation**
- `install_postgresql_schema.php` - Installation automatique du schéma

## 📋 Variables d'environnement Render

```
DB_TYPE=postgresql
DB_HOST=dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com
DB_PORT=5432
DB_NAME=epicerie_db
DB_USER=epicerie_db_user
DB_PASSWORD=zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o
```

## 🚀 Déploiement

1. **Configurer les variables d'environnement** dans Render
2. **Déployer le code** (Render détectera le Dockerfile)
3. **Installer le schéma** : `https://votre-app.onrender.com/install_postgresql_schema.php`
4. **Créer un admin** : `https://votre-app.onrender.com/install/create_admin.php`

## ⚠️ Note importante sur `mysqli_insert_id()`

Le code utilise `mysqli_insert_id($conn)` dans plusieurs endroits. Pour PostgreSQL, cette fonction ne fonctionnera pas directement.

**Solutions :**

### Option 1 : Utiliser la fonction helper (Recommandé)
Remplacez `mysqli_insert_id($conn)` par `db_get_insert_id($conn)` dans :
- `pages/tresorerie/tresorerie.php`
- `pages/fournisseurs/fournisseurs.php`
- `pages/clients/clients.php`
- `pages/commandes/commandes.php`
- `pages/ventes/ventes.php`
- `pages/stock/stock.php`
- `pages/stock/categories.php`

### Option 2 : Utiliser la méthode du wrapper
Si `$conn` est une instance de `PostgreSQLConnection`, utilisez :
```php
$id = $conn->insert_id();
```

## 🔄 Conversions automatiques

Le système convertit automatiquement :
- `CURDATE()` → `CURRENT_DATE`
- `MONTH()` → `EXTRACT(MONTH FROM ...)`
- `YEAR()` → `EXTRACT(YEAR FROM ...)`
- `SHOW TABLES LIKE 'table'` → `SELECT EXISTS (SELECT FROM information_schema.tables ...)`
- `DATE_SUB(CURDATE(), INTERVAL X DAY)` → `CURRENT_DATE - INTERVAL 'X' DAY`

## 📚 Documentation complète

- `DEPLOIEMENT_RENDER_POSTGRESQL.md` - Guide complet de déploiement
- `GUIDE_DEPLOIEMENT_RAPIDE.md` - Guide rapide
- `CONFIGURATION_RENDER.md` - Informations de connexion

## ✅ Vérification

Après déploiement, vérifiez :
1. ✅ Connexion à PostgreSQL réussie
2. ✅ Tables créées
3. ✅ Connexion admin fonctionnelle
4. ✅ Fonctionnalités principales opérationnelles

## 🐛 Dépannage

Si vous rencontrez des erreurs :
1. Vérifiez les logs Render
2. Vérifiez les variables d'environnement
3. Vérifiez que le schéma est installé
4. Consultez `DEPLOIEMENT_RENDER_POSTGRESQL.md` pour plus de détails

