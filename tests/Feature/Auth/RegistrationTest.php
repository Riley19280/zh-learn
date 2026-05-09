<?php

namespace Tests\Feature\Auth;

use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered() {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register() {
        $sectionOne = Section::factory()->create(['unit_number' => 1, 'section_number' => 1]);
        $sectionTwo = Section::factory()->create(['unit_number' => 2, 'section_number' => 1]);

        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'section_id' => $sectionOne->id,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertDatabaseHas('user_section', [
            'user_id' => $user->id,
            'section_id' => $sectionOne->id,
            'is_unlocked' => true,
        ]);

        $this->assertDatabaseMissing('user_section', [
            'user_id' => $user->id,
            'section_id' => $sectionTwo->id,
        ]);
    }
}
