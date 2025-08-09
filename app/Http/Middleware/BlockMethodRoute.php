<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockMethodRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if ($request->isMethod('POST') && $request->path() === 'penjualan/export/excel') {
        //     // Redirect, abort, or return a specific response
        //     return $next($request);
        // }

        // abort(404);

        // blocking semua route
        // if ($request->isMethod('GET')) {
        //     abort(404);
        // }

        // return $next($request);

        if (
            $request->isMethod('GET') &&
            preg_match('#^penjualan($|/export/[^/]+$|/report$)#', $request->path())
        ) {
            abort(404, 'Tidak bisa akses halaman!');
        }

        // if (
        //     $request->isMethod('GET') &&
        //     (
        //         preg_match('#^penjualan$#', $request->path()) || // /penjualan persis
        //         preg_match('#^penjualan/export/[^/]+$#', $request->path()) // /penjualan/export/{mode}
        //     )
        // ) {
        //     abort(404);
        // }

        return $next($request);
    }
}
