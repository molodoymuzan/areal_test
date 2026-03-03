INSERT INTO roles (name) VALUES ('director'), ('hr'), ('employee');

INSERT INTO statuses (name) VALUES ('active'), ('dismissed');

INSERT INTO departments (name) VALUES 
('Разработка'),
('Маркетинг'),
('HR'),
('Финансы'),
('Продажи');

INSERT INTO positions (name, department_id) VALUES 
('Senior Developer', 1),
('QA Engineer', 1),
('Project Manager', 2),
('HR Generalist', 3),
('Finance Manager', 4),
('Sales Manager', 5);

INSERT INTO passports (series, number) VALUES 
('4510', '123456'),
('4509', '987654'),
('4522', '334455'),
('4501', '112233'),
('4533', '778899'),
('4600', '998877'),
('4611', '223344'),
('4622', '556677');

INSERT INTO addresses (city, street, house, apartment, postal_code) VALUES 
('Москва', 'ул. Тверская', '12', '34', '123456'),
('Москва', 'пр. Мира', '5', '78', '123456'),
('Москва', 'ул. Академика Королева', '15', '45', '123456'),
('Москва', 'ул. Лесная', '8', '12', '123456'),
('Москва', 'пр. Вернадского', '45', '67', '123456'),
('Москва', 'ул. Новый Арбат', '15', '45', '123456'),
('Москва', 'ул. Тверская', '10', '23', '123456'),
('Москва', 'пр. Мира', '25', '12', '123456');

INSERT INTO contacts (type, value) VALUES 
('phone', '+7 (903) 777-55-22'),
('email', 'petrova@company.ru'),
('phone', '+7 (916) 111-22-33'),
('email', 'sokolov@company.ru'),
('phone', '+7 (925) 444-33-22'),
('email', 'volkova@company.ru'),
('phone', '+7 (499) 888-99-00'),
('email', 'morozov@company.ru'),
('phone', '+7 (926) 555-66-77'),
('email', 'ivanov@company.ru'),
('phone', '+7 (999) 444-55-66'),
('email', 'm.sokolova@company.ru'),
('phone', '+7 (999) 777-88-99'),
('email', 'p.kozlov@company.ru'),
('phone', '+7 (999) 888-99-00'),
('email', 'e.novikova@company.ru');

INSERT INTO users (last_name, first_name, middle_name, birth_date, department_id, position_id, salary, hire_date, role_id, status_id) 
VALUES ('Ветров', 'Александр', 'Игоревич', '1980-01-15', 1, 1, 350000, '2020-01-10', 1, 1);

INSERT INTO users (last_name, first_name, middle_name, birth_date, passport_id, address_id, department_id, position_id, salary, hire_date, role_id, status_id) VALUES 
('Соколова', 'Мария', 'Ивановна', '1988-03-21', 6, 6, 3, 4, 180000, '2021-03-15', 2, 1),
('Козлов', 'Петр', 'Сергеевич', '1985-11-30', 7, 7, 3, 4, 220000, '2020-06-01', 2, 1),
('Новикова', 'Елена', 'Дмитриевна', '1992-07-18', 8, 8, 3, 4, 175000, '2022-02-10', 2, 2);

INSERT INTO users (last_name, first_name, middle_name, birth_date, passport_id, address_id, department_id, position_id, salary, hire_date, role_id, status_id) VALUES 
('Петрова', 'Анна', 'Сергеевна', '1990-03-12', 1, 1, 1, 1, 285000, '2021-06-01', 3, 1),
('Соколов', 'Дмитрий', 'Алексеевич', '1985-07-19', 2, 2, 2, 3, 195000, '2019-11-20', 3, 1),
('Волкова', 'Елена', 'Викторовна', '1993-12-01', 3, 3, 3, 4, 175000, '2022-01-10', 3, 1),
('Морозов', 'Павел', 'Андреевич', '1980-05-22', 4, 4, 4, 5, 320000, '2018-09-05', 3, 2),
('Иванов', 'Иван', 'Петрович', '1988-11-15', 5, 5, 5, 6, 210000, '2020-03-15', 3, 1);

INSERT INTO user_contacts (user_id, contact_id) VALUES 
(2, 1), (2, 2),
(3, 3), (3, 4),
(4, 5), (4, 6),
(5, 7), (5, 8),
(6, 9), (6, 10),
(7, 11), (7, 12),
(8, 13), (8, 14),
(9, 15), (9, 16);

INSERT INTO auth (user_id, login_email, password_hash) VALUES 
(1, 'a.vetrov@company.ru', 'director123'),   
(2, 'm.sokolova@company.ru', 'hr123'),       
(3, 'p.kozlov@company.ru', 'hr456'),         
(4, 'e.novikova@company.ru', 'hr789');       