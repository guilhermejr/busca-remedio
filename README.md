# busca-remedio

Busca informações de um remédio

## Dependência

* PHP 7.4+
* php-xml

## Instalação

``` bash
$ composer require guilhermejr/busca-remedio
```

## Exemplo de uso via console

``` bash
$ vendor/bin/buscar-remedio array 7895296211215
```

## Exemplo de uso via código
```php
<?php

require 'vendor/autoload.php';

use BuscaRemedio\Buscador;

$buscador = new Buscador("7895296211215");
print_r($buscador->getJSON());
print_r($buscador->getArray());
```

## Contato
Dúvidas e Sugestões favor enviar e-mail para falecom@guilhermejr.net