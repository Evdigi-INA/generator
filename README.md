
https://user-images.githubusercontent.com/62506582/200510814-9b2ca922-bd35-4e02-a236-047c4b7b118d.mp4

<p align="center">Laravel starter app and CRUD generator.</p>

<div align="center">

[![All Contributors](https://img.shields.io/github/contributors/Zzzul/generator-src?style=flat-square)](https://github.com/Zzzul/generator/graphs/contributors)
![GitHub last commit](https://img.shields.io/github/last-commit/Zzzul/generator-src.svg?style=flat-square)
[![License](https://img.shields.io/github/license/Zzzul/generator-src.svg?style=flat-square)](LICENSE)
[![Issues](https://img.shields.io/github/issues/Zzzul/generator-src?style=flat-square)](Issues)
[![Forks](https://img.shields.io/github/forks/Zzzul/generator-src?style=flat-square)](Forks)
[![Stars](https://img.shields.io/github/stars/Zzzul/generator-src?style=flat-square)](Stars)

</div>

## Table of Contents
1. [Requirements](#requirements)
2. [Setup](#setup)
3. [What's inside?](#what-inside) 
4. [Features](#features)
5. [License](#license)
6. [Support](#support)

## Requirements
- [PHP ^8.1](https://www.php.net/releases/8.1/en.php)
- [Laravel ^9.x](https://laravel.com/)

## Setup
Installation
```sh
composer require zzzul/generator --dev
```

### For this package, there are two variations: [Simpe Version](#simple-version) and [Full Version](#full-version)
<h3 id="simple-version">Simple Version</h3>

Only the generator. included: [Yajra Datatables](https://yajrabox.com/docs/laravel-datatables/master/installation), 
[Intervention Image](https://image.intervention.io/v2), and [Bootstrap 5](https://getbootstrap.com/)

Publish assets
 ```sh
php artisan generator:publish simple
```

Register the provider in `config/app.php`
```php
  /*
   * Package Service Providers...
   */
  App\Providers\ViewComposerServiceProvider::class,
```

Then goes to ```/simple-generators/create/```

<hr>

<h3 id="full-version">Full Version</h3>

The generator + starter app. included: 
[Yajra Datatables](https://yajrabox.com/docs/laravel-datatables/master/installation), 
[Intervention Image](https://image.intervention.io/v2), 
[Laravel Fortify](https://laravel.com/docs/9.x/fortify), 
[Spatie Permission](https://spatie.be/docs/laravel-permission/v5/installation-laravel), and 
[Mazer Template](https://github.com/zuramai/mazer)

> Installing this package after a brand-new Laravel installation is necessary if you want to use the full version of it. because several files will be overwritten.

#### Install [Laravel Fortify](https://laravel.com/docs/9.x/fortify) & [Spatie Permission](https://spatie.be/docs/laravel-permission/v5/installation-laravel)
```sh
composer require laravel/fortify spatie/laravel-permission
```

Publish assets
```sh
php artisan generator:publish all
```
> Warning! Be careful with this command, it will overwrite several files, Don't run it multiple times.

Register the provider in `config/app.php`
```php
  /*
   * Package Service Providers...
   */
  App\Providers\FortifyServiceProvider::class,
  Spatie\Permission\PermissionServiceProvider::class,
  App\Providers\ViewComposerServiceProvider::class,
```

Run migration and seeder
```sh
php artisan migrate --seed
``` 
Then goes to ```/generators/create```

Account
- Email: admin@example.com
- Password: password

<h2 id="what-inside">What's inside?</h2>

#### Simple Version
- [Yajra datatable - ^10.x](https://yajrabox.com/docs/laravel-datatables/master/installation)
- [Intervention Image - ^2.x](https://image.intervention.io/v2)
- [Bootstrap - ^5.x](https://getbootstrap.com/)

#### Full Version
- [Yajra datatable - ^10.x](https://yajrabox.com/docs/laravel-datatables/master/installation)
- [Intervention Image - ^2.x](https://image.intervention.io/v2)
- [Laravel Forify - ^1.x](https://laravel.com/docs/9.x/fortify)
- [Spatie permission - ^5.x](https://github.com/spatie/laravel-permission)
- [Mazer template - ^2.x](https://github.com/zuramai/mazer/)

## Features
#### Simple version
- [x] CRUD Generator
    - Support more than 15 [column types migration](https://laravel.com/docs/9.x/migrations#available-column-types), like string, char, date, year, etc.
    - Datatables ([Yajra Datatables](https://github.com/yajra/laravel-datatables))
    - BelongsTo relation
    - Model casting
    - Image upload ([Intervention Image](https://image.intervention.io/v2))
    - Support [HTML 5 Input](https://developer.mozilla.org/en-US/docs/Learn/Forms/HTML5_input_types)
    - Request validations supported: 
        - required, in, image, min, max, string, email, number, date, exists, nullable, unique, comfirmed

#### Full version
- [x] CRUD  Generator
- [x] CRUD User
- [x] Roles and permissions ([Spatie Permission](https://spatie.be/docs/laravel-permission/v5/introduction))
- [x] Authentication ([Laravel Fortify](https://laravel.com/docs/9.x/fortify))
    - Login
    - Register
    - Forgot Password
    - 2FA Authentication
    - Update profile information 

## License
[MIT License](./LICENSE)

## Support
You can support me at [Github Sponsors](https://github.com/sponsors/Zzzul), [Ko-fi](https://ko-fi.com/mzulfahmi) or [Saweria](https://saweria.co/zzzul)

<a href="https://www.buymeacoffee.com/mzulfahmi" target="_blank">
<img src="https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png" alt="Buy Me A Coffee" style="height: 41px !important;width: 174px !important;box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 3px 2px 0px rgba(190, 190, 190, 0.5) !important;">
</a>
