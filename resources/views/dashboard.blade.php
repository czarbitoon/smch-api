@extends('layouts.app')

@section('content')
    <!-- Your content goes here -->
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Device 1</h5>
                        <p class="card-text">Device Description</p>
                        <a href="{{ route('device.show', 1) }}" class="btn btn-primary">View Device</a>
                        <a href="{{ route('report.create', 1) }}" class="btn btn-secondary">Report Issue</a>
                    </div>
                </div>
            </div>
            <!-- Add more cards for other devices -->
        </div>
    </div>
@endsection
