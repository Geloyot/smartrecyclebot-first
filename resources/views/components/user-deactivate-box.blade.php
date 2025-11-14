{{-- resources/views/components/user-deactivate-box.blade.php --}}
<div id="userDeactivate-{{ $user->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
    aria-hidden="true">
    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-xl w-full max-w-md overflow-auto p-6">
        <form action="/admin/user-deactivate/{{ $user->id }}" method="POST" class="space-y-4">
            @csrf
            {{-- Header --}}
            <div class="flex items-center justify-between border-b pb-3 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-100">
                    {{ $user->status == 'Deactivated' ? "Activate" : "Deactivate"}} {{ $user->name }}
                </h2>
                <button type="button" onclick="toggleModal('userDeactivate-{{ $user->id }}')"
                    class="text-gray-500 hover:text-gray-800 dark:hover:text-neutral-200 focus:outline-none">✕</button>
            </div>

            {{-- Confirmation Text --}}
            <div class="text-sm text-gray-700 dark:text-neutral-300 py-6">
                Are you sure you want to {{ $user->status == 'Deactivated' ? "activate" : "deactivate"}} this user?<br>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end space-x-3 pt-4 border-t dark:border-neutral-700">
                <button type="button" onclick="toggleModal('userDeactivate-{{ $user->id }}')"
                    class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring focus:ring-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="{{ $user->status == 'Deactivated' ? 'bg-yellow-200 hover:bg-yellow-400 text-black' : 'bg-red-600 hover:bg-red-800 text-white'}} px-4 py-2 rounded-md focus:outline-none focus:ring focus:ring-red-300">
                    Confirm {{ $user->status == 'Deactivated' ? "Activation" : "Deactivation"}}<br>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.toggle('hidden');
        modal.classList.toggle('flex');
    }
</script>
