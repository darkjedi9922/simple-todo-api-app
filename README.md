# Getting Started

## Installation

```bash
$ git clone https://github.com/darkjedi9922/inmost-test.git
$ cd inmost-test
$ composer install
```

Next create a **MySQL** database like you usually do. Then copy `.env.example` file and name it `.env`. In this file (`.env`) set values of `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` respectively to the created database settings.

After that it is possible to migrate and seed the database:

```bash
$ php artisan migrate --seed
```

## Running and testing

```bash
# Start a development server
$ php artisan serve

# Running tests
$ php artisan test
```

## API documentation

API documentation and running is provided in **Postman**:
https://documenter.getpostman.com/view/11836521/T17AjW6X?version=latest