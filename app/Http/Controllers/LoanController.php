<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with('book')->paginate();

        return response()->json(LoanResource::collection($loans));
    }

    public function store(StoreLoanRequest $request)
    {
        $this->authorize('create', Loan::class);

        $book = Book::find($request->input('book_id'));

        if (! $book->is_available || $book->available_copies === 0) {
            return response()->json(
                ['message' => 'No hay copias disponibles para este libro.'],
                422
            );
        }

        $loan = Loan::create([
            'requester_name' => $request->input('requester_name'),
            'book_id'        => $request->input('book_id'),
            'user_id'        => auth()->id(),
        ]);

        $book->update([
            'available_copies' => $book->available_copies - 1,
            'is_available'     => $book->available_copies - 1 > 0,
        ]);

        return response()->json(
            ['message' => 'Prestamo registrado exitosamente.', 'data' => $loan],
            201
        );
    }

    public function return(Loan $loan): JsonResponse
    {
        $this->authorize('return', $loan);

        if (! is_null($loan->return_at)) {
            return response()->json(
                ['message' => 'Este prestamo ya fue devuelto.'],
                422
            );
        }

        $loan->update(['return_at' => now()]);

        $loan->book->update([
            'available_copies' => $loan->book->available_copies + 1,
            'is_available'     => true,
        ]);

        return response()->json(
            ['message' => 'Devolucion registrada exitosamente.', 'data' => $loan->fresh()]
        );
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $loans = $user->role === 'bibliotecario'
            ? Loan::with('book')->latest()->get()
            : Loan::with('book')->where('user_id', $user->id)->latest()->get();

        return response()->json(['data' => $loans]);
    }

    public function show(string $id) {}

    public function update(Request $request, string $id) {}

    public function destroy(string $id) {}
}