<a href="{{route('po_settings')}}">
    <button class="btn text-white mb-3 mr-2 {{ isset($title) && $title=='China Cargo' ? 'bg-blue-500' : 'bg-gray-400' }}">China Cargo</button>
</a>
<a href="{{url('domestic_shippers')}}">
    <button class="btn text-white mb-3 mr-2 {{ isset($title) && $title=='Domestic Shipper' ? 'bg-blue-500' : 'bg-gray-400' }}">Domestic Shipper</button>
</a>

