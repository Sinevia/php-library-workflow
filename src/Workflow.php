<?php

namespace App\Models\Orders;

class Order extends \App\Models\BaseModel {

    protected $table = 'snv_orders_order';
    protected $primaryKey = 'Id';

    const TYPE_DELIVERY = 'Delivery';
    const TYPE_NONDELIVERY = 'NonDelivery'; // All Suntribe Swimwear orders are NonDelivery
    const STATUS_COMPLETED = 'Completed';
    const STATUS_PENDING = 'Pending';

    /**
     * @return \App\Models\Orders\OrderWorkflow
     */
    public function workflow() {
        $workflow = new OrderWorkflow($this);

        /* 1. No step? Set first step to send vendor purchase orders */
        if ($workflow->getCurrentStep() == null) {
            $workflow->setCurrentStep(OrderWorkflow::STEP_SEND_VENDOR_PURCHASE_ORDERS);
            $workflow->save();
        }

        /* 2. Purchase orders sent? Move to step to add tracking URLs */
        if ($workflow->getCurrentStep()->name == OrderWorkflow::STEP_SEND_VENDOR_PURCHASE_ORDERS) {
            $purchaseOrders = PurchaseOrder::byOrderId($this->Id);
            if (count($purchaseOrders) > 0) {
                $workflow->setCurrentStep(OrderWorkflow::STEP_ADD_TRACKING_URLS);
                $workflow->save();
            }
        }

        /* 3. All tracking URLs added? Move to step order fulfilled */
        if ($workflow->getCurrentStep()->name == OrderWorkflow::STEP_ADD_TRACKING_URLS) {
            $allItems = \App\Models\Orders\LineItem::where('OrderId', $this->Id)->count();
            $completedItems = \App\Models\Orders\LineItem::where('OrderId', $this->Id)->whereNotNull('TrackingNumber')->count();
            if ($allItems == $completedItems) {
                $workflow->setCurrentStep(OrderWorkflow::STEP_MARK_ORDER_AS_FULFILLED);
                $workflow->save();
            }
        }

        /* 4. Order copleted? */
        if ($workflow->getCurrentStep()->name == OrderWorkflow::STEP_MARK_ORDER_AS_FULFILLED) {
            if ($this->Status == self::STATUS_COMPLETED) {
                $result = $workflow->markStepAsCompleted(OrderWorkflow::STEP_MARK_ORDER_AS_FULFILLED);
                $workflow->save();
            }
        }

        return $workflow;
    }

    /**
     * @param string $shopifyId
     * @return Order
     */
    public static function findByShopifyId($shopifyId) {
        return self::where('ShopifyOrderId', $shopifyId)->first();
    }

    /**
     * @return int|null
     */
    public static function getLastShopifyOrderId() {
        $query = "CAST(ShopifyOrderId AS UNSIGNED) DESC";
        $lastOrder = \App\Models\Orders\Order::orderByRaw($query)->first();

        if ($lastOrder != null) {
            return $lastOrder->ShopifyOrderId;
        }

        return null;
    }

    /**
     * Returns the deserialized Shopify order
     * @return object
     */
    public function shopifyOrder() {
        return json_decode($this->ShopifyOrder);
    }

    /**
     * @return boolean
     */
    public static function tableCreate() {
        $o = new self;

        if (\Schema::connection($o->connection)->hasTable($o->table) == false) {
            return \Schema::connection($o->connection)->create($o->table, function (\Illuminate\Database\Schema\Blueprint $table) use ($o) {
                        $table->engine = 'InnoDB';
                        $table->string($o->primaryKey, 40)->primary();
                        $table->string('ShopifyOrderId', 50);
                        $table->string('Status', 20)->default('Pending');
                        $table->string('Type', 50)->default('Normal');
                        $table->datetime('FormEmailSentAt')->nullable();
                        $table->string('FormEmailSentTo', 255)->default('');
                        $table->text('FormResponse')->nullable();
                        $table->datetime('FormResponseAt')->nullable();
                        $table->text('ShopifyOrder')->nullable();
                        $table->text('State')->nullable();
                        $table->datetime('CreatedAt')->nullable();
                        $table->datetime('UpdatedAt')->nullable();
                        $table->datetime('DeletedAt')->nullable();
                        $table->index(['ShopifyOrderId', 'Status', 'Type']);
                    });
        }

        return true;
    }

    /**
     * @return boolean
     */
    public static function tableDelete() {
        $o = new self;
        return \Schema::connection($o->connection)->drop($o->table);
    }

}
