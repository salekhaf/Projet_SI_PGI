-- =====================================================
-- Table : depenses_diverses
-- Pour enregistrer les dépenses et entrées diverses
-- =====================================================
CREATE TABLE IF NOT EXISTS depenses_diverses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_operation ENUM('depense', 'entree') NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    notes TEXT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE INDEX idx_depenses_date ON depenses_diverses(date_operation);
CREATE INDEX idx_depenses_type ON depenses_diverses(type_operation);

