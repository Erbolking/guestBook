Simple Guest Book
=======================

Welcome to "Simple Guest Book" created just for fun

How to install?
---------------

1. Checkout project: ```bash
git clone git@github.com:Erbolking/guestBook.git
```

2. Using composer get all dependencies and proceed with the installation. Go to the project directory and run following command:
```bash
php composer.phar install
```
If you don't have composer yet you simply download it via curl ```curl -s http://getcomposer.org/installer | php```

3. Provide all needed information such as "database driver", "database host", "database name", "database username" etc..

4. Create database schema:
```bash
php app/console doctrine:schema:create
```
5. Add demo data:
```bash
php app/console doctrine:fixtures:load
```
6. Set permission and clear cache:
```bash
chmod -R 777 app web/uploads/images
php app/console cache:clear
```

7. Enjoy!