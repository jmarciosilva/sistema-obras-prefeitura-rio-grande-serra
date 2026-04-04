<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\ExecucaoObraController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\PerfilController;

// Admin
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\EmpresaController;
use App\Http\Controllers\Admin\StatusObraController;
use App\Http\Controllers\Admin\CategoriaConvenioController;
use App\Http\Controllers\Admin\OrgaoFinanciadorController;
use App\Http\Controllers\Admin\DemandaPropostaController;

// ───────────────────────────────────────────────────────────────
// 🔐 ROTAS DE AUTENTICAÇÃO (Laravel Breeze)
// ───────────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ───────────────────────────────────────────────────────────────
// 🔒 ROTAS PROTEGIDAS (USUÁRIO LOGADO)
// ───────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ───────────────────────────────────────────────────────────
    // 📊 DASHBOARD
    // ───────────────────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ───────────────────────────────────────────────────────────
    // 👤 PERFIL DO USUÁRIO
    // ───────────────────────────────────────────────────────────
    Route::prefix('perfil')->name('perfil.')->group(function () {
        Route::get('/', [PerfilController::class, 'edit'])->name('edit');
        Route::put('/atualizar', [PerfilController::class, 'update'])->name('update');
        Route::put('/senha', [PerfilController::class, 'updateSenha'])->name('update-senha');
    });

    // ───────────────────────────────────────────────────────────
    // 🏗️ OBRAS (ROTAS EXPLÍCITAS)
    // ───────────────────────────────────────────────────────────
    Route::prefix('obras')->name('obras.')->group(function () {
        Route::put('/obras/{obra}/convenios/sync', [ObraController::class, 'syncConvenios'])
            ->name('obras.convenios.sync')
            ->middleware('perfil:admin,tecnico');

        // 📄 LISTAGEM
        Route::get('/', [ObraController::class, 'index'])->name('index');

        // ➕ CRIAÇÃO
        Route::middleware('perfil:admin,tecnico')->group(function () {
            Route::get('/criar', [ObraController::class, 'create'])->name('create');
            Route::post('/salvar', [ObraController::class, 'store'])->name('store');
        });

        // ✏️ EDIÇÃO
        Route::middleware('perfil:admin,tecnico')->group(function () {
            Route::get('/{obra}/editar', [ObraController::class, 'edit'])->name('edit');
            Route::put('/{obra}/atualizar', [ObraController::class, 'update'])->name('update');
             Route::put('/{obra}/convenios/sync', [ObraController::class, 'syncConvenios'])->name('convenios.sync');
        });

        // 👁️ VISUALIZAÇÃO (SEMPRE POR ÚLTIMO)
        Route::get('/visualizar/{obra}', [ObraController::class, 'show'])->name('show');

        // 🗑️ EXCLUSÃO
        Route::middleware('perfil:admin')->group(function () {
            Route::delete('/{obra}/excluir', [ObraController::class, 'destroy'])->name('destroy');
        });



        // ───────────────
        // 📊 EXECUÇÕES (MEDIÇÕES)
        // ───────────────
        Route::prefix('{obra}/execucoes')->name('execucoes.')->group(function () {

            Route::get('/', [ExecucaoObraController::class, 'index'])->name('index');

            Route::middleware('perfil:admin,tecnico')->group(function () {
                Route::get('/criar',  [ExecucaoObraController::class, 'create'])->name('create');
                Route::post('/salvar', [ExecucaoObraController::class, 'store'])->name('store');
                Route::get('/{execucao}/editar',   [ExecucaoObraController::class, 'edit'])->name('edit');
                Route::put('/{execucao}/atualizar', [ExecucaoObraController::class, 'update'])->name('update');

                // ↓ NOVAS ROTAS — documentos da medição ↓
                Route::get(
                    '/{execucao}/documentos/{documento}/download',
                    [ExecucaoObraController::class, 'downloadDocumento']
                )
                    ->name('documentos.download');
            });

            Route::middleware('perfil:admin')->group(function () {
                Route::delete('/{execucao}/excluir', [ExecucaoObraController::class, 'destroy'])->name('destroy');

                // ↓ NOVA ROTA — remover documento da medição (somente admin) ↓
                Route::delete(
                    '/{execucao}/documentos/{documento}',
                    [ExecucaoObraController::class, 'destroyDocumento']
                )
                    ->name('documentos.destroy');
            });
        });

        // ───────────────
        // 📎 DOCUMENTOS
        // ───────────────
        Route::prefix('{obra}/documentos')->name('documentos.')->group(function () {

            Route::middleware('perfil:admin,tecnico')->group(function () {
                Route::post('/upload', [DocumentoController::class, 'store'])->name('store');
            });

            Route::middleware('perfil:admin')->group(function () {
                Route::delete('/{documento}/excluir', [DocumentoController::class, 'destroy'])->name('destroy');
            });

            Route::get('/{documento}/download', [DocumentoController::class, 'download'])->name('download');
        });
    });

    // ───────────────────────────────────────────────────────────
    // 📄 CONVÊNIOS
    // ───────────────────────────────────────────────────────────
    Route::prefix('convenios')->name('convenios.')->group(function () {

        Route::get('/', [ConvenioController::class, 'index'])->name('index');

        Route::middleware('perfil:admin,tecnico')->group(function () {
            Route::get('/criar', [ConvenioController::class, 'create'])->name('create');
            Route::post('/salvar', [ConvenioController::class, 'store'])->name('store');
            Route::get('/{convenio}/editar', [ConvenioController::class, 'edit'])->name('edit');
            Route::put('/{convenio}/atualizar', [ConvenioController::class, 'update'])->name('update');

            // ↓ NOVAS ROTAS ↓
            Route::get('/{convenio}/obras', [ConvenioController::class, 'obras'])->name('obras');
            Route::post('/{convenio}/obras/vincular', [ConvenioController::class, 'vincularObras'])->name('vincular-obras');
            Route::delete('/{convenio}/obras/{obra}', [ConvenioController::class, 'desvincularObra'])->name('desvincular-obra');
        });

        Route::get('/visualizar/{convenio}', [ConvenioController::class, 'show'])->name('show');

        Route::middleware('perfil:admin')->group(function () {
            Route::delete('/{convenio}/excluir', [ConvenioController::class, 'destroy'])->name('destroy');
        });
    });

    // ───────────────────────────────────────────────────────────
    // 📋 CONTRATOS
    // ───────────────────────────────────────────────────────────
    Route::prefix('contratos')->name('contratos.')->group(function () {

        Route::get('/', [ContratoController::class, 'index'])->name('index');

        Route::middleware('perfil:admin,tecnico')->group(function () {
            Route::get('/criar', [ContratoController::class, 'create'])->name('create');
            Route::post('/salvar', [ContratoController::class, 'store'])->name('store');
            Route::get('/{contrato}/editar', [ContratoController::class, 'edit'])->name('edit');
            Route::put('/{contrato}/atualizar', [ContratoController::class, 'update'])->name('update');
        });

        Route::get('/visualizar/{contrato}', [ContratoController::class, 'show'])->name('show');

        Route::middleware('perfil:admin')->group(function () {
            Route::delete('/{contrato}/excluir', [ContratoController::class, 'destroy'])->name('destroy');
        });
    });

    // ───────────────────────────────────────────────────────────
    // ⚙️ ADMIN (SOMENTE ADMIN)
    // ───────────────────────────────────────────────────────────
    Route::middleware('perfil:admin')->prefix('admin')->name('admin.')->group(function () {

        // 👤 Usuários
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/criar', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('usuarios/salvar', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('usuarios/visualizar/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::get('usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}/atualizar', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}/excluir', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        Route::patch('usuarios/{usuario}/toggle-ativo', [UsuarioController::class, 'toggleAtivo'])->name('usuarios.toggle-ativo');

        // 🏢 Empresas
        Route::get('empresas', [EmpresaController::class, 'index'])->name('empresas.index');
        Route::get('empresas/criar', [EmpresaController::class, 'create'])->name('empresas.create');
        Route::post('empresas/salvar', [EmpresaController::class, 'store'])->name('empresas.store');
        Route::get('empresas/visualizar/{empresa}', [EmpresaController::class, 'show'])->name('empresas.show');
        Route::get('empresas/{empresa}/editar', [EmpresaController::class, 'edit'])->name('empresas.edit');
        Route::put('empresas/{empresa}/atualizar', [EmpresaController::class, 'update'])->name('empresas.update');
        Route::delete('empresas/{empresa}/excluir', [EmpresaController::class, 'destroy'])->name('empresas.destroy');

        // 🏷️ Status de Obras
        Route::get('status-obras', [StatusObraController::class, 'index'])->name('status-obras.index');
        Route::get('status-obras/criar', [StatusObraController::class, 'create'])->name('status-obras.create');
        Route::post('status-obras/salvar', [StatusObraController::class, 'store'])->name('status-obras.store');
        Route::get('status-obras/visualizar/{status}', [StatusObraController::class, 'show'])->name('status-obras.show');
        Route::get('status-obras/{status}/editar', [StatusObraController::class, 'edit'])->name('status-obras.edit');
        Route::put('status-obras/{status}/atualizar', [StatusObraController::class, 'update'])->name('status-obras.update');
        Route::delete('status-obras/{status}/excluir', [StatusObraController::class, 'destroy'])->name('status-obras.destroy');

        // 📁 Categorias
        Route::get('categorias-convenio', [CategoriaConvenioController::class, 'index'])->name('categorias-convenio.index');
        Route::get('categorias-convenio/criar', [CategoriaConvenioController::class, 'create'])->name('categorias-convenio.create');
        Route::post('categorias-convenio/salvar', [CategoriaConvenioController::class, 'store'])->name('categorias-convenio.store');
        Route::get('categorias-convenio/visualizar/{categoria}', [CategoriaConvenioController::class, 'show'])->name('categorias-convenio.show');
        Route::get('categorias-convenio/{categoria}/editar', [CategoriaConvenioController::class, 'edit'])->name('categorias-convenio.edit');
        Route::put('categorias-convenio/{categoria}/atualizar', [CategoriaConvenioController::class, 'update'])->name('categorias-convenio.update');
        Route::delete('categorias-convenio/{categoria}/excluir', [CategoriaConvenioController::class, 'destroy'])->name('categorias-convenio.destroy');

        // 🏦 Órgãos
        Route::get('orgaos-financiadores', [OrgaoFinanciadorController::class, 'index'])->name('orgaos-financiadores.index');
        Route::get('orgaos-financiadores/criar', [OrgaoFinanciadorController::class, 'create'])->name('orgaos-financiadores.create');
        Route::post('orgaos-financiadores/salvar', [OrgaoFinanciadorController::class, 'store'])->name('orgaos-financiadores.store');
        Route::get('orgaos-financiadores/visualizar/{orgao}', [OrgaoFinanciadorController::class, 'show'])->name('orgaos-financiadores.show');
        Route::get('orgaos-financiadores/{orgao}/editar', [OrgaoFinanciadorController::class, 'edit'])->name('orgaos-financiadores.edit');
        Route::put('orgaos-financiadores/{orgao}/atualizar', [OrgaoFinanciadorController::class, 'update'])->name('orgaos-financiadores.update');
        Route::delete('orgaos-financiadores/{orgao}/excluir', [OrgaoFinanciadorController::class, 'destroy'])->name('orgaos-financiadores.destroy');

        // 📝 Demandas
        Route::get('demandas-propostas', [DemandaPropostaController::class, 'index'])->name('demandas-propostas.index');
        Route::get('demandas-propostas/criar', [DemandaPropostaController::class, 'create'])->name('demandas-propostas.create');
        Route::post('demandas-propostas/salvar', [DemandaPropostaController::class, 'store'])->name('demandas-propostas.store');
        Route::get('demandas-propostas/visualizar/{demanda}', [DemandaPropostaController::class, 'show'])->name('demandas-propostas.show');
        Route::get('demandas-propostas/{demanda}/editar', [DemandaPropostaController::class, 'edit'])->name('demandas-propostas.edit');
        Route::put('demandas-propostas/{demanda}/atualizar', [DemandaPropostaController::class, 'update'])->name('demandas-propostas.update');
        Route::delete('demandas-propostas/{demanda}/excluir', [DemandaPropostaController::class, 'destroy'])->name('demandas-propostas.destroy');
    });
});
