<?php

namespace Tests\Unit\Actions;

use App\Actions\ValidateDateFormat;
use PHPUnit\Framework\TestCase;

class ValidateDateFormatTest extends TestCase
{
    /** @test */
    public function make_sure_date_format_is_as_expected()
    {
        $expectedFormat = 'd/m/Y';

        $validDates = [
            '01/12/2022',
            '11/01/2021'
        ];

        foreach ($validDates as $date) {
            $this->assertTrue(ValidateDateFormat::make()->handle($date, $expectedFormat));
        }


        $invalidDates = [
            '2021-01-01',
            '2021-1-1',
            '21-1-1',
            '2021-31-12',
            '11/01/2021-14.04น.',
            '5/5/2022',
            '',
            null
        ];

        foreach ($invalidDates as $date) {
            $this->assertFalse(ValidateDateFormat::make()->handle($date, $expectedFormat));
        }
    }
}
