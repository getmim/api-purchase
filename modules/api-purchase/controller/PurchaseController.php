<?php
/**
 * PurchaseController
 * @package api-purchase
 * @version 0.0.2
 */

namespace ApiPurchase\Controller;

use Cart\Model\Cart;
use Cart\Model\CartItem as CItem;
use Purchase\Model\Purchase;
use Purchase\Model\PurchaseProduct as PProduct;
use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use Purchase\Library\Invoice;
use Cart\Library\Cart as _Cart;
use LibUser\Library\Fetcher;

class PurchaseController extends \Api\Controller
{
    public function createAction()
    {
        if (!$this->app->isAuthorized())
            return $this->resp(401);

        $cond = [];

        if ($this->user->isLogin())
            $cond['user'] = $this->user->id;
        elseif($user = $this->req->get('user'))
            $cond['user'] = $user;

        if (!isset($cond['user'])) {
            return $this->resp(401, 'Required `user` field is not set');
        }

        $user = Fetcher::getOne([
            'id' => $cond['user'],
            'status' => ['__op', '>', 0]
        ]);
        if (!$user) {
            return $this->resp(400, 'User not found');
        }

        $cart = Cart::getOne($cond);
        if (!$cart) {
            return $this->resp(401, 'Current user dont have any cart yet');
        }

        $form = new Form('api-purchase.create');
        if (!($valid = $form->validate())) {
            return $this->resp(422, $form->getErrors());
        }

        $items = $valid->items;

        $cart_items = CItem::get([
            'id' => $items,
            'cart' => $cart->id
        ]);

        if (!$cart_items) {
            return $this->resp(400, 'No cart item found');
        }

        $cart_items_id = [];
        $quantities = 0;
        $prices = 0;
        foreach ($cart_items as $item) {
            $cart_items_id[] = $item->id;
            $quantities+= $item->quantity;
            $prices+= $item->total;
        }

        CItem::remove(['id' => $cart_items_id]);
        _Cart::calculate($cart);

        $data = [
            'user'    => $cond['user'],
            'invoice' => 'INV-' . uniqid() . '-' . uniqid(),
            'status'  => 10,
            'items'   => count($cart_items),
            'quantity' => $quantities,
            'price'   => $prices,
            'total'   => $prices
        ];
        if (module_exists('purchase-delivery')) {
            $data['courier_fee'] = 0;
        }

        $purchase_id = Purchase::create($data);

        $purchase = Purchase::getOne(['id' => $purchase_id]);
        $invoice = Invoice::generate($purchase);

        Purchase::set(['invoice' => $invoice], ['id' => $purchase_id]);

        $purchase_products = [];
        foreach ($cart_items as $item) {
            $purchase_products[] = [
                'purchase' => $purchase_id,
                'product'  => $item->product,
                'quantity' => $item->quantity,
                'price'    => $item->price,
                'total'    => $item->total
            ];
        }

        PProduct::createMany($purchase_products);

        $purchase = Purchase::getOne(['id' => $purchase_id]);
        $purchase = Formatter::format('purchase', $purchase);

        return $this->resp(0, $purchase);
    }

    public function indexAction()
    {
        if (!$this->app->isAuthorized())
            return $this->resp(401);

        $cond = $this->req->getCond(['status']);

        if ($this->user->isLogin())
            $cond['user'] = $this->user->id;
        elseif($user = $this->req->get('user'))
            $cond['user'] = $user;

        if (!isset($cond['user'])) {
            return $this->resp(401, 'Required `user` field is not set');
        }

        $user = Fetcher::getOne([
            'id' => $cond['user'],
            'status' => ['__op', '>', 0]
        ]);
        if (!$user) {
            return $this->resp(400, 'User not found');
        }

        list($page, $rpp) = $this->req->getPager(12, 24);

        $purchases = Purchase::get($cond, $rpp, $page, ['created' => false]) ?? [];
        if ($purchases) {
            $purchases = Formatter::formatMany('purchase', $purchases);
        }

        $total = Purchase::count($cond);

        return $this->resp(0, $purchases, null, [
            'meta' => [
                'page' => $page,
                'rpp'  => $rpp,
                'total' => $total
            ]
        ]);
    }

    public function removeAction()
    {
        if (!$this->app->isAuthorized())
            return $this->resp(401);

        $cond = [
            'id' => $this->req->param->id,
            'status' => ['__op', '<', 3]
        ];

        if ($this->user->isLogin())
            $cond['user'] = $this->user->id;
        elseif($user = $this->req->get('user'))
            $cond['user'] = $user;

        if (!isset($cond['user'])) {
            return $this->resp(401, 'Required `user` field is not set');
        }

        $user = Fetcher::getOne([
            'id' => $cond['user'],
            'status' => ['__op', '>', 0]
        ]);
        if (!$user) {
            return $this->resp(400, 'User not found');
        }

        $purchase = Purchase::getOne($cond);
        if (!$purchase) {
            return $this->resp(404);
        }

        Purchase::set(['status' => 0], ['id' => $purchase->id]);

        $this->resp(0, 'success');
    }

    public function singleAction()
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

        $user = Fetcher::getOne([
            'id' => $cond['user'],
            'status' => ['__op', '>', 0]
        ]);
        if (!$user) {
            return $this->resp(400, 'User not found');
        }

        $purchase = Purchase::getOne($cond);
        if (!$purchase) {
            return $this->resp(404);
        }

        $purchase = Formatter::format('purchase', $purchase);

        return $this->resp(0, $purchase);
    }
}
