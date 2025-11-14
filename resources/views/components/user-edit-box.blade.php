{{-- resources/views/components/user-edit-box.blade.php --}}
<div id="userEdit-{{ $user->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    aria-hidden="true">
    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-xl w-full max-w-2xl overflow-auto p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-neutral-700">
            <h5 class="text-lg font-semibold text-gray-800 dark:text-neutral-100">
                Edit User Details
            </h5>
            <button type="button"
                class="text-gray-500 hover:text-gray-800 dark:hover:text-neutral-200 focus:outline-none"
                data-modal-hide="userEdit-{{ $user->id }}">✕</button>
        </div>

        {{-- Body --}}
        <form action="/admin/user-edit/{{ $user->id }}" method="POST" class="py-2 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Full Name --}}
                <div>
                    <label for="name_edit" class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">
                        Full Name:
                    </label>
                    <input type="text" name="name" id="name_edit" value="{{ old('name', $user->name) }}"
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email_edit"
                        class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        Email:
                    </label>
                    <input type="email" name="email" id="email_edit" value="{{ old('email', $user->email) }}"
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300"
                        required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 py-6">
                {{-- New Password --}}
                <div>
                    <label for="password_edit"
                        class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        New Password (optional):
                    </label>
                    <input type="password" name="password" id="password_edit"
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                </div>

                {{-- Confirm New Password --}}
                <div>
                    <label for="password_confirmation_edit"
                        class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        Confirm New Password:
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation_edit"
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                </div>
            </div>

            {{-- Select Role --}}
            <div class="md:col-span-2">
                <label for="role_id_edit" class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                    Select Role:
                </label>
                <div class="space-y-2">
                    @foreach ($roles as $role)
                        @if ($role->id < 3)
                            <label class="inline-flex items-center space-x-2">
                                <input type="radio" name="role_id" value="{{ $role->id }}"
                                    {{ old('role_id', $user->role_id) == $role->id ? 'checked' : '' }}
                                    class="form-radio h-4 w-8 text-blue-600 focus:ring-blue-500">
                                <span class="text-gray-700 dark:text-neutral-200">{{ $role->name }}</span>
                            </label>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end space-x-3 border-t border-gray-200 dark:border-neutral-700 py-2">
                <button type="button" data-modal-hide="userEdit-{{ $user->id }}"
                    class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring focus:ring-gray-300">Close</button>
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300">Save
                    Changes</button>
            </div>
        </form>
    </div>
</div>


{{-- Inline JS to open/close each user’s edit modal --}}
<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Open buttons: data-modal-target="userEdit-{{ $user->id }}"
        document.querySelectorAll('[data-modal-target]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = btn.getAttribute('data-modal-target');
                const modal = document.getElementById(targetId);
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        // Close buttons: data-modal-hide="userEdit-{{ $user->id }}"
        document.querySelectorAll('[data-modal-hide]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = btn.getAttribute('data-modal-hide');
                const modal = document.getElementById(targetId);
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });

        // Clicking outside the content closes the modal
        document.querySelectorAll('[id^="userEdit-"]').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    });
</script>
