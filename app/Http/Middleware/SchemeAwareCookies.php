<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Scheme-aware cookie flags — port of the Next.js cookieFlags() helper
 * (src/lib/auth.ts, commit "Fix login 'nothing happens': scheme-aware
 * cookies + CHIPS Partitioned").
 *
 * Why: browsers silently discard cookies whose flags do not match the
 * browsing context.
 *
 *  - Plain HTTP hosts cannot set Secure cookies at all (they are dropped),
 *    which wedged sign-in on non-TLS hosts.
 *  - Embedded contexts (preview panels, social embeds) need SameSite=None
 *    for the session to survive, but Chrome additionally requires the
 *    Partitioned (CHIPS) attribute for third-party cookies and Safari
 *    blocks non-partitioned ones outright.
 *
 * The middleware must run FIRST in the web group so every cookie emission
 * point (StartSession, CSRF token cookie, locale cookie, queued cookies)
 * observes the resolved flags.
 */
class SchemeAwareCookies
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (config('session.cross_site_disabled') === true) {
            // Escape hatch (COOKIE_CROSS_SITE=0): force the lax pair.
            $this->apply(false, 'lax', false);
        } else {
            $forwarded = strtolower(trim(explode(',', (string) $request->header('x-forwarded-proto', ''))[0]));
            if ($request->isSecure() || $forwarded === 'https') {
                $this->apply(true, 'none', true);
            } else {
                $this->apply(false, 'lax', false);
            }
        }

        return $next($request);
    }

    /**
     * Apply the resolved flags to both config namespaces read by cookie
     * emission points: config('session.*') (session + CSRF token cookies)
     * and config('cookie.*') (cookie() helper / queued cookies).
     */
    private function apply(bool $secure, string $sameSite, bool $partitioned): void
    {
        config([
            'session.secure' => $secure,
            'session.same_site' => $sameSite,
            'session.partitioned' => $partitioned,
            'cookie.secure' => $secure,
            'cookie.same_site' => $sameSite,
            'cookie.partitioned' => $partitioned,
        ]);

        // The CookieJar captures path/domain/secure/same_site as defaults
        // when the singleton is resolved. Re-apply them so queued cookies
        // (e.g. the ossz_locale switch) pick up the resolved flags even if
        // the jar already exists.
        try {
            $jar = app('cookie');
            $jar->setDefaultPathAndDomain(
                config('cookie.path', '/'),
                config('cookie.domain'),
                $secure,
                $sameSite,
            );
        } catch (\Throwable) {
            // Cookie service unavailable (e.g. outside a request) — skip.
        }
    }
}
