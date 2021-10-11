# Introduction
Hi, this is a tiny restful-api framework written by PHP. Main features:
- API path route match by regular expression
- Provide swagger GUI to browse & test web API (need to write yaml in doc-string of API properly)
- JWT token based authentication
- Fast CRUD API implementation by simply inherit BaseCRUDController and MySQLBaseDAO.
- Support page query, keyword search and order. Just need to implement pageQuery logic in DAO almostly.
- Respond HTTP status code by exception handling

# Directory structure
- Directory structure is as below:
```
.
+-- README.md   This document
+-- conf        Put config files here
|  +-- demo         "demo" application config file
|  +-- ...          You can add more web applications...
+-- test        Put API test case here
|  +-- demo         "demo" web API test cases
|  +-- ...          You can add more test cases for other web applications...
+-- webroot     Put web applications here. Also web root path to Apache
|  +-- demo         "demo" web application
|    +-- api            API path
|      +-- controller       Implement more API here
|      +-- core             The core framework, the fundamental, no business logic
|      +-- dao              Implement more data access object here
|      +-- index.php        Web API entrace point
|    +-- apidoc     Swagger web GUI
|  +-- ...      Create more web application here
```
- You can put front-end in folder you want at the same level as api folder

# Requirement
- Ubuntu 18
- PHP7
- MySQL

# Step by step to setup develop environment
To setup the develop environment, prepare Ubuntu 18 OS and follow the steps below:
1. Install:
- sudo apt-get update
- sudo apt-get install -y apache2
- sudo apt-get install -y libapache2-mod-php
- sudo apt-get install -y php-mysql
- sudo apt-get install -y mysql-server
#### To run test case, install python ddt
- sudo apt-get install -y python3-pip
- sudo pip3 install ddt

2. Clone git repo, put in /workspace:
- sudo mkdir workspace
- sudo chown www-data.www-data workspace
- sudo git clone {this git repo}

3. Setup Apache
#### Enable rewrite
- sudo a2enmod rewrite

#### Edit config
- sudo vi /etc/apache2/apache2.conf
```
	<Directory /workspace/webroot>
		Options Indexes FollowSymLinks
		AllowOverride None
		Require all granted
	</Directory>
```
- sudo vi /etc/apache2/sites-enabled/000-default.conf
```
	DocumentRoot /workspace/webroot
	<Directory /workspace/webroot>
		AllowOverride All
	</Directory>
```
4. Setup PHP
- sudo vi /etc/php/7.2/apache2/php.ini
#### Modify these.
```
	error_log = /var/log/apache2/php_errors.log
```
5. Setup log rotate daemon
- sudo vi /etc/logrotate.d/apache2
#### create 640 root adm # Mark this line.
```
     create 666 www-data www-data
```
7. Setup MySQL
#### Create DB user.
- sudo mysql
```
   CREATE USER 'demo'@'localhost' IDENTIFIED BY 'demopassword';
```
#### Initialize data.
- sudo /workspace/test/demo/demo_init.sh

6. Restart Apache
- sudo systemctl restart apache2

7. Run test cases
- sudo /workspace/test/demo/demo_test.sh

8. Use swagger GUI to test API
- Browse this URL:
```
http://{your IP}/demo/apidoc
```

