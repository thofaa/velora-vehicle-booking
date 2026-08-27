<?php

test('guest is redirected from home to login', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('dashboard'));
});
