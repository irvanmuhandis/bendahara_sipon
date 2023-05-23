<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Error;

class GroupController extends Controller
{
    public function index()
    {
        return Group::latest()
            ->paginate(10)->through(fn ($app) => [
                'id' => $app->id,
                'group_name' => $app->group_name,
                'desc' => $app->group_desc,
                'created_at' => $app->created_at->format('Y-m-d h:i A'),
                'updated_at' => $app->updated_at->format('Y-m-d h:i A'),
            ]);
    }

    public function list()
    {
        $apps = Group::all();

        $data = $apps->map(function ($app) {
            $app->created_at = $app->created_at->format('Y-m-d h:i A');
            $app->updated_at = $app->updated_at->format('Y-m-d h:i A');
            return $app;
        });
        return $data;
    }

    public function user()
    {
        $apps = Group::with('user')->paginate(5);
        return $apps;
    }

    public function show($id)
    {
        $grup = Group::where('id', '=', $id)->get();
        return $grup;
    }

    public function link()
    {
        $array = [];
        $users = request('user');
        foreach ($users as $user) {
            $data = User::find($user);
            $data->group_id = request('group');
            $data->save();
            array_push($array, $data);
        }
        return $array;
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
            'user_id' => request('userId'),
            'periode' => request('periode'),
            'pay_at' => request('pay_at'),
            'desc' => request('desc')
        ]);
        return $dispen;
    }

    public function update()
    {
        //     request()->validate([
        //         'name' => 'required',
        //         'peiod' => 'required|unique:dispens,email,' . $dispen->id,
        //         'password' => 'sometimes|min:8',
        //     ]);

        $grup = Group::where('id', '=', request('id'))->first();

        $grup->update([
            'group_name' => request('name'),
            'group_desc' => request('desc')
        ]);

        return $grup;
    }
    public function bulkDelete()
    {
        Group::whereIn('id', request('ids'))->delete();

        return response()->json(['message' => 'Groups deleted successfully!']);
    }
    public function destroy($id)
    {
        $RES = Group::where('id', '=', $id)->first()->delete();

        return $RES;
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

        $group = Group::where('group_name', 'like', "%{$searchQuery}%")->latest()->paginate(10);
        return response()->json($group);
    }
}
