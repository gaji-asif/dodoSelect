<x-app-layout>
    @section('title', 'User Logo')

        <x-card title="User Logo" md="5">
            <form method="POST" class="mx-2" action={{route('upload user logo')}} enctype="multipart/form-data">
                @csrf
                @if ($errors->any())
                    <div class="mb-4">
                        <div class="font-medium text-red-600">
                            {{ __('translation.Oops! Ada yang salah.') }}
                        </div>

                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session()->has('success'))
                    <div class="mb-4 text-md font-medium text-green-600">
                        {{ session()->get('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 text-md font-medium text-red-600">
                        {{ session()->get('error') }}
                    </div>
                @endif
                <div class="mt-4">
                    <x-label>
                      User
                    </x-label>
                    <x-select name="user_id" id="shipper">
                      <option disabled selected value="0">Select User</option>
                      @foreach ($users as $user)
                      <option value="{{ $user->id }}"> {{ $user->name }} </option>
                      @endforeach
                    </x-select>
                  </div>
                  <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Upload Logo
                    </label>
                    <input type="file" class="block mt-1 w-full" name="logo" id="logo">
                </div>

                <div class="flex justify-end mt-4">
                    <x-button tyoe="submit" color="blue">
                        Update Profile
                    </x-button>
                </div>
            </form>
        </x-card>


    </x-app-layout>
