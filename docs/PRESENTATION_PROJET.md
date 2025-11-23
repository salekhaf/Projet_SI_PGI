# 📊 Présentation du Projet : Smart Stock
## Système de Gestion d'Épicerie (PGI Web)

---

## 1. 🎯 Introduction et Contexte

### Présentation du projet
**Smart Stock** est une application web complète de gestion d'épicerie (PGI - Progiciel de Gestion Intégré) développée en PHP/MySQL. Elle permet de gérer l'ensemble des opérations d'une épicerie : stock, ventes, clients, fournisseurs, commandes et trésorerie.

### Objectifs
- Centraliser la gestion commerciale en une seule plateforme
- Automatiser les processus de gestion (stock, ventes, commandes)
- Fournir des statistiques en temps réel pour la prise de décision
- Faciliter le travail en équipe avec un système de rôles et permissions

### Public cible
- Épiceries et commerces de proximité
- Petits magasins de détail
- Entreprises nécessitant une gestion de stock simple et efficace

---

## 2. 🏗️ Architecture Technique

### Technologies utilisées
- **Backend** : PHP 7.4+ (MySQLi)
- **Base de données** : MySQL/MariaDB
- **Frontend** : HTML5, CSS3, JavaScript
- **Bibliothèques** : Chart.js (graphiques), FPDF (génération PDF)
- **Sécurité** : Prepared statements, password hashing (bcrypt), sessions PHP

### Structure de la base de données
- **8 tables principales** : utilisateurs, produits, clients, fournisseurs, catégories, ventes, achats, historique
- **Relations** : Clés étrangères pour l'intégrité référentielle
- **Index** : Optimisation des requêtes fréquentes

### Architecture MVC simplifiée
- Séparation logique/présentation
- Helpers réutilisables (historique, export, rôles)
- Connexion centralisée à la base de données

---

## 3. 📦 Fonctionnalités Principales

### 3.1 Gestion du Stock
**Page : `stock.php`**

- ✅ **CRUD complet** : Ajout, modification, suppression de produits
- ✅ **Informations gérées** : Nom, catégorie, prix d'achat/vente, quantité en stock, fournisseur
- ✅ **Recherche avancée** : Par nom de produit
- ✅ **Filtrage** : Par catégorie, niveau de stock (normal, bas, critique)
- ✅ **Pagination** : 15 produits par page
- ✅ **Export CSV** : Téléchargement des données pour Excel
- ✅ **Alertes visuelles** : Mise en évidence des stocks bas (< 10) et critiques (0)

### 3.2 Gestion des Ventes
**Page : `ventes.php`**

- ✅ **Enregistrement de ventes** : Création de nouvelles transactions
- ✅ **Sélection de produits** : Ajout multiple de produits à une vente
- ✅ **Calcul automatique** : Total calculé automatiquement
- ✅ **Association client** : Lien avec la base clients (optionnel)
- ✅ **Mise à jour automatique** : Réduction automatique du stock
- ✅ **Historique** : Liste de toutes les ventes avec détails
- ✅ **Détails de vente** : Page dédiée (`detailVente.php`) avec produits vendus

### 3.3 Gestion des Clients
**Page : `clients.php`**

- ✅ **CRUD complet** : Ajout, modification, suppression
- ✅ **Informations** : Nom, téléphone, email, adresse
- ✅ **Recherche** : Par nom, téléphone ou email
- ✅ **Pagination** : 15 clients par page
- ✅ **Export CSV** : Export des données clients

### 3.4 Gestion des Fournisseurs
**Page : `fournisseurs.php`**

- ✅ **CRUD complet** : Ajout, modification, suppression
- ✅ **Informations** : Nom, téléphone, email, adresse
- ✅ **Recherche** : Par nom, téléphone ou email
- ✅ **Pagination** : 15 fournisseurs par page
- ✅ **Export CSV** : Export des données fournisseurs

### 3.5 Gestion des Catégories
**Page : `categories.php`**

- ✅ **CRUD complet** : Ajout, modification, suppression
- ✅ **Compteur de produits** : Affichage du nombre de produits par catégorie
- ✅ **Intégration** : Utilisée pour organiser et filtrer les produits

### 3.6 Gestion des Commandes
**Page : `commandes.php`**

- ✅ **Création de commandes** : Commandes auprès des fournisseurs
- ✅ **Sélection de produits** : Ajout multiple de produits
- ✅ **Bon de commande PDF** : Génération automatique (`bonCommande.php`)
- ✅ **Historique** : Liste de toutes les commandes
- ✅ **Détails** : Page dédiée (`detailCommande.php`)

### 3.7 Trésorerie
**Page : `tresorerie.php`**

- ✅ **Vue d'ensemble financière** : Revenus, dépenses, bénéfices
- ✅ **Historique des transactions** : Liste chronologique
- ✅ **Calculs automatiques** : Bénéfices calculés en temps réel

---

## 4. 🚀 Fonctionnalités Avancées

### 4.1 Dashboard Interactif
**Page : `index.php`**

