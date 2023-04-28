<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Dispen;
use App\Enums\PayStatus;
use App\Enums\DebtStatus;
use App\Models\Debt;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Status\PayStatusController;


class DebtController extends Controller
{
    public function index()
    {
        return Debt::query()->with('user:id,name')
            ->when(request('status'), function ($query) {
                return $query->where('status', PayStatus::from(request(('status'))));
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

        $debts = Debt::query()
            ->with('user:id,name')
            ->when($debtorName, function ($query) use ($debtorName) {
                return $query->whereHas('user', function ($q) use ($debtorName) {
                    $q->where('name', 'LIKE', "%$debtorName%");
                });
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('status', PayStatus::from($status));
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
        Debt::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Debt deleted successfully!']);
    }

    public function destroy($debt)
    {
        Debt::where('id', request('id'))->delete();

        return response()->noContent();
    }

    public function update(Debt $debt)
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
