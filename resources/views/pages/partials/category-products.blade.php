@foreach ($products as $index => $product)
    @php($metaIndex = ($startIndex ?? 0) + $index)
    <div
        data-product-index="{{ $metaIndex }}"
        x-show="matches(meta[{{ $metaIndex }}])"
        :style="`order: ${rank(meta[{{ $metaIndex }}]) + 100000}`"
    >
        <x-ui.product-card :product="$product" />
    </div>
@endforeach
