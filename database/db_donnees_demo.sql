-- =====================================================
-- Script d'insertion de données de démonstration
-- Pour une épicerie (snack & nourriture)
-- =====================================================

USE epicerie_db;

-- =====================================================
-- Catégories supplémentaires
-- =====================================================
INSERT INTO categories (nom) VALUES
('Boissons'),
('Snacks'),
('Produits frais'),
('Sucreries'),
('Conserves'),
('Hygiène'),
('Petit-déjeuner')
ON DUPLICATE KEY UPDATE nom=nom;

-- =====================================================
-- Fournisseurs
-- =====================================================
INSERT INTO fournisseurs (nom, telephone, email, adresse) VALUES
('DistribSnack Maroc', '0522-123456', 'contact@distribsnack.ma', 'Casablanca, Bd Zerktouni'),
('BoissonPlus', '0522-234567', 'info@boissonplus.ma', 'Rabat, Hay Riad'),
('FreshFood Distribution', '0522-345678', 'ventes@freshfood.ma', 'Marrakech, Guéliz'),
('SweetCorp', '0522-456789', 'commercial@sweetcorp.ma', 'Fès, Centre-ville'),
('ConservPro', '0522-567890', 'contact@conservpro.ma', 'Tanger, Zone industrielle'),
('HygieneMaroc', '0522-678901', 'info@hygienemaroc.ma', 'Agadir, Anza'),
('CerealCo', '0522-789012', 'ventes@cerealco.ma', 'Meknès, Route de Fès')
ON DUPLICATE KEY UPDATE nom=nom;

-- =====================================================
-- Produits (épicerie)
-- =====================================================
INSERT INTO produits (nom, id_categorie, prix_achat, prix_vente, quantite_stock, fournisseur_id) VALUES
-- Boissons
('Coca-Cola 33cl', 1, 4.50, 6.00, 120, 2),
('Coca-Cola 1.5L', 1, 8.00, 11.00, 80, 2),
('Pepsi 33cl', 1, 4.50, 6.00, 100, 2),
('Sprite 33cl', 1, 4.00, 5.50, 90, 2),
('Fanta Orange 33cl', 1, 4.00, 5.50, 85, 2),
('Eau minérale Sidi Ali 1.5L', 1, 3.00, 4.50, 150, 2),
('Jus d\'orange Jafal 1L', 1, 6.00, 8.50, 60, 2),
('Thé Lipton 25 sachets', 1, 12.00, 16.00, 40, 2),
('Café Nescafé 200g', 1, 35.00, 45.00, 30, 2),

-- Snacks
('Chips Lay\'s Nature 150g', 2, 5.00, 7.50, 200, 1),
('Chips Lay\'s Barbecue 150g', 2, 5.00, 7.50, 180, 1),
('Chips Doritos Nacho 150g', 2, 6.00, 8.50, 150, 1),
('Cacahuètes grillées 200g', 2, 4.50, 6.50, 120, 1),
('Biscuits Oreo 154g', 2, 8.00, 11.00, 100, 1),
('Biscuits Prince 200g', 2, 6.00, 8.50, 110, 1),
('Popcorn salé 100g', 2, 3.50, 5.00, 80, 1),

-- Produits frais
('Yaourt Danone Nature 4x125g', 3, 8.00, 11.00, 60, 3),
('Yaourt Danone Fruits 4x125g', 3, 8.50, 12.00, 55, 3),
('Lait Centrale 1L', 3, 6.50, 9.00, 70, 3),
('Fromage Kiri 8 portions', 3, 12.00, 16.00, 40, 3),
('Beurre 250g', 3, 15.00, 20.00, 35, 3),
('Œufs (douzaine)', 3, 18.00, 24.00, 25, 3),

-- Sucreries
('Chocolat Milka 100g', 4, 6.00, 8.50, 150, 4),
('Chocolat Cadbury 100g', 4, 6.50, 9.00, 140, 4),
('Bonbons Haribo 200g', 4, 5.00, 7.50, 130, 4),
('Chewing-gum Mentos', 4, 3.00, 4.50, 200, 4),
('Barre chocolatée Snickers', 4, 4.00, 6.00, 180, 4),
('Barre chocolatée Twix', 4, 4.00, 6.00, 170, 4),
('Bonbons Tic Tac', 4, 4.50, 6.50, 160, 4),

