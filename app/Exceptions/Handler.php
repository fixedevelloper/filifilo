<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;


class Handler extends ExceptionHandler
{
    public function report(Throwable $e)
    {
        // Journalisez les exceptions comme vous le souhaitez
        parent::report($e);
    }

    public function render(Request $request, Throwable $e)
    {
        // Vous pouvez également définir comment les exceptions sont rendues pour les requêtes API
        if ($request->is('api/*')) {
            return $this->handleApiExceptions($e);
        }

        return parent::render($request, $e);
    }

    protected function handleApiExceptions(Throwable $e)
    {
        $response = [
            'message' => 'Une erreur interne s\'est produite.',
        ];

        // Si c'est une erreur 404, personnalisez le message
        if ($e->getStatusCode() === 404) {
            $response['message'] = 'La ressource demandée n\'a pas été trouvée.';
        }

        return response()->json($response, $e->getStatusCode() ?: 500);
    }
}
