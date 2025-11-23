# 📁 Structure du Projet Smart Stock

## Organisation proposée

```
epicerie/
├── config/              # Configuration
│   └── db_conn.php
│
├── includes/            # Helpers et fonctions réutilisables
│   ├── historique_helper.php
│   ├── permissions_helper.php
│   ├── role_helper.php
│   └── export_helper.php
│
├── pages/               # Pages principales
│   ├── auth/            # Authentification
│   │   ├── auth.php
│   │   ├── register.php
│   │   ├── login.php
│   │   └── logout.php
│   │
│   ├── public/          # Pages publiques
│   │   ├── accueil.php
│   │   └── services.php
│   │
│   ├── dashboard/       # Tableau de bord
│   │   └── index.php
│   │
│   ├── stock/           # Gestion du stock
│   │   ├── stock.php
│   │   └── categories.php
│   │
│   ├── ventes/         # Gestion des ventes
│   │   ├── ventes.php
│   │   └── detailVente.php
│   │
│   ├── commandes/      # Gestion des commandes
│   │   ├── commandes.php
│   │   ├── detailCommande.php
│   │   └── bonCommande.php
│   │
│   ├── clients/        # Gestion des clients
│   │   └── clients.php
│   │
│   ├── fournisseurs/   # Gestion des fournisseurs
│   │   └── fournisseurs.php
│   │
│   ├── tresorerie/     # Trésorerie
│   │   └── tresorerie.php
│   │
│   └── admin/          # Administration
│       ├── utilisateurs.php
│       └── demandes_acces.php
│
├── assets/             # Ressources statiques
│   ├── css/
│   │   ├── styles.css
│   │   └── styles_connected.css
│   │
│   └── images/
│       ├── logo_epicerie.png
│       ├── fond-accueil.png
│       ├── fond-auth.png
│       ├── fond-index.png
│       └── fond-stock.png
│
├── database/           # Scripts SQL
│   ├── db.sql
│   ├── db_historique.sql
│   ├── db_demandes_acces.sql
│   ├── db_depenses_diverses.sql
│   ├── db_permissions_utilisateurs.sql
│   └── db_donnees_demo.sql
│
├── install/            # Scripts d'installation
│   ├── create_admin.php
│   ├── fix_admin_password.php
│   ├── install_demandes_acces.php
│   ├── install_depenses_diverses.php
│   ├── install_permissions_utilisateurs.php
│   └── install_donnees_demo.php
│
├── docs/               # Documentation
│   ├── README.md
│   ├── ARCHITECTURE_SCHEMA.md
│   ├── PRESENTATION_PROJET.md
│   ├── PLAN_PRESENTATION_ORALE.md
│   ├── GUIDE_CONNEXION_ADMIN.md
│   ├── GUIDE_DEMANDES_ACCES.md
│   ├── GUIDE_PERMISSIONS_GRANULAIRES.md
│   ├── GUIDE_INTEGRATION_CSS.md
│   ├── README_DONNEES_DEMO.md
│   └── ...
│
├── vendor/             # Bibliothèques externes
│   └── fpdf/           # Bibliothèque FPDF
│
└── index.php           # Point d'entrée (redirection)
```

## Avantages de cette structure

✅ **Séparation claire** : Chaque type de fichier a son dossier  
✅ **Maintenabilité** : Plus facile de trouver et modifier les fichiers  
✅ **Scalabilité** : Facile d'ajouter de nouvelles fonctionnalités  
✅ **Sécurité** : Les fichiers sensibles sont mieux organisés  
✅ **Professionnalisme** : Structure standard pour les projets PHP  

