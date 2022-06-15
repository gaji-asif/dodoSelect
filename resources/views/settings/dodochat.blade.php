<x-app-layout>
    @section('title', 'DoDoChat')
    @push('top_css')
        <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css" rel="stylesheet">

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet" href="{{ asset('css/typeaheadjs.css') }}">
    @endpush
    <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('settings.menu')
        </div>
        <hr>

        <card>
            <div class="card-title my-4">
                <h4><strong>DoDoChat App</strong></h4>
            </div>
            <div class="mt-6">
                <h6><strong>Current version: </strong>1.0</h6>
                <h5>No new version is available</h5>
            </div>
        </card>
    </x-card>
</x-app-layout>
