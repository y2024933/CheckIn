<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::channel('publicAnnouncement', function ($user, $id) {
//     return true;
// });

// Broadcast::channel('Group.*', function ($user, $id) {
//     // return $user->id === account::findOrFail($id)->accountid;
//     return true;
// });

// Broadcast::channel('Group.it', function ($user, $id) {
//     // return $user->id === account::findOrFail($id)->accountid;
//     return [
//         'id' => $id,
//     ];
// });