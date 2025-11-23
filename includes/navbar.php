<?php
/**
 * Navigation bar réutilisable
 * Calcule automatiquement les chemins relatifs selon l'emplacement de la page
 */

// S'assurer que role_helper.php est inclus
if (!function_exists('displayRoleBadge')) {
    include_once(__DIR__ . '/role_helper.php');
}

// Déterminer le niveau de profondeur depuis la racine
$current_file = $_SERVER['PHP_SELF'];
$current_dir = dirname($current_file);

// Compter le nombre de niveaux (pages/dashboard/, pages/admin/, etc.)
$levels = substr_count($current_dir, '/') - 1; // -1 car on commence après le premier /
$base_path = str_repeat('../', $levels);

// Vérifier les demandes d'accès en attente (pour les admins)
$nb_demandes_attente = 0;
if (isset($conn)) {
    $table_exists = $conn->query("SHOW TABLES LIKE 'demandes_acces'");
    if ((is_object($table_exists) && method_exists($table_exists, 'num_rows') ? $table_exists->num_rows() : mysqli_num_rows($table_exists)) > 0) {
        if ($role === 'admin') {
            $check_demandes = $conn->query("SELECT COUNT(*) as total FROM demandes_acces WHERE statut = 'en_attente'");
            $nb_demandes_attente = (is_object($check_demandes) && method_exists($check_demandes, 'fetch_assoc') ? $check_demandes->fetch_assoc() : mysqli_fetch_assoc($check_demandes))['total'] ?? 0;
        }
    }
}

// Vérifier l'accès à la trésorerie
$acces_tresorerie = false;
if (isset($role)) {
    if ($role === 'admin' || $role === 'tresorier') {
        $acces_tresorerie = true;
    } elseif (function_exists('aPermission') && isset($conn) && isset($id_utilisateur)) {
        $acces_tresorerie = aPermission($conn, $id_utilisateur, 'acces_tresorerie');
    }
}
?>
<header>
  <nav class="navbar">
    <div class="nav-left">
      <a href="<?= $base_path ?>pages/dashboard/index.php" class="logo-link">
        <img src="<?= $base_path ?>assets/images/logo_epicerie.png" alt="Logo Smart Stock" class="logo-navbar">
      </a>
      <a href="<?= $base_path ?>pages/dashboard/index.php" class="nav-link">Tableau de bord</a>
      <a href="<?= $base_path ?>pages/stock/stock.php" class="nav-link">Stock</a>
      <a href="<?= $base_path ?>pages/ventes/ventes.php" class="nav-link">Ventes</a>
      <a href="<?= $base_path ?>pages/clients/clients.php" class="nav-link">Clients</a>
      <a href="<?= $base_path ?>pages/commandes/commandes.php" class="nav-link">Commandes</a>
      <a href="<?= $base_path ?>pages/stock/categories.php" class="nav-link">Catégories</a>
      <?php if (isset($role) && ($role === 'admin' || $role === 'responsable_approvisionnement')): ?>
        <a href="<?= $base_path ?>pages/fournisseurs/fournisseurs.php" class="nav-link">Fournisseurs</a>
      <?php endif; ?>
      <?php if (isset($acces_tresorerie) && $acces_tresorerie): ?>
        <a href="<?= $base_path ?>pages/tresorerie/tresorerie.php" class="nav-link">Trésorerie</a>
      <?php endif; ?>
      <?php if (isset($role) && $role === 'admin'): ?>
        <a href="<?= $base_path ?>pages/admin/demandes_acces.php" class="nav-link" style="position: relative;">
          🔐 Demandes
          <?php if ($nb_demandes_attente > 0): ?>
            <span style="background: #dc3545; color: white; border-radius: 50%; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75em; font-weight: bold; margin-left: 8px; position: absolute; top: -8px; right: -15px; box-shadow: 0 2px 8px rgba(220,53,69,0.4);">
              <?= $nb_demandes_attente ?>
            </span>
          <?php endif; ?>
        </a>
        <a href="<?= $base_path ?>pages/admin/utilisateurs.php" class="nav-link">Utilisateurs</a>
      <?php endif; ?>
    </div>
    <div style="display: flex; align-items: center; gap: 15px;">
      <?php if (isset($role)): ?>
        <span style="color: #666; font-size: 0.9em;">
          <?php 
          // S'assurer que displayRoleBadge est disponible
          if (!function_exists('displayRoleBadge')) {
              include_once(__DIR__ . '/role_helper.php');
          }
          // Si $role_badge est défini et est une chaîne, l'utiliser, sinon utiliser displayRoleBadge
          if (isset($role_badge) && is_string($role_badge)) {
              echo $role_badge;
          } else {
              echo displayRoleBadge($role);
          }
          ?>
        </span>
      <?php endif; ?>
      <a href="<?= $base_path ?>pages/auth/logout.php" class="logout">🚪 Déconnexion</a>
    </div>
  </nav>
</header>