**Statistiques en temps réel :**
- 💰 Ventes du jour, de la semaine, du mois
- 📦 Nombre total de produits et valeur du stock
- 👥 Nombre de clients
- ⚠️ Alertes de stock bas et critique

**Graphiques :**
- 📈 Graphique des ventes des 7 derniers jours (Chart.js)
- 🏆 Top 5 des produits les plus vendus

**Alertes :**
- Affichage des produits en stock bas (< 10 unités)
- Affichage des produits en stock critique (0 unité)
- Liens directs vers la gestion du stock

### 4.2 Recherche et Filtrage Avancés
- **Recherche multi-critères** : Nom, email, téléphone selon le contexte
- **Filtres dynamiques** : Catégorie, niveau de stock, rôle utilisateur
- **Réinitialisation** : Bouton pour effacer tous les filtres
- **Conservation des filtres** : Maintien lors de la pagination

### 4.3 Pagination Intelligente
- **15 éléments par page** : Performance optimisée
- **Navigation** : Boutons Précédent/Suivant
- **Compteur** : Affichage "X à Y sur Z éléments"
- **Filtres conservés** : Les filtres restent actifs lors de la navigation

### 4.4 Export de Données
- **Format CSV** : Compatible Excel
- **Encodage UTF-8** : Avec BOM pour Excel
- **Disponible pour** : Stock, clients, fournisseurs
- **Séparateur** : Point-virgule (;) pour Excel

### 4.5 Historique des Modifications
**Table : `historique`**

- ✅ **Enregistrement automatique** : Toutes les actions (ajout, modification, suppression)
- ✅ **Traçabilité complète** : Qui, quoi, quand, anciennes/nouvelles valeurs
- ✅ **Format JSON** : Stockage structuré des modifications
- ✅ **Tables concernées** : Produits, clients, fournisseurs, catégories, utilisateurs

---

## 5. 🔐 Sécurité et Gestion des Utilisateurs

### 5.1 Système d'Authentification
**Page : `auth.php`**

- ✅ **Inscription** : Création de compte avec validation
- ✅ **Connexion** : Authentification sécurisée
- ✅ **Hachage des mots de passe** : Bcrypt (password_hash)
- ✅ **Sessions PHP** : Gestion des sessions utilisateur
- ✅ **Protection des pages** : Vérification de connexion sur toutes les pages

### 5.2 Gestion des Rôles
**4 rôles disponibles :**

1. **Admin** : Accès complet à toutes les fonctionnalités
2. **Vendeur** : Gestion des ventes et clients (lecture seule pour stock)
3. **Responsable Approvisionnement** : Gestion du stock, commandes, fournisseurs
4. **Trésorier** : Accès à la trésorerie et aux statistiques financières

**Page : `utilisateurs.php`** (Admin uniquement)
- ✅ Liste de tous les utilisateurs
- ✅ Modification des rôles
- ✅ Recherche et filtrage par rôle
- ✅ Protection : Impossible de supprimer le dernier admin

### 5.3 Système de Demandes d'Accès
**Page : `demandes_acces.php`**

**Pour les utilisateurs :**
- ✅ Demande d'élévation de rôle
- ✅ Demande de permission spécifique
- ✅ Justification de la demande

**Pour les admins :**
- ✅ Visualisation des demandes en attente
- ✅ Approbation/Refus avec commentaires
- ✅ Mise à jour automatique du rôle si approuvé
- ✅ Historique complet des demandes
- ✅ Compteur de demandes en attente dans la navbar

### 5.4 Sécurité des Données
- ✅ **Prepared Statements** : Protection contre les injections SQL
- ✅ **Échappement HTML** : Protection XSS (htmlspecialchars)
- ✅ **Validation des entrées** : Vérification côté serveur
- ✅ **Contrôle d'accès** : Vérification des permissions par page

---

## 6. 🎨 Interface et Expérience Utilisateur

### 6.1 Design Moderne
- ✅ **CSS centralisé** : `styles_connected.css` pour cohérence
- ✅ **Responsive** : Adapté mobile, tablette, desktop
- ✅ **Animations** : Transitions fluides, effets au survol
- ✅ **Couleurs cohérentes** : Palette harmonieuse
- ✅ **Icônes** : Emojis pour une navigation intuitive

### 6.2 Navigation
- ✅ **Navbar fixe** : Accessible sur toutes les pages
- ✅ **Liens contextuels** : Affichage selon le rôle
- ✅ **Badges de rôle** : Identification visuelle
- ✅ **Compteurs** : Notifications visuelles (demandes en attente)

### 6.3 Messages Utilisateur
- ✅ **Messages de succès** : Confirmation des actions
- ✅ **Messages d'erreur** : Gestion des erreurs claire
- ✅ **Messages d'avertissement** : Alertes importantes
- ✅ **Design cohérent** : Style uniforme pour tous les messages

### 6.4 Formulaires
- ✅ **Validation** : Côté client et serveur
- ✅ **Design moderne** : Bordures arrondies, ombres
- ✅ **Feedback visuel** : États actif, focus, hover
- ✅ **Organisation claire** : Groupes logiques

---

## 7. 📊 Points Forts du Projet

