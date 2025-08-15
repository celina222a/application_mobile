CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('EMPLOYE','CHEF_PARC','ADMIN') NOT NULL DEFAULT 'EMPLOYE'
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    depart VARCHAR(255) NOT NULL,
    arrivee VARCHAR(255) NOT NULL,
    date_depart DATETIME NOT NULL,
    date_retour DATETIME,
    status ENUM('EN_ATTENTE','APPROUVEE','REFUSEE','ANNULEE') NOT NULL DEFAULT 'EN_ATTENTE',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    motif_refus VARCHAR(255) NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (approved_by) REFERENCES utilisateurs(id)
);

