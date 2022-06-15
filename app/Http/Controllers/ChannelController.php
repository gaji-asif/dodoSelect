<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $data = Channel::all();
        $title = 'channel';
        return view('settings.channel',compact('data', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $channel = new Channel();
        $channel->name = $request->name;
        $channel->display_channel = $request->display_channel;
        if ($request->hasFile('image')) {
            $upload = $request->file('image');
            $path =  Storage::disk('s3')->put('uploads/channel_image', $upload,'public');
            $channel->image = $path;
        }

        $channel->seller_id = Auth::user()->id;
        $channel->save();

        if($channel)
        {
            return redirect()->back()->with('success','Channel has been added successfully');
        }
        else{
            return redirect()->back()->with('danger','Something wnet wrong');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $channel = Channel::find($id);
        $channel->name = $request->name;
        $channel->display_channel = $request->display_channel;
        if ($request->hasFile('image')) {
            if(Storage::disk('s3')->exists($channel->image)) {
                Storage::disk('s3')->delete($channel->image);
            }
            $upload = $request->file('image');
            $path =  Storage::disk('s3')->put('uploads/channel_image', $upload,'public');
            $channel->image = $path;
        }
        $channel->seller_id = Auth::user()->id;
        $channel->update();

        if($channel)
        {
            return redirect()->back()->with('success','Channel has been updated successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $channel = Channel::where('id',$id)->where('seller_id',Auth::user()->id)->first();
        if(file_exists($channel->image)){
            unlink($channel->image);
        }
        $channel->delete();
        if($channel){
            return redirect()->back()->with('success','Channel has been deleted successfully');
        }
        else{
            return redirect()->back()->with('danger','Something went wrong');
        }
    }
}
