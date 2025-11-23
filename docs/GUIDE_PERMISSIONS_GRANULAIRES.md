# 🔐 Guide du Système de Permissions Granulaires

## Vue d'ensemble

Le système de permissions granulaires permet aux administrateurs de donner accès à des rubriques spécifiques aux utilisateurs, sans leur donner tous les droits d'un rôle complet.

## Fonctionnement

### 1. Demande d'accès par l'utilisateur

Un vendeur (ou autre utilisateur) peut demander :
- **Un accès général** : L'admin choisira les rubriques à accorder
- **Une permission spécifique** : Ex. "Accès à la Trésorerie", "Modifier le Stock", etc.

### 2. Traitement par l'administrateur

Lorsqu'un admin approuve une demande :
- **Pour une demande de permission spécifique** : L'admin voit une liste de toutes les rubriques disponibles avec des cases à cocher
- **L'admin peut sélectionner plusieurs rubriques** : L'utilisateur n'aura accès qu'aux rubriques cochées
- **L'admin peut ajouter d'autres rubriques** : Même si une permission spécifique était demandée, l'admin peut en accorder d'autres

### 3. Rubriques disponibles

Les rubriques suivantes peuvent être accordées :

| Permission | Description | Page concernée |
|------------|-------------|-----------------|
| `acces_tresorerie` | Accès à la Trésorerie | `tresorerie.php` |
| `modifier_stock` | Modifier le Stock | `stock.php` |
| `modifier_fournisseurs` | Modifier les Fournisseurs | `fournisseurs.php` |
| `creer_commandes` | Créer des Commandes | `commandes.php` |
| `modifier_categories` | Modifier les Catégories | `categories.php` |
| `modifier_clients` | Modifier les Clients | `clients.php` |
| `voir_utilisateurs` | Voir les Utilisateurs | `utilisateurs.php` |

## Installation

### 1. Créer la table `permissions_utilisateurs`

Exécutez le script SQL :
```sql
-- Fichier : db_permissions_utilisateurs.sql
```

Ou utilisez le script PHP :
```php
// Fichier : install_permissions_utilisateurs.php
// Accédez à : http://localhost/epicerie/install_permissions_utilisateurs.php
```

### 2. Vérifier les helpers

Assurez-vous que `permissions_helper.php` est inclus dans les pages qui utilisent les permissions.

## Utilisation dans le code

### Vérifier une permission

```php
include('permissions_helper.php');

// Vérifier si un utilisateur a une permission spécifique
if (aPermission($conn, $id_utilisateur, 'acces_tresorerie')) {
    // L'utilisateur a accès à la trésorerie
}

// Vérifier si un utilisateur a au moins une des permissions
if (aAuMoinsUnePermission($conn, $id_utilisateur, ['modifier_stock', 'modifier_fournisseurs'])) {
    // L'utilisateur peut modifier le stock OU les fournisseurs
}
```

### Ajouter une permission

```php
// Ajouter une permission à un utilisateur
ajouterPermission($conn, $id_utilisateur, 'acces_tresorerie', $id_admin, $id_demande_acces);
```

### Récupérer toutes les permissions d'un utilisateur

```php
$permissions = getPermissionsUtilisateur($conn, $id_utilisateur);
// Retourne : ['acces_tresorerie', 'modifier_stock', ...]
```

## Exemple d'utilisation dans une page

```php
<?php
session_start();
include('db_conn.php');
include('permissions_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$est_admin = ($role === 'admin');

// Vérifier l'accès
$peut_modifier = $est_admin || aPermission($conn, $id_utilisateur, 'modifier_stock');

if (!$peut_modifier) {
    // Rediriger ou afficher un message d'erreur
    header("Location: index.php");
    exit();
}

// Le reste du code de la page...
?>
```

## Interface Admin

Lorsqu'un admin traite une demande :

1. **Pour une demande "Accès général"** :
   - L'admin voit toutes les rubriques disponibles
   - Il peut cocher plusieurs rubriques
   - L'utilisateur n'aura accès qu'aux rubriques cochées

2. **Pour une demande de permission spécifique** :
   - La permission demandée est pré-cochée
   - L'admin peut ajouter d'autres rubriques
   - L'admin peut aussi retirer la permission demandée

3. **Validation** :
   - L'admin doit sélectionner au moins une rubrique pour approuver
   - Un message de confirmation liste les permissions accordées

## Avantages

✅ **Contrôle granulaire** : L'admin choisit exactement ce à quoi l'utilisateur a accès  
✅ **Flexibilité** : Plusieurs rubriques peuvent être accordées en une seule demande  
✅ **Sécurité** : Les permissions sont stockées en base de données et vérifiées à chaque accès  
✅ **Traçabilité** : Chaque permission est liée à une demande d'accès et à l'admin qui l'a accordée  

## Structure de la base de données

### Table `permissions_utilisateurs`

```sql
CREATE TABLE permissions_utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    date_attribution TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin_attribueur INT NULL,
    id_demande_acces INT NULL,
    UNIQUE KEY unique_permission_user (id_utilisateur, permission)
);
```

## Notes importantes

- Les admins ont toujours accès à tout (pas besoin de permissions)
- Les permissions sont vérifiées à chaque chargement de page
- Une permission ne peut être accordée qu'une seule fois par utilisateur (contrainte UNIQUE)
- La suppression d'un utilisateur supprime automatiquement ses permissions (CASCADE)

