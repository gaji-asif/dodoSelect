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
                <h4><strong>DoDoChat App Activities</strong></h4>
            </div>
            <hr />
            <div class="mt-6">
                <table class="w-full" id="datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Login time</th>
                            <th>Logout time</th>
                            <th>Session Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($i = 1)
                        @foreach($chatLogs as $log)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $log->name }}</td>
                                <td>{{ $log->username }}</td>
                                <td>{{ \Carbon\Carbon::parse($log->login_time)->toDayDateTimeString() }}
                                @if($log->logout_time ===  NULL)
                                    <td><span style="background: green;padding: 5px;color: white;border-radius: 3px;">Online</span></td>
                                    <td>{{\Carbon\Carbon::parse(now())->diffForHumans(\Carbon\Carbon::parse($log->login_time)) }}</td>
                                @else
                                    <td>{{ \Carbon\Carbon::parse($log->logout_time)->toDayDateTimeString() }}</td>
                                    <td>{{\Carbon\Carbon::parse($log->logout_time)->diffForHumans(\Carbon\Carbon::parse($log->login_time)) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </card>
    </x-card>
</x-app-layout>
<script type="text/javascript">
    $(function () {
        $('#datatable').DataTable({
            processing: true,
            order: [[0, "asc"]]
        });
    });
</script>
