<?php

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ds'])
    ->not->toBeUsed();
