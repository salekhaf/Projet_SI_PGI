# 🚀 Déploiement sur Render avec PostgreSQL

## ✅ Configuration effectuée

Votre projet a été adapté pour fonctionner avec **PostgreSQL** sur Render.

## 📋 Variables d'environnement à configurer

Dans votre service Render, ajoutez ces variables d'environnement :

```
DB_TYPE=postgresql
DB_HOST=dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com
DB_PORT=5432
DB_NAME=epicerie_db
DB_USER=epicerie_db_user
DB_PASSWORD=zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o
```

## 🔧 Fichiers créés/modifiés

### 1. `Dockerfile`
- ✅ Support PostgreSQL ajouté (extension `pgsql` et `pdo_pgsql`)
- ✅ Compatible MySQL et PostgreSQL

### 2. `config/db_conn.php`
- ✅ Détection automatique PostgreSQL/MySQL
- ✅ Wrapper de compatibilité mysqli → PostgreSQL
- ✅ Conversion automatique des requêtes SQL

### 3. `database/db_postgresql.sql`
- ✅ Schéma PostgreSQL complet
- ✅ Conversion depuis MySQL

### 4. `includes/db_compat_helper.php`
- ✅ Fonctions de compatibilité réutilisables
- ✅ `table_exists()` - Compatible MySQL/PostgreSQL
- ✅ `db_insert_id()` - Compatible MySQL/PostgreSQL

### 5. Helpers mis à jour
- ✅ `historique_helper.php` - Compatible PostgreSQL
- ✅ `permissions_helper.php` - Compatible PostgreSQL

## 📦 Installation de la base de données

### Option 1 : Via le script PHP (Recommandé)

1. Déployez votre application sur Render
2. Accédez à : `https://votre-app.onrender.com/install_postgresql_schema.php`
3. Le script va créer toutes les tables automatiquement

### Option 2 : Via psql (Ligne de commande)

```bash
PGPASSWORD=zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o psql -h dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com -U epicerie_db_user -d epicerie_db -f database/db_postgresql.sql
```

### Option 3 : Via un client PostgreSQL

1. Connectez-vous avec les informations fournies
2. Importez le fichier `database/db_postgresql.sql`

## 🔄 Conversion automatique

Le système convertit automatiquement les requêtes MySQL en PostgreSQL :

- `CURDATE()` → `CURRENT_DATE`
- `MONTH()` → `EXTRACT(MONTH FROM ...)`
- `YEAR()` → `EXTRACT(YEAR FROM ...)`
- `DATE_SUB(CURDATE(), INTERVAL X DAY)` → `CURRENT_DATE - INTERVAL 'X' DAY`
- `SHOW TABLES` → `SELECT EXISTS (SELECT FROM information_schema.tables ...)`

## ⚠️ Différences importantes

### Types de données
- MySQL : `INT AUTO_INCREMENT` → PostgreSQL : `SERIAL`
- MySQL : `ENUM` → PostgreSQL : `VARCHAR` avec `CHECK`
- MySQL : `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` → PostgreSQL : Identique

### Fonctions SQL
- Certaines fonctions MySQL n'existent pas en PostgreSQL
- Le wrapper convertit automatiquement les plus courantes

## ✅ Vérification après déploiement

1. **Test de connexion** :
   - L'application devrait se connecter automatiquement
   - Vérifiez les logs Render pour les erreurs

2. **Créer un admin** :
   - Accédez à : `https://votre-app.onrender.com/install/create_admin.php`

3. **Installer les données de démo** (optionnel) :
   - Accédez à : `https://votre-app.onrender.com/install/install_donnees_demo.php`

## 🐛 Dépannage

### Erreur de connexion
- Vérifiez les variables d'environnement
- Vérifiez que la base de données est accessible depuis Render
- Utilisez l'URL **interne** pour l'application

### Erreurs SQL
- Vérifiez les logs dans Render Dashboard
- Certaines requêtes peuvent nécessiter une conversion manuelle

### Tables manquantes
- Exécutez `install_postgresql_schema.php`
- Ou importez manuellement `database/db_postgresql.sql`

## 📝 Notes

- Le code fonctionne en **mode hybride** : MySQL local, PostgreSQL sur Render
- La détection est automatique basée sur l'host
- Les conversions SQL sont faites à la volée
- Certaines fonctions complexes peuvent nécessiter des ajustements

## 🔗 URLs importantes

- **URL interne** : `postgresql://epicerie_db_user:zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o@dpg-d4hi6agdl3ps739q8vr0-a/epicerie_db`
- **URL externe** : `postgresql://epicerie_db_user:zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o@dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com/epicerie_db`

