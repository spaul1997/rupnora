@extends('admin.layouts.app')

@section('title', 'Create Offer')

@section('content')
    <x-admin.page-header title="Create Offer" description="Create a dated category-wise or product-wise discount." :breadcrumb="[['label' => 'Discount & Offers', 'url' => route('admin.discount-offers.index')], ['label' => 'Create Offer']]" />

    <form method="POST" action="{{ route('admin.discount-offers.store') }}">
        @include('admin.discount-offers._form')
    </form>
@endsection
