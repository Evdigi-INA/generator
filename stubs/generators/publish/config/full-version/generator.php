<?php

return [
    /**
     * If any input file(image) as default will use options below.
     */
    'image' => [
        /**
         * Path for store the image.
         *
         * Available options:
         * 1. public or storage.public
         * 2. local or storage.local
         * 3. public_path
         * 4. S3
         */
        'disk' => 'public',

        /**
         * Will be used if image is nullable and default value is null.
         */
        'default' => 'https://placehold.co/300?text=No+Image+Available',

        /**
         * Crop the uploaded image using intervention image.
         */
        'crop' => true,

        /**
         * When set to true the uploaded image aspect ratio will still original.
         */
        'aspect_ratio' => true,

        /**
         * Crop image size.
         */
        'width' => 300,
        'height' => 300,
    ],

    'format' => [
        /**
         * Will be used to first year on select, if any column type year.
         */
        'first_year' => 1970,

        /**
         * If any date column type will cast and display used this format, but for input date still will used Y-m-d format.
         *
         * another most common format:
         * - M d Y
         * - d F Y
         * - Y m d
         */
        'date' => 'Y-m-d',

        /**
         * If any input type month will cast and display used this format.
         */
        'month' => 'Y/m',

        /**
         * If any input type time will cast and display used this format.
         */
        'time' => 'H:i',

        /**
         * If any datetime column type or datetime-local on input, will cast and display used this format.
         */
        'datetime' => 'Y-m-d H:i:s',

        /**
         * Limit string on index view for any column type text or long text.
         */
        'limit_text' => 100,
    ],

    /**
     * It will be used for generator to manage and showing menus on sidebar views.
     *
     * Example:
     * [
     *   'header' => 'Main',
     *
     *   // All permissions in menus[] and submenus[]
     *   'permissions' => ['test view'],
     *
     *   menus' => [
     *       [
     *          'title' => 'Main Data',
     *          'icon' => '<i class="bi bi-collection-fill"></i>',
     *          'route' => null,
     *
     *          // permission always null when isset submenus
     *          'permission' => null,
     *
     *          // All permissions on submenus[] and will empty[] when submenus equals to []
     *          'permissions' => ['test view'],
     *
     *          'submenus' => [
     *                 [
     *                     'title' => 'Tests',
     *                     'route' => '/tests',
     *                     'permission' => 'test view'
     *                  ]
     *               ],
     *           ],
     *       ],
     *  ],
     *
     * This code below always changes when you use a generator, and maybe you must format the code.
     */
    'sidebars' => [
        [
            'header' => 'Main',
            'permissions' => ['test view'],
            'menus' => [
                [
                    'title' => 'Main Data',
                    'icon' => '<i class="bi bi-collection-fill"></i>',
                    'route' => null,
                    'permission' => null,
                    'permissions' => ['test view'],
                    'submenus' => [
                        [
                            'title' => 'Tests',
                            'route' => '/tests',
                            'permission' => 'test view',
                        ],
                    ],
                ],
            ],
        ],
        [
            'header' => 'Users',
            'permissions' => ['user view', 'role & permission view'],
            'menus' => [
                [
                    'title' => 'Users',
                    'icon' => '<i class="bi bi-people-fill"></i>',
                    'route' => '/users',
                    'permission' => 'user view',
                    'permissions' => [],
                    'submenus' => [],
                ],
                [
                    'title' => 'Roles & permissions',
                    'icon' => '<i class="bi bi-person-check-fill"></i>',
                    'route' => '/roles',
                    'permission' => 'role & permission view',
                    'permissions' => [],
                    'submenus' => [],
                ],
            ],
        ],
    ],
];
