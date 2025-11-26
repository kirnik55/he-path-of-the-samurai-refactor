<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class IssController extends Controller
{
    protected function base(): string
    {
        return env('RUST_BASE', 'http://rust_iss:3000');
    }

    protected function getJson(string $url): array
    {
        try {
            $resp = Http::timeout(2)->get($url);
            if ($resp->successful()) {
                return $resp->json();
            }
        } catch (\Throwable $e) {

        }

        return [];
    }

    public function index()
    {
        $base = $this->base();

        $iss = $this->getJson($base . '/last');

        return view('iss', [
            'iss'  => $iss,   
            'last' => $iss,   
            'base' => $base,
        ]);
    }
}
