<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_full_name_accessor_returns_name(): void
    {
        $user = new User(['name' => 'Jane Doe']);

        $this->assertSame('Jane Doe', $user->full_name);
    }

    public function test_first_name_accessor_returns_first_token(): void
    {
        $user = new User(['name' => 'Jane Mary Doe']);

        $this->assertSame('Jane', $user->first_name);
    }

    public function test_last_name_accessor_returns_remaining_tokens(): void
    {
        $user = new User(['name' => 'Jane Mary Doe']);

        $this->assertSame('Mary Doe', $user->last_name);
    }

    public function test_last_name_accessor_returns_empty_string_when_single_name(): void
    {
        $user = new User(['name' => 'Plato']);

        $this->assertSame('', $user->last_name);
    }
}
