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
    /**
     * Display a listing of the resource for web.
     */
    public function index()
    {
        try {
            $transacciones = Transaction::with(['origenSucursal', 'destinoSucursal', 'productos'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transacciones,
                'message' => 'Transacciones obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las transacciones',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display a listing of the resource for API.
     */
    public function apiIndex(Request $request)
    {
        try {
            $query = Transaction::with(['origenSucursal', 'destinoSucursal', 'productos'])
                ->orderBy('created_at', 'desc');

            if ($request->has('sucursal_id')) {
                $query->where(function ($q) use ($request) {
                    $q->where('origen_sucursal_id', $request->sucursal_id)
                        ->orWhere('destino_sucursal_id', $request->sucursal_id);
                });
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where('codigo', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notas', 'LIKE', "%{$searchTerm}%");
            }

            $transacciones = $query->get();

            return response()->json([
                'success' => true,
                'data' => $transacciones,
                'message' => 'Transacciones obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las transacciones',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage for API.
     */
    public function apiStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'origen_sucursal_id' => 'required|exists:sucursals,id',
                'destino_sucursal_id' => 'required|exists:sucursals,id',
                'productos' => 'required|array|min:1',
                'productos.*.producto_id' => 'required|exists:productos,id',
                'productos.*.cantidad' => 'required|integer|min:1',
                'notas' => 'nullable|string',
                'prioridad' => 'required|in:low,medium,high'
            ]);

            $transaction = DB::transaction(function () use ($validated) {
                $transaction = Transaction::create([
                    'origen_sucursal_id' => $validated['origen_sucursal_id'],
                    'destino_sucursal_id' => $validated['destino_sucursal_id'],
                    'codigo' => 'TRX-' . time(),
                    'estado' => 'pendiente',
                    'notas' => $validated['notas'] ?? null,
                    'prioridad' => $validated['prioridad'],
                    'user_id' => Auth::id()
                ]);

                foreach ($validated['productos'] as $producto) {
                    $transaction->productos()->attach($producto['producto_id'], [
                        'cantidad' => $producto['cantidad'],
                        'estado' => 'pendiente'
                    ]);
                }

                event(new TransactionProcessed($transaction, 'created'));

                return $transaction->load('origenSucursal', 'destinoSucursal', 'productos');
            });

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Transacción creada exitosamente'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la transacción',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Display the specified resource for API.
     */
    public function apiShow(Transaction $transaction)
    {
        try {
            $transaction->load(['origenSucursal', 'destinoSucursal', 'productos', 'user']);

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Transacción obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la transacción',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage for API.
     */
    public function apiUpdate(Request $request, Transaction $transaction)
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:pendiente,en_transito,completada,cancelada',
                'notas' => 'nullable|string'
            ]);

            $transaction->update([
                'estado' => $validated['estado'],
                'notas' => $validated['notas'] ?? $transaction->notas
            ]);

            event(new TransactionProcessed($transaction, 'status_updated'));

            return response()->json([
                'success' => true,
                'data' => $transaction->load(['origenSucursal', 'destinoSucursal', 'productos']),
                'message' => 'Transacción actualizada exitosamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la transacción',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage for API.
     */
    public function apiDestroy(Transaction $transaction)
    {
        try {
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transacción eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la transacción',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Get realtime transactions feed for API.
     */
    public function apiRealtimeFeed()
    {
        try {
            $transactions = Transaction::with(['origenSucursal', 'destinoSucursal'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Feed de transacciones en tiempo real obtenido exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el feed de transacciones',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Update transaction status for API.
     */
    public function apiUpdateStatus(Request $request, Transaction $transaction)
    {
        try {
            $validated = $request->validate([
                'estado' => 'required|in:pendiente,en_transito,completada,cancelada',
                'notas' => 'nullable|string'
            ]);

            $transaction->update([
                'estado' => $validated['estado'],
                'notas' => $validated['notas'] ?? $transaction->notas
            ]);

            event(new TransactionProcessed($transaction, 'status_updated'));

            return response()->json([
                'success' => true,
                'data' => $transaction,
                'message' => 'Estado de la transacción actualizado exitosamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de la transacción',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }
}
