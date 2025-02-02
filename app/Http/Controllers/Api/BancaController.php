<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBancaRequest;
use App\Http\Requests\UpdateBancaRequest;
use App\Models\Banca;
use App\Models\File;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;

class BancaController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index()
    {
        $bancas = Banca::all();

        return response()->json(['bancas' => $bancas], 200);
    }

    public function store(StoreBancaRequest $request)
    {
        $validatedData = $request->validated();

        DB::beginTransaction();
        $banca = Banca::create($validatedData);
        $banca->formasPagamento()->sync($validatedData['formas_pagamento']);
        foreach ($request->bairro_entrega as $bairro_info) {
            $banca->bairros_info_entrega()->attach($bairro_info[0], ['taxa' => $bairro_info[1]]);
        }
        
        if ($banca->agricultor->associacao) {
            if ($banca->agricultor->associacao->feiras()->where('id', $banca->feira_id)->exists() || $banca->agricultor->organizacao->associacao->feiras()->where('id', $banca->feira_id)->exists()) {
                $banca->ativa = true;
            }
            
            $banca->save();
        }

        if ($request->hasFile('imagem')) {
            $this->fileService->storeFile($request->file('imagem'), $banca); // Armazenar a imagem
        } 
        DB::commit();

        return response()->json(['banca' => $banca], 201);
    }

    public function show($id)
    {
        $banca = Banca::findOrFail($id);

        return response()->json(['banca' => $banca], 200);
    }

    public function update(UpdateBancaRequest $request, $id)
    {
        $validatedData = $request->validated();
        $banca = Banca::findOrFail($id);

        DB::beginTransaction();
        $banca->update($validatedData);
        $banca->formasPagamento()->sync($validatedData['formas_pagamento']);
        foreach ($request->bairro_entrega as $bairro_info) {
            $banca->bairros_info_entrega()->detach();
            $banca->bairros_info_entrega()->attach($bairro_info[0], ['taxa' => $bairro_info[1]]);
        }

        if ($request->hasFile('imagem')) {
            $this->fileService->updateFile($request->file('imagem'), $banca); // Atualizar a imagem
        }
        DB::commit();

        return response()->json(['banca' => $banca], 200);
    }

    public function destroy($id)
    {
        $banca = Banca::findOrFail($id);
        $this->authorize('delete', $banca);

        $this->fileService->deleteFile($banca->file);
        $banca->delete();

        return response()->noContent();
    }

    public function getAgricultorBancas($agricultorId)
    {
        $bancas = User::findOrFail($agricultorId)->bancas;

        return response()->json(['bancas' => $bancas], 200);
    }

    public function getImagem($id)
    {
        $banca = Banca::findOrFail($id);
        $dados = $this->fileService->getFile($banca->file);

        return response($dados['file'])->header('Content-Type', $dados['mimeType']);
    }

    public function deleteImagem($id)
    {
        $banca = Banca::findOrFail($id);
        $this->authorize('deleteImagem', $banca);

        $this->fileService->deleteFile($banca->file);

        return response()->noContent();
    }
}
