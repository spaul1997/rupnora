<?php

namespace App\Services;

use App\Models\AffiliateCommissionRule;
use App\Models\AffiliateProfile;
use App\Models\Product;

class AffiliateCommissionRuleResolver
{
    /** @return array{type: string, rate: float, rule_id: ?int} */
    public function resolve(AffiliateProfile $affiliate, Product $product): array
    {
        if ($affiliate->commission_rate !== null) {
            return ['type' => 'affiliate', 'rate' => (float) $affiliate->commission_rate, 'rule_id' => null];
        }

        $active = AffiliateCommissionRule::query()->activeAt();

        $productRule = (clone $active)->where('scope_type', 'product')->where('product_id', $product->id)->first();
        if ($productRule) {
            return $this->result('product', $productRule);
        }

        $categoryRule = (clone $active)->where('scope_type', 'category')->where('category_id', $product->category_id)->first();
        if ($categoryRule) {
            return $this->result('category', $categoryRule);
        }

        $globalRule = (clone $active)->where('scope_type', 'global')->first();
        if ($globalRule) {
            return $this->result('global', $globalRule);
        }

        return ['type' => 'global_config', 'rate' => (float) config('affiliate.global_commission_rate', 5), 'rule_id' => null];
    }

    /** @return array{type: string, rate: float, rule_id: int} */
    private function result(string $type, AffiliateCommissionRule $rule): array
    {
        return ['type' => $type, 'rate' => (float) $rule->rate, 'rule_id' => $rule->id];
    }
}