### 7.1 Fonctionnalités Complètes
- ✅ **16+ fonctionnalités principales** couvrant tous les aspects de la gestion
- ✅ **CRUD complet** sur toutes les entités
- ✅ **Automatisation** : Mise à jour automatique du stock, calculs automatiques

### 7.2 Performance
- ✅ **Pagination** : Limitation du nombre d'éléments affichés
- ✅ **Index de base de données** : Optimisation des requêtes
- ✅ **Requêtes optimisées** : Jointures efficaces

### 7.3 Sécurité
- ✅ **Protection SQL** : Prepared statements partout
- ✅ **Protection XSS** : Échappement HTML
- ✅ **Gestion des sessions** : Sécurisée
- ✅ **Contrôle d'accès** : Basé sur les rôles

### 7.4 Expérience Utilisateur
- ✅ **Interface intuitive** : Navigation claire
- ✅ **Recherche rapide** : Trouver l'information facilement
- ✅ **Alertes visuelles** : Stocks bas, demandes en attente
- ✅ **Graphiques** : Visualisation des données

### 7.5 Traçabilité
- ✅ **Historique complet** : Toutes les modifications enregistrées
- ✅ **Audit trail** : Qui a fait quoi et quand
- ✅ **Export de données** : Sauvegarde et analyse externe

---

## 8. 🔧 Architecture Technique Détaillée

### 8.1 Structure des Fichiers
```
epicerie/
├── Pages principales
│   ├── index.php (Dashboard)
│   ├── stock.php
│   ├── ventes.php
│   ├── clients.php
│   ├── fournisseurs.php
│   ├── categories.php
│   ├── commandes.php
│   ├── tresorerie.php
│   └── utilisateurs.php
│
├── Authentification
│   ├── auth.php (Inscription/Connexion)
│   └── logout.php
│
├── Helpers
│   ├── db_conn.php (Connexion BDD)
│   ├── historique_helper.php
│   ├── export_helper.php
│   └── role_helper.php
│
├── Styles
│   ├── styles_connected.css
│   └── styles.css
│
└── Base de données
    ├── db.sql
    ├── db_historique.sql
    └── db_demandes_acces.sql
```

### 8.2 Base de Données
**8 tables principales :**
1. `utilisateurs` : Comptes utilisateurs et rôles
2. `categories` : Catégories de produits
3. `fournisseurs` : Informations fournisseurs
4. `produits` : Catalogue de produits
5. `clients` : Base clients
6. `ventes` : Transactions de vente
7. `details_vente` : Détails des produits vendus
8. `achats` : Commandes fournisseurs
9. `details_achat` : Détails des commandes
10. `historique` : Journal des modifications
11. `demandes_acces` : Demandes d'accès utilisateurs

---

## 9. 📈 Statistiques et Métriques

### Données affichées
- **Ventes** : Jour, semaine, mois, total
- **Stock** : Nombre de produits, valeur totale, alertes
- **Clients** : Nombre total
- **Graphiques** : Ventes sur 7 jours, top produits

### Calculs automatiques
- Bénéfices (revenus - dépenses)
- Valeur du stock (quantité × prix d'achat)
- Totaux de ventes par période
- Quantités vendues par produit

---

## 10. 🎯 Conclusion

### Résumé
Smart Stock est une solution complète et professionnelle de gestion d'épicerie, offrant :
- ✅ **16+ fonctionnalités** couvrant tous les besoins
- ✅ **Sécurité renforcée** avec gestion des rôles
- ✅ **Interface moderne** et intuitive
- ✅ **Performance optimisée** avec pagination et index
- ✅ **Traçabilité complète** avec historique

### Points d'excellence
1. **Complétude** : Tous les aspects de la gestion couverts
2. **Sécurité** : Protection contre les vulnérabilités courantes
3. **UX** : Interface claire et intuitive
4. **Maintenabilité** : Code organisé, helpers réutilisables
5. **Évolutivité** : Architecture permettant l'ajout de fonctionnalités

### Technologies maîtrisées
- PHP/MySQLi (Backend)
- MySQL (Base de données)
- HTML5/CSS3/JavaScript (Frontend)
- Chart.js (Visualisation)
- FPDF (Génération PDF)
- Sécurité web (SQL injection, XSS)

---

## 📝 Notes pour la Présentation

### Durée recommandée : 10-15 minutes

### Structure de présentation orale :
1. **Introduction** (2 min) : Contexte, objectifs
2. **Architecture** (2 min) : Technologies, structure
3. **Démonstration** (8-10 min) : 
   - Dashboard et statistiques
   - Gestion du stock (CRUD, recherche, filtres)
   - Gestion des ventes
   - Système de rôles et demandes d'accès
   - Export et historique
4. **Conclusion** (1 min) : Points forts, technologies

### Points à mettre en avant :
- ✅ Complétude des fonctionnalités
- ✅ Sécurité (prepared statements, rôles)
- ✅ Interface moderne et intuitive
- ✅ Automatisation (stock, calculs)
- ✅ Traçabilité (historique)

---

**Document créé pour la présentation du projet Smart Stock** 🚀


