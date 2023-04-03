<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Debts;
use App\Models\Dispen;
use App\Enums\DebtStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DebtsController extends Controller
{
    public function index()
    {
        return Debts::query()->with('user:id,name')
            ->when(request('status'), function ($query) {
                return $query->where('status', DebtStatus::from(request(('status'))));
            })
            ->latest()
            ->paginate(3)
            ->through(fn ($app) => [
                'id' => $app->id,
                'status' => [
                    'name' => $app->status->name,
                    'color' => $app->status->color(),
                ],
                'user' => $app->user,
                'title' => $app->title,
                'debt' => $app->debt,
                'remainder' => $app->remainder
            ]);
    }

    public function search()
    {
        $debtorName = request('query');
        $status = request('status');

        $debts = Debts::query()
            ->with('user:id,name')
            ->when($debtorName, function ($query) use ($debtorName) {
                return $query->whereHas('user', function ($q) use ($debtorName) {
                    $q->where('name', 'LIKE', "%$debtorName%");
                });
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', DebtStatus::from($status));
            })
            ->paginate(3)->through(fn ($app) => [
                'id' => $app->id,
                'status' => [
                    'name' => $app->status->name,
                    'color' => $app->status->color(),
                ],
                'user' => $app->user,
                'title' => $app->title,
                'debt' => $app->debt,
                'remainder' => $app->remainder
            ]);

        return response()->json($debts);
    }

    public function bulkDelete()
    {
        Debts::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Debts deleted successfully!']);
    }

    public function destroy($debt)
    {
        Debts::where('id', request('id'))->delete();

        return response()->noContent();
    }

    public function update(Debts $debt)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $debt->id,
        //         'password' => 'sometimes|min:8',
        //     ]);


        $debt->update([
            'user_id' => request('user_id'),
            'debt' => request('debt'),
            'remainder' => request('remainder'),
            'title' => request('title'),
            'status' => request('status')
        ]);

        return $debt;
    }

}
