# 🚀 Déploiement sur Render - Guide Rapide

## 📦 Fichiers créés

- ✅ `Dockerfile` - Configuration Docker pour PHP 8.2 + Apache
- ✅ `.dockerignore` - Fichiers à exclure du build
- ✅ `render.yaml` - Configuration Render (optionnel)
- ✅ `config/db_conn.php` - Mis à jour pour utiliser les variables d'environnement

## 🎯 Étapes de déploiement

### 1. Préparer le repository

1. Assurez-vous que tous les fichiers sont commités
2. Poussez sur GitHub/GitLab/Bitbucket

### 2. Créer la base de données MySQL sur Render

1. Connectez-vous à [Render Dashboard](https://dashboard.render.com)
2. Cliquez sur **"New +"** → **"PostgreSQL"** ou **"MySQL"**
3. Configurez :
   - **Name** : `epicerie-db`
   - **Database** : `epicerie_db`
   - **User** : (généré automatiquement)
   - **Password** : (généré automatiquement)
4. Notez les informations de connexion affichées

### 3. Créer le Web Service

1. Cliquez sur **"New +"** → **"Web Service"**
2. Connectez votre repository
3. Configurez :
   - **Name** : `smart-stock`
   - **Environment** : `Docker`
   - **Dockerfile Path** : `./Dockerfile`
   - **Docker Context** : `.`

### 4. Configurer les variables d'environnement

Dans les **Environment Variables** du service, ajoutez :

```
DB_HOST=votre-host-render
DB_NAME=epicerie_db
DB_USER=votre-username
DB_PASSWORD=votre-password
DB_PORT=3306
```

**Important** : Remplacez les valeurs par celles de votre base de données Render.

### 5. Déployer

1. Cliquez sur **"Create Web Service"**
2. Render va automatiquement :
   - Builder l'image Docker
   - Déployer l'application
   - Vous donner une URL (ex: `https://smart-stock.onrender.com`)

### 6. Initialiser la base de données

1. Connectez-vous à votre base de données MySQL sur Render
2. Importez les fichiers SQL dans l'ordre :
   ```
   database/db.sql
   database/db_historique.sql
   database/db_demandes_acces.sql
   database/db_depenses_diverses.sql
   database/db_permissions_utilisateurs.sql
   ```

3. Créez un compte admin :
   - Accédez à : `https://votre-app.onrender.com/install/create_admin.php`

4. (Optionnel) Installez les données de démo :
   - Accédez à : `https://votre-app.onrender.com/install/install_donnees_demo.php`

## ✅ Vérification

Une fois déployé, testez :
- ✅ Page d'accueil : `https://votre-app.onrender.com/`
- ✅ Connexion : `https://votre-app.onrender.com/pages/auth/auth.php`
- ✅ Dashboard : `https://votre-app.onrender.com/pages/dashboard/index.php`

## 🔧 Configuration locale pour développement

Pour le développement local, `db_conn.php` utilise toujours les valeurs par défaut :
- Host : `localhost`
- User : `root`
- Password : (vide)
- Database : `epicerie_db`

Sur Render, les variables d'environnement prennent le dessus automatiquement.

## 📝 Notes

- **HTTPS** : Render fournit automatiquement HTTPS
- **Port** : Render gère automatiquement le port
- **Sessions** : Les sessions PHP fonctionnent normalement
- **Fichiers uploadés** : Utilisez un service de stockage (S3, etc.) pour les fichiers persistants

## 🐛 Dépannage

### Erreur de connexion BDD
- Vérifiez les variables d'environnement
- Vérifiez que la base de données est bien créée
- Vérifiez les logs dans Render Dashboard

### Erreur 404
- Vérifiez que `index.php` existe à la racine
- Vérifiez les logs Apache dans Render

### Build échoue
- Vérifiez les logs de build dans Render
- Vérifiez que le Dockerfile est correct

