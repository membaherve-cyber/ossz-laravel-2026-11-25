<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Support\Auth;
use Illuminate\Http\Request;

/**
 * Customer account — port of src/app/account/** pages.
 */
class AccountController extends Controller
{
    private function user(): User
    {
        return User::findOrFail(session('user_id'));
    }

    public function index()
    {
        $user = $this->user();

        return view('account.home', [
            'user' => $user,
            'orders' => $user->orders()->with('items')->orderByDesc('created_at')->limit(5)->get(),
            'appointments' => Appointment::where('user_id', $user->id)->orderByDesc('slot_start')->limit(5)->get(),
        ]);
    }

    public function orders()
    {
        $user = $this->user();

        return view('account.orders', [
            // Orders placed while signed in, plus guest orders that used this
            // account's email (e.g. checked out before registering).
            'orders' => Order::query()
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere(function ($q2) use ($user) {
                            $q2->whereNull('user_id')->where('guest_email', $user->email);
                        });
                })
                ->with('items')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function appointments()
    {
        return view('account.appointments', [
            'appointments' => Appointment::where('user_id', $this->user()->id)->orderByDesc('slot_start')->get(),
        ]);
    }

    public function cancelAppointment(Request $request)
    {
        Appointment::where('id', (int) $request->input('id'))
            ->where('user_id', session('user_id'))
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function addresses()
    {
        return view('account.addresses', [
            'addresses' => Address::where('user_id', session('user_id'))->get(),
        ]);
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'label' => 'nullable|string|max:60',
            'full_name' => 'required|string|max:120',
            'phone' => 'required|string|max:40',
            'city' => 'nullable|string|max:80',
            'area' => 'nullable|string|max:120',
            'street' => 'nullable|string|max:200',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = session('user_id');
        $values = [
            'user_id' => $userId,
            'label' => $validated['label'] ?? 'Home',
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'city' => $validated['city'] ?? 'Douala',
            'area' => $validated['area'] ?? '',
            'street' => $validated['street'] ?? '',
            'notes' => $validated['notes'] ?? '',
        ];

        if (! empty($validated['id'])) {
            Address::where('id', (int) $validated['id'])->where('user_id', $userId)->update($values);
        } else {
            Address::create($values);
        }

        return back()->with('success', 'Address saved.');
    }

    public function deleteAddress(Request $request)
    {
        Address::where('id', (int) $request->input('id'))
            ->where('user_id', session('user_id'))
            ->delete();

        return back()->with('success', 'Address removed.');
    }

    public function profile()
    {
        return view('account.profile', ['user' => $this->user()]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:40',
            'password' => 'nullable|min:6|max:120',
            // Changing the password requires proving ownership of the account.
            'currentPassword' => 'required_with:password|nullable|string|max:120',
        ]);

        $user = $this->user();
        $values = [
            'full_name' => $validated['fullName'] ?? $user->full_name,
            'phone' => $validated['phone'] ?? $user->phone,
        ];
        if (! empty($validated['password'])) {
            if (! Auth::verifyPassword((string) ($validated['currentPassword'] ?? ''), $user->password_hash)) {
                return back()->with('error', 'Your current password is not correct — the password was not changed.');
            }
            $values['password_hash'] = Auth::hashPassword($validated['password']);
        }
        $user->update($values);

        return back()->with('success', 'Your details have been saved.');
    }

    public function wishlist()
    {
        $rows = Wishlist::with('product.images')->where('user_id', session('user_id'))->orderByDesc('created_at')->get();

        return view('account.wishlist', ['rows' => $rows]);
    }

    public function toggleWishlist(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $userId = session('user_id');
        // Validate the product: a bogus id must not crash on a FK violation.
        if ($productId < 1 || ! Product::find($productId)) {
            return back()->with('error', 'That piece could not be saved.');
        }
        $existing = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();
        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Removed from your wishlist.');
        }
        Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);

        return back()->with('success', 'Saved to your wishlist.');
    }
}