-- Conserves
('Thon en conserve 160g', 5, 8.00, 11.00, 50, 5),
('Sardines à l\'huile 125g', 5, 5.00, 7.00, 60, 5),
('Haricots verts 400g', 5, 6.00, 8.50, 45, 5),
('Pois chiches 400g', 5, 5.50, 7.50, 40, 5),
('Tomates pelées 400g', 5, 4.50, 6.50, 55, 5),

-- Hygiène
('Savon de Marseille 200g', 6, 3.50, 5.00, 80, 6),
('Shampooing Head & Shoulders 400ml', 6, 25.00, 35.00, 30, 6),
('Dentifrice Colgate 100ml', 6, 8.00, 12.00, 50, 6),
('Papier toilette 4 rouleaux', 6, 12.00, 18.00, 40, 6),
('Serviettes hygiéniques', 6, 15.00, 22.00, 35, 6),

-- Petit-déjeuner
('Céréales Corn Flakes 500g', 7, 18.00, 25.00, 40, 7),
('Céréales Chocapic 375g', 7, 20.00, 28.00, 35, 7),
('Miel 500g', 7, 25.00, 35.00, 25, 7),
('Confiture Bonne Maman 370g', 7, 15.00, 22.00, 30, 7),
('Pain de mie 400g', 7, 4.50, 6.50, 20, 7)
ON DUPLICATE KEY UPDATE nom=nom;

-- =====================================================
-- Clients (prénoms variés - diversité culturelle)
-- =====================================================
INSERT INTO clients (nom, telephone, email, adresse) VALUES
('Marie Dupont', '0612-345678', 'marie.dupont@email.com', 'Casablanca, Hay Hassani'),
('Jean Martin', '0612-456789', 'jean.martin@email.com', 'Rabat, Agdal'),
('Sofia Russo', '0612-567890', 'sofia.russo@email.com', 'Marrakech, Guéliz'),
('Liam O\'Connor', '0612-678901', 'liam.oconnor@email.com', 'Fès, Centre-ville'),
('Amina Diallo', '0612-789012', 'amina.diallo@email.com', 'Tanger, Marshan'),
('Carlos Mendes', '0612-890123', 'carlos.mendes@email.com', 'Agadir, Hay Mohammadi'),
('Jin Park', '0612-901234', 'jin.park@email.com', 'Meknès, Médina'),
('Olga Ivanova', '0613-012345', 'olga.ivanova@email.com', 'Casablanca, Maarif'),
('David Cohen', '0613-123456', 'david.cohen@email.com', 'Rabat, Hay Riad'),
('Isabella Rodriguez', '0613-234567', 'isabella.rodriguez@email.com', 'Marrakech, Hivernage'),
('Noah Williams', '0613-345678', 'noah.williams@email.com', 'Fès, Jnan El Ouard'),
('Fatima Benali', '0613-456789', 'fatima.benali@email.com', 'Tanger, Beni Makada'),
('Hiroshi Tanaka', '0613-567890', 'hiroshi.tanaka@email.com', 'Agadir, Talborjt'),
('Elena Popescu', '0613-678901', 'elena.popescu@email.com', 'Meknès, Zerhoun'),
('Ahmed Hassan', '0613-789012', 'ahmed.hassan@email.com', 'Casablanca, Anfa'),
('Lucas Schneider', '0613-890123', 'lucas.schneider@email.com', 'Rabat, Hay Riad'),
('Emma Johansson', '0613-901234', 'emma.johansson@email.com', 'Marrakech, Sidi Ghanem'),
('Victor Silva', '0614-012345', 'victor.silva@email.com', 'Fès, Saiss'),
('Sara Haddad', '0614-123456', 'sara.haddad@email.com', 'Tanger, Charf'),
('Arjun Patel', '0614-234567', 'arjun.patel@email.com', 'Agadir, Inezgane')
ON DUPLICATE KEY UPDATE nom=nom;

