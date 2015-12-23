# Versión IDMarinas #

Esta versión esta basada en la versión +nb Edition de Oliver Brendel ([NB Core](http://nb-core.org)).

## Requisitos ##

Los nuevos requisitos para esta versión son los siguientes:

* PHP 5.4 o superior (yo uso la versión 5.6 y no hay problemas)
* Base de datos soportadas:
    * No debería haber problemas al usar estas bases de datos ya que se usa una extensión PDO para usarlas.
        * Mysqli
        * Pgsql
        * Sqlsrv
        * Pdo_Mysql
        * Pdo_Sqlite
        * Pdo_Pgsql
* Fuente ([Font Awesome](http://fortawesome.github.io/Font-Awesome/)) 
        

## Instalacción ##

Instalar localmente Composer -> Instrucciones mas detalladas en ([Composer](https://getcomposer.org/doc/00-intro.md))
```
#!terminal
$ curl -sS https://getcomposer.org/installer | php
```

Se debe ejecutar el componser para que descargue he instale las dependencias
```
#!terminal
$ php composer.phar install
```

Esto instalará todas las nuevas dependencias que necesita la versión.

Para instalar la Fuente, solo es necesario seguir las instrucciones que se da en la página web.

### Cambios hechos ###

1. Se actualizado el script de la base de datos. Ahora usa el componente Zend DB para acceder a la base de datos. Las funciones antiguas siguen siendo validas.
    * Es posible realizar una consulta como `DB::query($sql);`
2. Se ha eliminado la insercción por referencia en la batalla, haciendo que el juego sea compatible con PHP 5.4.
3. En la edición de criaturas, se ha eliminado el que muestre el nivel en cada una de las criaturas, y se muestra como parte del titulo, además de algunos ajustes adicionales.
4. Se ha cambiado el como se visualiza y el orden de ciertas estadísticas del personaje.

### Añadidos ###

1. Se añade el componente Zend Paginator, que se puede usar desde la clase `DB::paginator()`


#### Para saber más sobre los componentes Zend ####

Visita la web de ([Zend Framework](http://framework.zend.com/manual/current/en/index.html)) para saber más sobre sus componentes y poder añadir más componentes a tu versión de juego.