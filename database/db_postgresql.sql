-- =====================================================
-- Base de données PostgreSQL pour Smart Stock
-- Conversion depuis MySQL pour Render
-- =====================================================

-- Note: PostgreSQL utilise des types différents de MySQL
-- Ce script est une conversion adaptée

-- =====================================================
-- Table : utilisateurs
-- =====================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'vendeur' CHECK (role IN ('admin','vendeur','responsable_approvisionnement', 'tresorier')),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table : categories
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL
);

-- =====================================================
-- Table : fournisseurs
-- =====================================================
CREATE TABLE IF NOT EXISTS fournisseurs (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    email VARCHAR(100),
    adresse TEXT
);

-- =====================================================
-- Table : produits
-- =====================================================
CREATE TABLE IF NOT EXISTS produits (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    id_categorie INTEGER,
    prix_achat DECIMAL(10,2) DEFAULT 0.00,
    prix_vente DECIMAL(10,2) DEFAULT 0.00,
    quantite_stock INTEGER DEFAULT 0,
    fournisseur_id INTEGER,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : clients
-- =====================================================
CREATE TABLE IF NOT EXISTS clients (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    email VARCHAR(100),
    adresse TEXT
);

-- =====================================================
-- Table : ventes
-- =====================================================
CREATE TABLE IF NOT EXISTS ventes (
    id SERIAL PRIMARY KEY,
    id_client INTEGER,
    id_utilisateur INTEGER,
    date_vente TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_client) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : details_vente
-- =====================================================
CREATE TABLE IF NOT EXISTS details_vente (
    id SERIAL PRIMARY KEY,
    id_vente INTEGER NOT NULL,
    id_produit INTEGER NOT NULL,
    quantite INTEGER DEFAULT 1,
    prix_unitaire DECIMAL(10,2) DEFAULT 0.00,
    sous_total DECIMAL(10,2) GENERATED ALWAYS AS (quantite * prix_unitaire) STORED,
    FOREIGN KEY (id_vente) REFERENCES ventes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produits(id) ON DELETE CASCADE
);

-- =====================================================
-- Table : achats
-- =====================================================
CREATE TABLE IF NOT EXISTS achats (
    id SERIAL PRIMARY KEY,
    id_fournisseur INTEGER,
    date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_fournisseur) REFERENCES fournisseurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : details_achat
-- =====================================================
CREATE TABLE IF NOT EXISTS details_achat (
    id SERIAL PRIMARY KEY,
    id_achat INTEGER NOT NULL,
    id_produit INTEGER NOT NULL,
    quantite INTEGER DEFAULT 1,
    prix_achat DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_achat) REFERENCES achats(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produits(id) ON DELETE CASCADE
);

-- =====================================================
-- Table : historique
-- =====================================================
CREATE TABLE IF NOT EXISTS historique (
    id SERIAL PRIMARY KEY,
    id_utilisateur INTEGER NOT NULL,
    type_action VARCHAR(50) NOT NULL,
    table_concernée VARCHAR(50) NOT NULL,
    id_element INTEGER NOT NULL,
    description TEXT,
    anciennes_valeurs TEXT,
    nouvelles_valeurs TEXT,
    date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_historique_date ON historique(date_action);
CREATE INDEX IF NOT EXISTS idx_historique_table ON historique(table_concernée);

-- =====================================================
-- Table : demandes_acces
-- =====================================================
CREATE TABLE IF NOT EXISTS demandes_acces (
    id SERIAL PRIMARY KEY,
    id_utilisateur INTEGER NOT NULL,
    type_demande VARCHAR(50) NOT NULL CHECK (type_demande IN ('role', 'permission_specifique')),
    role_demande VARCHAR(50),
    permission_demande VARCHAR(100),
    raison TEXT,
    statut VARCHAR(50) DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'approuvee', 'refusee')),
    id_admin_approbateur INTEGER,
    date_demande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_traitement TIMESTAMP,
    commentaire_admin TEXT,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin_approbateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : permissions_utilisateurs
-- =====================================================
CREATE TABLE IF NOT EXISTS permissions_utilisateurs (
    id SERIAL PRIMARY KEY,
    id_utilisateur INTEGER NOT NULL,
    permission VARCHAR(100) NOT NULL,
    date_attribution TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin_attribueur INTEGER,
    id_demande_acces INTEGER,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin_attribueur) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (id_demande_acces) REFERENCES demandes_acces(id) ON DELETE SET NULL,
    UNIQUE (id_utilisateur, permission)
);

CREATE INDEX IF NOT EXISTS idx_permissions_user ON permissions_utilisateurs(id_utilisateur);
CREATE INDEX IF NOT EXISTS idx_permissions_permission ON permissions_utilisateurs(permission);

-- =====================================================
-- Table : depenses_diverses
-- =====================================================
CREATE TABLE IF NOT EXISTS depenses_diverses (
    id SERIAL PRIMARY KEY,
    type_operation VARCHAR(50) NOT NULL CHECK (type_operation IN ('depense', 'entree')),
    libelle VARCHAR(255) NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INTEGER NOT NULL,
    notes TEXT,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_depenses_date ON depenses_diverses(date_operation);
CREATE INDEX IF NOT EXISTS idx_depenses_type ON depenses_diverses(type_operation);

