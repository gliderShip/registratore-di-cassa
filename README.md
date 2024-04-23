Registratore Di Cassa "Facile"
===============================

![screenshot.png](Documentation%2Fscreenshot.png)

Requirements
------------

  * PHP 8.2.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

Installation
------------

From inside the project directory, run the following commands:

```bash
$ composer install
```

Usage
-----

The data are loaded from the doctrine fixtures.

The project uses the included SQLite database.

```bash
$ symfony bin/console doctrine:fixtures:load
```


**Start the Cash Register.**
```bash
$ bin/console cash-register:start
```

Tests
-----

wip ...
