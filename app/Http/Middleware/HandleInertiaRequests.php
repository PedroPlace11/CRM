<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => fn () => [
                'user' => $request->user()?->only(['id', 'name', 'email']),
                'roles' => $request->user()?->getRoleNames() ?? [],
            ],
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'ziggy' => fn () => array_merge((new \Tighten\Ziggy\Ziggy)->toArray(), [
                'location' => $request->url(),
            ]),
        ]);
    }
}
