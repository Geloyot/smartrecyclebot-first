{{-- resources/views/components/user-add-box.blade.php --}}
<div id="userAdd" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" aria-hidden="true">
    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-xl w-full max-w-2xl mx-4">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <h5 class="text-lg font-semibold text-gray-800 dark:text-neutral-100">
                Add User to Database
            </h5>
            <button type="button"
                class="text-gray-500 hover:text-gray-800 dark:hover:text-neutral-200 focus:outline-none"
                data-modal-hide="userAdd">
                ✕
            </button>
        </div>

        {{-- Modal Body --}}
        <form action="/admin/user-add" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-neutral-200 mb-1">
                        Full Name:
                    </label>
                    <input type="text" name="name" id="name" placeholder="Enter full name..."
                        value="{{ old('name') }}"
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                </div>
                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        Email:
                    </label>
                    <input type="email" name="email" id="email" placeholder="Enter email address..."
                        value="{{ old('email') }}" required
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        Password:
                    </label>
                    <input type="password" name="password" id="password" placeholder="Enter password..." required
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation"
                        class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        Confirm Password:
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        placeholder="Re-enter password..." required
                        class="block w-full border border-gray-300 dark:border-neutral-700 rounded-md bg-white dark:bg-neutral-800 text-gray-900 dark:text-neutral-100 px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Select Role --}}
                <div class="md:col-span-2">
                    <label for="role_id" class="block text-sm font-semibold text-gray-700 dark:text-neutral-200 mb-1">
                        Select Role:
                    </label>
                    <div class="space-y-2">
                        @foreach ($roles as $role)
                            @if ($role->id < 3)
                                <label class="inline-flex items-center space-x-2">
                                    <input type="radio" name="role_id" value="{{ $role->id }}"
                                        {{ $role->id == old('role_id', 1) ? 'checked' : '' }}
                                        class="form-radio h-4 w-8 text-blue-600 focus:ring-blue-500">
                                    <span class="text-gray-700 dark:text-neutral-200">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-neutral-700">
                <button type="button" data-modal-hide="userAdd"
                    class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring focus:ring-gray-300">
                    Close
                </button>
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300">
                    Register User
                </button>
            </div>
        </form>
    </div>
</div>

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
        const modalId = 'userAdd';
        const modal   = document.getElementById(modalId);
        const openBtn = document.getElementById('openUserModal');

        if (openBtn && modal) {
            openBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        }

        // Close buttons
        document.querySelectorAll(`[data-modal-hide="${modalId}"]`)
            .forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            });

        // Click outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
</script>

