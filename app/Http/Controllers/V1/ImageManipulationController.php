<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\ResizeImageRequest;
use App\Http\Resources\V1\ImageManipulationResoure;
use App\Models\Album;
use App\Models\ImageManipulation;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use Intervention\Image\Facades\Image;

// use  Intervention\Image\Image;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, )
    {
        return ImageManipulationResoure::collection(ImageManipulation::where('user_id', $request->user()->id)->paginate(2));
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function byAlbum(Request $request, Album $album)
    {
        if ($request->user()->id != $album->user_id):
            return abort(404, 'Un_Authorizes');
        endif;

        $where = ['album_id' => $album->id];
        return ImageManipulationResoure::collection(ImageManipulation::where($where)->paginate(2));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ResizeImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(ResizeImageRequest $request)
    {
       $all = $request->all();

       /** @var UploadedFile|string $image */
       $image = $all['image'];
       unset($all['image']);           // remove image

       $data = [
           'type' => ImageManipulation::TYPE_RESIZE,
           'data' => json_encode($all),
           'user_id' => $request->user()->id
       ];

        if(isset($all['album_id'])):
            $album = Album::find($all['album_id']);

            if ($request->user()->id != $album->user_id):
                return abort(404, 'Un_Authorizes');
            endif;
    
            $data['album_id'] = $all ['album_id'];   
        endif;

        // create random folder to save images
        $dir = 'images/'.Str::random().'/';
        $absolutePath = public_path($dir);
        File::makeDirectory($absolutePath);

        if($image instanceof UploadedFile):
            $data['name'] = $image->getClientOriginalName();     // get the original file name

            $file_name = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();

            $image->move($absolutePath, $data['name']);

        else:
            $data['name'] = pathinfo($image,PATHINFO_BASENAME);

            $file_name = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);

            copy($image, $absolutePath.$data['name']);

        endif;

        $data['path'] = $dir.$data['name'];
        $original_path = $absolutePath.$data['name'];
        
        $width = $all['w'];
        $height = $all['h'] ?? false;

        list($new_width, $new_height, $new_image) = $this->getImageWidthAndHeight($width, $height, $original_path);    // get new width and height
        $resized_file_name = $file_name.'-resized.'.$extension;
        $new_image->resize($new_width, $new_height)->save($absolutePath.$resized_file_name);

        $data['output_path'] = $dir.$resized_file_name;

        $imageManipulation = ImageManipulation::create($data);
        return new ImageManipulationResoure($imageManipulation);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, ImageManipulation $image)
    {
        if ($request->user()->id != $image->user_id):
            return abort(404, 'Un_Authorizes');
        endif;

        return new ImageManipulationResoure($image);
    }

 
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, ImageManipulation $image)
    {
        if ($request->user()->id != $image->user_id):
            return abort(404, 'Un_Authorizes');
        endif;

        $image->delete();
        return response('deleted', 204);
    }


    protected function getImageWidthAndHeight($width, $height, string $original_path)
    {
        
        $image = Image::make($original_path);
        $originalWidth = $image->width();
        $original_height = $image->height();
        
        // if orignal 100 , request with 50% , result --> 100 * 50% = 50px
        
        if (str_ends_with($width, '%')):
            $ratio_width = (float)str_replace('%', '', $width);
            $ratio_height = $height ? (float)str_replace('%', '', $height) : $ratio_width;

            $new_width = ($originalWidth * $ratio_width) / 100;
            $new_height = ($original_height * $ratio_height) / 100;
        
        else:
            $new_width = (float)$width;
            $new_height = $height ? (float)$height : ($original_height * $new_width) / $originalWidth;

        endif;

        return [$new_width, $new_height, $image];
    }
}
