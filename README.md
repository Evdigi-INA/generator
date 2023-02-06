
https://user-images.githubusercontent.com/62506582/200510814-9b2ca922-bd35-4e02-a236-047c4b7b118d.mp4

<p align="center">Laravel starter app and CRUD generator.</p>

<div align="center">

[![All Contributors](https://img.shields.io/github/contributors/EvdigiIna/generator-src?style=flat-square)](https://github.com/EvdigiIna/generator/graphs/contributors)
![GitHub last commit](https://img.shields.io/github/last-commit/EvdigiIna/generator-src.svg?style=flat-square)
[![License](https://img.shields.io/github/license/EvdigiIna/generator-src.svg?style=flat-square)](LICENSE)
[![Issues](https://img.shields.io/github/issues/EvdigiIna/generator-src?style=flat-square)](Issues)
[![Forks](https://img.shields.io/github/forks/EvdigiIna/generator-src?style=flat-square)](Forks)
[![Stars](https://img.shields.io/github/stars/EvdigiIna/generator-src?style=flat-square)](Stars)

</div>

## Table of Contents
1. [Setup](#setup)
2. [Usage](#usage)
3. [Requirements](#requirements)
4. [What's inside?](#what-inside) 
5. [Features](#features)
6. [License](#license)
7. [Support](#support)

## Setup
1. Installation
```sh
composer require EvdigiIna/generator --dev
```
> Since this package will overwrite some files, it must be installed after a brand-new Laravel installation.

2. Register the provider in ``` config/app.php ```
```php
 /*
  * Package Service Providers...
  */
  EvdigiIna\Generator\Providers\GeneratorServiceProvider::class,
```

3. Publish vendor 
```sh
php artisan generator:publish
```

4.  Run migration and seeder
```sh
php artisan migrate --seed
``` 

5. Start development server
```sh
php artisan serve
``` 

## Usage
Go to ```/generators/create```

Login
- Email: admin@example.com
- Password: password


## Requirements
- [PHP ^8.1](https://www.php.net/releases/8.1/en.php)

<h2 id="what-inside">What's inside?</h2>

- [Laravel - ^9.x](https://laravel.com/)
- [Laravel Forify - ^1.x](https://laravel.com/docs/9.x/fortify)
- [Laravel Debugbar - ^3.x](https://github.com/barryvdh/laravel-debugbar)
- [Spatie permission - ^5.x](https://github.com/spatie/laravel-permission)
- [Yajra datatable - ^10.x](https://yajrabox.com/docs/laravel-datatables/master/installation)
- [Intervention Image - ^2.x](https://image.intervention.io/v2)
- [Mazer template - ^2.x](https://github.com/zuramai/mazer/)

## Features
- [x] Authentication ([Laravel Fortify](https://laravel.com/docs/9.x/fortify))
    - Login
    - Register
    - Forgot Password
    - 2FA Authentication
    - Update profile information 
- [x] Roles and permissions ([Spatie Permission](https://spatie.be/docs/laravel-permission/v5/introduction))
- [x] CRUD User
- [x] CRUD Generator
    - Support more than [15 column type migration](https://laravel.com/docs/9.x/migrations#available-column-types), like string, char, date, year, etc.
    - Datatables ([Yajra datatables](https://github.com/yajra/laravel-datatables))
    - BelongsTo relation
    - Model casting
    - Image upload ([Intervention Image](https://image.intervention.io/v2))
    - Support [HTML 5 Input](https://developer.mozilla.org/en-US/docs/Learn/Forms/HTML5_input_types)
    - Request validations supported: 
        - required, in, image, min, max, string, email, number, date, exists, nullable, unique, comfirmed

## License
[MIT License](./LICENSE)

## Support
You can support me at [Github Sponsors](https://github.com/sponsors/EvdigiIna), [Ko-fi](https://ko-fi.com/mzulfahmi) or [Saweria](https://saweria.co/EvdigiIna)

<a href="https://www.buymeacoffee.com/mzulfahmi" target="_blank">
<img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;">
</a>
