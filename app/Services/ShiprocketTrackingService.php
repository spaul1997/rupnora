<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ShiprocketTrackingService
{
    public function __construct(private ShiprocketService $shiprocket) {}

    /**
     * @return array<string, mixed>
     */
    public function refresh(Order $order, ?int $updatedBy = null): array
    {
        $tracking = $this->shiprocket->trackAwb($order);

        DB::transaction(function () use ($order, $tracking, $updatedBy): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $previousStatus = trim((string) $lockedOrder->shiprocket_status);
            $updates = [
                'shiprocket_status' => $tracking['status'],
                'shiprocket_tracking' => $tracking,
                'shiprocket_synced_at' => now(),
                'shiprocket_tracked_at' => now(),
            ];

            if (filled($tracking['courier_name'] ?? null)) {
                $updates['courier_name'] = $tracking['courier_name'];
            }

            if (filled($tracking['tracking_url'] ?? null)) {
                $updates['tracking_url'] = $tracking['tracking_url'];
            }

            if (filled($tracking['estimated_delivery'] ?? null)) {
                $updates['estimated_delivery'] = $tracking['estimated_delivery'];
            }

            $lockedOrder->update($updates);

            if (strcasecmp($previousStatus, (string) $tracking['status']) !== 0) {
                $lockedOrder->statusHistories()->create([
                    'status' => 'shiprocket_tracking_updated',
                    'remark' => 'Shiprocket shipment status updated to '.$tracking['status'].'.',
                    'updated_by' => $updatedBy,
                ]);
            }
        });

        return $tracking;
    }
}
