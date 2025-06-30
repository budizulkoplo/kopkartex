<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalApp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    function buildTree($elements, $parentId = null) {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $level=explode(';', $element->role);
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                //if(in_array(auth()->user()->getRoleNames()->first(), $level))
                //$element->dd=auth()->user()->getRoleNames()->first();
                $branch[] = $element;
            }
        } 
        return $branch;
    }
    public function handle(Request $request, Closure $next): Response
    {
        $menu = Menu::orderBy('seq', 'asc')->get();
        $request->merge([
            'menu' => $this->buildTree($menu),
            
            ]);
        return $next($request);
    }
}
