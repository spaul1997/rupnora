@extends('admin.layouts.app')

@section('title', 'Add Coupon')

@section('content')
    <x-admin.page-header title="Add Coupon" :breadcrumb="[['label' => 'Coupons', 'url' => route('admin.coupons.index')], ['label' => 'Add Coupon']]" />

    <form method="POST" action="{{ route('admin.coupons.store') }}">
        @include('admin.coupons._form')
    </form>
@endsection
