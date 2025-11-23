# 🔐 Guide du système de demandes d'accès

## 📋 Vue d'ensemble

Le système de demandes d'accès permet aux **vendeurs** de demander une élévation de privilèges ou des permissions spécifiques. Les **admins** peuvent ensuite approuver ou refuser ces demandes.

---

## 🎯 Fonctionnalités

### Pour les Vendeurs

1. **Créer une demande**
   - Demander un changement de rôle (Responsable approvisionnement, Trésorier)
   - Demander une permission spécifique (Modifier stock, Créer commandes, etc.)
   - Indiquer une raison pour justifier la demande

2. **Suivre ses demandes**
   - Voir le statut de toutes ses demandes (En attente, Approuvée, Refusée)
   - Consulter les commentaires des administrateurs

3. **Notifications**
   - Affichage sur le dashboard si des demandes sont en attente
   - Lien direct vers la page des demandes

### Pour les Admins

1. **Gérer les demandes**
   - Voir toutes les demandes en attente
   - Approuver ou refuser avec un commentaire
   - Historique complet des demandes

2. **Approbation automatique**
   - Si une demande de rôle est approuvée, le rôle est automatiquement changé
   - L'action est enregistrée dans l'historique

---

## 📊 Restrictions actuelles des Vendeurs

| Fonctionnalité | Accès Vendeur |
|----------------|---------------|
| **Stock** | Consultation uniquement |
| **Clients** | ✅ Modification autorisée |
| **Fournisseurs** | Consultation uniquement |
| **Commandes** | Consultation uniquement |
| **Catégories** | Consultation uniquement |
| **Utilisateurs** | ❌ Pas d'accès |

---

## 🚀 Utilisation

### Pour créer une demande (Vendeur)

1. Aller sur le **Tableau de bord**
2. Cliquer sur **"🔐 Demander un accès supplémentaire"**
3. Choisir le type de demande :
   - **Changement de rôle** : Pour devenir Responsable ou Trésorier
   - **Permission spécifique** : Pour obtenir une permission précise
4. Remplir la raison de la demande
5. Envoyer la demande

### Pour traiter une demande (Admin)

1. Aller sur **"Demandes d'accès"** dans la navigation
2. Voir les demandes en attente
3. Lire la raison de la demande
4. Ajouter un commentaire (optionnel)
5. Cliquer sur **Approuver** ou **Refuser**

---

## 💡 Avantages

✅ **Transparence** : Les vendeurs savent pourquoi ils n'ont pas accès à certaines fonctionnalités  
✅ **Traçabilité** : Toutes les demandes sont enregistrées  
✅ **Flexibilité** : Système de permissions granulaires  
✅ **Sécurité** : Seuls les admins peuvent approuver  
✅ **Historique** : Toutes les actions sont tracées  

---

## 🔧 Installation

1. Exécuter le script SQL `db_demandes_acces.sql` pour créer la table
2. Le fichier `demandes_acces.php` est déjà créé
3. Le lien est automatiquement ajouté dans la navigation pour les admins
4. Les vendeurs voient un bouton sur le dashboard

---

## 📝 Types de demandes

### Changement de rôle
- **Responsable approvisionnement** : Accès complet au stock, fournisseurs, commandes
- **Trésorier** : Accès à la trésorerie

### Permissions spécifiques
- **Modifier le stock** : Ajouter/modifier des produits
- **Modifier les fournisseurs** : Gérer les fournisseurs
- **Créer des commandes** : Passer des commandes fournisseurs
- **Modifier les catégories** : Gérer les catégories

---

## 🎨 Améliorations futures possibles

- [ ] Notifications par email
- [ ] Demandes avec dates d'expiration
- [ ] Permissions temporaires
- [ ] Système de workflow multi-niveaux
- [ ] Statistiques sur les demandes



