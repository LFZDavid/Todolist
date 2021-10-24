ToDoList
========

[![Maintainability](https://api.codeclimate.com/v1/badges/bea5d5c5ba9d1bd9b52e/maintainability)](https://codeclimate.com/github/LFZDavid/Todolist/maintainability)
[![Build Status](https://app.travis-ci.com/LFZDavid/Todolist.svg?branch=main)](https://app.travis-ci.com/LFZDavid/Todolist)
[![Coverage Status](https://coveralls.io/repos/github/LFZDavid/Todolist/badge.svg?branch=audit)](https://coveralls.io/github/LFZDavid/Todolist?branch=main)

Based on OpenClassroom course project #8 : [Améliorez un projet existant](https://openclassrooms.com/projects/ameliorer-un-projet-existant-1)

---

## Technical Requirements
* __PHP__ :
    * _version min_ : `5.5.9`
    * _version max_ : `7.1.33` _( recommended )_
* __Database__ :
    * MariaDb : `10.2`
    * Mysql : `5.7` _( recommended )_
* __Composer__ : `2`

more infos on : [Symfony documentation](https://symfony.com/doc/3.1/setup/web_server_configuration.html#apache-with-php-fpm)

---

## Installation
>### Get files : 
>```
>git clone https://github.com/LFZDavid/Todolist.git
>```

>### Install dependencies : 
>```
>cd Todolist/
>composer install
>```
>Follow the configuration steps
> _nb : you can change values in the generated file : `app/config/parameters.yml`_

>### Database :
>__Make sure your local server is running__ and use de command : 
>
>_`dev/prod :`_
>```
>bin/console d:d:d --if-exists --force
>bin/console d:d:c
>bin/console d:schema:update --force
>```
>_`test :`_
>```
>bin/console d:d:d --if-exists --force --env=test
>bin/console d:d:c --env=test
>bin/console d:schema:update --force --env=test
>```

>### Install demo data: _( `optional` )_
>    ```
>    bin/console d:f:l
>    ```

---