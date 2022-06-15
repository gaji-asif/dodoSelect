@foreach($orders as $order)
<tr class="hover:bg-gray-100 border-b border-gray-200 py-10 even:bg-gray-100">
	<td class="px-4 py-2 border-2">{{ $order->date->format('d-m-Y') }}</td>
	<td class="px-4 py-2 border-2">{{ $order->buyer }}</td>
	<td class="px-4 py-2 border-2">{{ $order->phone }}</td>
	<td class="px-4 py-2 border-2">{{ $order->shipper }}</td>
	<td class="px-4 py-2 border-2">{{ $order->tracking_id }}</td>
</tr>
@endforeach