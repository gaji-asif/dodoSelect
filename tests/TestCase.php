<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Session::put('assignedPermissions', []);
    }

    /**
     * Tear down the test environment.
     *
     * @return void
     */
    public function tearDown(): void
    {
        Session::forget('assignedPermissions');
        parent::tearDown();
    }

    /**
     * Create user with specific role
     *
     * @param  string|null  $roleName
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    protected function createUser($roleName = null)
    {
        if (! empty($roleName)) {
            return User::factory([ 'role' => $roleName ])->create();
        }

        return User::factory()->create();
    }
}
