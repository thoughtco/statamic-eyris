<?php

namespace Thoughtco\StatamicAgency\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Statamic\Facades\User;

class LoginController
{
    public function generateLink(Request $request)
    {
        if (! $email = $request->input('email')) {
            return [
                'error' => 'invalid_email',
            ];
        }

        if (! $user = User::findByEmail($email)) {
            $blueprint = User::make()->blueprint();

            $user = User::make()
                ->email($email)
                ->password(Str::random(24))
                ->makeSuper(true);

            if ($blueprint->hasField('name')) {
                $user->set('name', $request->input('name'));
            } else {
                $user->set('first_name', Str::before($request->input('name'), ' '));
                $user->set('last_name', Str::after($request->input('name'), ' '));
            }

            $user->save();
        }

        $id = Str::random(32);

        Cache::put('statamic-agency::'.$id, $user->id(), now()->addMinutes(3));

        return [
            'url' => URL::temporarySignedRoute('statamic-agency.login', now()->addMinutes(63), ['id' => $id]),
        ];
    }

    public function login(Request $request, string $id)
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        if (! $userId = Cache::pull('statamic-agency::'.$id)) {
            abort(404);
        }

        if (! $user = User::find($userId)) {
            abort(404);
        }

        Auth::guard(config('statamic.users.guards.cp', 'web'))->login($user);

        // give the stache a moment
        sleep(1);

        return redirect(cp_route('index'));
    }
}
