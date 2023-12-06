<?php


function convertStingToArray(array $payload): bool|string
{
    foreach ($payload as $index => $item) {
        $payload[$index] = (is_string($item) ? str_contains($item, 'sometimes') : in_array('sometimes', $item)) ? 'optional' : "value ";

    }
    return json_encode($payload);
}

function convertStingToArrayInput(array $payload): array
{
    foreach ($payload as $index => $item) {
        $payload[$index] = "value ";

    }
    return $payload;
}

function createTransactionCode(): string
{
    return date('dmYHis') . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5));
}

