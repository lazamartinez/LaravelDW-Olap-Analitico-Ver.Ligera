<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Models\Transaction;
use App\Events\TransactionProcessed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['origenSucursal', 'destinoSucursal', 'productos'])
            ->orderBy('created_at', 'desc');

        if ($request->has('sucursal_id')) {
            $query->where(function($q) use ($request) {
                $q->where('origen_sucursal_id', $request->sucursal_id)
                  ->orWhere('destino_sucursal_id', $request->sucursal_id);
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origen_sucursal_id' => 'required|exists:sucursals,id',
            'destino_sucursal_id' => 'required|exists:sucursals,id',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'notas' => 'nullable|string',
            'prioridad' => 'required|in:low,medium,high'
        ]);

        return DB::transaction(function () use ($validated) {
            $transaction = Transaction::create([
                'origen_sucursal_id' => $validated['origen_sucursal_id'],
                'destino_sucursal_id' => $validated['destino_sucursal_id'],
                'codigo' => 'TRX-' . time(),
                'estado' => 'pendiente',
                'notas' => $validated['notas'] ?? null,
                'prioridad' => $validated['prioridad'],
                'user_id' => Auth::id() // Uso auth con facade
            ]);

            foreach ($validated['productos'] as $producto) {
                $transaction->productos()->attach($producto['producto_id'], [
                    'cantidad' => $producto['cantidad'],
                    'estado' => 'pendiente'
                ]);
            }

            event(new TransactionProcessed($transaction, 'created'));

            return response()->json($transaction->load('origenSucursal', 'destinoSucursal', 'productos'));
        });
    }
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_transito,completada,cancelada',
            'notas' => 'nullable|string'
        ]);

        $transaction->update([
            'estado' => $validated['estado'],
            'notas' => $validated['notas'] ?? $transaction->notas
        ]);

        event(new TransactionProcessed($transaction, 'status_updated'));

        return response()->json($transaction);
    }

    public function realtimeFeed()
    {
        $transactions = Transaction::with(['origenSucursal', 'destinoSucursal'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($transactions);
    }
    
}