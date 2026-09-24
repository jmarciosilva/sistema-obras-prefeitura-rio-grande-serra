{{--
|  Modal de exclusão de medição (somente admin) com motivo obrigatório.
|  Abrir com: $dispatch('excluir-medicao', { url: '...', resumo: '10/06/2026 — R$ 1.000,00' })
|  O motivo e o snapshot da medição ficam no Histórico de Atividades.
--}}
@if (auth()->user()->perfil === 'admin')
    @error('motivo')
        <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
            ⚠️ Medição não excluída: {{ $message }}
        </div>
    @enderror

    <div x-data="{ aberto: false, url: '', resumo: '' }"
        @excluir-medicao.window="aberto = true; url = $event.detail.url; resumo = $event.detail.resumo"
        @keydown.escape.window="aberto = false">
        <div x-show="aberto" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <form :action="url" method="POST" @click.outside="aberto = false"
                class="bg-white rounded-xl p-6 w-full max-w-md space-y-4">
                @csrf
                @method('DELETE')

                <div class="text-center">
                    <h3 class="text-lg font-semibold text-red-600">Excluir medição</h3>
                    <p class="text-sm text-slate-700 mt-1 font-medium" x-text="resumo"></p>
                    <p class="text-sm text-slate-600 mt-2">A medição e seus documentos anexados serão removidos.</p>
                    <p class="text-xs text-slate-500 mt-1">
                        ⚠️ O saldo e percentual exibidos nas demais medições não serão recalculados retroativamente.
                    </p>
                </div>

                <div class="space-y-1 text-left">
                    <label for="motivo-exclusao" class="text-sm font-medium text-slate-700">
                        Motivo da exclusão <span class="text-red-600">*</span>
                    </label>
                    <textarea id="motivo-exclusao" name="motivo" rows="3" required minlength="10" maxlength="1000"
                        placeholder="Ex.: medição lançada em duplicidade"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                    <p class="text-xs text-slate-500">🔒 Fica registrado no Histórico de Atividades, com os dados da medição.</p>
                </div>

                <div class="flex justify-center gap-3">
                    <button type="button" @click="aberto = false" class="px-4 py-2 border rounded">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-red-600 text-white rounded">Excluir</button>
                </div>
            </form>
        </div>
    </div>
@endif
