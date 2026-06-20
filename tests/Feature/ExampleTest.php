<?php

test('the application redirects the guest root path to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
