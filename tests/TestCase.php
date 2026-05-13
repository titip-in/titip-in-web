<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $fakeVector = array_fill(0, 768, 0.1);

        Str::macro('toEmbeddings', function () use ($fakeVector) {
            return $fakeVector;
        });

        Stringable::macro('toEmbeddings', function () use ($fakeVector) {
            return $fakeVector;
        });
    }
}