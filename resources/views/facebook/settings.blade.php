<x-app-layout>
    @section('title')
        {{ __('translation.Lazada Settings') }}
    @endsection

    @push('bottom_css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
    @endpush

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Facebook - Settings'))
        <div class="col-span-12">
        @if(!empty(session('msg')))
            <div class="row">
                <div class="col-lg-12 mt-3">
                    <div class="w-full  col-span-12 md:col-span-12">
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 my-2 rounded relative" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <div class="alert-content">{{ session('msg') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-body">
                        <div class="w-full lg:w-1/4 mb-6 lg:mb-3">
                            <x-button color="green" id="BtnAuth">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="ml-2">
                                    {{ __('translation.Authorize Facebook') }}
                                </span>
                            </x-button> <br />
                        </div>
                        <div class="card-title" style="margin-bottom: 25px;">
                            <h4>
                                <strong>{{ __('translation.List Of Pages') }} @if (isset($data)) ({{count($data)}}) @endif</strong>
                            </h4>
                            <div class="pb-6" id="form-message" style="display:none;"></div>
                        </div>

                        <div class="w-full overflow-x-auto">
                            <table class="table-auto border-collapse w-full border mt-4" id="facebook_pages">
                                <thead class="border bg-gray-500">
                                <tr>
                                    <th class="px-4 py-2 border-2">Id</th>
                                    <th class="px-4 py-2 border-2">Photo</th>
                                    <th class="px-4 py-2 border-2">Name</th>
                                    <th class="px-4 py-2 border-2">Username</th>
                                    <th class="px-4 py-2 border-2">Email</th>
                                    <th class="px-4 py-2 border-2">Autoreply</th>
                                    <th class="px-4 py-2 border-2">PVT reply</th>
                                    <th class="px-4 py-2 border-2" style="width: 170px;">Manage</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if($pages != null)
                                        @php($i=1)
                                        @foreach($pages as $page)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td><img src="{{ $page->page_profile }}" /> </td>
                                                <td>{{ $page->page_name }}</td>
                                                <td>{{ $page->username }}</td>
                                                <td>{{ $page->page_email }}</td>
                                                <td>{{ ucfirst($page->autoreply_enabled) }}</td>
                                                <td>{{ ucfirst($page->private_reply_enabled) }}</td>
                                                <td style="width:10%">
                                                    <button class="btn btn-sm btn-success" type="button" id="{{ $page->id }}" onclick="autoReplyConfig(this.id)"><i class="fa fa-pen"></i></button>&nbsp;
                                                    <button class="btn btn-sm btn-danger" id="{{ $page->id }}" onclick="deletePage(this.id)" type="button"><i class="fa fa-trash"></i> </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Add page autoreply modal -->
    <x-modal.modal-medium id="__modalAutoReply">
        <x-modal.header>
            <x-modal.title>
                {{ ucwords(__('translation.Autoreply Configuration')) }}
            </x-modal.title>
            <x-modal.close-button class="btn-close_edit-autoreply" />
        </x-modal.header>
        <x-modal.body>
            <form method="POST" action="{{ route('facebook.autoreply.campaign') }}">
                @csrf
                <div id="form-autoreply"></div>
                <button class="btn btn-sm btn-success float-right mr-3" type="submit">Submit</button>
                <br />
            </form>
        </x-modal.body>
    </x-modal.modal-medium>

    @push('bottom_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#BtnAuth').click(function() {
                    window.location = '{{route('facebook.auth')}}'
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                $('#facebook_pages').DataTable({
                    processing: true,
                    order: [
                        [0, "asc"]
                    ]
                });

                $('body').on('click', '.btn-close_edit-autoreply', function () {
                    $('#__modalAutoReply').doModal('close');
                });
            });

            function autoReplyConfig(id)
            {
                $('#__modalAutoReply').doModal('open');

                $.ajax({
                    url: '{{ route('facebook.page.edit') }}?id=' + id,
                    beforeSend: function() {
                        $('#form-autoreply').html('Loading');
                    }
                }).done(function(result) {
                    $('#form-autoreply').html(result);
                });
            }

            function deletePage(id)
            {
                let result = confirm("Are you sure?");
                if(result){
                    $.ajax({
                        url: '{{ route('facebook.page.delete', ['id'=>'__fbID__']) }}'.replace('__fbID__', id),
                        type: "POST",
                        data: {'id':id},
                    }).done(function(response) {
                        let formMsg = $('#form-message');
                        formMsg.html('<div class="alert alert-warning" role="alert">'+response.message+'</div>');
                        formMsg.show();
                        location.reload();
                    });
                }
            }
        </script>
    @endpush

</x-app-layout>
