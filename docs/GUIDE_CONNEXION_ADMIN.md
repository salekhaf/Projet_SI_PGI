# 🔐 Guide : Comment se connecter en tant qu'admin

## 📋 Méthode 1 : Créer un compte admin (Recommandé)

### Étape 1 : Créer le compte
1. Accédez à : `http://localhost/epicerie/create_admin.php`
2. Le compte admin sera créé automatiquement
3. **IMPORTANT** : Supprimez le fichier `create_admin.php` après utilisation

### Étape 2 : Se connecter
1. Allez sur : `http://localhost/epicerie/auth.php`
2. Utilisez les identifiants :
   - **Email** : `admin@epicerie.com`
   - **Mot de passe** : `admin123`
3. Cliquez sur "Se connecter"

### Étape 3 : Changer le mot de passe (Recommandé)
1. Une fois connecté, allez dans "Utilisateurs"
2. Modifiez votre mot de passe pour plus de sécurité

---

## 📋 Méthode 2 : Utiliser un compte existant

### Si un compte admin existe déjà :

1. **Vérifier dans la base de données** :
   - Ouvrez phpMyAdmin
   - Allez dans la base `epicerie_db`
   - Consultez la table `utilisateurs`
   - Cherchez un utilisateur avec `role = 'admin'`

2. **Si vous avez oublié le mot de passe** :
   - Utilisez le script SQL dans `create_admin.php` pour réinitialiser
   - Ou modifiez directement dans phpMyAdmin

---

## 📋 Méthode 3 : Transformer un vendeur en admin

### Via phpMyAdmin :
```sql
-- Remplacez 'email@exemple.com' par l'email du vendeur
UPDATE utilisateurs 
SET role = 'admin' 
WHERE email = 'email@exemple.com';
```

### Via l'interface (si vous êtes déjà admin) :
1. Connectez-vous en tant qu'admin
2. Allez dans "Utilisateurs"
3. Modifiez le rôle de l'utilisateur souhaité

---

## 🔒 Sécurité

### ⚠️ Actions importantes après création :
1. ✅ **Changez le mot de passe par défaut** (`admin123`)
2. ✅ **Supprimez `create_admin.php`** après utilisation
3. ✅ **Ne partagez jamais les identifiants admin**
4. ✅ **Utilisez un mot de passe fort**

---

## 🎯 Identifiants par défaut

| Champ | Valeur |
|------|--------|
| **Email** | `admin@epicerie.com` |
| **Mot de passe** | `admin123` |
| **Rôle** | `admin` |

⚠️ **Ces identifiants sont par défaut. Changez-les immédiatement après la première connexion !**

---

## 🆘 Problèmes courants

### "Aucun compte trouvé avec cet email"
- Vérifiez que le compte admin existe dans la base de données
- Exécutez `create_admin.php` pour créer le compte

### "Mot de passe incorrect"
- Le mot de passe par défaut est : `admin123`
- Si cela ne fonctionne pas, réinitialisez-le via SQL (voir `create_admin.php`)

### "Vous n'avez pas les permissions"
- Vérifiez que votre rôle est bien `admin` dans la table `utilisateurs`
- Modifiez-le via phpMyAdmin si nécessaire

---

## 📞 Support

Si vous rencontrez des problèmes :
1. Vérifiez que la base de données est bien connectée (`db_conn.php`)
2. Vérifiez que la table `utilisateurs` existe
3. Consultez les logs d'erreur PHP



