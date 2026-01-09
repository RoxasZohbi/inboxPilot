@extends('layouts.dashboard')

@section('title', 'Page Title')

@section('content')
<h1>Dashboard Page</h1>
@endsection

@push('scripts')
    <script>
        // Your page-specific JavaScript code here
        console.log('This script runs only on this page');
        
        document.addEventListener('DOMContentLoaded', function() {
            // Your DOM manipulation code
        });
    </script>
@endpush