<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(): View
    {
        return view('accounts.index', ['kind' => 'users', 'records' => User::orderBy('full_name')->get()]);
    }

    public function create(): View
    {
        return view('accounts.form', ['kind' => 'users', 'record' => new User]);
    }

    public function edit(User $user): View
    {
        return view('accounts.form', ['kind' => 'users', 'record' => $user]);
    }

    public function store(UserRequest $request, AvatarService $avatars): RedirectResponse
    {
        $this->save($request, new User, $avatars);

        return to_route('users.index')->with('success', 'User created.');
    }

    public function update(UserRequest $request, User $user, AvatarService $avatars): RedirectResponse
    {
        $this->save($request, $user, $avatars);

        return to_route('users.index')->with('success', 'User updated.');
    }

    private function save(UserRequest $request, User $user, AvatarService $avatars): void
    {
        $data = $request->safe()->except('avatar');
        $newPath = null;
        $oldPath = null;
        try {
            if ($request->hasFile('avatar')) {
                $newPath = $avatars->store($request->file('avatar'));
                $data['avatar'] = $newPath;
            }
            DB::transaction(function () use ($user, $data, &$oldPath): void {
                // Lock the current row so concurrent replacements clean up the right photo.
                $account = $user->exists ? User::whereKey($user->id)->lockForUpdate()->firstOrFail() : $user;
                $oldPath = $account->avatar;
                $account->fill($data)->saveOrFail();
            });
        } catch (Throwable $exception) {
            $avatars->delete($newPath);
            report($exception);
            throw ValidationException::withMessages([
                $request->hasFile('avatar') ? 'avatar' : 'username' => 'We could not save this account. Your existing details and photo are safe. Please try again.',
            ]);
        }
        if ($newPath) {
            $avatars->delete($oldPath);
        }
    }
}
