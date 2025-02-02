<?php

namespace App\Http\Controllers\Api;

use App\Models\Banca;
use App\Models\Produto;
use App\Models\ProdutoTabelado;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Http\Controllers\Controller;
use App\Services\FileService;
use Exception;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $produtos = Produto::all();

        return response()->json(['produtos' => $produtos], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProdutoRequest $request)
    {
        $validatedData = $request->validated();
        $banca = Banca::find($request->banca_id);

        $produto = $banca->produtos()->onlyTrashed()->where(['produto_tabelado_id' => $validatedData['produto_tabelado_id']])->first();

        if($produto) {
            $produto->restore();
            $produto->update($validatedData);
        } else {
            try {
                $produto  = $banca->produtos()->create($validatedData);
            } catch (Exception $e) {
                if ($e->getCode() === '23505') {
                    return response()->json(['erro' => 'O produto já existe na banca.'], 400);
                }
            }
        }

        return response()->json(['produto' => $produto], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $produto = Produto::findOrFail($id);

        return response()->json(['produto' => $produto], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProdutoRequest $request, $id)
    {
        $validatedData = $request->validated();
        $produto = Produto::find($id);

        $produto->update($validatedData);

        return response()->json(['produto' => $produto]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produto = Produto::findOrfail($id);
        $this->authorize('delete', $produto);

        $produto->delete();

        return response()->noContent();
    }

    public function getTabelados()
    {
        $produtos = ProdutoTabelado::all();
        
        $produtos->map(function ($produto) {
            $arquivo = $produto->file;
            if ($arquivo) {
                $produto->setAttribute('imagem', base64_encode(file_get_contents($arquivo->path)));
            }
        });

        return response()->json(['produtos' => $produtos], 200);
    }

    public function getCategorias()
    {
        return response()->json(['categorias' => ProdutoTabelado::distinct()->pluck('categoria')], 200);
    }

    public function getBancaProdutos($id)
    {
        $banca = Banca::findOrFail($id);

        return response()->json(['produtos' => $banca->produtos], 200);
    }

    public function getImagem($id)
    {
        $produto = ProdutoTabelado::findOrFail($id);
        $caminho = $produto->file->path;

        $dados['file'] = file_get_contents($caminho);
        $dados['mimeType'] = mime_content_type($caminho);

        return response($dados['file'])->header('Content-Type', $dados['mimeType']);
    }
}
