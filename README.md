# LAB 03 - Clinic Appointment Booking

## Description

This project is a clinic appointment booking system
developed using PHP, MySQL, HTML and CSS.

It allows patients to submit appointment requests.

## Technologies

- HTML
- CSS
- PHP
- MySQL
- PDO

## Database Setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin.
3. Select the SQL tab.
4. Run the database.sql file.
5. The database lab03_clinic will be created.

## Project Setup

1. Copy the project folder into C:\xampp\htdocs.
2. Start Apache and MySQL.
3. Open your browser.
4. Visit:

http://localhost/LAB03_Clinic_Appointment/

## Validation Rules

- Patient name is required.
- Email is optional but must be valid if provided.
- Phone number is required.
- Department is required.
- Preferred doctor is required.
- Appointment date must be in the future.
- Time slot is required.
- Reason must contain 10 to 500 characters.
- Consent is required.
- A doctor cannot have two appointments at the same date and time.

## Database

Table name: appointments

New appointments receive Pending status automatically.

## Security

- PDO is used for database connections.
- Prepared statements are used for database insertion.
- User input is validated.
- Output is escaped using htmlspecialchars().