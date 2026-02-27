<?php

namespace Tests\Unit;

use App\DataAccess\Models\Uloga;
use Tests\TestCase;

class UlogaModelTest extends TestCase
{
    public function test_uloga_has_correct_table_name(): void
    {
        $uloga = new Uloga();
        $this->assertSame('uloga', $uloga->getTable());
    }

    public function test_uloga_has_fillable_naziv(): void
    {
        $uloga = new Uloga();
        $this->assertContains('naziv', $uloga->getFillable());
    }
}
