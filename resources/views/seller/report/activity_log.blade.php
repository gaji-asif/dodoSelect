<x-app-layout>
    @section('title', 'Manage Product Cost')

    @push('top_css')
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    <style>
        table.dataTable tbody td img{
            padding: 0;
        }
    </style>

    {{-- <a href="{{asset('qrcode.svg')}}" download><img src="{{asset('qrcode.svg')}}" alt=""></a> --}}
    @if (\App\Models\Role::checkRolePermissions('Can access menu: Report - Activity Log'))
        <x-card title="Activity Log">
            <div class="flex justify-between flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full" id="datatable">
                        <thead>
                        <tr>
                        <tr>
                            <th>
                                {{ __('translation.ID') }}
                            </th>
                            <th>
                                {{ __('translation.Action') }}
                            </th>
                            <th>
                                {{ __('translation.Product Name') }}
                            </th>
                            <th>
                                {{ __('translation.Product Code') }}
                            </th>
                            <th>
                                {{ __('translation.Quantity') }}
                            </th>
                            <th>
                                {{ __('translation.User Name') }}
                            </th>
                            <th>
                                {{ __('translation.Date') }}
                            </th>
                            <th>
                                {{ __('translation.Action') }}
                            </th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
        <script>
            // $(document).ready(function() {
                const activityLogTableUrl = '{{ route('activity_log.data') }}';

                var activityLogTable = $('#datatable').DataTable({
                    // $('#datatable').DataTable({
                    serverSide: true,
                    processing: true,
                    pagingType: 'numbers',
                    ajax: {
                        type: 'GET',
                        url: activityLogTableUrl
                    },
                    order: [
                        [ 0, 'desc' ]
                    ],
                });

                const undoActivityLog = (el) => {
                    let id = el.getAttribute('data-id');
                    let drop = confirm('Are you sure you want to undo it?');

                    if (drop) {
                        $.ajax({
                            url: '{{ route('activity_log.undo') }}',
                            type: 'post',
                            data: {
                                'id': id,
                                '_token': $('meta[name=csrf-token]').attr('content')
                            }
                        }).done(function(result) {
                            if (result.status === 1) {
                                alert('Data retrieved successful.');
                                location.reload();
                            } else {
                                alert(result.message);
                            }
                        });
                    }
                }
            // });
        </script>
    @endpush
</x-app-layout>
