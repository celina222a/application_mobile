
CREATE TABLE utilisateurs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    role ENUM('EMPLOYE','CHEF_PARC','ADMIN') NOT NULL DEFAULT 'EMPLOYE'
);


CREATE TABLE reservations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NULL,
    chauffeur ENUM('avec','sans') NOT NULL,
    trajet ENUM('aller_simple','aller_retour') NOT NULL,
    nb_personnes INT(11) NOT NULL,
    depart VARCHAR(100) NOT NULL,
    arrivee VARCHAR(100) NOT NULL,
    date_depart DATE NOT NULL,
    heure_depart TIME NOT NULL,
    date_retour DATE NULL,
    heure_retour TIME NULL,
    etat ENUM('new','accepted','cancelled') NOT NULL DEFAULT 'new',
    motif_annulation TEXT NULL,
    CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
);


