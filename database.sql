CREATE DATABASE IF NOT EXISTS lab03_clinic
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE lab03_clinic;

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(30) NOT NULL,
    department VARCHAR(100) NOT NULL,
    preferred_doctor VARCHAR(100) NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot VARCHAR(30) NOT NULL,
    reason VARCHAR(500) NOT NULL,
    consent TINYINT(1) NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_doctor_booking
    (preferred_doctor, appointment_date, time_slot)
);