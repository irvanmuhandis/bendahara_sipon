<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    public function index()
    {
        return Group::latest()
            ->paginate(3)->through(fn ($app) => [
                'id' => $app->id,
                'name' => $app->name,
                'desc' => $app->desc,
                'created_at' => $app->created_at->format('Y-m-d h:i A'),
                'updated_at' => $app->updated_at->format('Y-m-d h:i A'),
            ]);
    }

    public function store()
    {
        // request()->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:dispens,email',
        //     'password' => 'required|min:8',
        // ]);
        $user = User::where('id', '=', request('userId'))->first();
        $dispen = Group::create([
            'user_name' => $user->name,
            'user_id' => request('userId'),
            'periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'desc' => request('desc')
        ]);
        return $dispen;
    }

    public function update(Group $dispen)
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $name = User::where('id', '=', request('userId'))->first()->name;

        $dispen->update([
            'user_name' => $name,
            'user_id' => request('userId'),
            'periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'desc' => request('desc')
        ]);

        return $dispen;
    }
    public function bulkDelete()
    {
        Group::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Groups deleted successfully!']);
    }
    public function destroy(Group $dispen)
    {
        $dispen->delete();

        return response()->noContent();
    }

    public function changeRole(Group $dispen)
    {
        $dispen->update([
            'role' => request('role'),
        ]);

        return response()->json(['success' => true]);
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Group::where('name', 'like', "%{$searchQuery}%")->latest()->paginate(3);
        return response()->json($group);
    }
}
