<?php

namespace App\Observers;

use App\Models\Client;

class ClientObserver
{
    public function creating(Client $client)
    {
        if (auth()->check()) {
            $client->team_id = auth()->user()->team_id;
        }

        if (! $client->avatar_color) {
            // Generate a light color
            $r = rand(10, 128);
            $g = rand(10, 128);
            $b = rand(10, 128);
            $hexColor = sprintf('#%02x%02x%02x', $r, $g, $b);

            $client->avatar_color = $hexColor;
        }
    }
}
