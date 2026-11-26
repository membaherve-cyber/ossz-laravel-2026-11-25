<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Collection;
use App\Models\ContactMessage;
use App\Models\DeliveryZone;
use App\Models\Faq;
use App\Models\HomeBlock;
use App\Models\JournalPost;
use App\Models\LookbookItem;
use App\Models\NewsletterSignup;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Cart;
use App\Support\Notify;
use App\Support\ProductQuery;
use Illuminate\Http\Request;

/**
 * Storefront pages — a page-per-method port of the Next.js
 * src/app/**​/page.tsx server components.
 */
class PageController extends Controller
{
    public function home()
    {
        $blocks = HomeBlock::where('is_published', true)->orderBy('sort_order')->get();
        $newArrivals = ProductQuery::list(['sort' => 'newest', 'limit' => 8]);
        $featured = ProductQuery::list(['featured' => true, 'limit' => 8]);

        $newIds = collect($newArrivals)->take(4)->pluck('id')->all();
        $featuredShown = array_values(array_filter(
            array_slice($featured, 0, 8),
            fn ($p) => ! in_array($p['id'], $newIds)
        ));
        $newArrivalsShown = array_slice($newArrivals, 0, 4);

        return view('pages.home', [
            'hero' => $blocks->firstWhere('type', 'hero') ?? $blocks->first(),
            'banner' => $blocks->firstWhere('type', 'banner'),
            'invite' => $blocks->firstWhere('type', 'quote'),
            'newArrivals' => $newArrivalsShown,
            'featured' => $featuredShown,
            'collections' => Collection::where('is_published', true)->orderBy('sort_order')->limit(3)->get(),
            'looks' => LookbookItem::orderBy('sort_order')->limit(3)->get(),
            'posts' => JournalPost::where('status', 'published')->orderByDesc('published_at')->limit(2)->get(),
        ]);
    }

    public function shop(Request $request)
    {
        $filters = $request->only(['category', 'collection', 'size', 'colour', 'min', 'max', 'availability', 'sort', 'q']);
        if ($request->filled('min')) $filters['minPrice'] = (int) $request->input('min');
        if ($request->filled('max')) $filters['maxPrice'] = (int) $request->input('max');
        $allProducts = ProductQuery::list($filters);

        // Server-side pagination (9 per page)
        $perPage    = 9;
        $pageNo     = max(1, (int) $request->input('page', 1));
        $total      = count($allProducts);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $pageNo     = min($pageNo, $totalPages);
        $products   = array_slice($allProducts, ($pageNo - 1) * $perPage, $perPage);

        return view('pages.shop', [
            'products'   => $products,
            'facets'     => ProductQuery::facets(),
            'total'      => $total,
            'totalPages' => $totalPages,
            'pageNo'     => $pageNo,
            'query'      => $request->input('q', ''),
        ]);
    }

    public function product(string $slug)
    {
        $data = ProductQuery::bySlug($slug);
        abort_if(! $data, 404);

        $related = ProductQuery::list([
            'category' => $data['product']->category?->slug,
            'limit' => 4,
        ]);

        return view('pages.product', [
            ...$data,
            'related' => array_values(array_filter($related, fn ($p) => $p['id'] !== $data['product']->id)),
            'settings' => \App\Support\Settings::all(),
        ]);
    }

