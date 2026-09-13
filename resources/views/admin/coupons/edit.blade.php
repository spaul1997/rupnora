@extends('admin.layouts.app')

@section('title', 'Edit Coupon')

@section('content')
    <x-admin.page-header title="Edit Coupon" :breadcrumb="[['label' => 'Coupons', 'url' => route('admin.coupons.index')], ['label' => $coupon->code]]" />

    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
        @include('admin.coupons._form')
    </form>
@endsection
