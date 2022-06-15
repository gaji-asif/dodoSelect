<?php

namespace App\Rules;

use App\Models\SheetName;
use Illuminate\Contracts\Validation\Rule;

class UniqueSheetName implements Rule
{
    /** @var int */
    protected $sheetDocId;

    /** @var int */
    protected $exceptSheetNameId = null;

    /**
     * Create a new rule instance.
     *
     * @param  int  $sheetDocId
     * @param  int|null  $exceptSheetNameId
     * @return void
     */
    public function __construct(int $sheetDocId, ?int $exceptSheetNameId)
    {
        $this->sheetDocId = $sheetDocId;
        $this->exceptSheetNameId = $exceptSheetNameId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $sheetName
     * @return bool
     */
    public function passes($attribute, $sheetName)
    {
        $existingSheetName = SheetName::query()
            ->where('sheet_doc_id', $this->sheetDocId)
            ->where('sheet_name', trim($sheetName))
            ->first();

        if (!empty($existingSheetName)) {
            if (empty($this->exceptSheetNameId)) {
                return false;
            }

            return $existingSheetName->id == $this->exceptSheetNameId;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.unique');
    }
}
