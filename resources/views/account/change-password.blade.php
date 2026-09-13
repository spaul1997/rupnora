<x-layouts.app :title="$title">
    <x-account.shell active="change-password">
        <h1 class="font-display text-2xl text-charcoal sm:text-3xl">Change Password</h1>
        <p class="mt-2 text-sm text-muted">Choose a strong password you haven't used before.</p>

        <form class="mt-8 max-w-md space-y-5" onsubmit="event.preventDefault(); Alpine.store('ui').notify('Password updated successfully', 'success'); this.reset()">
            <div>
                <label class="label-luxe">Current Password</label>
                <input type="password" required class="input-luxe" placeholder="Enter current password">
            </div>
            <div>
                <label class="label-luxe">New Password</label>
                <input type="password" required minlength="8" class="input-luxe" placeholder="Minimum 8 characters">
            </div>
            <div>
                <label class="label-luxe">Confirm New Password</label>
                <input type="password" required minlength="8" class="input-luxe" placeholder="Re-enter new password">
            </div>
            <button type="submit" class="btn-primary">Update Password</button>
        </form>
    </x-account.shell>
</x-layouts.app>
