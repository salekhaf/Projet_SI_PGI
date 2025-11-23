# 📁 Guide de Réorganisation du Projet

## 🎯 Objectif

Réorganiser le projet pour une meilleure structure et maintenabilité.

## 📋 Étapes de Réorganisation

### 1. Exécuter le script de réorganisation

Accédez à : `http://localhost/epicerie/reorganiser_projet.php`

Ce script va automatiquement déplacer tous les fichiers dans leur dossier approprié.

### 2. Mettre à jour les chemins dans les fichiers

Après la réorganisation, vous devez mettre à jour les chemins dans certains fichiers :

#### Fichiers à modifier :

**Tous les fichiers PHP dans `pages/`** doivent mettre à jour les includes :

```php
// AVANT
include('db_conn.php');
include('historique_helper.php');

// APRÈS
include('../config/db_conn.php');
include('../includes/historique_helper.php');
```

**Fichiers dans `pages/auth/`** :
```php
include('../../config/db_conn.php');
include('../../includes/historique_helper.php');
```

**Fichiers dans `pages/public/`** :
```php
include('../../config/db_conn.php');
```

**Fichiers dans `pages/dashboard/`** :
```php
include('../../config/db_conn.php');
include('../../includes/role_helper.php');
```

**Fichiers dans `pages/stock/`, `pages/ventes/`, etc.** :
```php
include('../../config/db_conn.php');
include('../../includes/historique_helper.php');
```

#### Chemins des images et CSS :

```php
// AVANT
<img src="logo_epicerie.png">
<link rel="stylesheet" href="styles_connected.css">

// APRÈS
<img src="../../assets/images/logo_epicerie.png">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
```

#### Chemins de redirection :

```php
// AVANT
header("Location: auth.php");
header("Location: index.php");

// APRÈS
header("Location: ../auth/auth.php");
header("Location: ../dashboard/index.php");
```

## 🔧 Script de mise à jour automatique

Un script peut être créé pour mettre à jour automatiquement tous les chemins. Voici les patterns à rechercher/remplacer :

### Patterns de remplacement

1. **Includes de configuration** :
   - `include('db_conn.php')` → `include('../config/db_conn.php')`
   - `include('../db_conn.php')` → `include('../../config/db_conn.php')`

2. **Includes de helpers** :
   - `include('historique_helper.php')` → `include('../includes/historique_helper.php')`
   - `include('permissions_helper.php')` → `include('../includes/permissions_helper.php')`
   - `include('role_helper.php')` → `include('../includes/role_helper.php')`
   - `include('export_helper.php')` → `include('../includes/export_helper.php')`

3. **Images** :
   - `src="logo_epicerie.png"` → `src="../../assets/images/logo_epicerie.png"`
   - `src="fond-accueil.png"` → `src="../../assets/images/fond-accueil.png"`

4. **CSS** :
   - `href="styles_connected.css"` → `href="../../assets/css/styles_connected.css"`
   - `href="styles.css"` → `href="../../assets/css/styles.css"`

5. **Redirections** :
   - `Location: auth.php` → `Location: ../auth/auth.php`
   - `Location: index.php` → `Location: ../dashboard/index.php`
   - `Location: logout.php` → `Location: ../auth/logout.php`

## ⚠️ Points d'attention

1. **Fichiers dans `pages/auth/`** : Utilisent `../../` pour remonter de 2 niveaux
2. **Fichiers dans `pages/dashboard/`** : Utilisent `../../` pour remonter de 2 niveaux
3. **Fichiers dans `pages/stock/`, etc.** : Utilisent `../../` pour remonter de 2 niveaux
4. **Fichiers dans `config/` et `includes/`** : Utilisent `../` pour remonter d'1 niveau

## ✅ Vérification

Après la réorganisation, vérifiez que :

1. ✅ Tous les fichiers sont dans leur dossier approprié
2. ✅ Les includes fonctionnent correctement
3. ✅ Les images s'affichent
4. ✅ Les CSS sont chargés
5. ✅ Les redirections fonctionnent
6. ✅ L'application fonctionne sans erreurs

## 🔄 Retour en arrière

Si vous voulez annuler la réorganisation, vous pouvez :

1. Restaurer depuis une sauvegarde
2. Ou utiliser Git pour revenir à l'état précédent

## 📝 Notes

- Faites toujours une **sauvegarde** avant de réorganiser
- Testez l'application après chaque modification
- Utilisez un outil de recherche/remplacement pour gagner du temps

