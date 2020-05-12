# hydrator
Allows extracting data from objects and getting objects from data

Usage example:

```php
// extract data
(new Hydrator($object))->extract();


//hydrate object
$object = (new Hydrator($source))->hydrate($object)

```

By default Hydrator create map for all source attributes. You can set custom map

```php
// extract data
(new Hydrator($object))->map(['id' => 'id', 'title' => 'title'])->extract();


//hydrate object
$object = (new Hydrator($source))->map(['id' => 'id', 'title' => 'title'])->hydrate($object)

```

Also you can extract and hydrate in strict mode
```php
// extract data
(new Hydrator($object))->extract(true);


//hydrate object
$object = (new Hydrator($source))->hydrate($object, true)

```