-- =====================================================
-- Achats (Commandes fournisseurs) - 15 achats
-- =====================================================
-- Achat 1
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(1, DATE_SUB(NOW(), INTERVAL 30 DAY), 850.00);
SET @id_achat_1 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_1, 10, 50, 5.00),
(@id_achat_1, 11, 40, 5.00),
(@id_achat_1, 12, 30, 6.00);

-- Achat 2
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(2, DATE_SUB(NOW(), INTERVAL 28 DAY), 1200.00);
SET @id_achat_2 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_2, 1, 100, 4.50),
(@id_achat_2, 2, 50, 8.00),
(@id_achat_2, 3, 80, 4.50);

-- Achat 3
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(3, DATE_SUB(NOW(), INTERVAL 25 DAY), 650.00);
SET @id_achat_3 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_3, 18, 30, 8.00),
(@id_achat_3, 19, 25, 8.50),
(@id_achat_3, 20, 40, 6.50);

-- Achat 4
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(4, DATE_SUB(NOW(), INTERVAL 22 DAY), 950.00);
SET @id_achat_4 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_4, 24, 80, 6.00),
(@id_achat_4, 25, 70, 6.50),
(@id_achat_4, 26, 60, 4.00);

-- Achat 5
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(5, DATE_SUB(NOW(), INTERVAL 20 DAY), 550.00);
SET @id_achat_5 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_5, 30, 30, 8.00),
(@id_achat_5, 31, 35, 5.00),
(@id_achat_5, 32, 25, 6.00);

-- Achat 6
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(6, DATE_SUB(NOW(), INTERVAL 18 DAY), 800.00);
SET @id_achat_6 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_6, 35, 20, 3.50),
(@id_achat_6, 36, 15, 25.00),
(@id_achat_6, 37, 25, 8.00);

-- Achat 7
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(7, DATE_SUB(NOW(), INTERVAL 15 DAY), 1100.00);
SET @id_achat_7 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_7, 40, 20, 18.00),
(@id_achat_7, 41, 15, 20.00),
(@id_achat_7, 42, 12, 25.00);

-- Achat 8
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(2, DATE_SUB(NOW(), INTERVAL 12 DAY), 900.00);
SET @id_achat_8 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_8, 4, 60, 4.00),
(@id_achat_8, 5, 55, 4.00),
(@id_achat_8, 6, 80, 3.00);

-- Achat 9
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(1, DATE_SUB(NOW(), INTERVAL 10 DAY), 700.00);
SET @id_achat_9 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_9, 13, 40, 4.50),
(@id_achat_9, 14, 35, 8.00),
(@id_achat_9, 15, 30, 6.00);

-- Achat 10
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(3, DATE_SUB(NOW(), INTERVAL 8 DAY), 600.00);
SET @id_achat_10 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_10, 21, 20, 12.00),
(@id_achat_10, 22, 15, 15.00),
(@id_achat_10, 23, 10, 18.00);

-- Achat 11
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(4, DATE_SUB(NOW(), INTERVAL 6 DAY), 750.00);
SET @id_achat_11 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_11, 27, 50, 4.00),
(@id_achat_11, 28, 45, 4.00),
(@id_achat_11, 29, 40, 4.50);

-- Achat 12
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(5, DATE_SUB(NOW(), INTERVAL 5 DAY), 500.00);
SET @id_achat_12 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_12, 33, 20, 5.50),
(@id_achat_12, 34, 25, 4.50);

-- Achat 13
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(6, DATE_SUB(NOW(), INTERVAL 4 DAY), 650.00);
SET @id_achat_13 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_13, 38, 20, 12.00),
(@id_achat_13, 39, 15, 15.00);

-- Achat 14
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(7, DATE_SUB(NOW(), INTERVAL 3 DAY), 550.00);
SET @id_achat_14 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_14, 43, 12, 15.00),
(@id_achat_14, 44, 10, 4.50);

