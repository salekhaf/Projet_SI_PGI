-- =====================================================
-- Table : permissions_utilisateurs
-- Pour stocker les permissions spécifiques accordées aux utilisateurs
-- =====================================================
CREATE TABLE IF NOT EXISTS permissions_utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    date_attribution TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin_attribueur INT NULL,
    id_demande_acces INT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin_attribueur) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (id_demande_acces) REFERENCES demandes_acces(id) ON DELETE SET NULL,
    UNIQUE KEY unique_permission_user (id_utilisateur, permission)
);

CREATE INDEX idx_permissions_user ON permissions_utilisateurs(id_utilisateur);
CREATE INDEX idx_permissions_permission ON permissions_utilisateurs(permission);

