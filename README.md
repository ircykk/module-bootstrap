# PrestaShop bootstrap module

PrestaShop bootstrap module containing most of features:

- Install, uninstall, upgrade
- Configure, admin tabs, CRUD
- Front & back office hooks
- Forms (HelperForm)
- Templates
- Override classes, controllers and views
- and more...

PHP version 5.3, PrestaShop 1.6-1.7x

### Installation

Navigate to `/modules/` directory and type:

```sh
$ git clone git@github.com:ircykk/module-bootstrap.git
```
or [download *.zip](https://github.com/ircykk/module-bootstrap/archive/master.zip) and unzip to `/modules/module_bootstrap/`.

### Docker

To run project with Docker just navigate to project directory and run
```sh
$ docker-compose up
```
Docker will start 3 containers:
- MySQL 5.7 (root password "admin")
- phpmyadmin on port 8181 (username "root" password "admin")
- PrestaShop 1.7.3.0

After Docker download and start containers will auto install PrestaShop.
Admin panel: http://localhost:8080/admin123
E-mail: demo@prestashop.com
Password: "12345678"

Directory `/module_bootstrap` is mapped to `/var/www/html/modules/module_bootstrap` in PrestaShop container.

We also have PhpMyAdmin on http://localhost:8181/

### Development

Feel free to contribute!
