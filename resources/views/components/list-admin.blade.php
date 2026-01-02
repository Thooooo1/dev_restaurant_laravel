<div class="flex gap-2 mb-4">
    @foreach ($admins as $item)
        <div class="relative">
            <div>
                <img src="{{ asset('/images/user/' . ($item->image ?? 'default.png')) }}" 
                    class="w-12 h-12 rounded-full border ml-4">
                <span class="status-indicator"
                    style="background-color: {{$item->line ? '#22c55e' : '#ff0000' }};"></span>
            </div>

            <span>
                {{$item->fullname}}
            </span>
        </div>

       @endforeach
</div>