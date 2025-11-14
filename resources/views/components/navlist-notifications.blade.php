{{-- resources/views/components/navlist-notifications.blade.php --}}
<div x-data="notificationNav()" x-init="init()" class="relative">
    <flux:navlist.item
        icon="bell"
        :href="route('notifications')"
        :current="request()->routeIs('notifications')"
        wire:navigate
        class="relative"
    >
        <template x-if="unread > 0">
            <span
                class="absolute top-2 right-4 inline-flex items-center justify-center
                       px-1 text-xs font-semibold leading-none text-white bg-red-600
                       rounded-full"
                x-text="unread"
            ></span>
        </template>
        {{ __('Notifications') }}
    </flux:navlist.item>
</div>

<script>
    function notificationNav() {
        return {
            unread: 0,

            init() {
                this.fetchUnread();
                setInterval(() => this.fetchUnread(), 60000);
            },

            async fetchUnread() {
                try {
                    const res = await fetch(@js(route('notifications.unreadCount')));
                    const { unread_count } = await res.json();
                    this.unread = unread_count;
                } catch (e) {
                    console.error('Failed to fetch unread count', e);
                }
            }
        }
    }
</script>
