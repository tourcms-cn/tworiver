Two River Ticket
================
Two River Ticket System Api

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist tourcms-cn/tworiver "*"
```

or add

```
"tourcms-cn/tworiver": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
$twoRiver = new TwoRiverTicket(
    'apiUrl',
    'mch_id',
    'key'
);
$data = $twoRiver->line()
print_r($data);
```
