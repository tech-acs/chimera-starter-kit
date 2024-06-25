@if ($errors->any())
    <div class="rounded-md bg-red-100 p-4 py-3 my-2 border border-red-400">
        <div class="flex">
            <div class="flex-shrink-0">
                <!-- Heroicon name: x-circle -->
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"></path>
                </svg>
            </div>
            <div class="ml-3  text-sm text-red-700">
                <p class="mb-1 font-semibold">There were {{ $errors->count() }} errors found</p>
                <ul class="ml-4">
                    @foreach($errors->all() as $error)
                        <li class="list-disc">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
