-- =====================================================
-- Table : demandes_acces
-- Pour gérer les demandes d'élévation de privilèges
-- =====================================================
CREATE TABLE IF NOT EXISTS demandes_acces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    type_demande ENUM('role', 'permission_specifique') NOT NULL,
    role_demande VARCHAR(50) NULL, -- Si type_demande = 'role'
    permission_demande VARCHAR(100) NULL, -- Si type_demande = 'permission_specifique'
    raison TEXT,
    statut ENUM('en_attente', 'approuvee', 'refusee') DEFAULT 'en_attente',
    id_admin_approbateur INT NULL,
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_traitement TIMESTAMP NULL,
    commentaire_admin TEXT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin_approbateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- Index pour améliorer les performances
CREATE INDEX idx_statut ON demandes_acces(statut);
CREATE INDEX idx_utilisateur ON demandes_acces(id_utilisateur);



