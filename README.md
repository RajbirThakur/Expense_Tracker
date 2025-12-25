# 💰 PHP Expense Tracker

A full-stack web application designed to help individuals track their income and expenses, categorize spending, and visualize financial habits through data-driven insights.

## 🚀 Overview

This project was built to bridge the gap between core PHP development and data visualization. It provides a secure platform for users to manage their daily finances while practicing intermediate concepts like session management, PDO security, and relational database design.

## ✨ Key Features

* **User Authentication:** Secure Registration and Login system using `password_hash()` and PHP Sessions.
* **CRUD Functionality:** Full Create, Read, Update, and Delete capabilities for financial transactions.
* **Data Visualization:** Interactive Dashboard featuring spending breakdowns using **Chart.js**.
* **Categorization:** Group expenses (e.g., Food, Rent, Salary, Utilities) for better organization.
* **Responsive Design:** Built with **Tailwind CSS** (or Bootstrap) to ensure accessibility across mobile and desktop devices.
* **Security:** Implements Prepared Statements (PDO) to prevent SQL Injection.

## 🛠️ Tech Stack

* **Backend:** PHP 8.x (Vanilla)
* **Database:** MySQL
* **Frontend:** HTML5, CSS3, Tailwind CSS, JavaScript
* **Charts:** Chart.js
* **Environment:** XAMPP / Laragon

## 📋 Database Schema

The application uses a relational structure:
- `users`: Stores encrypted credentials and profile info.
- `categories`: Preset and custom spending categories.
- `transactions`: Stores amount, type (income/expense), date, and user relationship.
