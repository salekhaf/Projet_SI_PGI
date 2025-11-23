# 🚀 Guide de Déploiement sur Render

## 📋 Prérequis

1. Compte Render (gratuit disponible)
2. Projet sur GitHub/GitLab/Bitbucket
3. Base de données MySQL (Render propose des bases de données)

## 🔧 Configuration

### 1. Fichiers créés

- ✅ `Dockerfile` - Configuration Docker pour PHP/Apache
- ✅ `.dockerignore` - Fichiers à exclure du build
- ✅ `render.yaml` - Configuration Render (optionnel)

### 2. Configuration de la base de données

#### Étape 1 : Créer une base de données MySQL sur Render

1. Connectez-vous à Render
2. Créez une nouvelle **PostgreSQL** ou **MySQL** database
3. Notez les informations de connexion :
   - Host
   - Port
   - Database name
   - Username
   - Password

#### Étape 2 : Modifier `config/db_conn.php`

Vous devrez modifier le fichier pour utiliser les variables d'environnement :

```php
<?php
// Configuration de la base de données avec variables d'environnement
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'epicerie_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
```

## 📦 Déploiement

### Option 1 : Déploiement avec Docker (Recommandé)

1. **Connecter votre repository** :
   - Allez sur Render Dashboard
   - Cliquez sur "New" → "Web Service"
   - Connectez votre repository GitHub/GitLab

2. **Configuration** :
   - **Name** : `smart-stock`
   - **Environment** : `Docker`
   - **Dockerfile Path** : `./Dockerfile`
   - **Docker Context** : `.`

3. **Variables d'environnement** :
   ```
   DB_HOST=votre-host-render
   DB_NAME=votre-database-name
   DB_USER=votre-username
   DB_PASSWORD=votre-password
   PHP_VERSION=8.2
   ```

4. **Déployer** :
   - Cliquez sur "Create Web Service"
   - Render va automatiquement builder et déployer

### Option 2 : Déploiement natif PHP

Si vous préférez ne pas utiliser Docker :

1. **Configuration** :
   - **Environment** : `PHP`
   - **Build Command** : (laisser vide)
   - **Start Command** : `php -S 0.0.0.0:$PORT -t .`

2. **Variables d'environnement** : (mêmes que ci-dessus)

## 🗄️ Initialisation de la base de données

### Étape 1 : Importer le schéma

1. Connectez-vous à votre base de données MySQL sur Render
2. Utilisez phpMyAdmin ou un client MySQL
3. Importez les fichiers SQL dans l'ordre :
   - `database/db.sql`
   - `database/db_historique.sql`
   - `database/db_demandes_acces.sql`
   - `database/db_depenses_diverses.sql`
   - `database/db_permissions_utilisateurs.sql`

### Étape 2 : Créer un compte admin

1. Accédez à : `https://votre-app.onrender.com/install/create_admin.php`
2. Ou importez directement dans la base de données

### Étape 3 : Installer les données de démonstration (optionnel)

1. Accédez à : `https://votre-app.onrender.com/install/install_donnees_demo.php`

## ⚙️ Configuration Apache pour Render

Le Dockerfile configure déjà Apache, mais vous pouvez ajouter un fichier `.htaccess` à la racine :

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^$ index.php [L]
```

## 🔐 Sécurité

### Variables d'environnement sensibles

Ne jamais commiter :
- Mots de passe de base de données
- Clés API
- Secrets

Utilisez les **Environment Variables** de Render.

### Protection des fichiers

Le `.htaccess` protège déjà :
- Fichiers `.sql`
- Fichiers `.md`
- Dossiers sensibles (`config/`, `includes/`, etc.)

## 📝 Notes importantes

1. **Port dynamique** : Render utilise un port dynamique via la variable `$PORT`
2. **HTTPS** : Render fournit automatiquement HTTPS
3. **Base de données** : Utilisez la base de données MySQL de Render (pas localhost)
4. **Chemins** : Les chemins relatifs fonctionnent normalement
5. **Sessions** : Les sessions PHP fonctionnent sur Render

## 🐛 Dépannage

### Erreur de connexion à la base de données

1. Vérifiez les variables d'environnement
2. Vérifiez que la base de données est bien créée
3. Vérifiez les permissions de l'utilisateur

### Erreur 404

1. Vérifiez que `index.php` existe à la racine
2. Vérifiez la configuration Apache
3. Vérifiez les logs dans Render Dashboard

### Erreur de permissions

1. Le Dockerfile configure déjà les permissions
2. Si nécessaire, ajustez dans le Dockerfile

## 🔗 Liens utiles

- [Documentation Render](https://render.com/docs)
- [Docker PHP Official](https://hub.docker.com/_/php)
- [Render Environment Variables](https://render.com/docs/environment-variables)

