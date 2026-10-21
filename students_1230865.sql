USE web1230865_db;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id CHAR(10) PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  mobile VARCHAR(30) NOT NULL,
  date_of_birth DATE NOT NULL,
  flat_no VARCHAR(30),
  street VARCHAR(100) NOT NULL,
  city VARCHAR(60) NOT NULL,
  country VARCHAR(60) NOT NULL,
  postal_code CHAR(6) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL
);

CREATE TABLE products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(120) NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  rating DECIMAL(2,1) NOT NULL,
  photo1 VARCHAR(120) NOT NULL,
  photo2 VARCHAR(120) NOT NULL,
  photo3 VARCHAR(120) NOT NULL,
  default_photo VARCHAR(120) NOT NULL
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id CHAR(10) NOT NULL UNIQUE,
  user_id CHAR(10) NOT NULL,
  order_date DATETIME NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  order_db_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(120) NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_db_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);

INSERT INTO users VALUES
('1000000001','Lina','Haddad','customer1@example.com','+970599111111','1996-04-12','2A','Ramallah','Ramallah','Palestine','123456','$2y$12$xxx','Customer'),
('1000000002','Omar','Nasser','customer2@example.com','+970599222222','1992-08-22','5B','Nablus','Nablus','Palestine','654321','$2y$12$xxx','Customer'),
('9000000001','Maya','Khalil','employee@example.com','+970599333333','1988-02-05','1','Hebron','Hebron','Palestine','112233','$2y$12$xxx','Employee');

INSERT INTO products
(product_name, category, description, price, quantity, rating, photo1, photo2, photo3, default_photo)
VALUES

('Hebron Ceramic Coffee Cups',
'Handcrafted Ceramics',
'Traditional ceramic coffee cups from Hebron.',
38.00,25,4.8,
'ceramic-cup.png','logo.png','olive-soap.png','ceramic-cup.png'),

('Nablus Olive Oil Soap Gift Box',
'Natural Olive-Oil Products',
'Natural olive oil soap from Nablus.',
26.00,40,4.7,
'olive-soap.png','logo.png','ceramic-cup.png','olive-soap.png'),

('Tatreez Embroidery Wall Hanging',
'Traditional Embroidery',
'Palestinian embroidery wall hanging.',
45.00,18,4.9,
'tatreez-wall-hanging.png','logo.png','olive-soap.png','tatreez-wall-hanging.png'),

('Olive Wood Keychains Set',
'Olive Wood Gifts',
'Handmade olive wood keychains.',
18.50,60,4.4,
'olive-wood-keychain.png','logo.png','ceramic-cup.png','olive-wood-keychain.png'),

('Palestinian Keffiyeh Scarf',
'Textile Gifts',
'Traditional Palestinian keffiyeh scarf.',
32.00,35,4.6,
'keffiyeh-scarf.png','logo.png','olive-soap.png','keffiyeh-scarf.png'),

('Dead Sea Salt Pouch',
'Natural Gifts',
'Natural Dead Sea salt pouch.',
14.00,50,4.2,
'dead-sea-salt.png','logo.png','olive-soap.png','dead-sea-salt.png'),

('Ceramic Evil Eye Plate',
'Handcrafted Ceramics',
'Decorative ceramic evil eye plate.',
29.00,20,4.5,
'evil-eye-plate.png','logo.png','ceramic-cup.png','evil-eye-plate.png'),

('Date Maamoul Gift Tin',
'Food Gifts',
'Traditional date maamoul cookies.',
22.00,30,4.3,
'date-maamoul.png','logo.png','ceramic-cup.png','date-maamoul.png'),

('Bethlehem Star Ornament',
'Home Decor',
'Bethlehem star decorative ornament.',
16.00,45,4.1,
'bethlehem-star.png','logo.png','olive-soap.png','bethlehem-star.png'),

('Embroidered Tote Bag',
'Textile Gifts',
'Palestinian embroidered tote bag.',
35.00,22,4.6,
'embroidered-tote.png','logo.png','ceramic-cup.png','embroidered-tote.png');