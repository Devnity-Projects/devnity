<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user->load('settings');
        
        return Inertia::render('Settings/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'email_verified_at' => $user->email_verified_at,
            ],
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                \Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            
            $avatarName = time() . '_' . $user->id . '.' . $request->file('avatar')->getClientOriginalExtension();
            
            // Store the file using the public disk
            $uploadedFile = $request->file('avatar')->storeAs('avatars', $avatarName, 'public');
            
            if ($uploadedFile) {
                // Update avatar directly since it's guarded
                $user->avatar = $avatarName;
                \Log::info('Avatar uploaded successfully: ' . $avatarName);
            } else {
                \Log::error('Failed to upload avatar');
                return back()->withErrors(['avatar' => 'Falha ao fazer upload da imagem']);
            }
        }

        // Remove avatar from validated data since we handle it separately
        unset($validated['avatar']);
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->forceFill(['email_verified_at' => null]);
        }

        $user->save();

        // Force reload user data to ensure avatar_url is updated  
        $user->refresh();

        // Use 303 redirect to force Inertia to make a fresh GET request
        return redirect()->route('settings.profile.edit', [], 303)->with('status', 'Perfil atualizado com sucesso!');
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Delete avatar if exists
        if ($user->avatar && \Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            \Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        // Update avatar directly since it's guarded
        $user->avatar = null;
        $user->save();

        return redirect()->route('settings.profile.edit', [], 303)->with('status', 'Avatar removido com sucesso!');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar if exists
        if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
            unlink(storage_path('app/public/avatars/' . $user->avatar));
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
