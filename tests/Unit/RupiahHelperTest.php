<?php

it('formats integer amounts as rupiah', function (int $amount, string $expected) {
    expect(rupiah($amount))->toBe($expected);
})->with([
    'zero' => [0, 'Rp 0'],
    'thousands' => [150000, 'Rp 150.000'],
    'millions' => [1500000, 'Rp 1.500.000'],
    'small' => [500, 'Rp 500'],
]);

it('parses masked rupiah strings back to integers', function (string $value, int $expected) {
    expect(rupiah_clean($value))->toBe($expected);
})->with([
    'masked' => ['Rp 150.000', 150000],
    'dots only' => ['1.500.000', 1500000],
    'plain' => ['200000', 200000],
    'empty' => ['', 0],
]);
