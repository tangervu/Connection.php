Connection.php
==============

A PHP class to transfer data using different protocols (sftp, ftp, http, etc). Utilizes PHPs ssh2, ftp and curl functions if available.

Installation
------------
The recommended way to install Connection.php is through [Composer](http://getcomposer.org).
```json
{
	"require": {
		"tangervu/connection": "dev-master"
	}
}
```

Example
-------
```php
<?php
require('vendor/autoload.php'); //Use composer autoload
$conn = new Connection('ftp://ftp.funet.fi');
//List directory contents
print_r($conn->ls());
//Display contents of the README file
echo $conn->get('README');
```

License
-------
LGPL v3