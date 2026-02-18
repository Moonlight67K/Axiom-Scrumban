<x-app-layout>
    <x-sidebar />

    <div class="flex-1 flex flex-col p-6">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Dashboard</h2>
        <p class="mt-4 text-gray-700 dark:text-gray-300">
            Welcome, {{ Auth::user()->name }}! You're logged in.
        </p>
    </div>
</x-app-layout>
