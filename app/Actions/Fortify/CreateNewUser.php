<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Section;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers {
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $this->syncSections($user, Section::findOrFail($input['section_id']));

        return $user;
    }

    private function syncSections(User $user, Section $section): void {
        $sections = Section::query()
            ->where('section_number', '<', $section->section_number)
            ->orWhere(function ($query) use ($section) {
                $query->where('section_number', '=', $section->section_number)
                    ->where('unit_number', '<=', $section->unit_number);
            })
            ->get();

        foreach ($sections as $section) {
            DB::table('user_section')->updateOrInsert(
                ['user_id' => $user->id, 'section_id' => $section->id],
                ['is_unlocked' => true, 'updated_at' => now(), 'created_at' => now()],
            );

            $wordIds = $section->words()->pluck('words.id');

            $user->words()->syncWithoutDetaching(
                $wordIds->mapWithKeys(fn ($id) => [$id => ['is_available' => true]])->all(),
            );
        }
    }
}
