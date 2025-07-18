<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tu peux ajouter une logique de permission ici si nécessaire
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:livraison,surplace,emporter',
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de commande est requis.',
            'type.in' => 'Le type de commande doit être : livraison, surplace ou emporter.',
            'store_id.required' => 'Le magasin est requis.',
            'store_id.exists' => 'Le magasin sélectionné n’existe pas.',
            'items.required' => 'Vous devez ajouter au moins un article.',
            'items.array' => 'Les articles doivent être envoyés sous forme de tableau.',
            'items.*.name.required' => 'Le nom de chaque article est requis.',
            'items.*.quantity.required' => 'La quantité est requise pour chaque article.',
            'items.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'items.*.quantity.min' => 'La quantité doit être au moins 1.',
            'items.*.price.required' => 'Le prix est requis pour chaque article.',
            'items.*.price.numeric' => 'Le prix doit être un nombre.',
            'items.*.price.min' => 'Le prix doit être au moins 0.',
            'items.*.total.required' => 'Le total est requis pour chaque article.',
            'items.*.total.numeric' => 'Le total doit être un nombre.',
            'items.*.total.min' => 'Le total doit être au moins 0.',
        ];
    }
}
