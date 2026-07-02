@php
    $responsavel = $responsavel ?? null;
@endphp

<div class="px-8 py-6 space-y-6">

    <div class="space-y-2">
        <label class="text-sm font-medium">Nome *</label>
        <input type="text" name="nome" value="{{ old('nome', $responsavel->nome ?? '') }}" required
            placeholder="Nome completo"
            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="space-y-2">
            <label class="text-sm font-medium">Registro (CREA/CAU)</label>
            <input type="text" name="registro" value="{{ old('registro', $responsavel->registro ?? '') }}"
                placeholder="Ex: CREA-SP 123456"
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="space-y-2">
            <label class="text-sm font-medium">Telefone</label>
            <input type="text" name="telefone" value="{{ old('telefone', $responsavel->telefone ?? '') }}"
                placeholder="(11) 98888-8888"
                class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
        </div>

    </div>

</div>
