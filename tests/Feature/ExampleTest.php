<?php

test('health endpoint is available', function () {
    $this->get('/up')->assertSuccessful();
});