-- Achat 15
INSERT INTO achats (id_fournisseur, date_achat, montant_total) VALUES
(2, DATE_SUB(NOW(), INTERVAL 1 DAY), 1000.00);
SET @id_achat_15 = LAST_INSERT_ID();
INSERT INTO details_achat (id_achat, id_produit, quantite, prix_achat) VALUES
(@id_achat_15, 7, 30, 6.00),
(@id_achat_15, 8, 20, 12.00),
(@id_achat_15, 9, 15, 35.00);

-- =====================================================
-- Ventes - 25 ventes réparties sur les 30 derniers jours
-- =====================================================

-- Vente 1
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(1, 2, DATE_SUB(NOW(), INTERVAL 29 DAY), 45.50);
SET @id_vente_1 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_1, 1, 3, 6.00),
(@id_vente_1, 10, 2, 7.50),
(@id_vente_1, 24, 1, 8.50);

-- Vente 2
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(2, 2, DATE_SUB(NOW(), INTERVAL 28 DAY), 32.00);
SET @id_vente_2 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_2, 2, 2, 11.00),
(@id_vente_2, 18, 1, 11.00);

-- Vente 3
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(3, 2, DATE_SUB(NOW(), INTERVAL 27 DAY), 67.50);
SET @id_vente_3 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_3, 24, 2, 8.50),
(@id_vente_3, 25, 3, 9.00),
(@id_vente_3, 26, 2, 6.00),
(@id_vente_3, 27, 1, 8.50);

-- Vente 4
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(4, 2, DATE_SUB(NOW(), INTERVAL 26 DAY), 28.50);
SET @id_vente_4 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_4, 4, 2, 5.50),
(@id_vente_4, 5, 2, 5.50),
(@id_vente_4, 6, 1, 4.50);

-- Vente 5
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(5, 2, DATE_SUB(NOW(), INTERVAL 25 DAY), 55.00);
SET @id_vente_5 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_5, 18, 2, 11.00),
(@id_vente_5, 19, 1, 12.00),
(@id_vente_5, 20, 2, 9.00),
(@id_vente_5, 21, 1, 16.00);

-- Vente 6
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(6, 2, DATE_SUB(NOW(), INTERVAL 24 DAY), 42.00);
SET @id_vente_6 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_6, 30, 2, 11.00),
(@id_vente_6, 31, 2, 7.00),
(@id_vente_6, 32, 1, 8.50);

-- Vente 7
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(7, 2, DATE_SUB(NOW(), INTERVAL 23 DAY), 38.50);
SET @id_vente_7 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_7, 11, 3, 7.50),
(@id_vente_7, 12, 2, 8.50);

-- Vente 8
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(8, 2, DATE_SUB(NOW(), INTERVAL 22 DAY), 89.00);
SET @id_vente_8 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_8, 24, 3, 8.50),
(@id_vente_8, 25, 2, 9.00),
(@id_vente_8, 36, 1, 35.00),
(@id_vente_8, 37, 2, 12.00);

-- Vente 9
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(9, 2, DATE_SUB(NOW(), INTERVAL 21 DAY), 51.00);
SET @id_vente_9 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_9, 1, 4, 6.00),
(@id_vente_9, 3, 3, 6.00),
(@id_vente_9, 10, 2, 7.50);

-- Vente 10
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(10, 2, DATE_SUB(NOW(), INTERVAL 20 DAY), 64.50);
SET @id_vente_10 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_10, 40, 1, 25.00),
(@id_vente_10, 41, 1, 28.00),
(@id_vente_10, 42, 1, 35.00);

-- Vente 11
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(11, 2, DATE_SUB(NOW(), INTERVAL 18 DAY), 35.00);
SET @id_vente_11 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_11, 13, 2, 6.50),
(@id_vente_11, 14, 1, 11.00),
(@id_vente_11, 15, 1, 8.50);

-- Vente 12
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(12, 2, DATE_SUB(NOW(), INTERVAL 17 DAY), 47.50);
SET @id_vente_12 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_12, 2, 2, 11.00),
(@id_vente_12, 6, 3, 4.50),
(@id_vente_12, 7, 1, 8.50);

-- Vente 13
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(13, 2, DATE_SUB(NOW(), INTERVAL 16 DAY), 72.00);
SET @id_vente_13 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_13, 18, 3, 11.00),
(@id_vente_13, 19, 2, 12.00),
(@id_vente_13, 20, 1, 9.00),
(@id_vente_13, 21, 1, 16.00);

