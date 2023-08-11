<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreEnderecoRequest;
use App\Models\Endereco;
use App\Models\User;

class UserConsumidorController extends UserController
{
    public function indexEndereco($consumidor_id)
    {
        $enderecos = User::findOrFail($consumidor_id)->enderecos;

        return response()->json(['enderecos' => $enderecos], 200);
    }

    public function storeEndereco(StoreEnderecoRequest $request)
    {
        $validatedData = $request->validated();

        $user = $request->user();
        $this->authorize('create', Endereco::class);

        $endereco = Endereco::make($validatedData);
        $user->enderecos()->save($endereco);

        return response()->json(['endereco' => $endereco], 201);
    }

    public function showEndereco($id)
    {
        $endereco = Endereco::findOrFail($id);

        return response()->json(['endereco' => $endereco], 200);
    }

    public function updateEndereco(StoreEnderecoRequest $request, $id)
    {
        $validatedData = $request->validated();

        $endereco = Endereco::findOrFail($id);
        $this->authorize('update', $endereco);

        $endereco->update($validatedData);

        return response()->json(['endereco' => $endereco], 200);
    }

    public function destroyEndereco($endereco_id)
    {
        $endereco = Endereco::findOrFail($endereco_id);
        $this->authorize('delete', $endereco);

        $endereco->delete();

        return response()->noContent();
    }
}
