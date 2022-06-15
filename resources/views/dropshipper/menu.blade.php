<a href="{{route('dropshippers')}}">
    <button class="btn mb-3 mr-2 {{ isset($title) && $title=='dropshippers' ? 'text-white bg-blue-500' : 'bg-gray-200 border' }}">Manage Dropshipper</button>
</a>
<a href="{{route('dropshipper.role')}}">
    <button class="btn mb-3 mr-2 {{ isset($title) && $title=='dropshipper role' ? 'text-white bg-blue-500' : 'bg-gray-200 border' }}">Role Management</button>
</a>
<a href="{{route('dropshipper.orders')}}">
    <button class="btn mb-3 mr-2 {{ isset($title) && $title=='dropshipper orders' ? 'text-white bg-blue-500' : 'bg-gray-200 border' }}">Dropshipper Orders</button>
</a>



