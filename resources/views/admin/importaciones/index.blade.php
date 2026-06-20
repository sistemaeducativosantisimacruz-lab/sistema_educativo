@extends('admin.layout')

@section('content')
    <script>window.location.href = "{{ route('admin.importaciones.create') }}";</script>
@endsection
