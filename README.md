# Farmer USSD System

A USSD and SMS application built with PHP and Africa's Talking API that allows users to interact with the system through USSD menus and SMS commands.

## Features

- USSD Menu System
  - User Registration
  - Profile Viewing
  - SMS Sending
- SMS Integration
  - Help Command
  - Info Command
  - Registration Command
- Database Logging
  - User Information
  - SMS Logs
  - USSD Sessions

## Prerequisites

- PHP 7.0 or higher
- MySQL 5.6 or higher
- XAMPP (or similar local development environment)
- Africa's Talking Account
- cURL PHP Extension

## Installation

1. Clone this repository to your XAMPP htdocs directory:
   ```
   cd /path/to/xampp/htdocs
   git clone GROUP_18_IZUKONDI_MUHIRWA_USSD_MINI_PROJECT
   ```

2. Create the database and tables:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file

3. Configure the application:
   - Open `config.php`
   - Update the Africa's Talking credentials:
     - `AT_API_KEY`:atsk_871d84ee73680edd4daf84c0034d5ce8aa061d65b75127996006135811097ff507aeb6c9
     - `AT_USERNAME`:sandbox
     - `AT_SENDER_ID`:PMoney
   - Update database credentials if needed

## Usage

### USSD Menu
1. Dial the USSD code (e.g., *384*1234#)
2. Follow the menu prompts:
   - 1: Register
   - 2: View Profile
   - 3: Send Message
   - 4: Exit

### SMS Commands
Send the following commands via SMS:
- `help`: Show available commands
- `info`: View your profile
- `register`: Get registration instructions

## Security Considerations

1. Always use HTTPS in production
2. Keep your API keys secure
3. Validate and sanitize all user inputs
4. Implement rate limiting
5. Use prepared statements for database queries

