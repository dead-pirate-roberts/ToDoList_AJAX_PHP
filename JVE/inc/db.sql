CREATE TABLE user_table(
    id INT AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    passwort VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
    );

CREATE TABLE todo_table (
    id INT AUTO_INCREMENT,
    UserId INT NOT NULL,
    Datum DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    todo VARCHAR(100) NOT NULL, 
    PRIMARY KEY (id),
    FOREIGN KEY (UserId) REFERENCES user_table(id)
    );


INSERT INTO user_table (id, name, passwort)
VALUES (1, 'Thea', '0000');


INSERT INTO user_table (id, name, passwort)
VALUES (2, 'Lara', '1111');


INSERT INTO user_table (id, name, passwort)
VALUES (3, 'Luisa', '2222');