<?php
/**
 * PurchaseItemController
 * @package api-purchase
 * @version 0.0.1
 */

namespace ApiPurchase\Controller;

use Purchase\Model\Purchase;
use Purchase\Model\PurchaseProduct as PProduct;
use LibFormatter\Library\Formatter;

class PurchaseItemController extends \Api\Controller
{
    public function indexAction()
    {
        if (!$this->app->isAuthorized())
            return $this->resp(401);

        $cond = [
            'id' => $this->req->param->id
        ];

        if ($this->user->isLogin())
            $cond['user'] = $this->user->id;
        elseif($user = $this->req->get('user'))
            $cond['user'] = $user;

        if (!isset($cond['user'])) {
            return $this->resp(401, 'Required `user` field is not set');
        }

        $purchase = Purchase::getOne($cond);
        if (!$purchase) {
            return $this->resp(404);
        }

        $items = PProduct::get(['purchase' => $purchase->id]);
        if ($items) {
            $items = Formatter::formatMany('purchase-product', $items, ['product']);
        }

        $this->resp(0, $items);
    }
}
