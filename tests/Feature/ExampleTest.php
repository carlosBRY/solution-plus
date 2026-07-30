<?php

it('returns a redirect to login response', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
