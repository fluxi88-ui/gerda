create database Konvoltic;
use konvolticdatenbank;

create table kunde(
kn_id int primary key not null auto_increment,
anrede varchar(15),
vorname varchar(30),
nachname varchar(30),
geburtstag date,
telefon varchar (25),
land varchar(50),
ort varchar(50),
plz int (10),
straße varchar(50));

create table account(
acc_id int primary key not null auto_increment,
kn_id int not null,
benutzername varchar(150) not null,
passwort varchar(255) not null,
e_mail varchar(100) not null,
firmen_UID varchar(40),
unternehmen varchar(250),
r_land varchar(50),
r_ort varchar(50),
r_plz int (10),
r_straße varchar(50),
foreign key (kn_id)references kunde(kn_id) on delete cascade);

create table bestellung(
bestell_id int primary key not null auto_increment,
acc_id int not null,
bestelldatum date not null,
menge int (10) not null,
gesamtpreis double not null,
foreign key (acc_id) references account(acc_id));

create table software(
software_id int primary key not null auto_increment,
bestell_id int not null,
bezeichnung varchar(200) not null,
lizenz varchar(200),
preis double not null,
abbonement varchar(200),
foreign key (bestell_id) references bestellung(bestell_id));

create table hardware(
isbn int primary key not null auto_increment,
bestell_id int not null,
bezeichnung varchar(150) not null,
preis double,
foreign key (bestell_id) references bestellung(bestell_id));

create table mitarbeiter(
mitarbeiter_id int primary key not null auto_increment,
bestell_id int not null,
vorname varchar(150) not null,
nachname varchar(150) not null,
anrede varchar(150),
plz int (10),
ort varchar(150),
land varchar(150),
abteilung varchar(150) not null,
foreign key (bestell_id) references bestellung(bestell_id));

create table lieferant(
lieferanten_id int primary key not null auto_increment,
mitarbeiter_id int not null,
fahrer varchar(50),
unternehmen varchar(50) not null,
kosten double,
foreign key (mitarbeiter_id)references mitarbeiter(mitarbeiter_id));

create table bestand(
bestand_id int primary key not null auto_increment,
mitarbeiter_id int not null,
lager varchar(150),
bestandsmenge double not null,
foreign key (mitarbeiter_id)references mitarbeiter(mitarbeiter_id));

