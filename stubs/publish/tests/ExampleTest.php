<?php

use function Pest\Laravel\get;

it(description: 'returns a successful response', closure: function (): void {
    get(uri: '/')->assertRedirect();
});
