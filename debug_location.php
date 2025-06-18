<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Concert;
use App\Models\Checkin;
use App\Http\Resources\CheckinResource;

// Test the specific concert
$concert = Concert::with(['location', 'location.country'])->find('019778fb-0b5f-7168-8862-2f870d21c0c2');

echo "Concert ID: " . $concert->id . "\n";
echo "Location ID: " . $concert->location_id . "\n";
echo "Location loaded: " . ($concert->location ? 'YES' : 'NO') . "\n";

if ($concert->location) {
    echo "Location name: " . $concert->location->name . "\n";
    echo "Location city: " . $concert->location->city . "\n";
    echo "Country loaded: " . ($concert->location->country ? 'YES' : 'NO') . "\n";
    if ($concert->location->country) {
        echo "Country name: " . $concert->location->country->name . "\n";
    }
}

// Test the checkin
$checkin = Checkin::with([
    'user:id,name,username,profile_photo_path',
    'concert:id,date,event_id',
    'concert.event:id,name,type,image_url',
    'concert.location:id,name,city,country_id',
    'concert.location.country:id,name',
    'concert.artists:id,name,image_url',
    'concert.artists.genres:id,genre',
    'photos:id,checkin_id,url,caption',
    'likes:id,checkin_id,user_id',
    'comments:id,checkin_id,comment,created_at,user_id',
    'comments.user:id,name,username,profile_photo_path',
    'rating:id,checkin_id,rating',
    'review:id,checkin_id,review'
])->find('019779a6-0052-71f7-9b7e-0e2534dfb6e9');

echo "\nCheckin ID: " . $checkin->id . "\n";
echo "Concert location loaded: " . ($checkin->concert->location ? 'YES' : 'NO') . "\n";

if ($checkin->concert->location) {
    echo "Location name: " . $checkin->concert->location->name . "\n";
    echo "Location city: " . $checkin->concert->location->city . "\n";
    echo "Country loaded: " . ($checkin->concert->location->country ? 'YES' : 'NO') . "\n";
    if ($checkin->concert->location->country) {
        echo "Country name: " . $checkin->concert->location->country->name . "\n";
    }
}

// Test the resource transformation
$resource = new CheckinResource($checkin);
$array = $resource->toArray(request());

echo "\nResource location: " . ($array['concert']['location'] ? 'NOT NULL' : 'NULL') . "\n";
if ($array['concert']['location']) {
    echo "Location name: " . $array['concert']['location']['name'] . "\n";
    echo "Location city: " . $array['concert']['location']['city'] . "\n";
    echo "Country: " . $array['concert']['location']['country'] . "\n";
}
