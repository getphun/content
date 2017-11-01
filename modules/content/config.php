<?php
/**
 * content config file
 * @package content
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'content',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/content',
    '__files' => [
        'modules/content' => [
            'install',
            'remove',
            'update'
        ]
    ],
    '__dependencies' => [
        'formatter',
        'lib-html5'
    ],
    '_services' => [],
    '_autoload' => [
        'classes' => [
            'Content\\Library\\Parser' => 'modules/content/library/Parser.php'
        ],
        'files' => []
    ]
];