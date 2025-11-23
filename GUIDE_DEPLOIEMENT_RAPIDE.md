# ⚡ Guide de déploiement rapide sur Render

## 🎯 Étapes rapides

### 1. Configuration des variables d'environnement

Dans le dashboard Render, ajoutez ces variables :

```
DB_TYPE=postgresql
DB_HOST=dpg-d4hi6agdl3ps739q8vr0-a.oregon-postgres.render.com
DB_PORT=5432
DB_NAME=epicerie_db
DB_USER=epicerie_db_user
DB_PASSWORD=zmZeIo47xHxa2ROp1XhooWQPKYv1tQ9o
```

### 2. Déployer le code

1. Connectez votre repository GitHub à Render
2. Render va automatiquement détecter le `Dockerfile`
3. Le build va installer les extensions PostgreSQL

### 3. Installer le schéma de base de données

Une fois déployé, accédez à :
```
https://votre-app.onrender.com/install_postgresql_schema.php
```

### 4. Créer un compte admin

```
https://votre-app.onrender.com/install/create_admin.php
```

### 5. C'est tout ! 🎉

Votre application est prête à être utilisée.

## 🔍 Vérification

- ✅ L'application se connecte à PostgreSQL
- ✅ Les tables sont créées
- ✅ Vous pouvez vous connecter en admin
- ✅ Toutes les fonctionnalités fonctionnent

## 📚 Documentation complète

Voir `DEPLOIEMENT_RENDER_POSTGRESQL.md` pour plus de détails.

