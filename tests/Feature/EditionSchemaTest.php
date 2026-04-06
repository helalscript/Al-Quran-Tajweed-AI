<?php

use Illuminate\Support\Facades\Schema;

it('has expected editions columns', function () {
    expect(Schema::hasTable('editions'))->toBeTrue();

    expect(Schema::hasColumns('editions', [
        'id',
        'identifier',
        'language',
        'name',
        'english_name',
        'format',
        'type',
        'direction',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