    public function collections()
    {
        return view('pages.collections', [
            'collections' => Collection::where('is_published', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function collection(string $slug)
    {
        $collection = Collection::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $products = ProductQuery::list(['collection' => $slug, 'limit' => 60]);

        return view('pages.collection-detail', compact('collection', 'products'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $products = $q !== '' ? ProductQuery::list(['q' => $q, 'limit' => 24]) : [];

        return view('pages.search', [
            'q' => $q,
            'products' => $products,
            'popular' => ProductQuery::list(['sort' => 'popular', 'limit' => 4]),
        ]);
    }

    public function journal()
    {
        return view('pages.journal', [
            'posts' => JournalPost::where('status', 'published')->orderByDesc('published_at')->get(),
        ]);
    }

    public function journalPost(string $slug)
    {
        $post = JournalPost::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('pages.journal-post', [
            'post' => $post,
            'more' => JournalPost::where('status', 'published')->where('slug', '!=', $slug)
                ->orderByDesc('published_at')->limit(2)->get(),
        ]);
    }

    public function lookbook()
    {
        return view('pages.lookbook', [
            'looks' => LookbookItem::orderBy('sort_order')->get(),
        ]);
    }

    public function faq()
    {
        return view('pages.faq', [
            'faqs' => Faq::orderBy('sort_order')->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function sizeGuide()
    {
        return view('pages.size-guide');
    }

    public function offline()
    {
        return view('pages.offline');
    }

    /**
     * XML sitemap — every public catalogue/journal page plus the static
     * storefront routes. Previously /sitemap.xml 404'd while /robots.txt
     * (added as a static file) already referenced it.
     */
    public function sitemap()
    {
        $base = rtrim(config('app.url') ?: url('/'), '/');
        $today = now()->toDateString();

        $urls = [
            ['loc' => '/', 'priority' => '1.0'],
            ['loc' => '/shop', 'priority' => '0.9'],
            ['loc' => '/collections', 'priority' => '0.8'],
            ['loc' => '/journal', 'priority' => '0.6'],
            ['loc' => '/lookbook', 'priority' => '0.6'],
            ['loc' => '/appointments', 'priority' => '0.6'],
            ['loc' => '/about', 'priority' => '0.5'],
            ['loc' => '/contact', 'priority' => '0.5'],
            ['loc' => '/faq', 'priority' => '0.5'],
            ['loc' => '/size-guide', 'priority' => '0.4'],
            ['loc' => '/order-lookup', 'priority' => '0.3'],
        ];

        $products = Product::query()->where('is_published', true)->orderBy('slug')
            ->get(['slug'])->map(fn ($p) => ['loc' => '/product/'.$p->slug, 'priority' => '0.8']);
        $collections = Collection::query()->where('is_published', true)->orderBy('slug')
            ->get(['slug'])->map(fn ($c) => ['loc' => '/collections/'.$c->slug, 'priority' => '0.7']);
        $posts = JournalPost::query()->where('status', 'published')->orderBy('slug')
            ->get(['slug'])->map(fn ($j) => ['loc' => '/journal/'.$j->slug, 'priority' => '0.5']);

        $all = collect($urls)->merge($products)->merge($collections)->merge($posts);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($all as $u) {
            $xml .= "  <url><loc>".e($base.$u['loc'])."</loc><lastmod>{$today}</lastmod>"
                ."<priority>{$u['priority']}</priority></url>\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function appointments()
    {
        return view('pages.appointments');
    }

    public function bookAppointment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'contact' => 'required|string|max:120',
            'service' => 'nullable|string|max:80',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:2000',
        ]);

        $start = new \DateTimeImmutable($validated['date'].'T'.$validated['time'].':00');
        if ($start->getTimestamp() < time()) {
            return back()->with('error', 'Please choose a time in the future.');
        }

        $clash = Appointment::where('slot_start', $start->format('Y-m-d H:i:s'))
            ->where('status', 'confirmed')->exists();
        if ($clash) {
            return back()->with('error', 'That slot has just been taken — please choose another time.');
        }

        $reference = make_reference('APT');
        $appointment = Appointment::create([
            'reference' => $reference,
            'user_id' => session('user_id'),
            'guest_name' => $validated['name'],
            'guest_contact' => $validated['contact'],
            'service' => $validated['service'] ?? 'Styling session',
            'slot_start' => $start,
            'slot_end' => $start->modify('+1 hour'),
            'status' => 'requested',
            'notes' => $validated['notes'] ?? '',
        ]);

        Notify::appointmentRequested($appointment);

        return back()->with('success', "Thank you, {$validated['name']}. Your request {$reference} has been received — our stylist will confirm shortly.");
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'contact' => 'required|string|max:120',
            'message' => 'required|string|max:4000',
        ]);

        ContactMessage::create($validated);
        Notify::contactMessage($validated);

        return back()->with('success', 'Thank you for writing to us. We reply within one business day.');
    }

    public function newsletter(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|max:160']);
        $email = strtolower(trim($validated['email']));
        NewsletterSignup::firstOrCreate(['email' => $email]);

        return back()->with('success', 'Thank you — you are on the list.');
    }

    public function orderLookupForm()
    {
        return view('pages.order-lookup');
    }

    public function orderLookup(Request $request)
    {
        $number = strtoupper(trim((string) $request->input('order_number')));
        $contact = trim((string) $request->input('contact'));

        return redirect()->route('order.track', ['number' => $number, 'contact' => $contact]);
    }

    public function orderTrack(string $number, Request $request)
    {
        $order = Order::where('order_number', strtoupper($number))->first();
        $items = collect();
        $contact = trim((string) $request->input('contact', ''));
        $matched = false;

        if ($order) {
            $digits = fn ($v) => preg_replace('/\D/', '', $v ?? '');
            $matched = strcasecmp($order->guest_email, $contact) === 0
                || ($contact !== '' && $digits($order->guest_phone) === $digits($contact));
            if ($matched) {
                $items = OrderItem::where('order_id', $order->id)->get();
            }
        }

        return view('pages.order-track', [
            'order' => $order,
            'items' => $items,
            'contact' => $contact,
            'matched' => $matched,
        ]);
    }

    public function switchLocale(Request $request, ?string $locale = null)
    {
        $target = ($locale ?: $request->input('locale')) === 'fr' ? 'fr' : 'en';

        // Attach the cookie to the redirect response directly (the queued
        // cookie jar is not reliably flushed for redirect responses here).
        return back()->withCookie(cookie(
            \App\Support\I18n::COOKIE,
            $target,
            60 * 24 * 365,
            '/',
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site') ?? 'lax'
        ));
    }
}
