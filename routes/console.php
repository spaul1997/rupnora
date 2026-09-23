<?php

use App\Models\Product;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:sync-prices', function () {
    $updated = 0;

    Product::query()->chunkById(200, function ($products) use (&$updated) {
        foreach ($products as $product) {
            $storedPrice = (float) $product->getRawOriginal('final_price');
            $currentPrice = $product->computeFinalPrice();

            if (abs($storedPrice - $currentPrice) < 0.01) {
                continue;
            }

            $product->save();
            $updated++;
        }
    });

    $this->info("Synchronized {$updated} product price".($updated === 1 ? '' : 's').'.');
})->purpose('Persist offer or discount expiry price changes and add them to product price history.');

Schedule::command('products:sync-prices')->dailyAt('00:05')->withoutOverlapping();
