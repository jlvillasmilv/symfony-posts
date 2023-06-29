Symfony Demo Posts Application
==============================

<p align="center"><a href="https://symfony.com" target="_blank">
    <img src="https://symfony.com/logos/symfony_black_02.svg">
</a></p>

[Symfony][1] is a **PHP framework** for web and console applications and a set
of reusable **PHP components**. Symfony is used by thousands of web
applications and most of the [popular PHP projects][2].
---

## Requirements

-   PHP 8.1.0 or higher;
-   Symfony installer
-   Composer
-   APACHE
-   Mysql (ver 8)

## Installation

1. #### Clone the repository from GitHub and open the directory:

```
git clone url repository
```

2. #### cd into your project directory

3. #### install via composer

```
composer install

php bin/console doctrine:migration:migrate
php bin/console doctrine:fixtures:load
```

## Start prepare the environment:

```
symfony serve
```

[1]: https://symfony.com
[2]: https://symfony.com/projects