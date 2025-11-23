-- Table pour l'historique des modifications
CREATE TABLE IF NOT EXISTS historique (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    type_action VARCHAR(50) NOT NULL, -- 'ajout', 'modification', 'suppression'
    table_concernée VARCHAR(50) NOT NULL, -- 'produits', 'clients', 'fournisseurs', etc.
    id_element INT NOT NULL,
    description TEXT,
    anciennes_valeurs TEXT, -- JSON pour stocker les anciennes valeurs
    nouvelles_valeurs TEXT, -- JSON pour stocker les nouvelles valeurs
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- Index pour améliorer les performances
CREATE INDEX idx_historique_date ON historique(date_action);
CREATE INDEX idx_historique_table ON historique(table_concernée);




