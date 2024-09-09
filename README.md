# Payroll Calculation System

This system is designed to manage payroll calculations efficiently, focusing on automating the computation of payment amounts based on worked hours, pay rate, and applicable deductions.

## Technologies Used

- Laravel 11
- MySQL 8.0
- PHPUnit for automated testing

## Prerequisites

Before setting up the project, ensure you have the following installed:
- PHP >= 8.0
- Composer
- MySQL

## Getting Started

Follow these instructions to set up the project on your local machine for development and testing purposes.

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/tosinezekiel/payroll-system.git
   cd payroll-system
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan test
   php artisan serve
   ``
