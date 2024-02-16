# Example with PrestaShop Integration Framework Components

## Installation

First use the following command to install all the packages from the composer.json file

```shell script
composer install --no-dev -o
```

## Changelog

### v2.0.0

- Remove old dependencies in composer.json
- Update [prestashop/module-lib-mbo-installer](https://github.com/PrestaShopCorp/module-lib-mbo-installer) to version 1.0
- Add new dependency management system
- Add [php-scoper](https://github.com/humbug/php-scoper) to resolve composer dependency conflicts with other modules

### v1.0.0

- Initial version

## Known issues

In some cases, merchants may encounter compatibility issues between the versions of libraries installed by different modules on PrestaShop. To solve this problem, you can use [php-scoper](https://github.com/humbug/php-scoper) to obtain a unique prefix for the namespaces in the vendor folder of your module.