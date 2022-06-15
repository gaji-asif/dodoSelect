@csrf
<input type="hidden" name="id" id="supplier_id" value="{{ $supplier->id }}">

<div class="mb-5">
    <x-label>
        {{ __('translation.Supplier Name') }} <x-form.required-mark />
    </x-label>
    <x-input type="text" name="supplier_name" value="{{ $supplier->supplier_name ?? old('supplier_name')}}" required/>
</div>

@if (!$supplier->supplierContacts->isEmpty())
    @foreach ($supplier->supplierContacts as $idx_supplier_contact => $supplier_contact)
        <div class="@if($idx_supplier_contact > 0) additional-supplier-contact--edit @endif flex flex-col md:flex-row md:gap-x-5">
            <div class="mb-5 md:w-2/5">
                <x-label>
                    {{ __('translation.Contact Channel') }}
                </x-label>
                <x-input type="text" name="contact_channel[]" value="{{ $supplier_contact->contact_channel }}"/>
            </div>
            <div class="mb-5 md:w-2/5">
                <x-label>
                    {{ __('translation.Contact') }}
                </x-label>
                <x-input type="text" name="contact[]" value="{{ $supplier_contact->contact }}"/>
            </div>
            <div class="mb-8 md:mb-5 md:pt-7 md:w-1/5">
                @if ($idx_supplier_contact === 0)
                    <x-button type="button" color="green" class="w-full" id="__btnAddNewSupplierContactEdit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="ml-2">
                                {{ __('translation.Add') }}
                            </span>
                    </x-button>
                @else
                    <x-button type="button" color="red" class="w-full __btnRemoveSupplierContactEdit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="ml-2">
                                {{ __('translation.Remove') }}
                            </span>
                    </x-button>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="flex flex-col md:flex-row md:gap-x-5">
        <div class="mb-5 md:w-2/5">
            <x-label>
                {{ __('translation.Contact Channel') }}
            </x-label>
            <x-input type="text" name="contact_channel[]"/>
        </div>
        <div class="mb-5 md:w-2/5">
            <x-label>
                {{ __('translation.Contact') }}
            </x-label>
            <x-input type="text" name="contact[]"/>
        </div>
        <div class="mb-8 md:mb-5 md:pt-7 md:w-1/5">
            <x-button type="button" color="green" class="w-full" id="__btnAddNewSupplierContactEdit">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span class="ml-2">
                        {{ __('translation.Add') }}
                    </span>
            </x-button>
        </div>
    </div>
@endif

<div id="__wrapperAdditionalSupplierContactEdit"></div>

<div class="hide" id="__newSupplierContactTemplateEdit">
    <div class="additional-supplier-contact--edit flex flex-col md:flex-row md:gap-x-5">
        <div class="mb-5 md:w-2/5">
            <x-label>
                {{ __('translation.Contact Channel') }}
            </x-label>
            <x-input type="text" name="contact_channel[]"/>
        </div>
        <div class="mb-5 md:w-2/5">
            <x-label>
                {{ __('translation.Contact') }}
            </x-label>
            <x-input type="text" name="contact[]"/>
        </div>
        <div class="mb-8 md:mb-5 md:pt-7 md:w-1/5">
            <x-button type="button" color="red" class="w-full __btnRemoveSupplierContactEdit">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="ml-2">
                        {{ __('translation.Remove') }}
                    </span>
            </x-button>
        </div>
    </div>
</div>

<div class="mb-5">
    <x-label>
        {{ __('translation.Address') }}
    </x-label>
    <x-form.textarea name="address" rows="3">{{ $supplier->address ?? old('address') }}</x-form.textarea>
</div>

<div class="mb-5">
    <x-label>
        {{ __('translation.Note') }}
    </x-label>
    <x-form.textarea name="note" rows="3">{{ $supplier->note ?? old('note') }}</x-form.textarea>
</div>

<div class="flex justify-end py-6">
    <x-button type="reset" color="gray" class="mr-1" id="__btnCancelModalUpdate">
        {{ __('translation.Cancel') }}
    </x-button>
    <x-button type="submit" color="blue" id="__btnUpdateSubmit">
        {{ __('translation.Update') }}
    </x-button>
</div>

<script>
    $(document).ready(function() {
        $('#__btnCancelModalUpdate').click(function() {
            $('body').removeClass('modal-open');
            $('#__modalUpdate').addClass('modal-hide');
        });

        $('#__btnAddNewSupplierContactEdit').click(function() {
            let newSupplierContactTemplate = $('#__newSupplierContactTemplateEdit').html();
            $('#__wrapperAdditionalSupplierContactEdit').append(newSupplierContactTemplate);

            initialRemoveSupplierContactButtonEdit();
        });

        const initialRemoveSupplierContactButtonEdit = () => {
            $('.__btnRemoveSupplierContactEdit').click(function() {
                $(this).parents(".additional-supplier-contact--edit").remove();
            });
        }

        initialRemoveSupplierContactButtonEdit();
    });

</script>
