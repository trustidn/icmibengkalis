<?php

namespace Tests;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Memo statis bertahan antar-test dalam satu proses; DB-nya tidak.
        SiteSetting::forgetCurrent();
    }
}
