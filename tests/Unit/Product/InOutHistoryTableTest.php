<?php

namespace Tests\Unit\Product;

use App\Models\User;
use Tests\TestCase;

class InOutHistoryTableTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'is_active' => User::ACTIVE,
            'role' => User::ROLE_MEMBER
        ]);

        $this->actingAs($user);
    }


    public function test_datatable_should_render_successfully()
    {
        $response = $this->get(route('in-out-datatable'));

        $response->assertStatus(200)
                ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_sort_by_id_column()
    {
        $response = $this->get(route('in-out-datatable'), [
            'order' => [
                [
                    'column' => 0,
                    'dir' => 1
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_sort_by_product_name_column()
    {
        $response = $this->get(route('in-out-datatable'), [
            'order' => [
                [
                    'column' => 1,
                    'dir' => 1
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_sort_by_date_time_column()
    {
        $response = $this->get(route('in-out-datatable'), [
            'order' => [
                [
                    'column' => 2,
                    'dir' => 1
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_sort_by_in_out_column()
    {
        $response = $this->get(route('in-out-datatable'), [
            'order' => [
                [
                    'column' => 3,
                    'dir' => 1
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_sort_by_quantity_column()
    {
        $response = $this->get(route('in-out-datatable'), [
            'order' => [
                [
                    'column' => 4,
                    'dir' => 1
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_sort_by_user_column()
    {
        $response = $this->get(route('in-out-datatable'), [
            'order' => [
                [
                    'column' => 5,
                    'dir' => 1
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }


    public function test_rendered_successfully_when_search_something()
    {
        $response = $this->get(route('in-out-datatable'), [
            'search' => [
                'value' => 'I am searching something'
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }
}
