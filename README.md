# Expression Parser

This package allows to substitute (map) process large amounts of data in flexible manner, providing inline processing scenarios:
### Install

`composer isntall matrunchyk/expression-parser`

### Usage

####Standard function handler

##### Simple substitution
Input: 
```
new Expression(
    '[attr1]',
    [
        'attr1' => 1,
        'attr2' => 2,
    ]
)
```
Output: `1`

##### get() helper and mapping arguments
Input:
```
new Expression(
    'get([attr1], {"map":{"a":1, "b": 2, "c": 3}})',
    [
        'attr1' => 'b',
    ]
)
```
Output: `2`

##### get() helper and counting not empty elements
Input:
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

####Laravel collection handler

##### Collect mapped arguments and take a first item

Input: 
```
new Expression(
    'first(collect([attr1], [attr2]))',
    [
        'attr1' => 'value 1',
        'attr2' => 'value 2',
    ]
)
```
Output: `value 1`

[WIP]
