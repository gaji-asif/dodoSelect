<x-app-layout>
    @section('title', 'Dashboard')

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style type="text/css">
        a:hover{
            text-decoration: none;
        }
        .bi bi-archive{
            font-weight: bold !important;
        }
    </style>


    {{-- <x-card title="Daily Summary" md="8">
        <div class="overflow-x-auto">
            <canvas class="h-16" id="chart" style=""></canvas>
        </div>
    </x-card> --}}

    <div class="w-full overflow-hidden col-span-12 md:col-span-4">
        <div>
            
       <!--  <div style="clear: both; margin-top: 20px;">
            <div class="card mb-3" style="text-align: center;">
              <div style="background-color: rgba(59,130,246); color: #FFFFFF;" class="card-header">Total Product</div>
              <div class="card-body text-success">
                <h5 class="card-title">0</h5>
               
            </div>
        </div>
    </div> -->

        <div class="flex flex-row w-full bg-white shadow-sm rounded p-4">
                    <div
                    class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-xl bg-blue-100 text-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                    <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </div>
            <div class="flex flex-col justify-center flex-grow ml-4">
                <div class="text-md text-gray-500">Total Products</div>
                <div class="font-bold text-xl">
                    {{ count($products) }}
                </div>
            </div>
        </div>


        {{-- <div class="flex flex-row w-full bg-white shadow-sm rounded p-4 mt-4">
                    <div
                    class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-xl bg-blue-100 text-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-person-bounding-box" viewBox="0 0 16 16">
            <path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"/>
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
            </svg>
            </div>
            <div class="flex flex-col justify-center flex-grow ml-4">
                <div class="text-md text-gray-500">Total Sellers</div>
                <div class="font-bold text-xl">
                    {{ count($sellers) }}
                </div>
            </div>
        </div> --}}


{{-- 
        <div class="flex flex-row w-full bg-white shadow-sm rounded p-4 mt-4">
                    <div
                    class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-xl bg-blue-100 text-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-emoji-sunglasses" viewBox="0 0 16 16">
            <path d="M4.968 9.75a.5.5 0 1 0-.866.5A4.498 4.498 0 0 0 8 12.5a4.5 4.5 0 0 0 3.898-2.25.5.5 0 1 0-.866-.5A3.498 3.498 0 0 1 8 11.5a3.498 3.498 0 0 1-3.032-1.75zM7 5.116V5a1 1 0 0 0-1-1H3.28a1 1 0 0 0-.97 1.243l.311 1.242A2 2 0 0 0 4.561 8H5a2 2 0 0 0 1.994-1.839A2.99 2.99 0 0 1 8 6c.393 0 .74.064 1.006.161A2 2 0 0 0 11 8h.438a2 2 0 0 0 1.94-1.515l.311-1.242A1 1 0 0 0 12.72 4H10a1 1 0 0 0-1 1v.116A4.22 4.22 0 0 0 8 5c-.35 0-.69.04-1 .116z"/>
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-1 0A7 7 0 1 0 1 8a7 7 0 0 0 14 0z"/>
            </svg>
            </div>
            <div class="flex flex-col justify-center flex-grow ml-4">
                <div class="text-md text-gray-500">Total Staffs</div>
                <div class="font-bold text-xl">
                    {{ count($staffs) }}
                </div>
            </div>
        </div>

        <div class="flex flex-row w-full bg-white shadow-sm rounded p-4 mt-4">
            <div class="flex items-center justify-center flex-shrink-0 h-16 w-16 rounded-xl bg-blue-100 text-blue-500">
                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3B82F6">
                    <path d="M0 0h24v24H0V0z" fill="none" />
                    <path
                    d="M15.55 13c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h12v-2H7l1.1-2h7.45zM6.16 6h12.15l-2.76 5H8.53L6.16 6zM7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                </svg>
            </div>
            <div class="flex flex-col justify-center flex-grow ml-4">
                <div class="text-md text-gray-500">Total Order in Last 7 Days</div>
                <div class="font-bold text-xl">
                    {{ $orders }}
                </div>
            </div>
        </div> --}}


</div>
</div>

    
    </x-app-layout>
