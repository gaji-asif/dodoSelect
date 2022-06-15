<x-app-layout>
    @section('title', 'Assign Permissions')

    @if(\App\Models\Role::checkRolePermissions('Can access menu: Manage Users - Role Management'))
        <x-card class="mt-0">
        <div class="" style="margin-top: -2rem">
            @include('seller.staff_roles.menu')
        </div>
        <hr>

        <card class="bg-gray-500 ">
            <div class="card-title my-4">
                <h4><strong>Assign Permissions</strong></h4>
            </div>
            <div class="mt-6">
                @if(session('success'))
                    <x-alert-success>{{ session('success') }}</x-alert-success>
                @endif
                @if ($errors->any())
                    <x-alert-danger>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert-danger>
                @endif
            </div>

            <div class="container">
                {!! Form::model($role, ['method' => 'POST','route' => ['assign_permission.save', $role->id]]) !!}
                <div class="row">
                    <div class="w-full">
                        <div class="form-group">
                            <strong class="text-blue-500 mb-3">Role:</strong><br>
                            {!! Form::text('name', null, array('value' => $role->name,'class' => 'form-control', 'disabled' => 'true')) !!}
                        </div>
                    </div>
                    <div class="w-full">
                        <div class="form-group">
                            <strong class="text-blue-500 pt-6">Permissions:</strong>
                            <br/>
                            @foreach($permission as $value)
                                <label class="mt-3">{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name')) }}
                                    {{ $value->name }}</label>
                                <br/>
                            @endforeach
                        </div>
                    </div>
                    <hr>
                    <div class="w-full text-center">
                        <x-button-link href="{{ route('role') }}" color="gray" class="mr-1">
                            {{ __('translation.Back') }}
                        </x-button-link>
                        <x-button type="submit" color="blue">
                            {{ __('translation.Submit') }}
                        </x-button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </card>
    </x-card>
    @endif

    @push('bottom_js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    @endpush

</x-app-layout>
