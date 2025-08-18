<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['jwt.auth']]);

Broadcast::channel('tracking.{orderId}', function ($user, $orderId) {
    // Ici tu peux vérifier que $user peut accéder à cette commande
    return true;
});


/*Broadcast::channel('transporter.*', function () {
    return true;
});*/

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    logger('****************' . $userId);
    return true;
});

Broadcast::channel('transporter.{transporterId}', function ($user, $transporterId) {
    // Exemple : vérifier que l’utilisateur peut écouter ce transporteur
    // return (int) $user->transporter_id === (int) $transporterId;
    logger('tr****************' . $transporterId);
    return true;
});
