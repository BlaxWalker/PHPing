# PHPing
> A simple web-based application to monitor IP devices and give you alert when they are down

## Table of contents
* [General info](#general-info)
* [Screenshots](#screenshots)
* [Requirements](#requirements)
* [Installation](#installation)
* [How-It-Works](#how-it-works)
* [Features](#features)
* [Notes](#notes)
* [Contact](#contact)

## General info
You may use PRTG or another monitoring tools for richer features :D

## Screenshots
![Example screenshot](./img/ss1.jpg?raw=true "Home Page")
![Example screenshot](./img/ss2.jpg?raw=true "Add New Device")
![Example screenshot](./img/ss3.jpg?raw=true "Telegram Bot Notification")
![Example screenshot](./img/ss4.jpg?raw=true "Email Notification")

## Requirements
* Microsoft Windows or Microsoft Windows Server as the server (you may adjust the code if you want to use it on Linux environment)
* Web Server (such as Apache, NGINX, etc.)
* Relational Database Management System (such as MySQL, MariaDB, etc.)
* PHP v5 or above

I use XAMPP for my environment, which already consist of Apache, MariaDB, and PHP

## Installation
If you are using XAMPP on default folder location ("C:\XAMPP"), you may use these steps, otherwise, adjust them with your own environment
* Download all files on this repo and place them on your web server public folder ("htdocs" folder for Apache-XAMPP)
* Create the database structure by importing "phping.sql" file into your RDBMS
* Create a scheduled task to run "PHPing.bat" file every minute on your environment via Task Scheduler
* Modify your database connection parameters on "/php/dbconfig.php" file
* Check the PHPing and php folder path in "PHPing.bat" file and make sure the path is same as your environment
* Add this 2 line on your "php.ini" file (the configuration file for PHP) to enable command prompt run from inside of PHP file
  [PHP_COM_DOTNET]
  extension=php_com_dotnet.dll
* Access to http://localhost/phping and add your first IP device there (modify "localhost" with your server name / address and server port (if any))
* If you want to using Telegram Bot and email notification function, modify the related parameters on "/php/PHPing.php" file

## How-It-Works
* Scheduler run PHPing.bat to run PHPing.php every minute
* PHPing.php get the ping result text file (from previous cycle) in "/log" folder and insert the ping result into database for each IP on the list
* PHPing.php run IMCP ping command for each IP on the list and store the result on plain text file in "/log" folder (for next cycle)

## Features
Currently, these are the basic features of PHPing, you may fork the code to add more functions or features
* Monitor IP on the list (alive or down)
* Notify user via Telegram Bot or email (adjust the parameter first to use this function)

## Notes
* monitoring "priority" means how often the IP device get ping-ed, so priority 1 means the IP device is critical hence it must be pinged every minute, and priority 2 means the IP device is less critical hence it only pinged every 2 minute, and so on, meanwhile priority 0 means the IP device is not pinged at all since it is not monitored (at your choice)
* the home page of http://localhost/phping is auto refreshed every minute (60 seconds), so no need to refresh the page manually
* To using Telegram Bot notification, create your Telegram bot first using this guide: https://core.telegram.org/bots, then use your bot "token" in "/php/PHPing.php" file
* PHPing use default php mail() function to send email notification, so make sure the mail server configuration in "php.ini" file correct (you may follow this guide: https://www.quackit.com/php/tutorial/php_mail_configuration.cfm), otherwise, you may fork the code to combine it with another PHP email library such as PHPMailer or others

## Contact
Created by [@eko.iswinarso](mailto:eko.iswinarso@gmail.com) - feel free to contact me!
