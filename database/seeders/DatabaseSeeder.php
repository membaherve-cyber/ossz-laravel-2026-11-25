<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\DeliveryZone;
use App\Models\Faq;
use App\Models\HomeBlock;
use App\Models\JournalPost;
use App\Models\LookbookItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Support\Auth;
use Illuminate\Database\Seeder;

/**
 * Seeds the live OSSZ catalogue — the same reference data the Next.js shop
 * bootstrapped itself with (scripts/seed.mjs + seed-real.mjs + seed-fr.mjs):
 * staff logins, categories, the three real collections (Wouri Nights,
 * Atelier Essentials, Sawa Sun), the twelve-piece real catalogue with its
 * dedicated photography, delivery zones, settings, the three live homepage
 * blocks, FAQs, three journal articles, the lookbook and a welcome coupon.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Staff & demo logins ────────────────────────────────────────
        // These are the SAME reference accounts the Next.js preview bootstraps
        // itself with (scripts/seed.mjs + scripts/seed-staff-logins.mjs) and
        // the same ones published in the sign-in page's hint box. They are
        // deliberately consistent across preview and production so the owner
        // never meets a credential mismatch again ("stay consistent"). They
        // are demo-grade values for this storefront, rotated at will via the
        // back-office profile page. SEED_STAFF_PASSWORD, when set, overrides
        // the short-username staff passwords (used by the CI integration job).
        $staffOverride = getenv('SEED_STAFF_PASSWORD') ?: null;
        $accounts = [
            // Short-username back-office logins (username OR email works).
            ['email' => 'admin@osszcollections.cm',   'username' => 'admin',   'full_name' => 'OSSZ Admin',          'role' => 'admin',    'password' => $staffOverride ?: 'admin'],
            ['email' => 'manager@osszcollections.cm', 'username' => 'manager', 'full_name' => 'OSSZ Manager',        'role' => 'admin',    'password' => $staffOverride ?: 'manager'],
            ['email' => 'staff.login@osszcollections.cm', 'username' => 'staff', 'full_name' => 'OSSZ Staff',       'role' => 'staff',    'password' => $staffOverride ?: 'staff'],
            // Demonstration accounts (published password: ossz2026).
            ['email' => 'owner@osszcollections.cm',   'username' => 'owner',   'full_name' => 'OSSZ Owner',          'role' => 'admin',    'password' => 'ossz2026'],
            ['email' => 'studio@osszcollections.cm',  'username' => 'studio',  'full_name' => 'OSSZ Studio',         'role' => 'uploader', 'password' => 'ossz2026'],
            ['email' => 'client@example.com',         'username' => null,      'full_name' => 'Demo Customer',       'role' => 'customer', 'password' => 'ossz2026'],
        ];
        foreach ($accounts as $s) {
            $creating = ! User::where('email', $s['email'])->exists();
            $values = [
                'full_name' => $s['full_name'],
                'role' => $s['role'],
            ];
            if ($s['username'] !== null) {
                $values['username'] = $s['username'];
            }
            if ($creating) {
                $values['password_hash'] = Auth::hashPassword($s['password']);
            }
            User::updateOrCreate(['email' => $s['email']], $values);
            if ($creating) {
                $this->command?->warn("Account created — {$s['role']}: {$s['email']} — password: {$s['password']}");
            }
        }

        // ── Categories ─────────────────────────────────────────────────
        $categories = [
            ['name' => 'Ready to Wear', 'name_fr' => 'Prêt-à-porter', 'slug' => 'ready-to-wear', 'sort_order' => 0],
            ['name' => 'Kaftans', 'name_fr' => 'Kaftans', 'slug' => 'kaftans', 'sort_order' => 1],
            ['name' => 'Agbada', 'name_fr' => 'Agbada', 'slug' => 'agbada', 'sort_order' => 2],
            ['name' => 'Pants', 'name_fr' => 'Pantalons', 'slug' => 'pants', 'sort_order' => 3],
            ['name' => 'Shirts', 'name_fr' => 'Chemises', 'slug' => 'shirts', 'sort_order' => 4],
            ['name' => 'Danshiki', 'name_fr' => 'Danshiki', 'slug' => 'danshiki', 'sort_order' => 5],
            ['name' => 'Oversize', 'name_fr' => 'Oversize', 'slug' => 'oversize', 'sort_order' => 6],
            ['name' => 'Hats', 'name_fr' => 'Chapeaux', 'slug' => 'hats', 'sort_order' => 7],
            ['name' => 'Shoes', 'name_fr' => 'Chaussures', 'slug' => 'shoes', 'sort_order' => 8],
            ['name' => 'Sandals', 'name_fr' => 'Sandales', 'slug' => 'sandals', 'sort_order' => 9],
            ['name' => 'Cufflinks', 'name_fr' => 'Boutons de manchette', 'slug' => 'cufflinks', 'sort_order' => 10],
            ['name' => 'Bags', 'name_fr' => 'Sacs', 'slug' => 'bags', 'sort_order' => 11],
            ['name' => 'Tie / Cravate', 'name_fr' => 'Cravate', 'slug' => 'tie-cravate', 'sort_order' => 12],
            ['name' => 'Bold Tie', 'name_fr' => 'Cravate audacieuse', 'slug' => 'bold-tie', 'sort_order' => 13],
            ['name' => 'Accessories', 'name_fr' => 'Accessoires', 'slug' => 'accessories', 'sort_order' => 14],
            // Categories used by the live catalogue
            ['name' => 'Evening', 'name_fr' => 'Soirée', 'slug' => 'evening', 'sort_order' => 100],
            ['name' => 'Tailoring', 'name_fr' => 'Tailleur', 'slug' => 'tailoring', 'sort_order' => 101],
            ['name' => 'Knitwear', 'name_fr' => 'Maille', 'slug' => 'knitwear', 'sort_order' => 102],
        ];
        foreach ($categories as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        // ── Collections (the three live ones) ──────────────────────────
        $collections = [
            [
                'name' => 'Wouri Nights', 'name_fr' => 'Nuits du Wouri', 'slug' => 'wouri-nights',
                'season' => 'Season 04', 'season_fr' => 'Saison 04',
                'description' => 'Evening pieces cut for the long, warm nights along the Wouri — fluid silks, hand-finished seams and a restrained shimmer.',
                'description_fr' => 'Des pièces de soirée taillées pour les longues nuits chaudes du Wouri — soies fluides, coutures finies à la main et un éclat retenu.',
                'cover_image' => '/catalogue/charcoal-draped-look-3.jpg', 'is_featured' => true, 'sort_order' => 0,
            ],
            [
                'name' => 'Atelier Essentials', 'name_fr' => "Essentiels d'Atelier", 'slug' => 'atelier-essentials',
                'season' => 'Permanent', 'season_fr' => 'Permanente',
                'description' => 'The quiet backbone of the OSSZ wardrobe: precise tailoring, honest cottons and pieces built to be worn for a decade.',
                'description_fr' => 'La colonne vertébrale discrète du vestiaire OSSZ : tailleur précis, cotons honnêtes et pièces faites pour durer dix ans.',
                'cover_image' => '/catalogue/camel-tailored-coat-2.jpg', 'is_featured' => true, 'sort_order' => 1,
            ],
            [
                'name' => 'Sawa Sun', 'name_fr' => 'Soleil Sawa', 'slug' => 'sawa-sun',
                'season' => 'Season 03', 'season_fr' => 'Saison 03',
                'description' => 'Light, breathable daywear inspired by Douala mornings — loose linen, raffia detail and colours drawn from the coast.',
                'description_fr' => 'Des vêtements de jour légers et respirants inspirés des matins de Douala — lin fluide, détails en raphia et couleurs de la côte.',
                'cover_image' => '/catalogue/olive-safari-set-2.jpg', 'is_featured' => false, 'sort_order' => 2,
            ],
        ];
        foreach ($collections as $col) {
            Collection::updateOrCreate(['slug' => $col['slug']], $col);
        }

        // ── Catalogue — the twelve live pieces and their photography ───
        $pieces = [
            [
                'key' => 'ivory-occasion-gown', 'name' => 'Ivoire Occasion Gown', 'name_fr' => "Robe d'Occasion Ivoire",
                'cat' => 'evening', 'col' => 'wouri-nights', 'base_price' => 295000, 'featured' => true,
                'description' => 'A softly structured occasion gown in ivory, photographed in our Douala studio.',
                'description_fr' => 'Une robe d\'occasion à la structure souple, en ivoire, photographiée dans notre studio de Douala.',
                'details' => 'Fluid lining with a sculpted bodice. Concealed back closure. Finished by hand in Akwa.',
                'details_fr' => 'Doublure fluide et buste sculpté. Fermeture dos dissimulée. Finie à la main à Akwa.',
                'care' => 'Dry clean only. Store on a padded hanger.', 'care_fr' => 'Nettoyage à sec uniquement. Conserver sur un cintre rembourré.',
                'colours' => ['Ivory', 'Champagne'],
            ],
            [
                'key' => 'mauve-signature-set', 'name' => 'Mauve Signature Set', 'name_fr' => 'Ensemble Signature Mauve',
                'cat' => 'ready-to-wear', 'col' => 'wouri-nights', 'base_price' => 245000, 'featured' => true,
                'description' => 'A two-piece signature set in soft mauve, cut for movement and evening light.',
                'description_fr' => 'Un ensemble deux pièces signature en mauve doux, taillé pour le mouvement et la lumière du soir.',
                'details' => 'Matching separates. Relaxed shoulder, tapered waist. Lined throughout.',
                'details_fr' => 'Pièces assorties. Épaule décontractée, taille ajustée. Entièrement doublé.',
                'care' => 'Dry clean recommended.', 'care_fr' => 'Nettoyage à sec recommandé.',
                'colours' => ['Mauve', 'Rose'],
            ],
            [
                'key' => 'teal-evening-piece', 'name' => 'Wouri Teal Evening Piece', 'name_fr' => 'Pièce du Soir Sarcelle Wouri',
                'cat' => 'evening', 'col' => 'wouri-nights', 'base_price' => 268000, 'featured' => true,
                'description' => 'Deep teal evening wear with a quiet sheen, made for the long nights along the Wouri.',
                'description_fr' => 'Une tenue de soirée sarcelle profond au lustre discret, pensée pour les longues nuits du Wouri.',
                'details' => 'Weighted drape. Hand-finished seams. Full lining.',
                'details_fr' => 'Tombé lesté. Coutures finies main. Doublure complète.',
                'care' => 'Dry clean only.', 'care_fr' => 'Nettoyage à sec uniquement.',
                'colours' => ['Teal', 'Obsidian'],
            ],
            [
                'key' => 'burgundy-statement', 'name' => 'Burgundy Statement Gown', 'name_fr' => 'Robe Déclaration Bordeaux',
                'cat' => 'evening', 'col' => 'wouri-nights', 'base_price' => 312000, 'featured' => true,
                'description' => 'A deep burgundy statement piece with sculptural volume through the skirt.',
                'description_fr' => 'Une pièce déclaration bordeaux profond, au volume sculptural sur la jupe.',
                'details' => 'Structured underskirt. Boned bodice. Made to order in three weeks.',
                'details_fr' => 'Jupon structuré. Buste baleiné. Fabriquée sur commande en trois semaines.',
                'care' => 'Specialist dry clean.', 'care_fr' => 'Nettoyage à sec spécialisé.',
                'colours' => ['Burgundy', 'Oxblood'],
            ],
            [
                'key' => 'amber-heritage-look', 'name' => 'Amber Heritage Look', 'name_fr' => 'Ensemble Patrimoine Ambre',
                'cat' => 'ready-to-wear', 'col' => 'sawa-sun', 'base_price' => 186000, 'featured' => true,
                'description' => 'Warm amber tones drawn from Cameroonian heritage textiles, shown in four views.',
                'description_fr' => 'Des tons ambrés chauds inspirés des textiles patrimoniaux camerounais, présentés en quatre vues.',
                'details' => 'Woven detail at the yoke. Breathable weight. Generous through the sleeve.',
                'details_fr' => 'Détail tissé à l\'empiècement. Grammage respirant. Manche généreuse.',
                'care' => 'Gentle hand wash. Line dry in shade.', 'care_fr' => 'Lavage main délicat. Séchage à l\'ombre.',
                'colours' => ['Amber', 'Ochre'],
            ],
            [
                'key' => 'charcoal-draped-look', 'name' => 'Charcoal Draped Look', 'name_fr' => 'Ensemble Drapé Anthracite',
                'cat' => 'tailoring', 'col' => 'atelier-essentials', 'base_price' => 198000, 'featured' => false,
                'description' => 'A charcoal draped silhouette, photographed from three angles in studio.',
                'description_fr' => 'Une silhouette drapée anthracite, photographiée sous trois angles en studio.',
                'details' => 'Asymmetric drape. Wool-blend suiting. Half-lined.',
                'details_fr' => 'Drapé asymétrique. Laine mélangée. Demi-doublure.',
                'care' => 'Dry clean. Brush after wear.', 'care_fr' => 'Nettoyage à sec. Brosser après le port.',
                'colours' => ['Charcoal', 'Slate'],
            ],
            [
                'key' => 'camel-tailored-coat', 'name' => 'Camel Tailored Coat', 'name_fr' => 'Manteau Tailleur Camel',
                'cat' => 'tailoring', 'col' => 'atelier-essentials', 'base_price' => 275000, 'featured' => false,
                'description' => 'A camel coat cut long and clean, the quiet backbone of a considered wardrobe.',
                'description_fr' => 'Un manteau camel coupé long et net, colonne vertébrale discrète d\'un vestiaire réfléchi.',
                'details' => 'Notch lapel. Horn buttons. Vented back.',
                'details_fr' => 'Revers cranté. Boutons en corne. Dos aéré.',
                'care' => 'Dry clean. Do not tumble dry.', 'care_fr' => 'Nettoyage à sec. Ne pas sécher en machine.',
                'colours' => ['Camel', 'Sand'],
            ],
            [
                'key' => 'olive-safari-set', 'name' => 'Olive Safari Set', 'name_fr' => 'Ensemble Safari Olive',
                'cat' => 'ready-to-wear', 'col' => 'sawa-sun', 'base_price' => 164000, 'featured' => false,
                'description' => 'An olive safari set in breathable cotton, made for Douala mornings.',
                'description_fr' => 'Un ensemble safari olive en coton respirant, pensé pour les matins de Douala.',
                'details' => 'Patch pockets. Self belt. Unlined for the heat.',
                'details_fr' => 'Poches plaquées. Ceinture assortie. Non doublé pour la chaleur.',
                'care' => 'Machine wash cold, gentle cycle.', 'care_fr' => 'Lavage machine à froid, cycle délicat.',
                'colours' => ['Olive', 'Khaki'],
            ],
            [
                'key' => 'midnight-column', 'name' => 'Midnight Column Dress', 'name_fr' => 'Robe Colonne Minuit',
                'cat' => 'evening', 'col' => 'wouri-nights', 'base_price' => 232000, 'featured' => false,
                'description' => 'A midnight-blue column dress that skims rather than clings.',
                'description_fr' => 'Une robe colonne bleu minuit qui effleure le corps sans le mouler.',
                'details' => 'Bias cut. Hidden side zip. Fully lined.',
                'details_fr' => 'Coupe en biais. Fermeture latérale invisible. Entièrement doublée.',
                'care' => 'Dry clean only.', 'care_fr' => 'Nettoyage à sec uniquement.',
                'colours' => ['Midnight', 'Navy'],
            ],
            [
                'key' => 'periwinkle-drape', 'name' => 'Periwinkle Drape Gown', 'name_fr' => 'Robe Drapée Pervenche',
                'cat' => 'evening', 'col' => 'wouri-nights', 'base_price' => 288000, 'featured' => false,
                'description' => 'A periwinkle gown with a single sculpted shoulder and a long, quiet fall.',
                'description_fr' => 'Une robe pervenche à l\'épaule sculptée unique et à la chute longue et discrète.',
                'details' => 'One-shoulder construction. Internal corsetry. Floor length.',
                'details_fr' => 'Construction une épaule. Corseterie interne. Longueur au sol.',
                'care' => 'Specialist dry clean.', 'care_fr' => 'Nettoyage à sec spécialisé.',
                'colours' => ['Periwinkle'],
            ],
            [
                'key' => 'sand-linen-look', 'name' => 'Sand Linen Look', 'name_fr' => 'Ensemble Lin Sable',
                'cat' => 'ready-to-wear', 'col' => 'sawa-sun', 'base_price' => 142000, 'featured' => false,
                'description' => 'Washed sand linen, soft from the first wear.',
                'description_fr' => 'Un lin sable lavé, doux dès le premier port.',
                'details' => 'European linen. Mother-of-pearl buttons. Relaxed fit.',
                'details_fr' => 'Lin européen. Boutons en nacre. Coupe décontractée.',
                'care' => 'Machine wash cold. Warm iron.', 'care_fr' => 'Lavage machine à froid. Repassage tiède.',
                'colours' => ['Sand', 'Ivory'],
            ],
            [
                'key' => 'noir-editorial', 'name' => 'Noir Editorial Piece', 'name_fr' => 'Pièce Éditoriale Noir',
                'cat' => 'evening', 'col' => 'atelier-essentials', 'base_price' => 254000, 'featured' => false,
                'description' => 'A black editorial piece, shot for the house lookbook.',
                'description_fr' => 'Une pièce éditoriale noire, photographiée pour le lookbook de la maison.',
                'details' => 'Sculpted shoulder. Matte finish. Fully lined.',
                'details_fr' => 'Épaule sculptée. Finition mate. Entièrement doublée.',
                'care' => 'Dry clean only.', 'care_fr' => 'Nettoyage à sec uniquement.',
                'colours' => ['Noir'],
            ],
        ];

        // Photography: two-angle galleries for the premium pieces, one image
        // for the rest — no photograph appears twice on the site.
        $photos = [
            'ivory-occasion-gown' => ['/catalogue/ivory-occasion-gown-1.jpg', '/catalogue/ivory-occasion-gown-2.jpg'],
            'mauve-signature-set' => ['/catalogue/mauve-signature-set-1.jpg', '/catalogue/mauve-signature-set-2.jpg'],
            'teal-evening-piece' => ['/catalogue/teal-evening-piece-1.jpg', '/catalogue/teal-evening-piece-2.jpg'],
            'burgundy-statement' => ['/catalogue/burgundy-statement-1.jpg', '/catalogue/burgundy-statement-2.jpg'],
            'amber-heritage-look' => ['/catalogue/amber-heritage-look-1.jpg', '/catalogue/amber-heritage-look-2.jpg'],
            'charcoal-draped-look' => ['/catalogue/charcoal-draped-look-1.jpg', '/catalogue/charcoal-draped-look-2.jpg'],
            'camel-tailored-coat' => ['/catalogue/camel-tailored-coat-1.jpg'],
            'olive-safari-set' => ['/catalogue/olive-safari-set-1.jpg'],
            'midnight-column' => ['/catalogue/midnight-column-1.jpg'],
            'periwinkle-drape' => ['/catalogue/periwinkle-drape-1.jpg'],
            'sand-linen-look' => ['/catalogue/sand-linen-look-1.jpg'],
            'noir-editorial' => ['/catalogue/noir-editorial-1.jpg'],
        ];

        $sizes = ['XS', 'S', 'M', 'L'];
        $catsBySlug = Category::pluck('id', 'slug');
        $colsBySlug = Collection::pluck('id', 'slug');

        foreach ($pieces as $i => $piece) {
            $photos[$piece['key']] ??= [];
            if (count($photos[$piece['key']]) === 0) {
                continue;
            }
            $slug = slugify($piece['name']);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $piece['name'],
                    'name_fr' => $piece['name_fr'],
                    'description' => $piece['description'],
                    'description_fr' => $piece['description_fr'],
                    'details' => $piece['details'],
                    'details_fr' => $piece['details_fr'],
                    'care_instructions' => $piece['care'],
                    'care_instructions_fr' => $piece['care_fr'],
                    'category_id' => $catsBySlug[$piece['cat']] ?? null,
                    'collection_id' => $colsBySlug[$piece['col']] ?? null,
                    'base_price' => $piece['base_price'],
                    'is_published' => true,
                    'is_featured' => (bool) $piece['featured'],
                    'popularity' => 100 - $i * 6,
                    'seo_title' => $piece['name'].' — OSSZ Collections',
                    'seo_description' => mb_substr($piece['description'], 0, 155),
                ]
            );

            // Gallery: every photo of the same article becomes a slide, in order.
            foreach ($photos[$piece['key']] as $j => $url) {
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => $j],
                    ['url' => $url, 'alt_text' => $piece['name'].' — view '.($j + 1)]
                );
            }

            foreach ($piece['colours'] as $colour) {
                foreach ($sizes as $k => $size) {
                    ProductVariant::updateOrCreate(
                        ['product_id' => $product->id, 'size' => $size, 'colour' => $colour],
                        [
                            'sku' => strtoupper(mb_substr($slug, 0, 8)).'-'.$size.'-'.strtoupper(mb_substr($colour, 0, 3)),
                            'stock_qty' => (($i + $k) % 5) + 2,
                            'low_stock_threshold' => 3,
                        ]
                    );
                }
            }
        }

        // ── Delivery zones ─────────────────────────────────────────────
        $zones = [
            ['name' => 'In-store pickup — Ange Raphael boutique', 'name_fr' => 'Retrait en boutique — Ange Raphael', 'method' => 'pickup', 'fee' => 0, 'eta_label' => 'Ready within 24h', 'eta_label_fr' => 'Prêt sous 24h'],
            ['name' => 'Douala local delivery', 'name_fr' => 'Livraison locale Douala', 'method' => 'douala_local', 'fee' => 2000, 'eta_label' => 'Same day', 'eta_label_fr' => 'Le jour même'],
            ['name' => 'National shipping — Cameroon', 'name_fr' => 'Livraison nationale — Cameroun', 'method' => 'national', 'fee' => 6500, 'eta_label' => '2–4 days', 'eta_label_fr' => '2 à 4 jours'],
        ];
        foreach ($zones as $zone) {
            DeliveryZone::updateOrCreate(['name' => $zone['name']], $zone + ['is_active' => true]);
        }

        // ── Settings ───────────────────────────────────────────────────
        $settings = [
            'whatsapp_number' => '+237694068219',
            'whatsapp_number2' => '+237651468831',
            'store_address' => 'Ange Raphael, Douala, Cameroon',
            'business_hours' => 'Monday to Friday, 9:00am - 6pm. Saturday 9:00am - 1pm',
            'contact_email' => 'info@osszcollection.com',
            'cod_enabled' => 'false',
            'mobile_money_enabled' => 'true',
            'card_enabled' => 'true',
            'free_delivery_threshold' => '150000',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // ── Homepage blocks — the three live ones ──────────────────────
        HomeBlock::updateOrCreate(
            ['type' => 'hero', 'sort_order' => 0],
            [
                'eyebrow' => '',
                'heading' => 'Bring Out The Class in You',
                'heading_fr' => 'Révélez la classe en vous',
                'body' => 'Hand-finished silks and quiet tailoring, made in our Ange Raphael atelier and delivered across Cameroon.',
                'body_fr' => 'Un service de couture sur mesure haut de gamme. Créés au sein de notre atelier de Douala et expédiés en toute sécurité dans toutes les régions du Cameroun.',
                'image_url' => '/catalogue/editorial-hero.jpg',
                'cta_label' => 'Discover our collections',
                'cta_label_fr' => 'Découvrir nos collections',
                'cta_href' => '/collections',
                'is_published' => true,
            ]
        );
        HomeBlock::updateOrCreate(
            ['type' => 'banner', 'sort_order' => 1],
            [
                'eyebrow' => 'Atelier Essentials',
                'eyebrow_fr' => "Essentiels d'Atelier",
                'heading' => 'The pieces you will keep for a decade',
                'heading_fr' => 'Les pièces que vous garderez dix ans',
                'body' => 'Precise tailoring in honest fabric — the quiet backbone of the OSSZ wardrobe.',
                'body_fr' => 'Un tailleur précis dans une étoffe honnête — la colonne vertébrale discrète du vestiaire OSSZ.',
                'image_url' => '/catalogue/editorial-b.jpg',
                'cta_label' => 'Shop essentials',
                'cta_label_fr' => 'Voir les essentiels',
                'cta_href' => '/collections/atelier-essentials',
                'is_published' => true,
            ]
        );
        HomeBlock::updateOrCreate(
            ['type' => 'quote', 'sort_order' => 2],
            [
                'eyebrow' => 'Our promise',
                'eyebrow_fr' => 'Notre promesse',
                'heading' => 'Fitted in person, wherever you are',
                'heading_fr' => 'Essayée en personne, où que vous soyez',
                'body' => 'Book a private styling appointment in Akwa, or let our concierge guide you online.',
                'body_fr' => 'Réservez un rendez-vous de style privé à notre boutique Ange Raphael, ou laissez notre concierge vous guider en ligne.',
                'image_url' => '/catalogue/amber-heritage-look-3.jpg',
                'cta_label' => 'Book an appointment',
                'cta_label_fr' => 'Prendre rendez-vous',
                'cta_href' => '/appointments',
                'is_published' => true,
            ]
        );

        // ── FAQs ───────────────────────────────────────────────────────
        $faqs = [
            ['category' => 'Delivery', 'category_fr' => 'Livraison', 'question' => 'How long does delivery take?', 'question_fr' => 'Quels sont les délais de livraison ?', 'answer' => 'Douala deliveries arrive the same day. National shipping reaches every region of Cameroon in 2–4 days. In-store pickup is ready within 24 hours.', 'answer_fr' => 'Les livraisons à Douala arrivent le jour même. L\'expédition nationale couvre tout le Cameroun en 2 à 4 jours. Le retrait en boutique est prêt sous 24 heures.', 'sort_order' => 1],
            ['category' => 'Payment', 'category_fr' => 'Paiement', 'question' => 'Which payment methods do you accept?', 'question_fr' => 'Quels moyens de paiement acceptez-vous ?', 'answer' => 'MTN Mobile Money, Orange Money, Visa and Mastercard. Cash on delivery is available in Douala on request.', 'answer_fr' => 'MTN Mobile Money, Orange Money, Visa et Mastercard. Le paiement à la livraison est possible à Douala sur demande.', 'sort_order' => 2],
            ['category' => 'Returns', 'category_fr' => 'Retours', 'question' => 'Can I return a piece?', 'question_fr' => 'Puis-je retourner une pièce ?', 'answer' => 'Unworn pieces may be returned within 14 days with their garment bag. Complimentary alterations are included within 30 days of purchase.', 'answer_fr' => 'Les pièces non portées sont reprises sous 14 jours avec leur housse. Les retouches sont offertes dans les 30 jours suivant l\'achat.', 'sort_order' => 3],
            ['category' => 'Sizing', 'category_fr' => 'Tailles', 'question' => 'How do OSSZ sizes run?', 'question_fr' => 'Comment taillent les pièces OSSZ ?', 'answer' => 'Our ready-to-wear runs true to size with generous ease through the shoulder. XS fits FR 34, S fits FR 36, M fits FR 38, L fits FR 40.', 'answer_fr' => 'Notre prêt-à-porter taille normalement, avec une aisance généreuse à l\'épaule. XS correspond au FR 34, S au FR 36, M au FR 38, L au FR 40.', 'sort_order' => 4],
        ];
        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq);
        }

        // ── Journal — the three live articles, bilingual, with covers ──
        $journal = [
            [
                'slug' => 'inside-the-akwa-atelier',
                'title' => 'Inside our Ange Raphael atelier', 'title_fr' => 'Dans notre atelier Ange Raphael',
                'excerpt' => 'Four tailors, one long table, and the patience that a hand-rolled hem demands.',
                'excerpt_fr' => "Quatre tailleurs, une longue table, et la patience qu'exige un ourlet roulé main.",
                'body' => "Our atelier is at Ange Raphael in Douala.\n\nEvery OSSZ piece begins on a long shared table where four tailors work in a rhythm they set themselves. A silk hem is rolled by hand — roughly forty minutes of work that most people will never notice, and that everyone feels when the dress moves.\n\nWe keep production small on purpose. A style is cut in a run of twenty, sometimes thirty, and when it is finished it is finished.",
                'body_fr' => "Notre atelier se trouve à Ange Raphael, à Douala.\n\nChaque pièce OSSZ commence sur une longue table partagée où quatre tailleurs travaillent au rythme qu'ils fixent eux-mêmes. Un ourlet de soie est roulé à la main — environ quarante minutes de travail que presque personne ne remarquera, et que tout le monde ressent lorsque la robe bouge.\n\nNous gardons volontairement de petites séries. Un modèle est coupé en vingt, parfois trente exemplaires, et lorsqu'il est terminé, il est terminé.",
                'cover_image' => '/catalogue/journal-atelier.png',
            ],
            [
                'slug' => 'how-to-wear-silk-in-the-humidity',
                'title' => 'How to wear silk in the humidity', 'title_fr' => "Porter la soie sous l'humidité",
                'excerpt' => 'Douala is warm and wet for much of the year. Silk still belongs here — with a little care.',
                'excerpt_fr' => "Douala est chaude et humide une bonne partie de l'année. La soie y a toute sa place — avec un peu de soin.",
                'body' => "The trick is weight and cut, not avoidance.\n\nChoose a bias cut that falls away from the body rather than clinging to it. Keep beading for the evening, when the air cools. And air a silk piece overnight before returning it to the wardrobe — never fold it damp.\n\nOur concierge can advise on fabric weight for any piece; simply ask.",
                'body_fr' => "Tout est affaire de poids et de coupe, non d'évitement.\n\nChoisissez une coupe en biais qui s'écarte du corps plutôt qu'elle ne s'y colle. Réservez le perlage au soir, quand l'air se rafraîchit. Et aérez une pièce en soie toute une nuit avant de la ranger — ne la pliez jamais humide.\n\nNotre concierge peut vous conseiller sur le grammage de n'importe quelle pièce ; il suffit de demander.",
                'cover_image' => '/catalogue/journal-silk-humidity.jpg',
            ],
            [
                'slug' => 'a-guide-to-your-first-fitting',
                'title' => 'A guide to your first fitting', 'title_fr' => 'Guide de votre premier essayage',
                'excerpt' => 'What to bring, what to expect, and why we take forty minutes rather than ten.',
                'excerpt_fr' => 'Quoi apporter, à quoi s\'attendre, et pourquoi nous prenons quarante minutes plutôt que dix.',
                'body' => "A first fitting at OSSZ takes about forty minutes.\n\nBring the shoes you intend to wear, and, if you can, a photograph of the occasion or setting. We will take eight measurements, discuss ease and movement, and agree a delivery date before you leave.\n\nAlterations on OSSZ pieces are complimentary within thirty days of purchase.",
                'body_fr' => "Un premier essayage chez OSSZ dure environ quarante minutes.\n\nApportez les chaussures que vous comptez porter et, si possible, une photographie de l'occasion ou du lieu. Nous prendrons huit mesures, discuterons de l'aisance et du mouvement, et conviendrons d'une date de livraison avant votre départ.\n\nLes retouches sur les pièces OSSZ sont offertes dans les trente jours suivant l'achat.",
                'cover_image' => '/catalogue/journal-first-fitting.png',
            ],
        ];
        foreach ($journal as $post) {
            JournalPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post + ['author_name' => 'OSSZ Studio', 'status' => 'published', 'published_at' => now()]
            );
        }

        // ── Lookbook — the two live editorial slides ───────────────────
        LookbookItem::updateOrCreate(
            ['image_url' => '/catalogue/midnight-column-2.jpg', 'sort_order' => 0],
            ['title' => 'Midnight Column', 'caption' => 'Midnight Column', 'caption_fr' => 'Midnight Column', 'media_type' => 'image', 'product_slug' => 'midnight-column-dress']
        );
        LookbookItem::updateOrCreate(
            ['image_url' => '/catalogue/editorial-a.jpg', 'sort_order' => 1],
            ['title' => 'In the atelier', 'caption' => 'In the atelier', 'caption_fr' => 'Dans l\'atelier', 'media_type' => 'image', 'product_slug' => '']
        );

        // ── Welcome coupon ─────────────────────────────────────────────
        Coupon::updateOrCreate(
            ['code' => 'BONJOUR10'],
            ['type' => 'percentage', 'value' => 10, 'is_active' => true]
        );
    }
}
