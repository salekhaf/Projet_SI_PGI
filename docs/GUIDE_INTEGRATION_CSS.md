# 🎨 Guide d'Intégration du CSS Moderne

## Fichier CSS créé
- **`styles_connected.css`** - Styles modernes et cohérents pour toutes les pages connectées

## Comment intégrer dans vos pages

### Étape 1 : Ajouter le lien CSS
Dans chaque page PHP connectée, ajoutez cette ligne dans le `<head>` :

```html
<link rel="stylesheet" href="styles_connected.css">
```

### Étape 2 : Adapter la structure HTML
Remplacez les styles inline par des classes CSS du fichier centralisé.

## Pages à modifier

### ✅ Pages principales à mettre à jour :
1. `index.php` - Tableau de bord
2. `stock.php` - Gestion du stock
3. `ventes.php` - Gestion des ventes
4. `clients.php` - Gestion des clients
5. `fournisseurs.php` - Gestion des fournisseurs
6. `categories.php` - Gestion des catégories
7. `commandes.php` - Commandes fournisseurs
8. `tresorerie.php` - Trésorerie
9. `utilisateurs.php` - Gestion des utilisateurs
10. `detailVente.php` - Détails d'une vente
11. `detailCommande.php` - Détails d'une commande
12. `bonCommande.php` - Bon de commande PDF

## Classes CSS disponibles

### Conteneurs
- `.container` - Container principal avec padding et ombre
- `.content-wrapper` - Wrapper avec animation fadeIn
- `.main-container` - Container pour le dashboard

### Boutons
- `.btn` - Bouton principal (orange)
- `.btn-secondary` - Bouton secondaire (bleu)
- `.btn-success` - Bouton succès (vert)
- `.btn-danger` - Bouton danger (rouge)
- `.btn-info` - Bouton info (cyan)
- `.btn-sm` - Bouton petit

### Messages
- `.message.success` - Message de succès
- `.message.error` - Message d'erreur
- `.message.warning` - Message d'avertissement

### Tableaux
- Les tableaux sont automatiquement stylés
- `.low-stock` - Ligne avec stock bas
- `.critical-stock` - Ligne avec stock critique

### Cartes
- `.card` - Carte standard
- `.stat-card` - Carte de statistique

### Pagination
- `.pagination` - Container de pagination
- `.pagination .active` - Page active

### Alertes
- `.alertes-box` - Container d'alertes
- `.alerte-item` - Item d'alerte
- `.alerte-item.critique` - Alerte critique
- `.alerte-item.bas` - Alerte stock bas

### Filtres
- `.filters` - Container de filtres

### Export
- `.export-buttons` - Container des boutons d'export

## Exemple de transformation

### AVANT :
```html
<div class="container" style="max-width: 1100px; margin: 60px auto; background: rgba(255,255,255,0.95); border-radius: 25px; padding: 40px;">
```

### APRÈS :
```html
<div class="content-wrapper">
```

## Structure HTML recommandée

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma Page - Smart Stock</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="styles_connected.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <!-- Navigation -->
        </nav>
    </header>
    
    <div class="main-container">
        <div class="content-wrapper">
            <!-- Contenu de la page -->
        </div>
    </div>
</body>
</html>
```

## Notes importantes

1. **Supprimer les styles inline** : Retirez les balises `<style>` dans chaque page
2. **Conserver les animations JavaScript** : Les scripts restent inchangés
3. **Adapter les classes** : Utilisez les classes CSS du fichier centralisé
4. **Responsive** : Le CSS est déjà responsive, pas besoin d'ajouter de media queries

## Avantages

✅ Design cohérent sur toutes les pages
✅ Maintenance facilitée (un seul fichier CSS)
✅ Performance améliorée (cache du navigateur)
✅ Responsive automatique
✅ Animations fluides
✅ Accessibilité améliorée




