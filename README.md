# FRIS Research Portal

## 1. Requirements
* Apache 2.4
* PHP 7.1
* MySQL 5.6 or 5.7 (percona)

## 2. Installation

### 2.1. Clone the project from Git
Clone the project from the Git server:
```
$ git clone https://github.com/oSoc19/fris.git
```

### 2.2. Setup database
Create the database (database/clean version.sql) in your local MySQL environment in order to be able to run the API locally.

### 2.2. Setup API
Host the API on the server of your choosing and update your credentials (line 7) in api/manager/DBManager.php 
```
return new PDO("mysql:host=hostname;dbname=dbname;charset=utf8mb4",'username','password', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
```

### 2.3. Setup module
Place the suggestions folder with the twig file in your views. You could use twig's {% include %} to add the results to any view.\
Change the link to the API URL (line 39 & 77) in suggestions/suggestions.html.twig to your hostname.
```
$url = '../api/process/suggestions.php';
```
Replace 'search_api_fulltext' by your URL parameter name (line 36 & 79).
```
let search = search_params.get('search_api_fulltext');
```