-- Vente 14
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(14, 2, DATE_SUB(NOW(), INTERVAL 15 DAY), 29.00);
SET @id_vente_14 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_14, 4, 3, 5.50),
(@id_vente_14, 5, 2, 5.50);

-- Vente 15
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(15, 2, DATE_SUB(NOW(), INTERVAL 14 DAY), 58.50);
SET @id_vente_15 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_15, 24, 2, 8.50),
(@id_vente_15, 25, 2, 9.00),
(@id_vente_15, 26, 3, 6.00),
(@id_vente_15, 27, 1, 8.50);

-- Vente 16
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(16, 2, DATE_SUB(NOW(), INTERVAL 12 DAY), 41.50);
SET @id_vente_16 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_16, 11, 2, 7.50),
(@id_vente_16, 12, 2, 8.50),
(@id_vente_16, 13, 1, 6.50);

-- Vente 17
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(17, 2, DATE_SUB(NOW(), INTERVAL 11 DAY), 95.00);
SET @id_vente_17 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_17, 1, 5, 6.00),
(@id_vente_17, 2, 3, 11.00),
(@id_vente_17, 36, 1, 35.00),
(@id_vente_17, 37, 1, 12.00);

-- Vente 18
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(18, 2, DATE_SUB(NOW(), INTERVAL 10 DAY), 33.50);
SET @id_vente_18 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_18, 30, 1, 11.00),
(@id_vente_18, 31, 2, 7.00),
(@id_vente_18, 32, 1, 8.50);

-- Vente 19
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(19, 2, DATE_SUB(NOW(), INTERVAL 9 DAY), 52.00);
SET @id_vente_19 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_19, 18, 2, 11.00),
(@id_vente_19, 19, 1, 12.00),
(@id_vente_19, 20, 2, 9.00);

-- Vente 20
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(20, 2, DATE_SUB(NOW(), INTERVAL 8 DAY), 44.00);
SET @id_vente_20 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_20, 24, 2, 8.50),
(@id_vente_20, 25, 1, 9.00),
(@id_vente_20, 26, 2, 6.00),
(@id_vente_20, 27, 1, 8.50);

-- Vente 21
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(1, 2, DATE_SUB(NOW(), INTERVAL 7 DAY), 38.00);
SET @id_vente_21 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_21, 4, 3, 5.50),
(@id_vente_21, 5, 2, 5.50),
(@id_vente_21, 6, 1, 4.50);

-- Vente 22
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(2, 2, DATE_SUB(NOW(), INTERVAL 6 DAY), 61.50);
SET @id_vente_22 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_22, 40, 1, 25.00),
(@id_vente_22, 41, 1, 28.00),
(@id_vente_22, 43, 1, 22.00);

-- Vente 23
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(3, 2, DATE_SUB(NOW(), INTERVAL 5 DAY), 49.00);
SET @id_vente_23 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_23, 1, 4, 6.00),
(@id_vente_23, 3, 3, 6.00),
(@id_vente_23, 10, 2, 7.50);

-- Vente 24
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(4, 2, DATE_SUB(NOW(), INTERVAL 3 DAY), 56.00);
SET @id_vente_24 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_24, 18, 2, 11.00),
(@id_vente_24, 19, 2, 12.00),
(@id_vente_24, 20, 1, 9.00);

-- Vente 25
INSERT INTO ventes (id_client, id_utilisateur, date_vente, total) VALUES
(5, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 42.50);
SET @id_vente_25 = LAST_INSERT_ID();
INSERT INTO details_vente (id_vente, id_produit, quantite, prix_unitaire) VALUES
(@id_vente_25, 11, 3, 7.50),
(@id_vente_25, 12, 2, 8.50);

-- Mise à jour des stocks après les ventes
-- (Les stocks ont déjà été mis à jour lors de l'insertion des ventes via l'application,
-- mais on peut ajuster manuellement si nécessaire)

SELECT '✅ Données de démonstration insérées avec succès !' AS message;

