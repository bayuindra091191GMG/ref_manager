<?php

namespace Tests\Unit;

use App\Libs\TaxUtilities;
use App\Models\Configuration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TaxUtilitiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('configurations');
        Schema::create('configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->float('value_int')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
        });
    }

    public function test_it_reads_ppn_percentage_from_configuration(): void
    {
        Configuration::query()->create([
            'key' => 'ppn',
            'value' => '12.5',
        ]);

        $this->assertSame(12.5, TaxUtilities::getPpnPercentage());
        $this->assertSame(12500.0, TaxUtilities::calculatePpnValue(100000, 12.5));
        $this->assertSame(112500.0, TaxUtilities::calculateTotalWithPpn(100000, 12.5));
    }

    public function test_it_falls_back_to_ten_percent_when_configuration_is_missing_or_invalid(): void
    {
        $this->assertSame(10.0, TaxUtilities::getPpnPercentage());

        Configuration::query()->create([
            'key' => 'ppn',
            'value' => 'not-a-number',
        ]);

        $this->assertSame(10.0, TaxUtilities::getPpnPercentage());
    }
}
