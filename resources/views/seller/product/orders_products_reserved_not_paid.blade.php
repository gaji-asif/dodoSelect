<div class="mt-2">
  <p>Product Name:</p>
  <h6><strong>{{$product_names->product_name}}</strong></h6>

  <p>Ordered Products:</p>
  <table class="table table-striped tbl_border">
    <thead>
      <tr class="table-success">
        <th scope="col">Order ID</th>
        <th scope="col">Date / Time</th>
        <th scope="col">Quantity</th>
      </tr>
    </thead>
    <tbody>
      @if(count($orders)>0)
      @if(isset($orders))
      @foreach($orders as $rows)
      <tr>
        <td><a href="{{ url('/order_management/') }}/{{$rows->id}}/edit">{{$rows->id}}</a></td>
        <td>
          Date: <strong>{{date('d-M-Y', strtotime($rows->created_at))}}</strong><br>
          Time: <strong>{{date('h:i', strtotime($rows->created_at))}}</strong>
        </td>
        <td>{{$rows->quantity}}
        </td>
      </tr>
      @endforeach
      @endif
      @else
      <tr>
        <td colspan="5" class="text-center">
          No Ordered Product Found
        </td>
      </tr>
      @endif
    </tbody>
  </table>
</div>