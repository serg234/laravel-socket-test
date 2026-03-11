<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Список користувачів</h3>

                @if($users->isEmpty())
                    <p>Інших користувачів поки що немає.</p>
                @else
                    <div class="space-y-3">
                        @foreach($users as $user)
                            <div style="margin-bottom: 20px" class="flex items-center justify-between border rounded p-3">
                                <div>
                                    <div class="font-medium">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>

                                <div>
                                    <a href="{{ route('messages.show', $user) }}" style="background-color: yellowgreen;"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        Написати
                                    </a>
                                </div>
                            </div>
                        @endforeach

                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
