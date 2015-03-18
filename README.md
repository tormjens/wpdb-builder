# wpdb Builder

An easier way to create custom queries using WordPress' wpdb functions. The class is based on the excellent [Pixie Query Builder](https://github.com/usmanhalalit/pixie).

Intended for plugin and theme developers craving sexier queries.

** Be aware. This class in early development stages. **

## Install

First clone the repository.

```
git clone https://github.com/tormjens/wpdb-builder.git wpdb-builder
```

Run a composer install inside the repository to generate the autoload files

```
composer install
```

Start using the class. Its namespaced under WpdbBuilder

```PHP
use \WpdbBuilder\Builder;

var_dump(( Builder::table( 'posts' )->all() );
```

## Example

A standard wpdb query looks something like:
```PHP
$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE id = %s LIMIT 1", 1 );
$result = $wpdb->get_row( $sql );
```

With the wpdb Builder the same query with the same results looks like:
```PHP
$result = Builder::table('posts')->where('id', 1)->first();
```

For more detailed documentation, please have a look at the [Pixie Query Builder](https://github.com/usmanhalalit/pixie) repository as most of the methods are availiable in this very class.

