# 🔧 Correction des Chemins - Instructions

## ✅ Corrections déjà effectuées

J'ai corrigé manuellement les fichiers suivants :
- ✅ `index.php` (racine) - Redirection vers accueil ou dashboard
- ✅ `pages/public/accueil.php` - Tous les chemins corrigés
- ✅ `pages/public/services.php` - Chemins des images corrigés
- ✅ `pages/auth/auth.php` - Includes et redirections corrigés

## 🚀 Correction automatique de tous les fichiers

Pour corriger automatiquement **tous les autres fichiers** du projet :

### Étape 1 : Exécuter le script

Accédez à : `http://localhost/epicerie/fix_all_paths.php`

Ce script va automatiquement :
- ✅ Corriger tous les `include()` et `require()`
- ✅ Corriger tous les chemins d'images (`src=`)
- ✅ Corriger tous les chemins CSS (`href=`)
- ✅ Corriger toutes les redirections (`Location:`)
- ✅ Corriger tous les liens (`href=`)

### Étape 2 : Vérifier

Après l'exécution, testez :
1. Page d'accueil : `http://localhost/epicerie/` ou `http://localhost/epicerie/index.php`
2. Connexion : `http://localhost/epicerie/pages/auth/auth.php`
3. Dashboard : `http://localhost/epicerie/pages/dashboard/index.php`

## 📋 Chemins corrigés

### Structure des chemins relatifs

Depuis `pages/public/` ou `pages/auth/` :
- Vers config : `../../config/`
- Vers includes : `../../includes/`
- Vers assets/images : `../../assets/images/`
- Vers assets/css : `../../assets/css/`
- Vers dashboard : `../dashboard/`
- Vers auth : `../auth/`

Depuis `pages/dashboard/`, `pages/stock/`, etc. :
- Vers config : `../../config/`
- Vers includes : `../../includes/`
- Vers assets/images : `../../assets/images/`
- Vers assets/css : `../../assets/css/`
- Vers auth : `../auth/`
- Vers dashboard : `../dashboard/` (ou `index.php` si dans dashboard)

## ⚠️ Si des erreurs persistent

1. Vérifiez les logs d'erreur PHP
2. Vérifiez que tous les fichiers sont bien dans leurs dossiers
3. Vérifiez les permissions des fichiers
4. Videz le cache du navigateur

## 📝 Fichiers à vérifier manuellement (si nécessaire)

Si le script automatique ne corrige pas tout, vérifiez manuellement :
- `pages/dashboard/index.php`
- `pages/stock/stock.php`
- `pages/ventes/ventes.php`
- `pages/commandes/commandes.php`
- `pages/clients/clients.php`
- `pages/fournisseurs/fournisseurs.php`
- `pages/tresorerie/tresorerie.php`
- `pages/admin/utilisateurs.php`
- `pages/admin/demandes_acces.php`

