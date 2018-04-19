# ⛓ Expression Parser

<p align="center">
<a href="https://travis-ci.org/matrunchyk/expression-parser"><img src="https://api.travis-ci.org/matrunchyk/expression-parser.svg?branch=master" alt="Build Status"></a>
<img src="https://poser.pugx.org/matrunchyk/expression-parser/d/total" alt="Total Downloads">
<img src="https://poser.pugx.org/matrunchyk/expression-parser/v/stable" alt="Latest Stable Version">
<img src="https://poser.pugx.org/pugx/badge-poser/license?format=flat" alt="License">
</p>

This package allows to parse with mapping map large amounts of data in flexible manner, providing various processing functions:

## 🔩 Install

`composer install di/expression-parser`

## ⚒ Usage


```
// Signature
$expression = new Expression(string $expression[, array $mappings = []]);
```

## 👀 Example

```
use DI\ExpressionParser\Expression;

$expression = 'or_x(equal([attr1], 1), in_array(explode([keywords]), "hello"))';

$mappings = [
    'attr1' => 1,
    'keywords' => 'hello,world',
];

$ex = new Expression($expression, $mappings);

echo $ex->value(); // true
```




## Standard function handlers

#### 🔗 Parameter substitution

📥 Input:

```
new Expression(
    '[attr1]',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `1`



#### 🔗 Parameter substitution with `has()` function

📥 Input:

```
new Expression(
    'has([attr1])',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `true`



#### 🔗 Substitution with `in_array()` function and scalar value

📥 Input:

```
new Expression(
    'in_array([keywords], "hello")',
    [
        'keywords' => [
            'hello',
            'world',
        ],
    ]
)
```

📤 Output: `true`


#### 🔗 Nested `in_array()` and `explode()` function and scalar value

📥 Input:

```
new Expression(
    'in_array(explode([keywords]), "hello")',
    [
        'keywords' => 'hello,world',
    ]
)
```

📤 Output: `true`



#### 🔗 Substitution with `matches_in_array()` function

📥 Input:

```
new Expression(
    'matches_in_array([keywords], "pool")',
    [
        'keywords' => [
            'swimming pool',
        ],
    ]
)
```

📤 Output: `true`



#### 🔗 Nested `explode()` `is_empty()` and functions

📥 Input:

```
new Expression(
    'is_empty(explode([keywords]))',
    [
        'keywords' => '',
    ]
)
```

📤 Output: `true`



#### 🔗 `implode()` with inline parameter substitution

📥 Input:

```
new Expression(
    'implode(([attr1],[attr2]))',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `hello world`



#### 🔗 `implode()` with inline parameter substitution and a separator flag

📥 Input:

```
new Expression(
    'implode(([attr1],[attr2]), ",")',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `hello,world`



#### 🔗 `explode()` with array substitution

📥 Input:

```
new Expression(
    'explode([Rooms])',
    [
        'Rooms' => 'Pantry,Study',
    ]
)
```

📤 Output: `['Pantry', 'Study']`



## Standard handlers with one or multiple flags


#### 🔗 `explode()` with array substitution and a separator flag

📥 Input:

```
new Expression(
    'explode([Rooms], ";")',
    [
        'Rooms' => 'Pantry;Study',
    ]
)
```

📤 Output: `['Pantry', 'Study']`



#### 🔗 `get()` function with `count` and `nullable` flags

📥 Input:

```
new Expression(
    'get([attr1], {"count":true, "nullable":false})',
    [
        'attr1' => [
            'a',
            'b',
            'c',
        ],
    ]
)
```

📤 Output: `3`



#### 🔗 Nested mapping with `map` flag in `get()` function

📥 Input:

```
new Expression(
    'get([attr1], {"map":{"a":1, "b": 2, "c": 3}})',
    [
        'attr1' => 'b',
    ]
)
```

📤 Output: `2`


#### 🔗 Case sensitive matching in array with `sensitive` flag

📥 Input:

```
new Expression(
    'matches_in_array([keywords], "pool", {"sensitive":true})',
    [
        'keywords' => [
            'Swimming Pool',
        ],
    ]
)
```

📤 Output: `false`



## Logical handlers


#### 🔗 `equal()` function

📥 Input:

```
new Expression(
    'equal([attr1], 1)',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `true`



#### 🔗 `great_than()` function

📥 Input:

```
new Expression(
    'great_than([attr1], 0)',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `true`



#### 🔗 Nested `not()` and `equal()` functions

📥 Input:

```
new Expression(
    'not(equal([attr1], 2))',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `true`




#### 🔗 Nested `not()` and `equal()` functions

📥 Input:

```
new Expression(
    'not(equal([attr1], 2))',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```

📤 Output: `true`


## Complex functions with unlimited nesting level


#### 🔗 Multiple function parameter substitution with nesting with `and_x()`

📥 Input:

```
new Expression(
    'and_x(equal([attr1], 1), in_array(explode([attr2]), "hello"))',
    [
        'attr1' => 1,
        'attr2' => 'hello,world',
    ]
)
```

📤 Output: `true`


#### 🔗 Multiple function parameter substitution with nesting with `or_x()`

📥 Input:

```
new Expression(
    'or_x(equal([attr1], 1), in_array(explode([attr2]), "hello"))',
    [
        'attr1' => 1,
        'attr2' => 'hello,world',
    ]
)
```

📤 Output: `true`


#### 🔗 Multiple function parameter substitution with nesting with `or_x()` and `not()`

📥 Input:

```
new Expression(
    'not(or_x(equal([attr1], 1), in_array(explode([attr2]), "word")))',
    [
        'attr1' => 2,
        'attr2' => 'hello,world',
    ]
)
```

📤 Output: `true`


#### 😳 Multiple nesting with a Closure

📥 Input:

```
new Expression(
    'first(take(sort(filter([attr1], [filter_func]), [dir]), [offset]))',
    [
        'attr1' => [
            10,
            30,
            20,
        ],
        'filter_func' => function (ExpressionParser $context, $value) {
            return array_filter($value, function ($item) use ($context) {
                return $item < $context->getMappings('filter_attr');
            });
        },
        'filter_attr' => 30,
        'dir' => 'desc',
        'offset' => 1,
    ]
)
```

📤 Output: `20`


## `Laravel Collection` helpers

The package already has a built-in support of Laravel Collection helpers.
For more informations about the available functions it supports please refer to the original Laravel Collection [documentation page](https://laravel.com/docs/5.6/collections#available-methods).


#### 🔗 Example usage of Laravel Collection functions

📥 Input:

```
new Expression(
    'first(collect([attr1], [attr2]))',
    [
        'attr1' => 'value 1',
        'attr2' => 'value 2',
    ]
)
```

📤 Output: `'value 1'`

## Extending with custom handlers

In order to extend or override current functionality, you will need to add your own handler class name to `config/handlers.php` file:

```
use DI\ExpressionParser\Handlers\Logical;
use DI\ExpressionParser\Handlers\Standard;
use DI\ExpressionParser\Handlers\LaravelCollectionAdapter;

return [
    Standard::class,
    Logical::class,
    LaravelCollectionAdapter::class,

    // Add custom expression handlers here:
    // \Acme\Handlers\CustomHandler::class,
    // 'Acme\Handlers\CustomHandler',
];
```

## 😍 Contribution

Please feel free to fork and help developing.


## 📃 License

[MIT](http://opensource.org/licenses/MIT)
