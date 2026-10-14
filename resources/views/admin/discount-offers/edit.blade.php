@extends('admin.layouts.app')

@section('title', 'Edit Offer')

@section('content')
    <x-admin.page-header title="Edit Offer" description="Update {{ $offer->reason }} and its product assignments." :breadcrumb="[['label' => 'Discount & Offers', 'url' => route('admin.discount-offers.index')], ['label' => 'Edit Offer']]" />

    <form method="POST" action="{{ route('admin.discount-offers.update', $offer) }}">
        @include('admin.discount-offers._form')
    </form>
@endsection
