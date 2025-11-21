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
    ('Vert');


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
    (1, 1), -- Boîte-1 - Plastique
    (1, 2), -- Boîte-1 - Métal
    (1, 3), -- Boîte-1 - Outil
    (1, 4), -- Boîte-1 - Jaune
    (1, 5), -- Boîte-1 - Rouge
    (1, 6), -- Boîte-1 - Bleu
    (1, 7), -- Boîte-1 - Vert
    (2, 1), -- Tournevis - Plastique
    (2, 2), -- Tournevis - Métal
    (2, 3), -- Tournevis - Outil
    (2, 4), -- Tournevis - Jaune
    (2, 5), -- Tournevis - Rouge
    (2, 6), -- Tournevis - Bleu
    (2, 7), -- Tournevis - Vert
    (3, 1), -- Scie - Plastique
    (3, 2), -- Scie - Métal
    (3, 3), -- Scie - Outil
    (3, 4), -- Scie - Jaune
    (3, 5), -- Scie - Rouge
    (3, 6), -- Scie - Bleu
    (3, 7), -- Scie - Vert
    (4, 1), -- Boîte-2 - Plastique
    (4, 2), -- Boîte-2 - Métal
    (4, 3), -- Boîte-2 - Outil
    (4, 4), -- Boîte-2 - Jaune
    (4, 5), -- Boîte-2 - Rouge
    (4, 6), -- Boîte-2 - Bleu
    (4, 7), -- Boîte-2 - Vert
    (5, 1), -- Clous - Plastique
    (5, 2), -- Clous - Métal
    (5, 3), -- Clous - Outil
    (5, 4), -- Clous - Jaune
    (5, 5), -- Clous - Rouge
    (5, 6), -- Clous - Bleu
    (5, 7), -- Clous - Vert
    (6, 1), -- Pinceau - Plastique
    (6, 2), -- Pinceau - Métal
    (6, 3), -- Pinceau - Outil
    (6, 4), -- Pinceau - Jaune
    (6, 5), -- Pinceau - Rouge
    (6, 6), -- Pinceau - Bleu
    (6, 7), -- Pinceau - Vert
    (7, 1), -- Marteau - Plastique
    (7, 2), -- Marteau - Métal
    (7, 3), -- Marteau - Outil
    (7, 4), -- Marteau - Jaune
    (7, 5), -- Marteau - Rouge
    (7, 6), -- Marteau - Bleu
    (7, 7); -- Marteau - Vert


SELECT * FROM Objet JOIN Correspond ON Objet_id = Correspond_Objet_id JOIN MotCle ON Correspond_MotCle_id = MotCle_id;
-- Affiche tous les objets avec leurs mots-clés associés

SELECT * FROM Objet WHERE MotCle_id = 2;
-- Affiche tous les objets ayant les mots-clés "Plastique" (2)

SELECT * FROM Objet, MotCle, Correspond WHERE Objet_id = Correspond_Objet_id AND MotCle_id = Correspond_MotCle_id AND Correspond_MotCle_id = 2 OR Correspond_MotCle_id = 4;
-- Affiche tous les objets ayant les mots-clés "Plastique" (2) ou "Rouge" (4)

SELECT * FROM Correspond WHERE Correspond_MotCle_id = 4 AND Correspond_Objet_id IN (SELECT Correspond_Objet_id FROM Correspond WHERE Correspond_MotCle_id = 2);
-- Affiche tous les objets ayant les mots-clés "Plastique" (2) et "Rouge" (4)

-- Affiche tous les objets avec plusieurs mots-clés associés
SELECT * FROM Objet JOIN Correspond ON Objet_id = Correspond_Objet_id JOIN MotCle ON Correspond_MotCle_id = MotCle_id GROUP BY Objet_id HAVING COUNT(MotCle_id) > 1;

SELECT o.nom, COUNT(c.idMotCle) AS nbMotsCles FROM Objet o JOIN Correspond c ON o.id = c.idObjet GROUP BY o.idHAVING COUNT(c.idMotCle) > 1;
