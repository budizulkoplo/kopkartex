<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalApp
{
    protected function buildTree($elements, array $roleNames, $parentId = null): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $roleNames, $element->id);

                if ($children) {
                    $element->children = $children;
                }

                if (! $element->hasRoleAccess($roleNames) && empty($children)) {
                    continue;
                }

                if ($children) {
                    $element->setAttribute('children', $children);
                }

                $branch[] = $element;
            }
        }

        return $branch;
    }

    public function handle(Request $request, Closure $next, $role = null): Response
    {
        $user = Auth::user();

        // Redirect jika user belum login
        if (!$user) {
            return redirect()->route('login');
        }

        // Cek role jika parameter diberikan
        if ($role && $user->ui !== $role) {
            // Redirect ke halaman sesuai role
            return match ($user->ui) {
                'admin' => redirect()->route('dashboard'),
                'user' => redirect()->route('mobile.home'),
                default => redirect()->route('login'),
            };
        }

        // Build menu
        $menu = Menu::orderBy('seq', 'asc')->get();
        $roleNames = $user->getRoleNames()->all();

        $request->merge([
            'menu' => $this->buildTree($menu, $roleNames),
        ]);

        return $next($request);
    }
}
