-- =====================================================
-- Base de données : epicerie_db
-- Projet : PGI Web pour une épicerie (snack & nourriture)
-- Auteur : Équipe de 2 étudiants
-- Date : 2025
-- =====================================================

CREATE DATABASE IF NOT EXISTS epicerie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE epicerie_db;

-- =====================================================
-- Table : utilisateurs
-- =====================================================
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','vendeur','responsable_approvisionnement', 'tresorier') DEFAULT 'vendeur',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Table : categories
-- =====================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL
);

-- =====================================================
-- Table : fournisseurs
-- =====================================================
CREATE TABLE fournisseurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    email VARCHAR(100),
    adresse TEXT
);

-- =====================================================
-- Table : produits
-- =====================================================
CREATE TABLE produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    id_categorie INT,
    prix_achat DECIMAL(10,2) DEFAULT 0.00,
    prix_vente DECIMAL(10,2) DEFAULT 0.00,
    quantite_stock INT DEFAULT 0,
    fournisseur_id INT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : clients
-- =====================================================
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    email VARCHAR(100),
    adresse TEXT
);

-- =====================================================
-- Table : ventes
-- =====================================================
CREATE TABLE ventes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT,
    id_utilisateur INT,
    date_vente TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_client) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : details_vente
-- =====================================================
CREATE TABLE details_vente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_vente INT NOT NULL,
    id_produit INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_unitaire DECIMAL(10,2) DEFAULT 0.00,
    sous_total DECIMAL(10,2) GENERATED ALWAYS AS (quantite * prix_unitaire) STORED,
    FOREIGN KEY (id_vente) REFERENCES ventes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produits(id) ON DELETE CASCADE
);

-- =====================================================
-- Table : achats
-- =====================================================
CREATE TABLE achats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_fournisseur INT,
    date_achat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_fournisseur) REFERENCES fournisseurs(id) ON DELETE SET NULL
);

-- =====================================================
-- Table : details_achat
-- =====================================================
CREATE TABLE details_achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_achat INT NOT NULL,
    id_produit INT NOT NULL,
    quantite INT DEFAULT 1,
    prix_achat DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (id_achat) REFERENCES achats(id) ON DELETE CASCADE,
    FOREIGN KEY (id_produit) REFERENCES produits(id) ON DELETE CASCADE
);

-- =====================================================
-- Données d’exemple
-- =====================================================

-- Catégories
INSERT INTO categories (nom) VALUES
('Boissons'),
('Snacks'),
('Produits frais');

-- Fournisseurs
INSERT INTO fournisseurs (nom, telephone, email, adresse) VALUES
('DistribSnack', '060000001', 'contact@distribsnack.com', 'Casablanca'),
('BoissonPlus', '060000002', 'contact@boissonplus.com', 'Rabat');

-- Produits
INSERT INTO produits (nom, id_categorie, prix_achat, prix_vente, quantite_stock, fournisseur_id)
VALUES
('Coca-Cola 33cl', 1, 4.00, 6.00, 50, 2),
('Chips Lay’s Nature', 2, 3.00, 5.00, 100, 1),
('Yaourt Danone', 3, 2.50, 4.00, 80, 1);

-- Utilisateurs
INSERT INTO utilisateurs (nom, email, mot_de_passe, role)
VALUES
('Admin Principal', 'admin@epicerie.com', MD5('admin123'), 'admin'),
('Vendeur 1', 'vendeur1@epicerie.com', MD5('vendeur123'), 'vendeur');

-- Clients
INSERT INTO clients (nom, telephone, email, adresse)
VALUES
('Client Test', '0611111111', 'client@test.com', 'Casablanca');

-- Exemple de vente
INSERT INTO ventes (id_client, id_utilisateur, total)
VALUES (1, 2, 15.00);

INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire)
VALUES
(1, 1, 1, 6.00),
(1, 2, 1, 5.00),
(1, 3, 1, 4.00);

-- Exemple d’achat
INSERT INTO achats (id_fournisseur, montant_total)
VALUES (1, 100.00);

INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat)
VALUES
(1, 2, 50, 3.00);
