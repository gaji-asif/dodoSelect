@csrf

<div class="mt-6">
    <input type="hidden" name="id" value="{{ $id }}">
   
    <input type="radio" name="check" value="1" id="checkin" checked ><label for="checkin"> Check In</label> 
    <input type="radio" name="check" value="0" id="checkout" style="margin-left: 30px"><label for="checkout"> Check Out</label> <br>

</div>
<div class="mt-6">
    <x-label>
        Quantity
    </x-label>
    <x-input type="text" name="quantity" id="quantity" required>
    </x-input>
</div>


<div class="flex justify-end py-4">
    <x-button color="blue">Update</x-button>
</div>
