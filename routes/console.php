<?php

use App\Jobs\ReconcileStaleAffiliateWithdrawals;
use App\Jobs\ReleaseAffiliateCommissions;
use App\Models\Product;
use App\Services\DiscountOfferService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:sync-prices', function () {
    $updated = DB::transaction(
        fn () => app(DiscountOfferService::class)->synchronizeScheduledOffers()
    );

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
})->purpose('Persist offer or discount activation and expiry price changes and add them to product price history.');

Schedule::command('products:sync-prices')->dailyAt('00:01')->withoutOverlapping();
Schedule::job(new ReleaseAffiliateCommissions)->hourly()->withoutOverlapping();
Schedule::job(new ReconcileStaleAffiliateWithdrawals)->hourlyAt(15)->withoutOverlapping();
