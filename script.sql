DROP DATABASE IF EXISTS Ouketi;

CREATE DATABASE Ouketi;

DEFAULT CHARACTER SET utf8mb4;

DEFAULT COLLATE utf8mb4_general_ci;

USE Ouketi;

CREATE TABLE MotsCles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(20) NOT NULL
);

-- Insérer des mots-clés dans la table MotsCles
INSERT INTO
    MotsCles (libelle)
VALUES
    ('Plastique'),
    ('Métal'),
    ('Outil'),
    ('Jaune'),
    ('Rouge'),
    ('Bleu'),
    ('Vert'),
    ('Bois');

-- Créer la table Objet
CREATE TABLE Objet (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nom VARCHAR(20) NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    estConteneur BOOLEAN NOT NULL DEFAULT FALSE,
    estContenuDans INT,
    FOREIGN KEY (estContenuDans) REFERENCES Objet(id)
);

-- Insérer des objets dans la table Objet
INSERT INTO
    Objet (nom, estConteneur, estContenuDans)
VALUES
    ('Boîte-1', TRUE, NULL),
    ('Tournevis', FALSE, 1),
    ('Scie', FALSE, 1),
    ('Boîte-2', TRUE, NULL),
    ('Clous', FALSE, 4),
    ('Pinceau', FALSE, 4),
    ('Marteau', FALSE, 4);

-- Créer la table Correspond
CREATE TABLE Correspond (
    idObjet INT NOT NULL,
    idMotCle INT NOT NULL,
    PRIMARY KEY (idObjet, idMotCle),
    FOREIGN KEY (idObjet) REFERENCES Objet(id),
    FOREIGN KEY (idMotCle) REFERENCES MotsCles(id)
);

-- Insérer des correspondances dans la table Correspond
INSERT INTO
    Correspond (idObjet, idMotCle)
VALUES
    (1, 2), -- Boîte-1 - Métal
    (1, 5), -- Boîte-1 - Rouge
    (2, 1), -- Tournevis - Plastique
    (2, 3), -- Tournevis - Outil
    (2, 4), -- Tournevis - Jaune
    (3, 2), -- Scie - Métal
    (3, 3), -- Scie - Outil
    (3, 6), -- Scie - Bleu
    (4, 6), -- Boîte-2 - Bleu
    (4, 7), -- Boîte-2 - Vert
    (4, 8), -- Boîte-2 - Bois
    (5, 2), -- Clous - Métal
    (5, 5), -- Clous - Rouge
    (5, 7), -- Clous - Vert
    (6, 1), -- Pinceau - Plastique
    (6, 3), -- Pinceau - Outil
    (6, 4), -- Pinceau - Jaune
    (6, 5), -- Pinceau - Rouge
    (6, 6), -- Pinceau - Bleu
    (6, 7), -- Pinceau - Vert
    (6, 8), -- Pinceau - Bois
    (7, 1), -- Marteau - Plastique
    (7, 2), -- Marteau - Métal
    (7, 3), -- Marteau - Outil
    (7, 4), -- Marteau - Jaune
    (7, 7), -- Marteau - Vert
    (7, 8); -- Marteau - Bois


-- Affiche tous les objets avec leurs mots-clés associés
SELECT o.*, m.libelle AS mot_cle
FROM Objet o
JOIN Correspond c ON o.id = c.idObjet
JOIN MotsCles m ON c.idMotCle = m.id;

-- Affiche tous les objets ayant le mot-clé "Métal" (id=2)
SELECT DISTINCT o.*
FROM Objet o
JOIN Correspond c ON o.id = c.idObjet
WHERE c.idMotCle = 2;

-- Affiche tous les objets ayant les mots-clés "Métal" (2) OU "Rouge" (5)
SELECT DISTINCT o.*
FROM Objet o
JOIN Correspond c ON o.id = c.idObjet
WHERE c.idMotCle IN (2, 5);

SELECT * FROM Correspond WHERE Correspond_MotCle_id = 4 AND Correspond_Objet_id IN (SELECT Correspond_Objet_id FROM Correspond WHERE Correspond_MotCle_id = 2);
-- Affiche tous les objets ayant les mots-clés "Plastique" (2) et "Rouge" (4)

-- Affiche tous les objets avec plusieurs mots-clés associés
SELECT * FROM Objet JOIN Correspond ON Objet_id = Correspond_Objet_id JOIN MotCle ON Correspond_MotCle_id = MotCle_id GROUP BY Objet_id HAVING COUNT(MotCle_id) > 1;

SELECT o.nom, COUNT(c.idMotCle) AS nbMotsCles FROM Objet o JOIN Correspond c ON o.id = c.idObjet GROUP BY o.idHAVING COUNT(c.idMotCle) > 1;
