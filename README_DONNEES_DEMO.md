# 📦 Guide d'installation des données de démonstration

Ce guide explique comment ajouter des données de démonstration réalistes dans votre application d'épicerie.

## 📋 Contenu des données

Les données incluent :

- **7 catégories** : Boissons, Snacks, Produits frais, Sucreries, Conserves, Hygiène, Petit-déjeuner
- **7 fournisseurs** : DistribSnack Maroc, BoissonPlus, FreshFood Distribution, etc.
- **44 produits** : Produits typiques d'une épicerie (Coca-Cola, Chips, Yaourts, Chocolats, etc.)
- **20 clients** : Avec des prénoms marocains communs (Ahmed, Fatima, Mohamed, Aicha, etc.)
- **15 achats** : Commandes fournisseurs réparties sur les 30 derniers jours
- **25 ventes** : Ventes réparties sur les 30 derniers jours avec différents clients

## 🚀 Installation

### Méthode 1 : Script PHP (Recommandé)

1. Accédez à : `http://localhost/epicerie/install_donnees_demo.php`
2. Le script va automatiquement :
   - Insérer toutes les catégories
   - Insérer tous les fournisseurs
   - Insérer tous les produits
   - Insérer tous les clients
   - Créer les achats et mettre à jour les stocks
   - Créer les ventes et mettre à jour les stocks
3. Un résumé s'affichera à la fin

**Avantages :**
- ✅ Gère automatiquement les IDs existants
- ✅ Met à jour les stocks correctement
- ✅ Utilise des prepared statements (sécurisé)
- ✅ Affiche un résumé détaillé

### Méthode 2 : Script SQL

1. Ouvrez phpMyAdmin
2. Sélectionnez la base de données `epicerie_db`
3. Allez dans l'onglet "SQL"
4. Copiez-collez le contenu de `db_donnees_demo.sql`
5. Cliquez sur "Exécuter"

**Note :** Cette méthode peut nécessiter des ajustements si des données existent déjà.

## ⚠️ Important

- Les données sont insérées avec `ON DUPLICATE KEY UPDATE` pour éviter les doublons
- Si vous exécutez le script plusieurs fois, les données existantes seront mises à jour
- Les stocks sont automatiquement calculés (achats ajoutent, ventes retranchent)
- Les ventes sont associées à l'utilisateur avec `id = 2` (Vendeur 1)

## 📊 Données incluses

### Produits par catégorie

**Boissons (9 produits)**
- Coca-Cola, Pepsi, Sprite, Fanta
- Eau minérale, Jus d'orange
- Thé, Café

**Snacks (7 produits)**
- Chips Lay's, Doritos
- Cacahuètes, Popcorn
- Biscuits Oreo, Prince

**Produits frais (6 produits)**
- Yaourts Danone
- Lait, Fromage, Beurre
- Œufs

**Sucreries (7 produits)**
- Chocolats Milka, Cadbury
- Bonbons Haribo, Mentos
- Barres Snickers, Twix

**Conserves (5 produits)**
- Thon, Sardines
- Haricots, Pois chiches
- Tomates pelées

**Hygiène (5 produits)**
- Savon, Shampooing
- Dentifrice
- Papier toilette, Serviettes

**Petit-déjeuner (5 produits)**
- Céréales Corn Flakes, Chocapic
- Miel, Confiture
- Pain de mie

### Clients

20 clients avec des prénoms variés (diversité culturelle) :
- **Prénoms français** : Marie Dupont, Jean Martin, Lucas Schneider
- **Prénoms italiens** : Sofia Russo, Isabella Rodriguez
- **Prénoms irlandais** : Liam O'Connor
- **Prénoms africains** : Amina Diallo
- **Prénoms portugais/espagnols** : Carlos Mendes, Victor Silva
- **Prénoms asiatiques** : Jin Park, Hiroshi Tanaka
- **Prénoms russes** : Olga Ivanova
- **Prénoms juifs** : David Cohen
- **Prénoms américains** : Noah Williams, Emma Johansson
- **Prénoms arabes** : Fatima Benali, Ahmed Hassan, Sara Haddad
- **Prénoms roumains** : Elena Popescu
- **Prénoms indiens** : Arjun Patel

Tous avec des adresses dans différentes villes marocaines.

## 🔄 Réinitialisation

Si vous voulez réinitialiser toutes les données :

1. **Attention :** Cela supprimera toutes les données existantes !
2. Exécutez dans phpMyAdmin :
```sql
TRUNCATE TABLE details_vente;
TRUNCATE TABLE ventes;
TRUNCATE TABLE details_achat;
TRUNCATE TABLE achats;
TRUNCATE TABLE produits;
TRUNCATE TABLE clients;
TRUNCATE TABLE fournisseurs;
TRUNCATE TABLE categories;
```
3. Puis réexécutez le script d'installation.

## ✅ Vérification

Après l'installation, vérifiez :

1. **Tableau de bord** : Vous devriez voir des statistiques (ventes, stock, etc.)
2. **Page Stock** : 44 produits avec des stocks variés
3. **Page Clients** : 20 clients
4. **Page Fournisseurs** : 7 fournisseurs
5. **Page Ventes** : 25 ventes réparties sur 30 jours
6. **Page Commandes** : 15 commandes fournisseurs
7. **Page Trésorerie** : Graphiques et statistiques financières

## 📝 Notes

- Les prix sont en dirhams marocains (MAD)
- Les dates sont réparties sur les 30 derniers jours
- Les stocks sont réalistes (certains produits peuvent être en stock bas)
- Les ventes incluent plusieurs produits par transaction

, 