<?php

declare(strict_types=1);

use App\Models\Config;
use App\Services\DataTable;
use Tests\SlimTestCase;

final class DataTableTest extends SlimTestCase
{
    protected function tearDown(): void
    {
        Config::where('item', 'like', 'datatable_test_%')->delete();
        parent::tearDown();
    }

    public function testFiltersSortsAndLimitsRowsUsingDataTablesParameters(): void
    {
        Config::where('item', 'like', 'datatable_test_%')->delete();
        foreach (['alpha', 'beta', 'gamma'] as $value) {
            $config = new Config();
            $config->item = 'datatable_test_' . $value;
            $config->value = $value;
            $config->class = 'test';
            $config->is_public = 0;
            $config->type = 'string';
            $config->default = '';
            $config->mark = '';
            $config->save();
        }

        $request = $this->createRequest('POST', '/datatable')->withParsedBody([
            'draw' => '7',
            'start' => '0',
            'length' => '1',
            'search' => ['value' => 'a'],
            'order' => [['column' => '0', 'dir' => 'asc']],
            'columns' => [['data' => 'item']],
        ]);

        $query = Config::where('item', 'like', 'datatable_test_%');
        $result = DataTable::paginate($query, $request, ['item', 'value'], ['item', 'value']);

        self::assertSame(7, $result['draw']);
        self::assertSame(3, $result['recordsTotal']);
        self::assertSame(3, $result['recordsFiltered']);
        self::assertCount(1, $result['data']);
        self::assertSame('datatable_test_alpha', $result['data']->first()->item);
    }
}
