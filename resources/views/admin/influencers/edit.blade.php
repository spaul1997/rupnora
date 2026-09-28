@extends('admin.layouts.app')

@section('title', 'Edit Influencer')

@section('content')
    <x-admin.page-header title="Edit Influencer" :breadcrumb="[['label' => 'Influencers', 'url' => route('admin.influencers.index')], ['label' => $influencer->full_name]]" />

    <form method="POST" action="{{ route('admin.influencers.update', $influencer) }}" enctype="multipart/form-data">
        @include('admin.influencers._form')
    </form>
@endsection
