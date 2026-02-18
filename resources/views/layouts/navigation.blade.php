<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">

    <div class="flex h-16 items-center justify-between px-4">

        <!-- Hamburger -->
        <button @click="open = true"
            class="p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
            ☰
        </button>

        <!-- Right User Dropdown -->
        <div>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-100 focus:outline-none">
                        {{ Auth::user()->name }}
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        Profile
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            Log Out
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>

    <!-- Sidebar -->
    <div x-show="open"
         x-transition
         class="fixed inset-0 flex z-40">

        <!-- Overlay -->
        <div @click="open = false"
             class="fixed inset-0 bg-black opacity-40">
        </div>

        <!-- Sidebar Panel -->
        <div class="relative flex flex-col w-64 bg-white dark:bg-gray-800 shadow-lg">

           <!-- Sidebar Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-700">
            <div class="flex items-center space-x-2">
                <!-- Logo -->
                <img src="{{ asset('assets/axiomlogo.jpg') }}" alt="Axiom Logo" class="h-10 w-10 object-contain rounded-full">

                <!-- Title -->
            </div>

                <button @click="open = false"
                    class="text-gray-600 dark:text-gray-300 focus:outline-none">
                    ✕
                </button>
            </div>

            <!-- Sidebar Links -->
            <nav class="mt-6 space-y-2">
                <a href="{{ route('dashboard') }}"
                   class="block px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                    Dashboard
                </a>

                <a href="{{ route('taskassignment') }}"
                   class="block px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                    Task Assignment
                </a>

                <a href="{{ route('kanbanteams') }}"
                   class="block px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                    Kanban Boards Teams
                </a>

                <a href="{{ route('taskprogress') }}"
                   class="block px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                    Task Progress Updates
                </a>

                 <a href="{{ route('reminders') }}"
                   class="block px-6 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                     Reminders Implementation
                </a>
            </nav>


        </div>
    </div>

</nav>
