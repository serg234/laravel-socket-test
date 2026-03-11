<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chat with') }} {{ $chatUser->name }}
        </h2>
        <br>
        <a href="{{ route('users.index') }}" style="background-color: yellowgreen;"
           class="inline-flex items-center px-2 py-1 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
            Список користувачів
        </a>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">
                    Діалог з {{ $chatUser->name }}
                </h3>

                <div id="messages-list" class="space-y-3 mb-6">
                    @forelse($messages as $message)
                        <div class="border rounded p-3 {{ $message->sender_id === auth()->id() ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <div class="text-sm text-gray-600 mb-1">
                                <strong>
                                    {{ $message->sender_id === auth()->id() ? 'Ви' : $message->sender->name }}
                                </strong>
                            </div>

                            <div class="text-gray-900">
                                {{ $message->body }}
                            </div>

                            <div class="text-xs text-gray-500 mt-2">
                                {{ $message->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    @empty
                        <p id="empty-messages-text" class="text-gray-500">Повідомлень поки що немає.</p>
                    @endforelse
                </div>

                <form id="message-form" method="POST" action="{{ route('messages.store', $chatUser) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                            Повідомлення
                        </label>

                        <textarea
                            id="body"
                            name="body"
                            rows="4"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            required
                        >{{ old('body') }}</textarea>
                        <div id="body-error" class="text-red-600 text-sm mt-1"></div>

                        @error('body')
                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <button style="background-color: yellowgreen;"
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                        >
                            Надіслати
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.chatConfig = {
            authUserId: {{ auth()->id() }},
            chatUserId: {{ $chatUser->id }},
        };
    </script>

</x-app-layout>
