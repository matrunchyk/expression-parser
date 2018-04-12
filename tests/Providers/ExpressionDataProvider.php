<?php declare(strict_types=1);

use DI\ExpressionParser\Expression;
use DI\ExpressionParser\ExpressionParser;

return [
    'simple substitution' => [
        new Expression('[attr1]'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        '1',
    ],
    'get helper and mapping parameters' => [
        new Expression('get([attr1], {"map":{"a":1, "b": 2, "c": 3}})'),
        [
            'attr1' => 'b',
        ],
        2,
    ],
    'get helper and parameters with substitutions' => [
        new Expression('get([attr1], {"count":true, "nullable":false})'),
        [
            'attr1' => [
                'a',
                'b',
                'c',
            ],
        ],
        3,
    ],
    'matches_in_array helper and parameter with invalid substitution' => [
        new Expression('matches_in_array([keywords], "pool", {"sensitive":true})'),
        [
            'keywords' => [
                'Swimming Pool',
            ],
        ],
        false,
    ],
    'matches_in_array helper with substitution' => [
        new Expression('matches_in_array([keywords], "pool")'),
        [
            'keywords' => [
                'swimming pool',
            ],
        ],
        true,
    ],
    'explode & is_empty nested helpers with substitutions' => [
        new Expression('is_empty(explode([keywords]))'),
        [
            'keywords' => '',
        ],
        true,
    ],
    'substitution is missing' => [
        new Expression('[attr3]'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        '',
    ],
    'implode helper with a separator with substitutions' => [
        new Expression('implode(([attr1],[attr2]), ",")'),
        [
            'attr1' => 'hello',
            'attr2' => 'world',
        ],
        'hello,world',
    ],
    'implode helper with substitutions' => [
        new Expression('implode(([attr1],[attr2]))'),
        [
            'attr1' => 'hello',
            'attr2' => 'world',
        ],
        'hello world',
    ],
    'explode helper with substitution' => [
        new Expression('explode([Rooms])'),
        [
            'Rooms' => 'Pantry,Study',
        ],
        ['Pantry', 'Study'],
    ],
    'has helper with substitution' => [
        new Expression('has([attr1])'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        true,
    ],
    'equal helper with substitution [experiment]' => [
        new Expression('or_x(equal([attr1], 1), in_array(explode([keywords]), "hello"))'),
        [
            'attr1' => 1,
            'keywords' => 'hello,world',
        ],
        true,
    ],
    'equal helper with substitution' => [
        new Expression('equal([attr1], 1)'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        true,
    ],
    'equal helper with substitution (false)' => [
        new Expression('equal([attr1], 2)'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        false,
    ],
    'great_than helper with substitution' => [
        new Expression('great_than([attr1], 0)'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        true,
    ],
    'great_than helper with substitution (false)' => [
        new Expression('great_than([attr1], 2)'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        false,
    ],
    'has helper with missing substitution' => [
        new Expression('has([attr3])'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        false,
    ],
    'explode helper with a separator with substitutions' => [
        new Expression('explode([Rooms], ";")'),
        [
            'Rooms' => 'Pantry;Study',
        ],
        ['Pantry', 'Study'],
    ],
    'in_array helper with invalid substitution' => [
        new Expression('in_array([keywords], "word")'),
        [
            'keywords' => [
                'hello',
                'world',
            ],
        ],
        false,
    ],
    'in_array helper with substitution' => [
        new Expression('in_array([keywords], "hello")'),
        [
            'keywords' => [
                'hello',
                'world',
            ],
        ],
        true,
    ],
    'explode & in_array nested helpers with invalid substitution' => [
        new Expression('in_array(explode([keywords]), "word")'),
        [
            'keywords' => 'hello,world',
        ],
        false,
    ],
    'explode & in_array nested helpers with substitution' => [
        new Expression('in_array(explode([keywords]), "hello")'),
        [
            'keywords' => 'hello,world',
        ],
        true,
    ],
    'explode & is_empty nested helpers with invalid substitutions' => [
        new Expression('is_empty(explode([keywords]))'),
        [
            'keywords' => 'hello,world',
        ],
        false,
    ],
    'not & equal nested helpers with substitutions' => [
        new Expression('not(equal([attr1], 2))'),
        [
            'attr1' => 1,
            'attr2' => 2,
        ],
        true,
    ],
    'and_x & equal & in_array & explode nested helpers with substitutions' => [
        new Expression('and_x(equal([attr1], 1), in_array(explode([attr2]), "hello"))'),
        [
            'attr1' => 1,
            'attr2' => 'hello,world',
        ],
        true,
    ],
    'or_x & equal & in_array & explode nested helpers with substitutions' => [
        new Expression('or_x(equal([attr1], 1), in_array(explode([attr2]), "hello"))'),
        [
            'attr1' => 1,
            'attr2' => 'hello,world',
        ],
        true,
    ],
    'or_x & equal & in_array & explode nested helpers with invalid substitution' => [
        new Expression('or_x(equal([attr1], 1), in_array(explode([attr2]), "word"))'),
        [
            'attr1' => 2,
            'attr2' => 'hello,world',
        ],
        false,
    ],
    'not & or_x & equal & in_array & explode nested helpers with substitution' => [
        new Expression('not(or_x(equal([attr1], 1), in_array(explode([attr2]), "word")))'),
        [
            'attr1' => 2,
            'attr2' => 'hello,world',
        ],
        true,
    ],
    'testing_laravel_collection' => [
        new Expression('first(collect([attr1], [attr2]))'),
        [
            'attr1' => 'value 1',
            'attr2' => 'value 2',

        ],
        'value 1',
    ],
    'custom invoker parameters' => [
        new Expression('first(take(sort(filter([attr1], [filter_func]), [dir]), [offset]))'),
        [
            'attr1' => [
                10,
                30,
                20,
            ],
            'filter_func' => function(ExpressionParser $context, $value) {
                return array_filter($value, function ($item) use ($context) {
                    return $item < $context->mappings['filter_attr'];
                });
            },
            'filter_attr' => 30,
            'dir' => 'desc',
            'offset' => 1,
        ],
        20,
    ],
];
