<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\ResizeImageRequest;

use App\Models\Album;
use App\Models\ImageManipulation;

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
    public function index()
    {
        $images_data = ImageManipulation::all();

        return $images_data;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function byAlbum(Album $album)
    {

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
           'user_id' => null
       ];

        if(isset($all['album_id'])):
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
        return $imageManipulation;
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function show(ImageManipulation $imageManipulation)
    {
        return $imageManipulation;
    }

 
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ImageManipulation  $imageManipulation
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImageManipulation $imageManipulation)
    {
        $imageManipulation->delete();

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
