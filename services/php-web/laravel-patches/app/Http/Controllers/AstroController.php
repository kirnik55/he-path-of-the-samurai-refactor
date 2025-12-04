<?php

namespace App\Http\Controllers;

use App\Http\Requests\AstroEventsRequest;


class AstroController extends Controller
{
    public function page()
    {
        return view('astro');
    }

    public function events(AstroEventsRequest $request)
    {
        $data = $request->validated();

        $lat  = (float) $data['lat'];
        $lon  = (float) $data['lon'];
        $days = (int)   $data['days'];

        $from = now('UTC')->toDateString();
        $to   = now('UTC')->addDays($days)->toDateString();
        $time = now('UTC')->format('H:i:s');

        $appId  = env('ASTRO_APP_ID', '');
        $secret = env('ASTRO_APP_SECRET', '');
        if ($appId === '' || $secret === '') {
            return response()->json(['error' => 'Missing ASTRO_APP_ID/ASTRO_APP_SECRET'], 500);
        }

        $auth = base64_encode($appId . ':' . $secret);

        $bodies  = ['sun', 'moon'];
        $results = [];

        foreach ($bodies as $body) {
            $url = 'https://api.astronomyapi.com/api/v2/bodies/events/' . $body . '?' . http_build_query([
                'latitude'   => $lat,
                'longitude'  => $lon,
                'elevation'  => 0,
                'from_date'  => $from,
                'to_date'    => $to,
                'time'       => $time,
                'output'     => 'table',
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Basic ' . $auth,
                    'Content-Type: application/json',
                    'User-Agent: monolith-iss/1.0',
                ],
                CURLOPT_TIMEOUT        => 25,
            ]);
            $raw  = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 0;
            $err  = curl_error($ch);
            curl_close($ch);

            if ($raw === false || $code >= 400) {
                $results[$body] = [
                    'error' => $err ?: ("HTTP " . $code),
                    'code'  => $code,
                    'raw'   => $raw,
                ];
            } else {
                $json = json_decode($raw, true);
                $results[$body] = $json ?? ['raw' => $raw];
            }
        }

        return response()->json($results);
    }
}
