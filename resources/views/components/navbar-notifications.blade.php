{{-- Notifications Icon with Dropdown --}}
<div x-data="notificationWidget()" x-init="init()" class="relative">
    {{-- Icon & badge --}}
    <flux:tooltip :content="__('Notifications')" position="bottom">
        <flux:navbar.item
            @click="open = !open"
            class="h-10 max-lg:hidden [&>div>svg]:size-5"
            icon="bell"
            :label="__('Notifications')"
        >
            <template x-if="unread > 0">
                <span class="absolute top-1 right-1 inline-flex items-center justify-center
                             px-1 text-xs font-semibold leading-none text-white bg-red-600
                             rounded-full" x-text="unread"></span>
            </template>
        </flux:navbar.item>
    </flux:tooltip>

    {{-- Dropdown Panel --}}
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 mt-2 w-80 z-50 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-xl"
    >
        <div class="p-4 space-y-3 text-sm text-gray-700 dark:text-gray-300">
            <div class="flex items-center justify-between">
                <a href="{{ route('notifications') }}">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white underline">Notifications</h3>
                </a>
                <button
                    @click="markAllRead()"
                    class="text-xs text-blue-600 hover:underline dark:text-blue-400"
                >Mark all read</button>
            </div>

            <template x-if="items.length === 0">
                <p class="text-center text-gray-500 dark:text-gray-400">No notifications.</p>
            </template>

            <template x-for="notif in items" :key="notif.id">
                <div class="flex items-start gap-2 py-2 border-b last:border-b-0">
                    <span
                      :class="{
                        'text-blue-500': notif.level === 'info',
                        'text-yellow-500': notif.level === 'warning',
                        'text-red-500': notif.level === 'error'
                      }"
                      class="size-4"
                    >●</span>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white" x-text="notif.title"></p>
                        <p x-text="notif.message"></p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500" x-text="notif.timestamp"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function notificationWidget() {
        return {
            open: false,
            unread: 0,
            items: [],

            init() {
                // initial fetch
                this.fetch();

                // poll every 60s
                setInterval(() => this.fetch(), 60000);

                // listen for real-time pushes
                if (window.Echo) {
                    // 1) Global notifications channel
                    Echo.channel('notifications')
                        .listen('NotificationCreated', e => {
                            this.items.unshift(e);
                            this.unread++;
                        });

                    // 2) Private per-user channel
                    const userId = @js(auth()->id());
                    Echo.private(`notifications.${userId}`)
                        .listen('NotificationCreated', e => {
                            this.items.unshift(e);
                            this.unread++;
                        });
                }
            },

            async fetch() {
                try {
                    const res = await fetch(@js(route('notifications.recent')));
                    const json = await res.json();
                    this.unread = json.unread_count;
                    this.items  = json.notifications;
                } catch (err) {
                    console.error('Failed to fetch notifications', err);
                }
            },

            async markAllRead() {
                try {
                    await fetch(@js(route('notifications.markAllRead')), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content }
                    });
                    // mark client-side
                    this.unread = 0;
                    this.items.forEach(n => n.is_read = true);
                } catch (err) {
                    console.error('Failed to mark all read', err);
                }
            }
        }
    }
</script>
