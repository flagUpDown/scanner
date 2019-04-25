# scanner

这是一个用PHP写的扫描工具，主要是用于扫描PHP文件中的某些函数调用


## 下载

使用composer进行下载

```
composer require flagupdown/scanner
```

下载成功之后,在开始写代码的文件中包含composer自动加载器

```
require 'vendor/autoload.php';
```

## 使用

存在被扫描的PHP文件`data.php`

```php
<?php
fn("param0","param1");
```

使用`scanner`库对该文件进行扫描

```php
require 'vendor/autoload.php';
use FlagUpDown\ScanFunction;

//指明扫描函数，可以扫描多个
$scanFunction = new ScanFunction(['fn']);

//扫描指定文件
$result = $scanFunction->scan('data.php');
```

返回结果是一个数组：

```php
[
  'file name' => [
    'function name' => [
      ["param0", "param1"], //扫描到的参数列表
      [...] // 该函数第二次调用时，扫描到的参数列表
      ... // 该函数第n次调用时，扫描到的参数列表
    ]
  ]
]
```

#### 扫描器忽略

+ 注释：

```php
// fn("param0","param1");
/* fn("param0","param1"); */
```

+ 字面量：

```php
'fn("param0","param1")';
"fn('param0','param1')";
```

+ PHP以外的部分：

```php+HTML
fn("param0","param1");
<?php
 /* php code */
?>
fn("param0","param1");
```

#### 扫描目录

可以递归的扫描指定一个目录下的所有PHP文件，同时也允许忽略一些指定目录

```php
$scanFunction->scan('dir');
$scanFunction->scan('dir', ['ignoreDir0', 'ignoreDir1']);
```

