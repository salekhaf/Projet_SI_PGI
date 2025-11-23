# 🏪 Smart Stock - Système de Gestion d'Épicerie

Système de gestion complet pour une épicerie (PGI Web) développé en PHP/MySQL.

## 📁 Structure du Projet

```
epicerie/
├── config/              # Configuration (db_conn.php)
├── includes/            # Helpers réutilisables
├── pages/               # Pages de l'application
│   ├── auth/            # Authentification
│   ├── public/          # Pages publiques
│   ├── dashboard/       # Tableau de bord
│   ├── stock/           # Gestion du stock
│   ├── ventes/         # Gestion des ventes
│   ├── commandes/      # Gestion des commandes
│   ├── clients/        # Gestion des clients
│   ├── fournisseurs/   # Gestion des fournisseurs
│   ├── tresorerie/     # Trésorerie
│   └── admin/          # Administration
├── assets/             # Ressources statiques (CSS, images)
├── database/           # Scripts SQL
├── install/            # Scripts d'installation
├── docs/               # Documentation
└── vendor/             # Bibliothèques externes (FPDF)
```

## 🚀 Installation

### 1. Prérequis
- PHP 7.4+
- MySQL 5.7+
- Serveur web (Apache/Nginx) ou XAMPP

### 2. Configuration

1. Importez la base de données :
   ```sql
   -- Exécutez database/db.sql dans phpMyAdmin
   ```

2. Configurez la connexion :
   ```php
   // Modifiez config/db_conn.php avec vos paramètres
   ```

3. Créez un compte admin :
   ```
   Accédez à : install/create_admin.php
   ```

### 3. Installation des données de démonstration

```
Accédez à : install/install_donnees_demo.php
```

## 📚 Documentation

Toute la documentation est disponible dans le dossier `docs/` :

- **ARCHITECTURE_SCHEMA.md** - Schéma de l'architecture
- **PRESENTATION_PROJET.md** - Présentation complète du projet
- **GUIDE_CONNEXION_ADMIN.md** - Guide de connexion admin
- **GUIDE_PERMISSIONS_GRANULAIRES.md** - Système de permissions
- **README_DONNEES_DEMO.md** - Guide des données de démonstration

## 🔧 Fonctionnalités

### Gestion du Stock
- ✅ Gestion des produits (CRUD)
- ✅ Gestion des catégories
- ✅ Alertes de stock bas
- ✅ Recherche et filtrage
- ✅ Pagination

### Gestion des Ventes
- ✅ Création de ventes
- ✅ Détails des ventes
- ✅ Historique complet

### Gestion des Commandes
- ✅ Création de commandes fournisseurs
- ✅ Mise à jour automatique du stock
- ✅ Génération de bons de commande

### Trésorerie
- ✅ Tableau de bord financier
- ✅ Graphiques (Chart.js)
- ✅ Export CSV
- ✅ Gestion des dépenses diverses

### Administration
- ✅ Gestion des utilisateurs
- ✅ Système de rôles (admin, vendeur, responsable, trésorier)
- ✅ Demandes d'accès avec permissions granulaires
- ✅ Historique des modifications

## 👥 Rôles et Permissions

- **Admin** : Accès complet à toutes les fonctionnalités
- **Vendeur** : Gestion des ventes et clients
- **Responsable Approvisionnement** : Gestion du stock et commandes
- **Trésorier** : Accès à la trésorerie

## 🔐 Sécurité

- ✅ Prepared statements (protection SQL injection)
- ✅ Password hashing (bcrypt)
- ✅ Protection XSS (htmlspecialchars)
- ✅ Gestion des sessions
- ✅ Contrôle d'accès basé sur les rôles

## 📝 Notes

- Les fichiers de configuration sont dans `config/`
- Les helpers réutilisables sont dans `includes/`
- Les scripts d'installation sont dans `install/`
- La documentation est dans `docs/`

## 🛠️ Réorganisation

Pour réorganiser le projet selon la nouvelle structure :

```
Accédez à : reorganiser_projet.php
```

⚠️ **Attention** : Cette opération déplace les fichiers. Faites une sauvegarde avant !

## 📞 Support

Pour toute question, consultez la documentation dans `docs/`.

