# ⚙️ Configuration Render - Informations de Connexion

## 📋 Variables d'environnement à configurer

Dans votre service Render, configurez ces variables d'environnement :

```
DB_TYPE=postgresql
DB_HOST=dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com
DB_PORT=5432
DB_NAME=epicerie_db
DB_USER=epicerie_db_user
DB_PASSWORD=zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o
```

## 🔗 URLs de connexion

### URL interne (pour l'application)
```
postgresql://epicerie_db_user:zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o@dpg-d4hi6agdl3ps739q8vr0-a/epicerie_db
```

### URL externe (pour outils externes)
```
postgresql://epicerie_db_user:zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o@dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com/epicerie_db
```

## 📝 Commandes utiles

### Connexion via psql
```bash
PGPASSWORD=zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o psql -h dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com -U epicerie_db_user epicerie_db
```

### Import du schéma
```bash
psql -h dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com -U epicerie_db_user -d epicerie_db -f database/db_postgresql.sql
```

## ⚠️ Important

1. **Sécurité** : Ne commitez JAMAIS ces informations dans Git
2. **Variables d'environnement** : Configurez-les dans le dashboard Render
3. **Base de données** : Utilisez l'URL interne pour l'application
4. **Conversion** : Le code a été adapté pour supporter PostgreSQL automatiquement

## 🔄 Migration depuis MySQL

Si vous avez déjà des données MySQL :

1. Exportez vos données MySQL
2. Convertissez le format SQL (voir `database/db_postgresql.sql`)
3. Importez dans PostgreSQL

## ✅ Vérification

Après configuration, testez la connexion :
- L'application devrait se connecter automatiquement
- Les requêtes seront converties automatiquement MySQL → PostgreSQL

