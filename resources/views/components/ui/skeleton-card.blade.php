@props(['count' => 8])

@for ($i = 0; $i < $count; $i++)
    <div class="space-y-3.5">
        <div class="skeleton aspect-square rounded-2xl"></div>
        <div class="skeleton h-3 w-1/3 rounded"></div>
        <div class="skeleton h-4 w-4/5 rounded"></div>
        <div class="skeleton h-3 w-2/5 rounded"></div>
        <div class="skeleton h-4 w-1/2 rounded"></div>
    </div>
@endfor
