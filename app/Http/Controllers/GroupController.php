<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Santri;
use Error;
use Exception;

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
        return Group::all();
    }

    public function santri()
    {
        $apps = Group::with('santri')->paginate(5);

        return $apps;
    }

    public function form()
    {
        try {
            $nis = request('santri');
            $nis = json_decode($nis, true);

            if ($nis) {
                $santris = Santri::whereIn('nis', $nis)->with('group')->get();
                return $santris;
            }
        } catch (Exception $e) {
            logger($e);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }


    public function show($id)
    {
        $grup = Group::where('id', '=', $id)->get();
        return $grup;
    }

    public function link()
    {
        $array = [];
        $santris = request('santri');
        foreach ($santris as $santri) {
            $data = Santri::where('nis','=',$santri)->first();
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

        $data = Group::create([
            'group_name' => request('name'),
            'group_desc' => request('desc')
        ]);
        return $data;
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

    public function santri_search(Group $dispen)
    {
        $searchQuery = request('query');


        $apps = Group::with(['santri' => function ($query) use ($searchQuery) {
            $query->where('fullname', 'like', "%{$searchQuery}%")->get();
        }])->paginate(5);

        return $apps;
    }

    public function search()
    {
        $searchQuery = request('query');

        $group = Group::where('group_name', 'like', "%{$searchQuery}%")->latest()->paginate(10);
        return response()->json($group);
    }
}
