<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: ../auth/auth.php");
    exit();
}

include('../../config/db_conn.php');
include('../../includes/historique_helper.php');
include('../../includes/role_helper.php');
include_once('../../includes/permissions_helper.php');

$role = $_SESSION['role'];
$id_utilisateur = $_SESSION['id_utilisateur'];
$message = "";

// Vérifier si la table existe, sinon la créer
$table_exists = $conn->query("SHOW TABLES LIKE 'demandes_acces'");
if ((is_object($table_exists) && method_exists($table_exists, 'num_rows') ? $table_exists->num_rows() : mysqli_num_rows($table_exists)) == 0) {
    // Créer la table si elle n'existe pas
    $create_table = "CREATE TABLE IF NOT EXISTS demandes_acces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_utilisateur INT NOT NULL,
        type_demande ENUM('role', 'permission_specifique') NOT NULL,
        role_demande VARCHAR(50) NULL,
        permission_demande VARCHAR(100) NULL,
        raison TEXT,
        statut ENUM('en_attente', 'approuvee', 'refusee') DEFAULT 'en_attente',
        id_admin_approbateur INT NULL,
        date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_traitement TIMESTAMP NULL,
        commentaire_admin TEXT NULL,
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (id_admin_approbateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
    )";
    $conn->query($create_table);
}

// Les vendeurs et trésoriers peuvent faire des demandes
$peut_demander = in_array($role, ['vendeur', 'tresorier']);
// Seuls les admins peuvent approuver
$peut_approuver = ($role === 'admin');

if (!$peut_demander && !$peut_approuver) {
    header("Location: ../dashboard/index.php");
    exit();
}

