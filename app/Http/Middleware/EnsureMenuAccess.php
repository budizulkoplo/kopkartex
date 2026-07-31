<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();

        if (! $routeName) {
            abort(403, 'Route ini belum memiliki nama dan tidak bisa diverifikasi akses menunya.');
        }

        $allowedMenus = Menu::query()
            ->whereNotNull('link')
            ->where('link', '!=', '')
            ->get()
            ->filter(fn (Menu $menu) => $menu->hasRoleAccess($user->getRoleNames()->all()));

        $routeNames = $this->routeAliases($routeName);

        $allowed = $allowedMenus->contains(function (Menu $menu) use ($routeNames) {
            $menuLink = trim((string) $menu->link);

            if ($menuLink === '' || $menuLink === '#') {
                return false;
            }

            if (in_array($menuLink, $routeNames, true)) {
                return true;
            }

            foreach ($routeNames as $routeName) {
                foreach ($this->routePatternsFromMenuLink($menuLink) as $pattern) {
                    if ($pattern !== '' && str_starts_with($routeName, $pattern . '.')) {
                        return true;
                    }
                }
            }

            return false;
        });

        abort_unless($allowed, 403, 'Anda tidak memiliki akses ke menu ini.');

        return $next($request);
    }

    protected function routePatternsFromMenuLink(string $menuLink): array
    {
        $segments = explode('.', $menuLink);
        $patterns = [$menuLink];
        $lastSegment = end($segments);
        $groupSuffixes = ['index', 'list', 'form'];

        if (count($segments) > 1 && in_array($lastSegment, $groupSuffixes, true)) {
            array_pop($segments);
            $patterns[] = implode('.', $segments);
        }

        return array_values(array_unique(array_filter($patterns)));
    }

    protected function routeAliases(string $routeName): array
    {
        $aliases = [$routeName];

        if (str_starts_with($routeName, 'penjualan.')) {
            $aliases[] = 'jual.' . substr($routeName, strlen('penjualan.'));
        }

        return array_values(array_unique($aliases));
    }
}
