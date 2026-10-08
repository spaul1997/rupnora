@props([
    'headers' => [],
    'tableClass' => '',
    'columnClasses' => [],
])

<div class="admin-card overflow-hidden">
    <div class="overflow-x-auto">
        <table @class(['admin-table', $tableClass])>
            <thead class="bg-gray-50">
                <tr>
                    @foreach ($headers as $header)
                        <th @class([
                            $columnClasses[$loop->index] ?? '',
                            'text-right' => str_starts_with($header, '!'),
                        ])>{{ ltrim($header, '!') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @isset($footer)
        {{ $footer }}
    @endisset
</div>
