<?php

namespace App\Service;

use App\Entity\Receipt;
use App\Entity\Product;
use App\Entity\DiscountRule;
use App\Entity\ReceiptLineDiscount;

readonly class DiscountManager
{

    public function __construct()
    {
    }

    public function updateProductDiscounts(Receipt $receipt, Product $product): void
    {
        $discountRules = $product->getDiscountRules();

        foreach ($discountRules as $discountRule) {
            $this->updateRuleDiscounts($discountRule, $receipt, $product);
        }
    }

    private function updateRuleDiscounts(DiscountRule $bundleRule, Receipt $receipt, Product $product): void
    {
        $bundleProducts = $bundleRule->getProducts();
        $productDiscountRules = $bundleRule->getProductDiscountRules();

        foreach ($bundleProducts as $bundleProduct) {
            $productReceiptLine = $receipt->getReceiptLine($bundleProduct);
            $productReceiptLine?->removeDiscounts();
        }

        if (!$receipt->hasBundleProducts($bundleProducts)) {
            return;
        }

        $amount = $bundleRule->getAmount();
        $discountType = $bundleRule->getType();
        if ($amount) {
            foreach ($bundleProducts as $bundleProduct) {
                $receiptLine = $receipt->getReceiptLine($bundleProduct);
                $quantity = $receiptLine->getQuantity();
                while ($quantity > 0) {
                    $name = $bundleRule->getName();
                    $discount = new ReceiptLineDiscount($name, $discountType, $amount);
                    $receiptLine->addDiscount($discount);
                    --$quantity;
                }
            }
        }

        foreach ($productDiscountRules as $productDiscountRule) {
            $discountedProduct = $productDiscountRule->getProduct();
            $requiredProducts = $bundleRule->getProductsByQuantity();
            $receiptProducts = $receipt->getProductsByQuantity();
            while ($this->areRequiredProductsAvailable($receiptProducts, $requiredProducts)) {
                $amount = $productDiscountRule->getAmount();
                $discountType = $productDiscountRule->getType();
                if ($amount) {
                    $name = $productDiscountRule->getName();
                    $discount = new ReceiptLineDiscount($name, $discountType, $amount);
                    $receipt->addProductDiscount($discountedProduct, $discount);
                }
                $this->removeDiscountedProducts($receiptProducts, $requiredProducts);
            }
        }
    }

    private function areRequiredProductsAvailable(array $receiptProducts, array $requiredProducts): bool{
        foreach ($requiredProducts as $productCode => $requiredQuantity) {
            if (!isset($receiptProducts[$productCode]) || $receiptProducts[$productCode] < $requiredQuantity) {
                return false;
            }
        }

        return true;
    }

    private function removeDiscountedProducts(array &$receiptProducts, array $requiredProducts): void{
        foreach ($requiredProducts as $productCode => $requiredQuantity) {
            $receiptProducts[$productCode] -= $requiredQuantity;
        }
    }
}
