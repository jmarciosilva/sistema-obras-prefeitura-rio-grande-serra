<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    /** Upload de documento vinculado a uma Obra. */
    public function store(Request $request, Obra $obra)
    {
        $request->validate([
            'arquivo'   => 'required|file|max:20480', // 20 MB
            'tipo'      => 'required|in:' . implode(',', array_keys(Documento::$tipos)),
            'descricao' => 'nullable|string|max:300',
        ]);

        $arquivo = $request->file('arquivo');

        $caminho = $arquivo->store("documentos/obras/{$obra->id}", 'public');

        $obra->documentos()->create([
            'user_id'      => auth()->id(),
            'tipo'         => $request->tipo,
            'nome_original'=> $arquivo->getClientOriginalName(),
            'caminho'      => $caminho,
            'mime_type'    => $arquivo->getMimeType(),
            'tamanho_bytes'=> $arquivo->getSize(),
            'descricao'    => $request->descricao,
        ]);

        return back()->with('sucesso', 'Documento anexado com sucesso!');
    }

    /** Download seguro do arquivo. */
    public function download(Obra $obra, Documento $documento)
    {
        abort_unless(Storage::disk('public')->exists($documento->caminho), 404);

        return Storage::disk('public')->download(
            $documento->caminho,
            $documento->nome_original
        );
    }

    /** Remove documento (e o arquivo físico via model event). */
    public function destroy(Obra $obra, Documento $documento)
    {
        $documento->delete();

        return back()->with('sucesso', 'Documento removido.');
    }
}
