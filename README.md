# FStorage

```php
use jinteng\filestorage\FStorage;


FStorage::$url = 'http://192.168.1.20/';
FStorage::$appName = 'testapp';
FStorage::$appKey = '1234567890';
FStorage::$appKeyId = 1;


$file = FStorage::uploadFile($_FILES['file']);
var_dump($file);
```