// --- CRÉATION D'UNE DEMANDE (Vendeur/Trésorier) ---
if ($peut_demander && isset($_POST['creer_demande'])) {
    $type_demande = trim($_POST['type_demande']);
    $role_demande = isset($_POST['role_demande']) ? trim($_POST['role_demande']) : null;
    $permission_demande = isset($_POST['permission_demande']) ? trim($_POST['permission_demande']) : null;
    $raison = trim($_POST['raison']);

    if (empty($raison)) {
        $message = "⚠️ Veuillez indiquer une raison pour votre demande.";
    } else {
        $stmt = $conn->prepare("INSERT INTO demandes_acces (id_utilisateur, type_demande, role_demande, permission_demande, raison) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_utilisateur, $type_demande, $role_demande, $permission_demande, $raison);
        
        if ($stmt->execute()) {
            $message = "✅ Votre demande a été envoyée avec succès. Un administrateur va l'examiner.";
        } else {
            $message = "❌ Erreur lors de l'envoi de la demande : " . (isset($GLOBALS['is_postgresql']) && is_object($conn) && get_class($conn) === 'PostgreSQLConnection' ? $conn->error() : mysqli_error($conn));
        }
        $stmt->close();
    }
}

// --- TRAITEMENT D'UNE DEMANDE (Admin) ---
if ($peut_approuver && isset($_POST['traiter_demande'])) {
    $id_demande = intval($_POST['id_demande']);
    $action = trim($_POST['action']); // 'approuver' ou 'refuser'
    $commentaire = trim($_POST['commentaire'] ?? '');

    // Récupérer la demande
    $stmt = $conn->prepare("SELECT * FROM demandes_acces WHERE id = ? AND statut = 'en_attente'");
    $stmt->bind_param("i", $id_demande);
    $stmt->execute();
    $result = $stmt->get_result();
    $demande = (is_object($result) && method_exists($result, 'fetch_assoc') ? $result->fetch_assoc() : mysqli_fetch_assoc($result));
    $stmt->close();

    if ($demande) {
        if ($action === 'approuver') {
            // Approuver la demande
            $nouveau_statut = 'approuvee';
            
            // Si c'est une demande de rôle, changer le rôle
            if ($demande['type_demande'] === 'role' && !empty($demande['role_demande'])) {
                $nouveau_role = $demande['role_demande'];
                
                // Vérifier que ce n'est pas le dernier admin
                if ($nouveau_role !== 'admin') {
                    // Récupérer l'ancien rôle pour l'historique
                    $stmt_old = $conn->prepare("SELECT nom, role FROM utilisateurs WHERE id = ?");
                    $stmt_old->bind_param("i", $demande['id_utilisateur']);
                    $stmt_old->execute();
                    $result_old = $stmt_old->get_result();
                    $ancien = (is_object($result_old) && method_exists($result_old, 'fetch_assoc') ? $result_old->fetch_assoc() : mysqli_fetch_assoc($result_old));
                    $stmt_old->close();
                    
                    $stmt_update = $conn->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $nouveau_role, $demande['id_utilisateur']);
                    
                    if ($stmt_update->execute()) {
                        enregistrer_historique($conn, $id_utilisateur, 'modification', 'utilisateurs', $demande['id_utilisateur'], 
                            "Rôle changé via demande d'accès approuvée: {$ancien['nom']} ({$ancien['role']} → {$nouveau_role})", 
                            $ancien, ['role' => $nouveau_role]);
                        $message = "✅ Demande approuvée et rôle mis à jour.";
                    } else {
                        $message = "❌ Erreur lors de la mise à jour du rôle.";
                    }
                    $stmt_update->close();
                } else {
                    $message = "⚠️ Impossible d'attribuer le rôle admin via une demande.";
                }
            } elseif ($demande['type_demande'] === 'permission_specifique') {
                // Si c'est une demande de permission spécifique, attribuer les permissions sélectionnées
                $permissions_selectionnees = isset($_POST['permissions']) ? $_POST['permissions'] : [];
                
                // Si accès_general demandé, permettre la sélection de toutes les rubriques
                // Sinon, si une permission spécifique est demandée, l'ajouter par défaut si aucune sélection
                if (empty($permissions_selectionnees) && !empty($demande['permission_demande']) && $demande['permission_demande'] !== 'acces_general') {
                    // Si l'admin n'a rien sélectionné mais qu'une permission spécifique était demandée, l'accorder
                    $permissions_selectionnees = [$demande['permission_demande']];
                }
                
                if (!empty($permissions_selectionnees)) {
                    $permissions_accordees = [];
                    $permissions_disponibles = getPermissionsDisponibles();
                    
                    foreach ($permissions_selectionnees as $permission) {
                        if (isset($permissions_disponibles[$permission])) {
                            if (ajouterPermission($conn, $demande['id_utilisateur'], $permission, $id_utilisateur, $id_demande)) {
                                $permissions_accordees[] = $permissions_disponibles[$permission]['nom'];
                            }
                        }
                    }
                    
                    if (!empty($permissions_accordees)) {
                        $message = "✅ Demande approuvée. Permissions accordées : " . implode(', ', $permissions_accordees);
                    } else {
                        $message = "⚠️ Aucune permission valide sélectionnée.";
                    }
                } else {
                    $message = "⚠️ Veuillez sélectionner au moins une rubrique à accorder.";
                }
            } else {
                $message = "✅ Demande approuvée.";
            }
        } else {
            $nouveau_statut = 'refusee';
            $message = "❌ Demande refusée.";
        }

        // Mettre à jour le statut de la demande
        $stmt_update = $conn->prepare("UPDATE demandes_acces SET statut = ?, id_admin_approbateur = ?, date_traitement = NOW(), commentaire_admin = ? WHERE id = ?");
        $stmt_update->bind_param("siss", $nouveau_statut, $id_utilisateur, $commentaire, $id_demande);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

// --- RÉCUPÉRATION DES DEMANDES ---
if ($peut_demander) {
    // Vendeur : voir ses propres demandes
    $query = "SELECT d.*, u.nom as admin_nom 
              FROM demandes_acces d 
              LEFT JOIN utilisateurs u ON d.id_admin_approbateur = u.id 
              WHERE d.id_utilisateur = ? 
              ORDER BY d.date_demande DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $mes_demandes = $stmt->get_result();
    $stmt->close();
} else {
    // Admin : voir toutes les demandes en attente
    $demandes_en_attente = $conn->query("
        SELECT d.*, u.nom as demandeur_nom, u.email as demandeur_email, u.role as demandeur_role
        FROM demandes_acces d
        JOIN utilisateurs u ON d.id_utilisateur = u.id
        WHERE d.statut = 'en_attente'
        ORDER BY d.date_demande ASC
    ");
    
    // Toutes les demandes pour historique
    $toutes_demandes = $conn->query("
        SELECT d.*, u.nom as demandeur_nom, u.email as demandeur_email, u.role as demandeur_role, a.nom as admin_nom
        FROM demandes_acces d
        JOIN utilisateurs u ON d.id_utilisateur = u.id
        LEFT JOIN utilisateurs a ON d.id_admin_approbateur = a.id
        ORDER BY d.date_demande DESC
        LIMIT 50
    ");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>🔐 Demandes d'accès - Smart Stock</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../../assets/css/styles_connected.css">
</head>
<body>

<?php
// Inclure la navbar réutilisable
// Ne pas définir $role_badge ici, navbar.php utilisera displayRoleBadge() directement
include('../../includes/navbar.php');
?>

<div class="main-container">
    <div class="content-wrapper">
        <h1><?= $peut_demander ? '🔐 Mes demandes d\'accès' : '🔐 Gestion des demandes d\'accès' ?></h1>
        
        <p style="margin-bottom: 25px;">
            <a href="../dashboard/index.php" class="btn btn-secondary">⬅️ Retour au tableau de bord</a>
        </p>

        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : (strpos($message, '⚠️') !== false ? 'warning' : 'error') ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($peut_demander): ?>
            <!-- Interface Vendeur/Trésorier -->
            <div style="background: #fff8f0; border: 2px solid #fa8c0f33; padding: 25px; border-radius: 15px; margin-bottom: 30px;">
                <h3>📝 Créer une nouvelle demande</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Vous pouvez demander une élévation de privilèges ou un accès spécifique. 
                    Un administrateur examinera votre demande.
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Type de demande :</label>
                        <select name="type_demande" id="type_demande" required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0;">
                            <option value="">-- Sélectionner --</option>
                            <option value="role">Changement de rôle</option>
                            <option value="permission_specifique">Permission spécifique</option>
                        </select>
                    </div>

                    <div class="form-group" id="role_demande_group" style="display: none;">
                        <label>Rôle demandé :</label>
                        <select name="role_demande" style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0;">
                            <option value="responsable_approvisionnement">📦 Responsable approvisionnement</option>
                            <option value="tresorier">💼 Trésorier</option>
                        </select>
                    </div>

                    <div class="form-group" id="permission_demande_group" style="display: none;">
                        <label>Type d'accès demandé :</label>
                        <select name="permission_demande" style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0;">
                            <option value="acces_general">🔓 Accès général (l'admin choisira les rubriques)</option>
                            <option value="acces_tresorerie">💰 Accès à la Trésorerie</option>
                            <option value="modifier_stock">📦 Modifier le Stock</option>
                            <option value="modifier_fournisseurs">🚚 Modifier les Fournisseurs</option>
                            <option value="creer_commandes">📋 Créer des Commandes</option>
                            <option value="modifier_categories">🏷️ Modifier les Catégories</option>
                            <option value="modifier_clients">👥 Modifier les Clients</option>
                        </select>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            💡 Si vous choisissez "Accès général", l'administrateur sélectionnera les rubriques auxquelles vous aurez accès.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Raison de la demande :</label>
                        <textarea name="raison" required placeholder="Expliquez pourquoi vous avez besoin de cet accès..." style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0; min-height: 100px;"></textarea>
                    </div>

                    <button type="submit" name="creer_demande" class="btn">📤 Envoyer la demande</button>
                </form>
            </div>

            <h3>📋 Mes demandes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Détail</th>
                        <th>Raison</th>
                        <th>Statut</th>
                        <th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($mes_demandes && (is_object($mes_demandes) && method_exists($mes_demandes, 'num_rows') ? $mes_demandes->num_rows() : mysqli_num_rows($mes_demandes)) > 0): ?>
                        <?php while ($d = (is_object($mes_demandes) && method_exists($mes_demandes, 'fetch_assoc') ? $mes_demandes->fetch_assoc() : mysqli_fetch_assoc($mes_demandes))): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($d['date_demande'])) ?></td>
                            <td><?= $d['type_demande'] === 'role' ? 'Changement de rôle' : 'Permission spécifique' ?></td>
                            <td>
                                <?php if ($d['type_demande'] === 'role'): ?>
                                    <?= displayRoleBadge($d['role_demande']) ?>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= htmlspecialchars($d['permission_demande']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($d['raison']) ?></td>
                            <td>
                                <?php
                                $badge_class = '';
                                $badge_text = '';
                                switch($d['statut']) {
                                    case 'en_attente':
                                        $badge_class = 'badge-warning';
                                        $badge_text = '⏳ En attente';
                                        break;
                                    case 'approuvee':
                                        $badge_class = 'badge-success';
                                        $badge_text = '✅ Approuvée';
                                        break;
                                    case 'refusee':
                                        $badge_class = 'badge-danger';
                                        $badge_text = '❌ Refusée';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
                            </td>
                            <td>
                                <?php if ($d['commentaire_admin']): ?>
                                    <?= htmlspecialchars($d['commentaire_admin']) ?>
                                <?php else: ?>
                                    <em style="color: #999;">—</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                Aucune demande pour le moment.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php else: ?>
            <!-- Interface Admin -->
            <div style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); padding: 25px; border-radius: 15px; margin-bottom: 30px; border-left: 5px solid #ffc107; box-shadow: 0 5px 15px rgba(255,193,7,0.2);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="font-size: 2.5em;">⏳</div>
                    <div>
                        <h3 style="margin: 0; color: #856404; font-size: 1.5em;">Demandes en attente</h3>
                        <p style="color: #856404; margin: 5px 0 0 0; font-size: 1em;">
                            <strong>Mode Admin :</strong> Lisez la raison, commentez si besoin, puis approuvez ✅ ou refusez ❌
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if ($demandes_en_attente && (is_object($demandes_en_attente) && method_exists($demandes_en_attente, 'num_rows') ? $demandes_en_attente->num_rows() : mysqli_num_rows($demandes_en_attente)) > 0): ?>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
                    <strong style="color: #dc3545; font-size: 1.1em;">
                        📊 <?= (is_object($demandes_en_attente) && method_exists($demandes_en_attente, 'num_rows') ? $demandes_en_attente->num_rows() : mysqli_num_rows($demandes_en_attente)) ?> demande<?= (is_object($demandes_en_attente) && method_exists($demandes_en_attente, 'num_rows') ? $demandes_en_attente->num_rows() : mysqli_num_rows($demandes_en_attente)) > 1 ? 's' : '' ?> nécessite<?= (is_object($demandes_en_attente) && method_exists($demandes_en_attente, 'num_rows') ? $demandes_en_attente->num_rows() : mysqli_num_rows($demandes_en_attente)) > 1 ? 'nt' : '' ?> votre attention
                    </strong>
                </div>
                <div style="display: grid; gap: 20px;">
                    <?php while ($d = (is_object($demandes_en_attente) && method_exists($demandes_en_attente, 'fetch_assoc') ? $demandes_en_attente->fetch_assoc() : mysqli_fetch_assoc($demandes_en_attente))): ?>
                    <div style="background: white; border: 2px solid #e0e0e0; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: 0.3s;" onmouseover="this.style.borderColor='#fa8c0f'; this.style.transform='translateY(-3px)'" onmouseout="this.style.borderColor='#e0e0e0'; this.style.transform='translateY(0)'">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <strong style="color: #666; font-size: 0.9em;">👤 Demandeur</strong>
                                <div style="margin-top: 8px;">
                                    <strong style="font-size: 1.1em; color: #333;"><?= htmlspecialchars($d['demandeur_nom']) ?></strong><br>
                                    <small style="color: #666;"><?= htmlspecialchars($d['demandeur_email']) ?></small><br>
                                    <?= displayRoleBadge($d['demandeur_role']) ?>
                                </div>
                            </div>
                            <div>
                                <strong style="color: #666; font-size: 0.9em;">📋 Type de demande</strong>
                                <div style="margin-top: 8px;">
                                    <span class="badge badge-info" style="font-size: 0.95em;">
                                        <?= $d['type_demande'] === 'role' ? '🔄 Changement de rôle' : '🔑 Permission spécifique' ?>
                                    </span><br>
                                    <?php if ($d['type_demande'] === 'role'): ?>
                                        <div style="margin-top: 10px;">
                                            <small style="color: #666;">Nouveau rôle demandé :</small><br>
                                            <?= displayRoleBadge($d['role_demande']) ?>
                                        </div>
                                    <?php else: ?>
                                        <div style="margin-top: 10px;">
                                            <small style="color: #666;">Permission demandée :</small><br>
                                            <span class="badge badge-warning" style="font-size: 0.95em;"><?= htmlspecialchars($d['permission_demande']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <strong style="color: #666; font-size: 0.9em;">📅 Date</strong>
                                <div style="margin-top: 8px; color: #333; font-weight: 500;">
                                    <?= date('d/m/Y à H:i', strtotime($d['date_demande'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #fa8c0f;">
                            <strong style="color: #666; font-size: 0.9em;">💬 Raison de la demande</strong>
                            <p style="margin: 10px 0 0 0; color: #333; line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($d['raison'])) ?>
                            </p>
                        </div>
                        
                        <form method="POST" id="form_demande_<?= $d['id'] ?>">
                            <input type="hidden" name="id_demande" value="<?= $d['id'] ?>">
                            
                            <?php if ($d['type_demande'] === 'permission_specifique' && ($d['permission_demande'] === 'acces_general' || empty($d['permission_demande']))): ?>
                                <!-- Sélection des rubriques pour les demandes de permission générale -->
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
                                    <strong style="color: #666; font-size: 1em; display: block; margin-bottom: 15px;">
                                        🔑 Sélectionnez les rubriques à accorder :
                                    </strong>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                                        <?php 
                                        $permissions_disponibles = getPermissionsDisponibles();
                                        foreach ($permissions_disponibles as $key => $perm): 
                                        ?>
                                            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; transition: 0.2s;" 
                                                   onmouseover="this.style.borderColor='#17a2b8'; this.style.backgroundColor='#f0f8ff'" 
                                                   onmouseout="this.style.borderColor='#e0e0e0'; this.style.backgroundColor='white'">
                                                <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key) ?>" 
                                                       style="margin-top: 3px; cursor: pointer; width: 18px; height: 18px;">
                                                <div style="flex: 1;">
                                                    <strong style="display: block; color: #333; margin-bottom: 4px;"><?= htmlspecialchars($perm['nom']) ?></strong>
                                                    <small style="color: #666; font-size: 0.85em;"><?= htmlspecialchars($perm['description']) ?></small>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <p style="margin-top: 15px; color: #666; font-size: 0.9em;">
                                        <strong>💡 Note :</strong> Vous pouvez sélectionner plusieurs rubriques. L'utilisateur n'aura accès qu'aux rubriques que vous cochez.
                                    </p>
                                </div>
                            <?php elseif ($d['type_demande'] === 'permission_specifique' && !empty($d['permission_demande']) && $d['permission_demande'] !== 'acces_general'): ?>
                                <!-- Pour une permission spécifique, permettre de modifier ou d'ajouter d'autres rubriques -->
                                <div style="background: #fff3cd; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                                    <strong style="color: #856404; font-size: 0.95em; display: block; margin-bottom: 10px;">
                                        📋 Permission demandée : <span style="color: #333;"><?= htmlspecialchars($d['permission_demande']) ?></span>
                                    </strong>
                                    <p style="color: #856404; font-size: 0.9em; margin: 0;">
                                        Vous pouvez accorder cette permission spécifique, ou sélectionner d'autres rubriques supplémentaires ci-dessous.
                                    </p>
                                </div>
                                <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
                                    <strong style="color: #666; font-size: 1em; display: block; margin-bottom: 15px;">
                                        🔑 Sélectionnez les rubriques à accorder :
                                    </strong>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px;">
                                        <?php 
                                        $permissions_disponibles = getPermissionsDisponibles();
                                        foreach ($permissions_disponibles as $key => $perm): 
                                            $checked = ($key === $d['permission_demande']) ? 'checked' : '';
                                        ?>
                                            <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: white; border: 2px solid #e0e0e0; border-radius: 8px; cursor: pointer; transition: 0.2s;" 
                                                   onmouseover="this.style.borderColor='#17a2b8'; this.style.backgroundColor='#f0f8ff'" 
                                                   onmouseout="this.style.borderColor='#e0e0e0'; this.style.backgroundColor='white'">
                                                <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($key) ?>" <?= $checked ?>
                                                       style="margin-top: 3px; cursor: pointer; width: 18px; height: 18px;">
                                                <div style="flex: 1;">
                                                    <strong style="display: block; color: #333; margin-bottom: 4px;"><?= htmlspecialchars($perm['nom']) ?></strong>
                                                    <small style="color: #666; font-size: 0.85em;"><?= htmlspecialchars($perm['description']) ?></small>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <p style="margin-top: 15px; color: #666; font-size: 0.9em;">
                                        <strong>💡 Note :</strong> La permission demandée est pré-cochée. Vous pouvez en ajouter d'autres ou la retirer.
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div style="display: flex; gap: 15px; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #666;">
                                        💭 Commentaire (optionnel) :
                                    </label>
                                    <textarea name="commentaire" placeholder="Ajoutez un commentaire pour le demandeur..." style="width: 100%; padding: 12px; border-radius: 10px; border: 2px solid #e0e0e0; font-size: 0.95em; resize: vertical; min-height: 80px; font-family: inherit;"></textarea>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 10px; min-width: 200px;">
                                    <button type="submit" name="traiter_demande" value="approuver" class="btn" style="background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 15px 25px; font-weight: 700; font-size: 1.1em; border: none; border-radius: 10px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(40,167,69,0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(40,167,69,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(40,167,69,0.3)'" onclick="return validerApprobation(<?= $d['id'] ?>, '<?= $d['type_demande'] ?>');">
                                        ✅ Approuver
                                    </button>
                                    <button type="submit" name="traiter_demande" value="refuser" class="btn" style="background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 15px 25px; font-weight: 700; font-size: 1.1em; border: none; border-radius: 10px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(220,53,69,0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(220,53,69,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(220,53,69,0.3)'" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette demande de <?= htmlspecialchars($d['demandeur_nom']) ?> ?');">
                                        ❌ Refuser
                                    </button>
                                </div>
                                <input type="hidden" name="action" value="">
                            </div>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="message success">
                    ✅ Aucune demande en attente.
                </div>
            <?php endif; ?>

            <h3 style="margin-top: 40px;">📜 Historique des demandes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Demandeur</th>
                        <th>Rôle actuel</th>
                        <th>Type</th>
                        <th>Détail</th>
                        <th>Statut</th>
                        <th>Traité par</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($toutes_demandes && (is_object($toutes_demandes) && method_exists($toutes_demandes, 'num_rows') ? $toutes_demandes->num_rows() : mysqli_num_rows($toutes_demandes)) > 0): ?>
                        <?php while ($d = (is_object($toutes_demandes) && method_exists($toutes_demandes, 'fetch_assoc') ? $toutes_demandes->fetch_assoc() : mysqli_fetch_assoc($toutes_demandes))): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($d['date_demande'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($d['demandeur_nom']) ?></strong><br>
                                <small style="color: #666;"><?= htmlspecialchars($d['demandeur_email']) ?></small>
                            </td>
                            <td><?= displayRoleBadge($d['demandeur_role']) ?></td>
                            <td><?= $d['type_demande'] === 'role' ? 'Changement de rôle' : 'Permission spécifique' ?></td>
                            <td>
                                <?php if ($d['type_demande'] === 'role'): ?>
                                    <?= displayRoleBadge($d['role_demande']) ?>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= htmlspecialchars($d['permission_demande']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badge_class = '';
                                $badge_text = '';
                                switch($d['statut']) {
                                    case 'en_attente':
                                        $badge_class = 'badge-warning';
                                        $badge_text = '⏳ En attente';
                                        break;
                                    case 'approuvee':
                                        $badge_class = 'badge-success';
                                        $badge_text = '✅ Approuvée';
                                        break;
                                    case 'refusee':
                                        $badge_class = 'badge-danger';
                                        $badge_text = '❌ Refusée';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= $badge_text ?></span>
                            </td>
                            <td>
                                <?php if ($d['admin_nom']): ?>
                                    <strong><?= htmlspecialchars($d['admin_nom']) ?></strong>
                                <?php else: ?>
                                    <em style="color: #999;">—</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                Aucune demande dans l'historique.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
// Afficher/masquer les champs selon le type de demande
document.getElementById('type_demande')?.addEventListener('change', function() {
    const type = this.value;
    document.getElementById('role_demande_group').style.display = (type === 'role') ? 'block' : 'none';
    document.getElementById('permission_demande_group').style.display = (type === 'permission_specifique') ? 'block' : 'none';
});

// Fonction pour valider l'approbation (vérifier qu'au moins une rubrique est sélectionnée pour les permissions)
function validerApprobation(idDemande, typeDemande) {
    const form = document.getElementById('form_demande_' + idDemande);
    
    if (typeDemande === 'permission_specifique') {
        const checkboxes = form.querySelectorAll('input[name="permissions[]"]:checked');
        if (checkboxes.length === 0) {
            alert('⚠️ Veuillez sélectionner au moins une rubrique à accorder avant d\'approuver la demande.');
            return false;
        }
    }
    
    // Définir l'action
    form.querySelector('input[name="action"]').value = 'approuver';
    return true;
}

// Gérer les boutons d'approbation/refus
document.querySelectorAll('form[id^="form_demande_"]').forEach(form => {
    form.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const action = this.value;
            form.querySelector('input[name="action"]').value = action;
        });
    });
});
</script>

</body>
</html>

