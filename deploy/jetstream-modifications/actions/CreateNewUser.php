<?php

namespace App\Actions\Fortify;

use Uneca\Chimera\Models\Invitation;
use Uneca\Chimera\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input)
    {
        $input = array_merge($input, ['email' => Crypt::decryptString($input['invited_email'])]);
        Validator::make(
            $input,
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 'string', 'email', 'max:255', 'unique:users',
                    Rule::exists('invitations')->where(function ($query) use ($input) {
                        return $query->where('email', $input['email']);
                    })
                ],
                'password' => $this->passwordRules(),
                'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            ],
            ['email.exists' => 'Your email could not be found among the valid invites.']
        )->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'is_suspended' => config('chimera.require_account_approval'),
            ]);

            $invitation = Invitation::where('email', $input['email'])->firstOrFail();
            if (! empty($invitation->role)) {
                try {
                    Role::findByName($invitation->role);
                    $user->assignRole($invitation->role);
                } catch (RoleDoesNotExist $exception) {
                    // Do nothing
                }
            }
            if (! empty($invitation->areaRestriction)) {
                //$user->imposeAreRestriction($invitation->areaRestriction);
                $user->areaRestrictions()->create([
                    'path' => $invitation->areaRestriction
                ]);
            }
            $invitation->delete();

            return $user;
        });
    }
}
