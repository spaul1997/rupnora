@extends('admin.layouts.app')

@section('title', 'Edit FAQ')

@section('content')
    <x-admin.page-header title="Edit FAQ" :breadcrumb="[['label' => 'FAQ', 'url' => route('admin.faqs.index')], ['label' => 'Edit FAQ']]" />

    <form method="POST" action="{{ route('admin.faqs.update', $faq) }}">
        @include('admin.faqs._form')
    </form>
@endsection
