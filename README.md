# GROUP_18_IZUKONDI_MUHIRWA_USSD_MINI_PROJECT
The Farmer USSD System *384*1742# is a quick and easy mobile service that helps farmers access agricultural tips, market prices, weather updates, and order farm inputs without internet. It works on any phone, and farmers receive SMS confirmations after use.
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
   git clone [repository-url]
   ```

2. Create the database and tables:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file

3. Configure the application:
   - Open `config.php`
   - Update the Africa's Talking credentials:
     - `AT_API_KEY`: Your Africa's Talking API Key
     - `AT_USERNAME`: Your Africa's Talking Username
     - `AT_SENDER_ID`: Your SMS Sender ID
   - Update database credentials if needed

4. Configure Africa's Talking:
   - Log in to your Africa's Talking account
   - Set up a USSD service with the following callback URL:
     ```
     http://your-domain.com/ussd_handler.php
     ```
   - Set up an SMS callback URL:
     ```
     http://your-domain.com/sms_handler.php
     ```

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

