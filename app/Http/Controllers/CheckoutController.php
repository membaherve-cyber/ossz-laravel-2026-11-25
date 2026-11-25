<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Cart;
use App\Support\Notify;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = Cart::view();
        if (! ($cart['id'] ?? null) || count($cart['lines']) === 0) {
            return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
        }

        return view('pages.checkout', [
            'cart' => $cart,
            'zones' => DeliveryZone::where('is_active', true)->orderBy('fee')->get(),
        ]);
    }

    public function placeOrder(Request $request)
    {
        $cart = Cart::view();
        if (! ($cart['id'] ?? null) || count($cart['lines']) === 0) {
            return back()->with('error', 'Your cart is empty.');
        }

        $validated = $request->validate([
            'customerName' => 'required|string|max:120',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'deliveryMethod' => 'nullable|string|max:40',
            'zoneId' => 'nullable|integer',
            'paymentMethod' => 'nullable|string|max:40',
            'city' => 'nullable|string|max:80',
            'area' => 'nullable|string|max:120',
            'street' => 'nullable|string|max:200',
            'notes' => 'nullable|string|max:2000',
            'createAccount' => 'nullable|boolean',
            'password' => 'nullable|string|min:6|max:120',
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()->with('error', 'Please provide either an email address or a phone number so we can reach you.')
                ->withInput();
        }

        $zone = DeliveryZone::where('is_active', true)
            ->where(function ($q) use ($validated) {
                $q->where('id', (int) ($validated['zoneId'] ?? 0))
                    ->orWhere('method', $validated['deliveryMethod'] ?? 'national');
            })
            ->orderBy('id')
            ->first();

        $subtotal = $cart['subtotal'];
        $discount = $cart['couponCode'] ? Cart::computeDiscount($cart['couponCode'], $subtotal) : 0;
        $discount = min($discount, $subtotal);
        $deliveryMethod = $validated['deliveryMethod'] ?? 'national';
        $deliveryFee = $deliveryMethod === 'pickup' ? 0 : ($zone->fee ?? 6500);

        // Free nationwide delivery above the configured threshold (pickup is
        // already free and the threshold does not apply to it).
        if ($deliveryMethod !== 'pickup') {
            $threshold = (int) Settings::get('free_delivery_threshold', '150000');
            if ($threshold > 0 && ($subtotal - $discount) >= $threshold) {
                $deliveryFee = 0;
            }
        }

        // The free_delivery coupon zeroes the fee only when the coupon is
        // actually usable (active, unexpired, within its usage limit) — the
        // same conditions computeDiscount() enforces for money discounts.
        $coupon = null;
        if ($cart['couponCode']) {
            $coupon = Coupon::where('code', strtoupper($cart['couponCode']))
                ->where('is_active', true)
                ->first();
            if ($coupon && $coupon->expires_at && $coupon->expires_at->isPast()) {
                $coupon = null;
            }
            if ($coupon && $coupon->usage_limit > 0 && $coupon->times_used >= $coupon->usage_limit) {
                $coupon = null;
            }
            if ($coupon?->type === 'free_delivery' && $deliveryMethod !== 'pickup') {
                $deliveryFee = 0;
            }
        }
        $total = $subtotal - $discount + $deliveryFee;

        $userId = session('user_id');
        $paymentMethod = $validated['paymentMethod'] ?? 'mobile_money_mtn';

        // Optional account creation at checkout.
        if (! $userId && $request->boolean('createAccount') && ! empty($validated['password'])) {
            $email = strtolower((string) $validated['email']);
            if ($email && ! \App\Models\User::where('email', $email)->exists()) {
                $user = \App\Models\User::create([
                    'email' => $email,
                    'phone' => $validated['phone'] ?? '',
                    'full_name' => $validated['customerName'],
                    'password_hash' => \App\Support\Auth::hashPassword($validated['password']),
                    'role' => 'customer',
                ]);
                $userId = $user->id;
                session(['user_id' => $user->id]);
                Cart::mergeGuestCart($user->id);
                $cart = Cart::view();
            }
        }

        try {
            $order = DB::transaction(function () use ($cart, $validated, $userId, $paymentMethod, $deliveryMethod, $zone, $deliveryFee, $subtotal, $discount, $total, $coupon) {
            $order = Order::create([
                'order_number' => make_order_number(),
                'user_id' => $userId,
                'guest_email' => strtolower((string) ($validated['email'] ?? '')),
                'guest_phone' => $validated['phone'] ?? '',
                'customer_name' => $validated['customerName'],
                'status' => 'placed',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'coupon_code' => $cart['couponCode'] ?? '',
                'payment_method' => $paymentMethod,
                // Nothing is trusted as paid before staff verification: mobile
                // money transfers and card payments must upload proof / be
                // confirmed in the backoffice. COD is collected on delivery.
                'payment_status' => 'pending',
                'delivery_method' => $deliveryMethod,
                'delivery_zone' => $zone?->name ?? 'In-store pickup',
                'shipping_snapshot' => [
                    'fullName' => $validated['customerName'],
                    'phone' => $validated['phone'] ?? '',
                    'city' => $validated['city'] ?? 'Douala',
                    'area' => $validated['area'] ?? '',
                    'street' => $validated['street'] ?? '',
                    'notes' => $validated['notes'] ?? '',
                    'zone' => $zone?->name ?? 'In-store pickup',
                ],
            ]);

            foreach ($cart['lines'] as $line) {
                // Refuse overselling: re-read stock inside the transaction.
                $variant = ProductVariant::where('id', $line['variantId'])->lockForUpdate()->first();
                if (! $variant || (int) $variant->stock_qty < $line['quantity']) {
                    throw new \RuntimeException('Insufficient stock for '.$line['name'].' ('.$line['size'].' / '.$line['colour'].'). Please adjust the quantity.');
                }
                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $line['variantId'],
                    'product_name' => $line['name'],
                    'product_slug' => $line['slug'],
                    'variant_label' => $line['size'].' · '.$line['colour'],
                    'image_url' => $line['image'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unitPrice'],
                ]);
                ProductVariant::where('id', $line['variantId'])->decrement('stock_qty', $line['quantity']);
                Product::where('id', $line['productId'])->increment('popularity', $line['quantity']);
            }

            \App\Models\CartItem::where('cart_id', $cart['id'])->delete();
            \App\Models\Cart::where('id', $cart['id'])->update(['coupon_code' => null]);

            return $order;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        // Count a coupon as used only when an order actually carried it.
        if ($coupon) {
            $coupon->increment('times_used');
        }

        Notify::orderPlaced($order->id);

        return redirect()->route('order.track', ['number' => $order->order_number])
            ->with('success', 'Your order has been placed.');
    }

    public function paymentProofForm(Request $request)
    {
        return view('pages.payment-proof', [
            'orderNumber' => (string) $request->input('order', ''),
        ]);
    }

    public function paymentProof(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:24',
            // Contact confirmation — an order number alone must not allow
            // overwriting someone else's payment proof.
            'contact' => 'required|string|max:160',
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp,avif,pdf|max:8192',
        ]);

        $order = Order::where('order_number', strtoupper(trim($validated['order_number'])))->first();
        if (! $order) {
            return back()->with('error', 'We could not find that order number.');
        }

        $contact = trim($validated['contact']);
        $digits = fn ($v) => preg_replace('/\D/', '', $v ?? '');
        $matched = strcasecmp((string) $order->guest_email, $contact) === 0
            || ($contact !== '' && $digits($order->guest_phone) === $digits($contact));
        if (! $matched) {
            return back()->with('error', 'That contact does not match the order. Use the email or phone the order was placed with.');
        }

        $path = $request->file('proof')->store('payment-proofs', 'local_custom');
        $order->update([
            'payment_proof_url' => $path,
            'payment_status' => 'pending',
        ]);

        return back()->with('success', 'Thank you — your payment proof has been received and our team will confirm it shortly.');
    }
}
