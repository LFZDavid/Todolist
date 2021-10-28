ToDoList
========

[![Maintainability](https://api.codeclimate.com/v1/badges/bea5d5c5ba9d1bd9b52e/maintainability)](https://codeclimate.com/github/LFZDavid/Todolist/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/bea5d5c5ba9d1bd9b52e/test_coverage)](https://codeclimate.com/github/LFZDavid/Todolist/test_coverage)
[![Build Status](https://app.travis-ci.com/LFZDavid/Todolist.svg?branch=develop)](https://app.travis-ci.com/LFZDavid/Todolist)
[![Coverage Status](https://coveralls.io/repos/github/LFZDavid/Todolist/badge.svg?branch=develop)](https://coveralls.io/github/LFZDavid/Todolist?branch=develop)

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

## Technical Requirements
---
* PHP ( version >= 7.1.3 )
* Database : 
    * mariadb ( version >= 10.2 )
    <br>or 
    * mysql ( version >= 5.7 )
* composer ( version : >= 2 )

more infos : _[symfony documentation](https://symfony.com/doc/current/setup.html#technical-requirements)_

---

## Installation
>1. ### Get files : 
>```
>git clone https://github.com/LFZDavid/Todolist.git
>```

>2. ### Install dependencies : 
>```
>cd Todolist/
>composer install
>```

>3. ### Database :
>* set database connection in `.env` file
>```
>DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
>```
>__`Make sure your local server is running`__ and use de command : 
>```
>bin/console doctrine:database:drop --if-exists --force
>bin/console doctrine:database:create
>bin/console doctrine:schema:update --force
>bin/console d:d:d --if-exists --force -e test,
>bin/console d:d:c -e test,
>bin/console d:schema:update --force -e test
>```

>### Install demo data: _( `optional` )_
>    ```
>    bin/console doctrine:fixtures:load
>    ```

---