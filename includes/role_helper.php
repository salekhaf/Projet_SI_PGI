<?php
/**
 * Helper pour afficher les rôles avec des badges colorés
 */

function getRoleBadge($role) {
    $badges = [
        'admin' => [
            'class' => 'badge-danger',
            'icon' => '👑',
            'text' => 'Admin',
            'color' => '#dc3545'
        ],
        'responsable_approvisionnement' => [
            'class' => 'badge-warning',
            'icon' => '📦',
            'text' => 'Responsable',
            'color' => '#ffc107'
        ],
        'vendeur' => [
            'class' => 'badge-info',
            'icon' => '💰',
            'text' => 'Vendeur',
            'color' => '#17a2b8'
        ],
        'tresorier' => [
            'class' => 'badge-success',
            'icon' => '💼',
            'text' => 'Trésorier',
            'color' => '#28a745'
        ]
    ];
    
    return $badges[$role] ?? [
        'class' => 'badge-secondary',
        'icon' => '👤',
        'text' => htmlspecialchars($role ?: '—'),
        'color' => '#6c757d'
    ];
}

function displayRoleBadge($role) {
    $badge = getRoleBadge($role);
    return "<span class='badge {$badge['class']}' style='padding: 6px 12px; border-radius: 20px; font-weight: 600; display: inline-block;'>
        {$badge['icon']} {$badge['text']}
    </span>";
}

function getRoleDescription($role) {
    $descriptions = [
        'admin' => 'Accès complet à toutes les fonctionnalités',
        'responsable_approvisionnement' => 'Gestion du stock, fournisseurs et commandes',
        'vendeur' => 'Gestion des ventes et clients',
        'tresorier' => 'Accès à la trésorerie et finances'
    ];
    
    return $descriptions[$role] ?? 'Rôle non défini';
}



