<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\Organization;

$orgs = Organization::all();
echo "Total Organizations: " . $orgs->count() . "\n\n";

foreach($orgs as $org) {
    echo "ID: {$org->id} | Name: {$org->name}\n";
    echo "  Channels: " . $org->channels()->count() . "\n";
    echo "  Posts: " . $org->posts()->count() . "\n";
    echo "  Public Posts: " . $org->posts()->whereHas('channel', function ($q) {
        $q->where('type', 'public');
    })->count() . "\n\n";
}
