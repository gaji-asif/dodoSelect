<a href="{{route('staff.manage')}}">
    <button class="btn mb-3 mr-2 {{ isset($title) && $title=='users' ? 'text-white bg-blue-500' : 'bg-gray-200 border' }}">Manage Users</button>
</a>
<a href="{{route('role')}}">
    <button class="btn mb-3 mr-2 {{ isset($title) && $title=='role' ? 'text-white bg-blue-500' : 'bg-gray-200 border' }}">Role Management</button>
</a>
<a href="{{route('staff.permissions')}}">
    <button class="btn mb-3 mr-2 {{ isset($title) && $title=='permission' ? 'text-white bg-blue-500' : 'bg-gray-200 border' }}">Permissions</button>
</a>


