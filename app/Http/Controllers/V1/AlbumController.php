<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Http\Resources\V1\AlbumResource;

use App\Models\Album;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $albums = Album::all();
        // return $albums;

        // return AlbumResource::collection(Album::all());

        return AlbumResource::collection(Album::where('user_id', $request->user()->id)->paginate(2));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAlbumRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlbumRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = $request->user()->id;
        $album = Album::create($data);

        return new AlbumResource($album);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Album $album)
    {
        if ($request->user()->id != $album->user_id):
            return abort(404, 'Un_Authorizes');
        endif;

        return new AlbumResource($album);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAlbumRequest  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        if ($request->user()->id != $album->user_id):
            return abort(404, 'Un_Authorizes');
        endif;

        $album->update($request->all());
        return new AlbumResource($album);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Album $album)
    {
        if ($request->user()->id != $album->user_id):
            return abort(404, 'Un_Authorizes');
        endif;

        $album->delete();
        return response('deleted', 204);
    }
}
